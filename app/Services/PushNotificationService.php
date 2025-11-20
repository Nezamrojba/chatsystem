<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class PushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Initialize Firebase Messaging
            // Check if Firebase is configured
            $defaultProject = config('firebase.default', 'app');
            $projectConfig = config("firebase.projects.{$defaultProject}");
            
            if (!$projectConfig) {
                Log::warning('Firebase not configured. Push notifications will be disabled.');
                $this->messaging = null;
                return;
            }

            $credentials = $projectConfig['credentials'] ?? null;
            
            if (!$credentials) {
                Log::warning('Firebase credentials not configured. Push notifications will be disabled.');
                $this->messaging = null;
                return;
            }

            $factory = new Factory();
            
            // Use service account JSON file if provided
            if ($credentials && file_exists($credentials)) {
                $factory->withServiceAccount($credentials);
            } elseif ($credentials) {
                // Try to use credentials as-is (might be JSON string or path)
                $factory->withServiceAccount($credentials);
            }

            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase Messaging: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Send push notification to user
     */
    public function sendToUser(User $user, Message $message, string $title = null, string $body = null): bool
    {
        if (!$this->messaging || !$user->fcm_token) {
            return false;
        }

        try {
            // Ensure message user is loaded
            if (!$message->relationLoaded('user')) {
                $message->load('user');
            }
            
            // Default title and body
            $title = $title ?? $message->user->name ?? 'New message';
            
            if (!$body) {
                if ($message->type === 'text' && $message->body) {
                    $body = $message->body;
                } elseif ($message->isVoiceNote()) {
                    $body = '🎤 Voice message';
                } else {
                    $body = 'New message';
                }
            }

            // Truncate long messages
            if (strlen($body) > 100) {
                $body = substr($body, 0, 100) . '...';
            }

            $notification = Notification::create($title, $body);

            $messageData = [
                'type' => 'message',
                'conversation_id' => (string) $message->conversation_id,
                'message_id' => (string) $message->id,
                'user_id' => (string) $message->user_id,
            ];

            $cloudMessage = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification($notification)
                ->withData($messageData)
                ->withDefaultSounds()
                ->withHighPriority();

            $this->messaging->send($cloudMessage);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send push notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            // If token is invalid, clear it
            if (str_contains($e->getMessage(), 'Invalid') || str_contains($e->getMessage(), 'not found')) {
                $user->update(['fcm_token' => null]);
            }

            return false;
        }
    }

    /**
     * Send push notification to multiple users
     */
    public function sendToUsers(array $users, Message $message, string $title = null, string $body = null): int
    {
        $sent = 0;
        foreach ($users as $user) {
            if ($this->sendToUser($user, $message, $title, $body)) {
                $sent++;
            }
        }
        return $sent;
    }
}

