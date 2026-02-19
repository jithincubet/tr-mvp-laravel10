<?php

namespace App\Services;

use App\Models\User;
use App\Models\Currency;
use App\Models\Event;
use App\Models\EventSession;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Report Service
 * Handles report generation and data aggregation
 */
class ReportService
{
    /**
     * Generate currency status report
     */
    public function generateCurrencyStatusReport(int $clientId, array $options = []): array
    {
        $currencies = Currency::where('client_id', $clientId)
            ->where('current', true)
            ->where('disabled', false)
            ->with(['user', 'endorsement'])
            ->get();

        $summary = [
            'total' => $currencies->count(),
            'active' => $currencies->filter(fn($c) => !$c->date_expired || $c->date_expired->isFuture())->count(),
            'expired' => $currencies->filter(fn($c) => $c->date_expired && $c->date_expired->isPast())->count(),
            'expiring_30_days' => $currencies->filter(fn($c) => 
                $c->date_expired && 
                $c->date_expired->isFuture() && 
                $c->date_expired->diffInDays(now()) <= 30
            )->count(),
            'expiring_60_days' => $currencies->filter(fn($c) => 
                $c->date_expired && 
                $c->date_expired->isFuture() && 
                $c->date_expired->diffInDays(now()) <= 60
            )->count(),
            'expiring_90_days' => $currencies->filter(fn($c) => 
                $c->date_expired && 
                $c->date_expired->isFuture() && 
                $c->date_expired->diffInDays(now()) <= 90
            )->count(),
        ];

        $byEndorsement = $currencies->groupBy('endorsement_id')->map(function ($group) {
            $endorsement = $group->first()->endorsement;
            return [
                'endorsement_id' => $endorsement?->id,
                'endorsement_name' => $endorsement?->name,
                'endorsement_code' => $endorsement?->code,
                'total' => $group->count(),
                'active' => $group->filter(fn($c) => !$c->date_expired || $c->date_expired->isFuture())->count(),
                'expired' => $group->filter(fn($c) => $c->date_expired && $c->date_expired->isPast())->count(),
            ];
        })->values();

        return [
            'generated_at' => now()->toIso8601String(),
            'client_id' => $clientId,
            'summary' => $summary,
            'by_endorsement' => $byEndorsement,
            'data' => $options['include_details'] ?? false ? $currencies : null,
        ];
    }

    /**
     * Generate training activity report
     */
    public function generateTrainingActivityReport(
        int $clientId,
        Carbon $startDate,
        Carbon $endDate,
        array $options = []
    ): array {
        $events = Event::where('client_id', $clientId)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->with(['form', 'sessions', 'participants'])
            ->get();

        $sessions = EventSession::where('client_id', $clientId)
            ->whereBetween('session_date', [$startDate, $endDate])
            ->with(['user', 'event'])
            ->get();

        $summary = [
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'total_events' => $events->count(),
            'total_sessions' => $sessions->count(),
            'sessions_completed' => $sessions->where('status', 'completed')->count(),
            'sessions_approved' => $sessions->where('status', 'approved')->count(),
            'sessions_passed' => $sessions->where('pass_fail_result', 'pass')->count(),
            'sessions_failed' => $sessions->where('pass_fail_result', 'fail')->count(),
            'pass_rate' => $this->calculatePassRate($sessions),
            'unique_trainees' => $sessions->pluck('user_id')->unique()->count(),
            'unique_instructors' => $sessions->pluck('instructor_id')->filter()->unique()->count(),
        ];

        $byForm = $events->groupBy('form_id')->map(function ($group) {
            $form = $group->first()->form;
            $formSessions = $group->flatMap->sessions;
            return [
                'form_id' => $form?->id,
                'form_name' => $form?->name,
                'events_count' => $group->count(),
                'sessions_count' => $formSessions->count(),
                'pass_rate' => $this->calculatePassRate($formSessions),
            ];
        })->values();

        $byMonth = $sessions->groupBy(fn($s) => $s->session_date?->format('Y-m'))->map(function ($group, $month) {
            return [
                'month' => $month,
                'sessions' => $group->count(),
                'passed' => $group->where('pass_fail_result', 'pass')->count(),
                'failed' => $group->where('pass_fail_result', 'fail')->count(),
            ];
        })->values();

        return [
            'generated_at' => now()->toIso8601String(),
            'client_id' => $clientId,
            'summary' => $summary,
            'by_form' => $byForm,
            'by_month' => $byMonth,
        ];
    }

