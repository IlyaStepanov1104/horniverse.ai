<?php

namespace App\Console\Commands;

use App\Models\Configuration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Jobs\UpdateUserBalance;

class UpdateUserAttempts extends Command
{
    protected $signature = 'users:update-attempts';
    protected $description = 'Update user attempts based on token balance';

    public function handle()
    {
        $batchSize = 100; // Размер пачки для обработки

        User::select('id', 'wallet_address')
            ->whereNotNull('wallet_address')
            ->where('is_admin', false)
            ->chunk($batchSize, function ($users) {
                foreach ($users as $user) {
                    UpdateUserBalance::dispatch($user);
                }
            });

        $tomorrowConfig = Configuration::where('key', 'x-tomorrow-link')->first();
        Configuration::updateOrCreate(
            ['key' => 'x-post-link'],
            ['value' => $tomorrowConfig->value]
        );


        $tomorrowConfig = Configuration::where('key', 'telegram-tomorrow-link')->first();
        Configuration::updateOrCreate(
            ['key' => 'telegram-link'],
            ['value' => $tomorrowConfig->value]
        );

        $this->info('User attempts update dispatched successfully.');
    }
}
