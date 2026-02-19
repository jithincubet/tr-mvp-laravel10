<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Endorsement;
use App\Models\EventSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Currency Service
 * Handles currency calculations, expiry, and updates
 */
class CurrencyService
{
    /**
     * Process currency updates after session approval
     */
    public function processSessionApproval(EventSession $session): void
    {
        $event = $session->event;
        $endorsementIds = $session->endorsement_ids ?? [];
        
        // Also get endorsements from event
        $eventEndorsementIds = $event->endorsements()->pluck('endorsement_id')->toArray();
        $allEndorsementIds = array_unique(array_merge($endorsementIds, $eventEndorsementIds));

        foreach ($allEndorsementIds as $endorsementId) {
            $this->updateCurrency($session, $endorsementId);
        }
    }

    /**
     * Update or create currency record for user/endorsement
     */
    public function updateCurrency(EventSession $session, int $endorsementId): Currency
    {
        $endorsement = Endorsement::with('schedule')->find($endorsementId);
        
        if (!$endorsement) {
            throw new \InvalidArgumentException("Endorsement {$endorsementId} not found");
        }

        // Deactivate previous current currencies
        Currency::where('user_id', $session->user_id)
            ->where('endorsement_id', $endorsementId)
            ->where('current', true)
            ->update(['current' => false]);

        // Calculate expiry date
        $expiryDate = null;
        if ($endorsement->schedule) {
            $expiryDate = $this->calculateExpiryDate(
                $session->session_date ?? now(),
                $endorsement->schedule
            );
        }

        // Create new currency record
        return Currency::create([
            'client_id' => $session->client_id,
            'user_id' => $session->user_id,
            'endorsement_id' => $endorsementId,
            'session_id' => $session->id,
            'date_qualified' => $session->session_date ?? now(),
            'date_expired' => $expiryDate,
            'status' => 'active',
            'current' => true,
            'passes' => $session->pass_fail_result === 'pass',
            'fails' => $session->pass_fail_result === 'fail',
            'score' => 0,
            'progress' => 100,
        ]);
    }

    /**
     * Calculate expiry date based on schedule
     */
    public function calculateExpiryDate(Carbon $qualifiedDate, $schedule): Carbon
    {
        $expireMonths = $schedule->expire_months ?? 12;
        return $qualifiedDate->copy()->addMonths($expireMonths);
    }

    /**
     * Get expiring currencies for a client
     */
    public function getExpiringCurrencies(int $clientId, int $days = 30): Collection
    {
        return Currency::where('client_id', $clientId)
            ->where('current', true)
            ->where('disabled', false)
            ->whereNotNull('date_expired')
            ->whereBetween('date_expired', [now(), now()->addDays($days)])
            ->with(['user', 'endorsement'])
            ->orderBy('date_expired')
            ->get();
    }

    /**
     * Get expired currencies for a client
     */
    public function getExpiredCurrencies(int $clientId): Collection
    {
        return Currency::where('client_id', $clientId)
            ->where('current', true)
            ->where('disabled', false)
            ->whereNotNull('date_expired')
            ->where('date_expired', '<', now())
            ->with(['user', 'endorsement'])
            ->orderBy('date_expired')
            ->get();
    }

    /**
     * Get currency status for a user
     */
    public function getUserCurrencyStatus(int $userId): array
    {
        $currencies = Currency::where('user_id', $userId)
            ->where('current', true)
            ->where('disabled', false)
            ->with('endorsement')
            ->get();

        $active = $currencies->filter(fn($c) => 
            !$c->date_expired || $c->date_expired->isFuture()
        );

        $expiringSoon = $currencies->filter(fn($c) => 
            $c->date_expired && 
            $c->date_expired->isFuture() && 
            $c->date_expired->diffInDays(now()) <= 30
        );

        $expired = $currencies->filter(fn($c) => 
            $c->date_expired && $c->date_expired->isPast()
        );

        return [
            'total' => $currencies->count(),
            'active' => $active->count(),
            'expiring_soon' => $expiringSoon->count(),
            'expired' => $expired->count(),
            'currencies' => $currencies,
        ];
    }

    /**
     * Manually extend currency expiry
     */
    public function extendExpiry(Currency $currency, Carbon $newExpiryDate, ?string $reason = null): Currency
    {
        $currency->update([
            'date_expired' => $newExpiryDate,
            'notes' => $reason 
                ? "{$currency->notes}\n\nExtended on " . now()->format('Y-m-d') . ": {$reason}" 
                : $currency->notes,
        ]);

        return $currency;
    }

    /**
     * Suspend a currency
     */
    public function suspendCurrency(Currency $currency, ?string $reason = null): Currency
    {
        $currency->update([
            'status' => 'suspended',
            'notes' => $reason 
                ? "{$currency->notes}\n\nSuspended on " . now()->format('Y-m-d') . ": {$reason}" 
                : $currency->notes,
        ]);

        return $currency;
    }

    /**
     * Reactivate a suspended currency
     */
    public function reactivateCurrency(Currency $currency): Currency
    {
        $currency->update([
            'status' => 'active',
        ]);

        return $currency;
    }

    /**
     * Get currencies by endorsement for a client
     */
    public function getCurrenciesByEndorsement(int $clientId, int $endorsementId): Collection
    {
        return Currency::where('client_id', $clientId)
            ->where('endorsement_id', $endorsementId)
            ->where('current', true)
            ->where('disabled', false)
            ->with('user')
            ->orderBy('date_expired')
            ->get();
    }
}
