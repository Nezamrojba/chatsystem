<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Mazen user
        $mazen = User::updateOrCreate(
            ['username' => 'Mazen'],
            [
                'name' => 'Mazen',
                'username' => 'Mazen',
                'password' => Hash::make('password'),
            ]
        );

        // Seed Maher user
        $maher = User::updateOrCreate(
            ['username' => 'Maher'],
            [
                'name' => 'Maher',
                'username' => 'Maher',
                'password' => Hash::make('password'),
            ]
        );

        // Create default conversation between Mazen and Maher for MVP
        // Check if conversation already exists
        $existingConversation = Conversation::where('type', 'private')
            ->whereHas('users', fn($q) => $q->where('users.id', $mazen->id))
            ->whereHas('users', fn($q) => $q->where('users.id', $maher->id))
            ->whereRaw('(select count(*) from conversation_user where conversation_user.conversation_id = conversations.id) = 2')
            ->first();

        if (!$existingConversation) {
            $conversation = Conversation::create([
                'title' => null, // Private conversations don't need titles
                'type' => 'private',
                'created_by' => $mazen->id,
                'last_message_at' => now(),
            ]);

            // Attach both users to the conversation
            $conversation->users()->attach([$mazen->id, $maher->id], ['joined_at' => now()]);
        }
    }
}
