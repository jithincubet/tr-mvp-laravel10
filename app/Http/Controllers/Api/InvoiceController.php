<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\BillingRecord;
use App\Models\ClientProduct;
use App\Models\InvoiceDetail;
use App\Models\InvoiceHeader;
use App\Models\InvoiceSetting;
use App\Models\TierBracket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Invoice Controller
 * Manages invoice generation, status, PDF storage, and email distribution
 */
class InvoiceController extends BaseController
{
    /**
     * GET /api/v1/billing/invoices
     */
    public function index(): JsonResponse
    {
        $invoices = InvoiceHeader::with('client:id,name')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($invoices);
    }

    /**
     * GET /api/v1/billing/invoices/{id}/line-items
     */
    public function lineItems(InvoiceHeader $invoice): JsonResponse
    {
        return $this->success($invoice->details);
    }

    /**
     * PATCH /api/v1/billing/invoices/{id}
     * Update invoice status
     */
    public function updateStatus(Request $request, InvoiceHeader $invoice): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,distributed,paid,partly-paid,deleted',
        ]);

        $invoice->update([
            'status' => $validated['status'],
            'status_date' => now()->toDateString(),
        ]);

        return $this->success($invoice, 'Invoice status updated');
    }

    /**
     * PATCH /api/v1/billing/invoices/batch
     * Batch update status for multiple invoices
     */
    public function batchStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:inv_header,id',
            'status' => 'required|string|in:pending,approved,distributed,paid,partly-paid,deleted',
        ]);

        InvoiceHeader::whereIn('id', $validated['ids'])->update([
            'status' => $validated['status'],
            'status_date' => now()->toDateString(),
        ]);

        $count = count($validated['ids']);

        return $this->success(null, "{$count} invoices updated to {$validated['status']}");
    }

    /**
     * PATCH /api/v1/billing/invoices/{id}/rounding
     * Set rounding adjustment
     */
    public function updateRounding(Request $request, InvoiceHeader $invoice): JsonResponse
    {
        $validated = $request->validate([
            'rounding' => 'required|numeric',
        ]);

        $total = $invoice->net_total + $invoice->vat_amount + $validated['rounding'];

        $invoice->update([
            'rounding' => $validated['rounding'],
            'total' => $total,
        ]);

        return $this->success($invoice, 'Rounding updated');
    }

    /**
     * DELETE /api/v1/billing/invoices/{id}
     */
    public function destroy(InvoiceHeader $invoice): JsonResponse
    {
        // Unlink billing records from this invoice's line items
        BillingRecord::whereIn(
            'invoice_body_id',
            $invoice->details()->pluck('id')
        )->update(['invoice_body_id' => null]);

        $invoice->details()->delete();
        $invoice->delete();

        return $this->success(null, 'Invoice deleted successfully');
    }

    /**
     * DELETE /api/v1/billing/invoices/batch
     * Batch delete multiple invoices
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:inv_header,id',
        ]);

        $invoices = InvoiceHeader::whereIn('id', $validated['ids'])->get();

        foreach ($invoices as $invoice) {
            BillingRecord::whereIn(
                'invoice_body_id',
                $invoice->details()->pluck('id')
            )->update(['invoice_body_id' => null]);

            $invoice->details()->delete();
            $invoice->delete();
        }

        $count = count($validated['ids']);

        return $this->success(null, "{$count} invoices deleted");
    }

    /**
     * POST /api/v1/billing/invoices
     * Generate invoices for a billing period
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date|after_or_equal:fromDate',
            'rebillRecords' => 'nullable|boolean',
            'includedClientIds' => 'nullable|array',
            'includedClientIds.*' => 'integer',
            'excludedClientIds' => 'nullable|array',
            'excludedClientIds.*' => 'integer',
        ]);

        $settings = InvoiceSetting::instance();
        $results = [];

        // Get eligible clients
        $clientQuery = Client::where('excluded_from_invoicing', false);

        if (!empty($validated['includedClientIds'])) {
            $clientQuery->whereIn('id', $validated['includedClientIds']);
        }
        if (!empty($validated['excludedClientIds'])) {
            $clientQuery->whereNotIn('id', $validated['excludedClientIds']);
        }

        $clients = $clientQuery->get();

        foreach ($clients as $client) {
            // Get client subscriptions
            $clientProducts = ClientProduct::where('client_id', $client->id)
                ->where('enabled', true)
                ->with(['product', 'tier.brackets'])
                ->get();

            if ($clientProducts->isEmpty()) {
                continue;
            }

            DB::beginTransaction();

            try {
                $invoiceNumber = $settings->next_invoice_number;
                $settings->increment('next_invoice_number');

                // Create invoice header
                $header = InvoiceHeader::create([
                    'client_id' => $client->id,
                    'invoice_number' => $invoiceNumber,
                    'currency' => $client->currency ?? 'SEK',
                    'vat_rate' => $client->vat_rate ?? $settings->vat_percentage,
                    'status' => 'pending',
                    'status_date' => now()->toDateString(),
                    'period_start' => $validated['fromDate'] ?? null,
                    'period_end' => $validated['toDate'] ?? null,
                    'our_reference' => $settings->company_reference,
                    'client_reference' => $client->your_reference,
                    'payment_terms_days' => $settings->payment_terms_days,
                    'due_date' => now()->addDays($settings->payment_terms_days ?? 30)->toDateString(),
                    'user_count' => 0,
                    'net_total' => 0,
                    'vat_amount' => 0,
                    'total' => 0,
                ]);

                $bodies = [];
                $netTotal = 0;

                foreach ($clientProducts as $cp) {
                    // Calculate pricing based on tier type
                    $userCount = BillingRecord::where('client_id', $client->id)
                        ->unbilled()
                        ->count();

                    $linePrice = $this->calculateTierPrice($cp->tier, $userCount);

                    $detail = InvoiceDetail::create([
                        'invoice_header_id' => $header->id,
                        'product_id' => $cp->product_id,
                        'tier_id' => $cp->tier_id,
                        'tier_type' => $cp->tier?->tier_type,
                        'description' => $cp->product->name . ($cp->tier ? ' - ' . $cp->tier->name : ''),
                        'price' => $userCount > 0 ? $linePrice / $userCount : 0,
                        'quantity' => $userCount,
                    ]);

                    $netTotal += $linePrice;
                    $bodies[] = $detail;
                }

                $vatAmount = $netTotal * ($header->vat_rate / 100);

                $header->update([
                    'user_count' => BillingRecord::where('client_id', $client->id)->unbilled()->count(),
                    'net_total' => $netTotal,
                    'vat_amount' => $vatAmount,
                    'total' => $netTotal + $vatAmount,
                ]);

                DB::commit();

                $results[] = [
                    'header' => $header,
                    'bodies' => $bodies,
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $this->success($results, 'Invoices generated successfully', 201);
    }

    /**
     * POST /api/v1/billing/invoices/manual
     * Create a manual invoice with custom line items
     */
    public function createManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clientId' => 'required|integer|exists:tr2_clients,id',
            'invoiceNumber' => 'required|integer',
            'invoiceDate' => 'required|date',
            'paymentTermsDays' => 'required|integer|min:0',
            'dueDate' => 'required|date',
            'currency' => 'required|string|max:3',
            'vatRate' => 'required|numeric|min:0',
            'ourReference' => 'nullable|string|max:255',
            'clientReference' => 'nullable|string|max:255',
            'lineItems' => 'required|array|min:1',
            'lineItems.*.description' => 'required|string|max:500',
            'lineItems.*.quantity' => 'required|numeric|min:0',
            'lineItems.*.unitPrice' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $header = InvoiceHeader::create([
                'client_id' => $validated['clientId'],
                'invoice_number' => $validated['invoiceNumber'],
                'currency' => $validated['currency'],
                'vat_rate' => $validated['vatRate'],
                'status' => 'pending',
                'status_date' => $validated['invoiceDate'],
                'payment_terms_days' => $validated['paymentTermsDays'],
                'due_date' => $validated['dueDate'],
                'our_reference' => $validated['ourReference'] ?? null,
                'client_reference' => $validated['clientReference'] ?? null,
                'user_count' => 0,
                'net_total' => 0,
                'vat_amount' => 0,
                'total' => 0,
            ]);

            $netTotal = 0;

            foreach ($validated['lineItems'] as $item) {
                InvoiceDetail::create([
                    'invoice_header_id' => $header->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unitPrice'],
                ]);

                $netTotal += $item['quantity'] * $item['unitPrice'];
            }

            $vatAmount = $netTotal * ($validated['vatRate'] / 100);

            $header->update([
                'net_total' => $netTotal,
                'vat_amount' => $vatAmount,
                'total' => $netTotal + $vatAmount,
            ]);

            DB::commit();

            return $this->success($header->fresh(), 'Manual invoice created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * POST /api/v1/billing/invoices/{id}/pdf
     * Store a generated PDF for an invoice
     */
    public function storePdf(Request $request, InvoiceHeader $invoice): JsonResponse
    {
        $validated = $request->validate([
            'pdf_base64' => 'required|string',
        ]);

        $pdfContent = base64_decode($validated['pdf_base64']);
        $path = "invoices/{$invoice->id}/invoice-{$invoice->invoice_number}.pdf";

        Storage::disk('local')->put($path, $pdfContent);

        $invoice->update([
            'pdf_storage_path' => $path,
            'pdf_generated_at' => now(),
        ]);

        return $this->success(null, 'PDF stored successfully');
    }

    /**
     * POST /api/v1/billing/invoices/send
     * Send invoice emails to clients
     */
    public function sendEmails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'integer|exists:inv_header,id',
        ]);

        $results = [];

        foreach ($validated['invoice_ids'] as $invoiceId) {
            $invoice = InvoiceHeader::with('client')->find($invoiceId);
            $email = $invoice->client->email_invoice ?? null;

            if (!$email) {
                $results[] = [
                    'invoice_id' => $invoiceId,
                    'status' => 'failed',
                    'error' => 'No email address configured',
                ];
                continue;
            }

            // TODO: Dispatch actual email via mail provider
            $results[] = [
                'invoice_id' => $invoiceId,
                'status' => 'sent',
                'recipient' => $email,
            ];
        }

        return $this->success(['results' => $results]);
    }

    /**
     * Calculate price based on tier and user count
     */
    private function calculateTierPrice($tier, int $userCount): float
    {
        if (!$tier || $userCount === 0) {
            return 0;
        }

        $brackets = $tier->brackets->sortBy('min_users');

        if ($tier->tier_type === 'volume') {
            foreach ($brackets as $bracket) {
                $max = $bracket->max_users ?? PHP_INT_MAX;
                if ($userCount >= $bracket->min_users && $userCount <= $max) {
                    return $bracket->price_flat_fee + ($bracket->price_unit * $userCount);
                }
            }
        }

        if ($tier->tier_type === 'graduated') {
            $total = 0;
            $remaining = $userCount;

            foreach ($brackets as $bracket) {
                if ($remaining <= 0) break;

                $max = $bracket->max_users ?? PHP_INT_MAX;
                $bracketSize = $max - $bracket->min_users + 1;
                $usersInBracket = min($remaining, $bracketSize);

                $total += $bracket->price_flat_fee + ($bracket->price_unit * $usersInBracket);
                $remaining -= $usersInBracket;
            }

            return $total;
        }

        // Standard: single bracket
        $bracket = $brackets->first();
        if ($bracket) {
            return $bracket->price_flat_fee + ($bracket->price_unit * $userCount);
        }

        return 0;
    }
}