    /**
     * Generate instructor performance report
     */
    public function generateInstructorReport(int $clientId, Carbon $startDate, Carbon $endDate): array
    {
        $sessions = EventSession::where('client_id', $clientId)
            ->whereBetween('session_date', [$startDate, $endDate])
            ->whereNotNull('instructor_id')
            ->with(['instructor', 'event.form'])
            ->get();

        $byInstructor = $sessions->groupBy('instructor_id')->map(function ($group) {
            $instructor = $group->first()->instructor;
            return [
                'instructor_id' => $instructor?->id,
                'instructor_name' => $instructor?->full_name,
                'total_sessions' => $group->count(),
                'sessions_approved' => $group->where('status', 'approved')->count(),
                'sessions_passed' => $group->where('pass_fail_result', 'pass')->count(),
                'sessions_failed' => $group->where('pass_fail_result', 'fail')->count(),
                'pass_rate' => $this->calculatePassRate($group),
                'forms_trained' => $group->pluck('event.form.name')->filter()->unique()->values(),
            ];
        })->sortByDesc('total_sessions')->values();

        return [
            'generated_at' => now()->toIso8601String(),
            'client_id' => $clientId,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'instructors' => $byInstructor,
        ];
    }

    /**
     * Generate trainee progress report
     */
    public function generateTraineeProgressReport(int $userId): array
    {
        $user = User::with([
            'traineeProfile',
            'currencies.endorsement',
            'eventParticipations.event.form',
        ])->find($userId);

        if (!$user) {
            throw new \InvalidArgumentException("User {$userId} not found");
        }

        $sessions = EventSession::where('user_id', $userId)
            ->with(['event.form', 'grades.blockElement'])
            ->orderBy('session_date', 'desc')
            ->get();

        return [
            'generated_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
            ],
            'trainee_profile' => $user->traineeProfile,
            'currencies' => [
                'active' => $user->currencies->filter(fn($c) => 
                    $c->current && (!$c->date_expired || $c->date_expired->isFuture())
                )->count(),
                'expiring_soon' => $user->currencies->filter(fn($c) => 
                    $c->current && $c->date_expired && $c->date_expired->isFuture() && 
                    $c->date_expired->diffInDays(now()) <= 30
                )->count(),
                'expired' => $user->currencies->filter(fn($c) => 
                    $c->current && $c->date_expired && $c->date_expired->isPast()
                )->count(),
            ],
            'training_summary' => [
                'total_sessions' => $sessions->count(),
                'sessions_passed' => $sessions->where('pass_fail_result', 'pass')->count(),
                'sessions_failed' => $sessions->where('pass_fail_result', 'fail')->count(),
                'pass_rate' => $this->calculatePassRate($sessions),
            ],
            'recent_sessions' => $sessions->take(10)->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->session_date?->format('Y-m-d'),
                'form' => $s->event?->form?->name,
                'result' => $s->pass_fail_result,
                'status' => $s->status,
            ]),
        ];
    }

    /**
     * Calculate pass rate from sessions
     */
    private function calculatePassRate(Collection $sessions): ?float
    {
        $completed = $sessions->whereIn('pass_fail_result', ['pass', 'fail']);
        
        if ($completed->isEmpty()) {
            return null;
        }

        $passed = $completed->where('pass_fail_result', 'pass')->count();
        return round(($passed / $completed->count()) * 100, 1);
    }
}
