<?php

namespace App\Services;

use App\Models\EventSession;
use App\Models\EventSessionGrade;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Session Service
 * Handles session grading, approval workflow, and currency updates
 */
class SessionService
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Submit grades for a session
     */
    public function submitGrades(EventSession $session, array $grades): EventSession
    {
        DB::transaction(function () use ($session, $grades) {
            foreach ($grades as $gradeData) {
                EventSessionGrade::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'block_element_id' => $gradeData['block_element_id'],
                    ],
                    [
                        'grade_value' => $gradeData['grade_value'],
                        'notes' => $gradeData['notes'] ?? null,
                    ]
                );
            }

            // Calculate pass/fail result
            $result = $this->calculateResult($session);
            $session->update([
                'pass_fail_result' => $result,
                'status' => 'completed',
            ]);
        });

        return $session->fresh(['grades']);
    }

    /**
     * Calculate session result based on grades
     */
    public function calculateResult(EventSession $session): string
    {
        $grades = $session->grades()->with('blockElement.grading')->get();
        
        if ($grades->isEmpty()) {
            return 'incomplete';
        }

        $hasFails = false;
        $hasIncomplete = false;

        foreach ($grades as $grade) {
            if ($grade->grade_value === null) {
                $hasIncomplete = true;
                continue;
            }

            $grading = $grade->blockElement->grading;
            if ($grading && $grading->pass_value) {
                // Check if grade meets pass criteria
                if ($this->isFailingGrade($grade->grade_value, $grading)) {
                    $hasFails = true;
                }
            }
        }

        if ($hasIncomplete) {
            return 'incomplete';
        }

        return $hasFails ? 'fail' : 'pass';
    }

    /**
     * Check if a grade value is failing
     */
    private function isFailingGrade(string $gradeValue, $grading): bool
    {
        $scale = $grading->scale;
        $passValue = $grading->pass_value;

        // Numeric comparison
        if (is_numeric($gradeValue) && is_numeric($passValue)) {
            return (float) $gradeValue < (float) $passValue;
        }

        // Letter grade comparison (simplified)
        $gradeOrder = ['F' => 0, 'D' => 1, 'C' => 2, 'B' => 3, 'A' => 4];
        if (isset($gradeOrder[strtoupper($gradeValue)]) && isset($gradeOrder[strtoupper($passValue)])) {
            return $gradeOrder[strtoupper($gradeValue)] < $gradeOrder[strtoupper($passValue)];
        }

        return false;
    }

    /**
     * Approve a session
     */
    public function approveSession(EventSession $session, int $approverUserId): EventSession
    {
        DB::transaction(function () use ($session, $approverUserId) {
            $session->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by_user_id' => $approverUserId,
                'status' => 'approved',
            ]);

            // Update currencies based on session result
            $this->currencyService->processSessionApproval($session);
        });

        return $session->fresh();
    }

    /**
     * Reject a session
     */
    public function rejectSession(EventSession $session, int $rejectorUserId, ?string $reason = null): EventSession
    {
        $session->update([
            'status' => 'rejected',
            'notes_admin' => $reason,
        ]);

        // TODO: Notify instructor and trainee

        return $session;
    }

    /**
     * Flag session for admin attention
     */
    public function flagForAdminAttention(EventSession $session, string $reason): EventSession
    {
        $session->update([
            'requires_admin_attention' => true,
            'admin_attention_reason' => $reason,
        ]);

        // TODO: Notify admins

        return $session;
    }

    /**
     * Archive a session
     */
    public function archiveSession(EventSession $session, ?string $reason = null): EventSession
    {
        $session->update([
            'archived_at' => now(),
            'archive_reason' => $reason,
        ]);

        return $session;
    }

    /**
     * Get sessions requiring approval
     */
    public function getSessionsAwaitingApproval(int $clientId): Collection
    {
        return EventSession::where('client_id', $clientId)
            ->where('status', 'completed')
            ->where('is_approved', false)
            ->whereNull('archived_at')
            ->orderBy('session_date', 'desc')
            ->get();
    }

    /**
     * Get sessions requiring admin attention
     */
    public function getSessionsRequiringAttention(int $clientId): Collection
    {
        return EventSession::where('client_id', $clientId)
            ->where('requires_admin_attention', true)
            ->whereNull('archived_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update LTC recommendation
     */
    public function updateLtcRecommendation(
        EventSession $session,
        string $recommendation,
        ?string $notes = null
    ): EventSession {
        $session->update([
            'ltc_recommendation' => $recommendation,
            'ltc_recommendation_notes' => $notes,
        ]);

        return $session;
    }
}
