<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            'alice@example.test' => ['Alice', [
                'The user prefers TypeScript over JavaScript.',
                'The user uses macOS.',
            ]],
            'bob@example.test' => ['Bob', [
                'The user writes Python.',
                'The user uses Windows.',
            ]],
            'carol@example.test' => ['Carol', []],
        ];

        foreach ($accounts as $email => [$name, $memories]) {
            $user = User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => bcrypt('password')]);

            $user->memories()->delete();

            foreach ($memories as $memory) {
                $user->memories()->create(['content' => $memory, 'source' => 'seeder']);
            }
        }
    }
}
