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
            
            // Also check for base64 encoded JSON in environment variable (for Koyeb)
            if (!$credentials && env('FIREBASE_CREDENTIALS_JSON')) {
                $credentialsJson = base64_decode(env('FIREBASE_CREDENTIALS_JSON'));
                if ($credentialsJson) {
                    // Create temporary file for Firebase SDK
                    $tempFile = sys_get_temp_dir() . '/firebase-credentials-' . uniqid() . '.json';
                    file_put_contents($tempFile, $credentialsJson);
                    $credentials = $tempFile;
                }
            }
            
            if (!$credentials) {
                Log::warning('Firebase credentials not configured. Push notifications will be disabled.');
                $this->messaging = null;
                return;
            }

            $factory = new Factory();
            
            // Use service account JSON file if provided
            // Handle both absolute and relative paths
            $credentialsPath = $credentials;
            
            // If relative path, make it absolute from base_path
            if ($credentials && !file_exists($credentials) && !str_starts_with($credentials, '/')) {
                $credentialsPath = base_path($credentials);
            }
            
            if ($credentialsPath && file_exists($credentialsPath)) {
                $factory->withServiceAccount($credentialsPath);
                Log::info('Firebase credentials loaded', ['path' => $credentialsPath]);
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

            $result = $this->messaging->send($cloudMessage);
            
            Log::info('Push notification sent successfully', [
                'user_id' => $user->id,
                'message_id' => $message->id,
                'fcm_token' => substr($user->fcm_token, 0, 20) . '...',
                'result' => $result
            ]);

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
