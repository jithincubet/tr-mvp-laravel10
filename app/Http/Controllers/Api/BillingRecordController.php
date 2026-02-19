<?php

namespace App\Http\Controllers\Api;

use App\Models\BillingRecord;
use App\Models\InvoiceDetail;
use App\Models\InvoiceHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Billing Record Controller
 * Manages individual billing records linking users to courses
 */
class BillingRecordController extends BaseController
{
    /**
     * GET /api/v1/billing/billing-records
     * Supports filters: client_id, unbilled, invoice_id, invoice_body_ids, include
     */
    public function index(Request $request): JsonResponse
    {
        $query = BillingRecord::query();

        // Filter by client
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Only unbilled records
        if ($request->boolean('unbilled')) {
            $query->unbilled();
        }

        // Filter by invoice
        if ($request->has('invoice_id')) {
            $query->whereHas('invoiceDetail', function ($q) use ($request) {
                $q->where('invoice_header_id', $request->invoice_id);
            });
        }

        // Filter by invoice body IDs with full details
        if ($request->has('invoice_body_ids') && $request->input('include') === 'details') {
            $bodyIds = explode(',', $request->input('invoice_body_ids'));
            $query->whereIn('invoice_body_id', $bodyIds);

            $records = $query->get()->map(function ($record) {
                $record->user_name = $record->user?->name;
                $record->user_email = $record->user?->email;
                $record->course_name = $record->course?->name;
                return $record;
            });

            return $this->success($records);
        }

        // Include user and client names
        if ($request->input('include') === 'users') {
            $records = $query->get()->map(function ($record) {
                $record->user_name = $record->user?->name;
                $record->client_name = $record->client?->name;
                return $record;
            });

            return $this->success($records);
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/billing/billing-records
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:tr2_clients,id',
            'user_id' => 'required|integer|exists:b_client_users,id',
            'course_id' => 'required|integer|exists:b_courses,id',
            'price' => 'required|numeric|min:0',
        ]);

        $record = BillingRecord::create($validated);

        return $this->success($record, 'Billing record created successfully', 201);
    }

    /**
     * PATCH /api/v1/billing/billing-records/{id}
     * Update the price on a billing record
     */
    public function updatePrice(Request $request, BillingRecord $billingRecord): JsonResponse
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $billingRecord->update($validated);

        return $this->success($billingRecord, 'Price updated successfully');
    }

    /**
     * DELETE /api/v1/billing/billing-records/{id}
     * Hard-delete a billing record
     */
    public function destroy(BillingRecord $billingRecord): JsonResponse
    {
        $billingRecord->forceDelete();

        return $this->success(null, 'Billing record deleted successfully');
    }

    /**
     * PATCH /api/v1/billing/billing-records/{id}/soft-delete
     * Soft-delete a billing record
     */
    public function softDelete(BillingRecord $billingRecord): JsonResponse
    {
        $billingRecord->delete();

        return $this->success(null, 'Billing record soft-deleted');
    }

    /**
     * POST /api/v1/billing/billing-records/recalculate
     * Recalculate totals for an invoice line item and its parent header
     */
    public function recalculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_body_id' => 'required|integer|exists:inv_details,id',
            'invoice_header_id' => 'required|integer|exists:inv_header,id',
        ]);

        $detail = InvoiceDetail::findOrFail($validated['invoice_body_id']);
        $header = InvoiceHeader::findOrFail($validated['invoice_header_id']);

        // Recalculate line item from its billing records
        $records = BillingRecord::where('invoice_body_id', $detail->id)->get();
        $lineTotal = $records->sum('price');
        $lineQuantity = $records->count();

        $detail->update([
            'price' => $lineQuantity > 0 ? $lineTotal / $lineQuantity : 0,
            'quantity' => $lineQuantity,
        ]);

        // Recalculate header totals from all line items
        $allDetails = $header->details;
        $netTotal = $allDetails->sum(fn($d) => $d->price * $d->quantity);
        $vatAmount = $netTotal * ($header->vat_rate / 100);
        $total = $netTotal + $vatAmount + ($header->rounding ?? 0);

        $header->update([
            'net_total' => $netTotal,
            'vat_amount' => $vatAmount,
            'total' => $total,
        ]);

        return $this->success([
            'netTotal' => $netTotal,
            'vatAmount' => $vatAmount,
            'total' => $total,
            'lineTotal' => $lineTotal,
            'lineQuantity' => $lineQuantity,
        ]);
    }
}