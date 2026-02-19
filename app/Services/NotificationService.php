<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationRule;
use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;

/**
 * Notification Service
 * Handles notification triggers, dispatch, and email sending
 */
class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?int $ruleId = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'rule_id' => $ruleId,
            'client_id' => User::find($userId)?->client_id,
        ]);
    }

    /**
     * Send notification through all configured channels
     */
    public function dispatch(Notification $notification): void
    {
        $rule = $notification->rule;
        $channels = $rule?->channels ?? ['in_app'];

        foreach ($channels as $channel) {
            match ($channel) {
                'email' => $this->sendEmail($notification),
                'in_app' => $this->markAsSent($notification),
                default => null,
            };
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(Notification $notification): void
    {
        $user = $notification->user;
        $rule = $notification->rule;
        $template = $rule?->template;

        if (!$user || !$user->email) {
            return;
        }

        $subject = $template?->subject ?? $notification->title;
        $body = $this->renderTemplate($template?->body_html ?? $notification->message, [
            'user' => $user,
            'notification' => $notification,
        ]);

        // Log the email
        $emailLog = EmailLog::create([
            'client_id' => $notification->client_id,
            'recipient_email' => $user->email,
            'recipient_name' => $user->full_name,
            'subject' => $subject,
            'body_html' => $body,
            'email_type' => $notification->type,
            'template_id' => $template?->id,
            'notification_ids' => [$notification->id],
            'status' => 'pending',
        ]);

        try {
            // TODO: Implement actual email sending
            // Mail::to($user->email)->send(new NotificationMail($subject, $body));

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $emailLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        $this->markAsSent($notification);
    }

    /**
     * Mark notification as sent
     */
    protected function markAsSent(Notification $notification): void
    {
        $notification->update(['sent_at' => now()]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->update(['read_at' => now()]);
        return $notification;
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadNotifications(int $userId): Collection
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get notification count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Process notification rules (scheduled task)
     */
    public function processRules(int $clientId): void
    {
        $rules = NotificationRule::where('client_id', $clientId)
            ->where('disabled', false)
            ->get();

        foreach ($rules as $rule) {
            $this->processRule($rule);
        }
    }

    /**
     * Process a single notification rule
     */
    protected function processRule(NotificationRule $rule): void
    {
        // Implementation depends on trigger_type
        // e.g., 'currency_expiring', 'event_reminder', etc.
        match ($rule->trigger_type) {
            'currency_expiring' => $this->processCurrencyExpiringRule($rule),
            'event_reminder' => $this->processEventReminderRule($rule),
            default => null,
        };
    }

    /**
     * Process currency expiring notifications
     */
    protected function processCurrencyExpiringRule(NotificationRule $rule): void
    {
        $days = $rule->trigger_days ?? 30;
        
        // TODO: Get expiring currencies and create notifications
    }

    /**
     * Process event reminder notifications
     */
    protected function processEventReminderRule(NotificationRule $rule): void
    {
        $days = $rule->trigger_days ?? 1;
        
        // TODO: Get upcoming events and create notifications
    }

    /**
     * Render email template with variables
     */
    protected function renderTemplate(string $template, array $data): string
    {
        $user = $data['user'] ?? null;
        $notification = $data['notification'] ?? null;

        $replacements = [
            '{{user_name}}' => $user?->full_name ?? '',
            '{{user_first_name}}' => $user?->first_name ?? '',
            '{{user_email}}' => $user?->email ?? '',
            '{{notification_title}}' => $notification?->title ?? '',
            '{{notification_message}}' => $notification?->message ?? '',
            '{{date}}' => now()->format('Y-m-d'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }
}
