<?php

namespace App\Jobs;

use App\Http\Controllers\AdminController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UpdateUserBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $rpcUrl = 'https://solana-mainnet.g.alchemy.com/v2/tF6s8G0xy2HuJivZ2kLzrrr70P3MA15_';
        $tokenMint = '6biQcSwYXPcb1DU9fNKUoem2FHHAXeFBmnnRrrdJpump';

        $response = Http::withOptions(['verify' => false])->post($rpcUrl, [
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'getTokenAccountsByOwner',
            'params'  => [
                $this->user->wallet_address,
                ['mint' => $tokenMint],
                ['encoding' => 'jsonParsed']
            ]
        ]);

        $balance = 0;
        if ($response->successful()) {
            $data = $response->json();
            try {
                $balance = $data['result']['value'][0]['account']['data']['parsed']['info']['tokenAmount']['uiAmount'] ?? 0;
            } catch (\Exception $e) {
                $balance = 0;
            }
        }

        $goldLimit = AdminController::getConfigValue('gold_attempts');
        $silverLimit = AdminController::getConfigValue('silver_attempts');
        $bronzeLimit = AdminController::getConfigValue('bronze_attempts');

        DB::table('users')->where('id', $this->user->id)->update(['reposted' => 0, 'subscribed' => 0]);

        if ($balance > $goldLimit) {
            DB::table('users')->where('id', $this->user->id)->increment('attemps', 10);
        } else if ($balance > $silverLimit) {
            DB::table('users')->where('id', $this->user->id)->increment('attemps', 5);
        } else if ($balance > $bronzeLimit) {
            DB::table('users')->where('id', $this->user->id)->increment('attemps', 3);
        } else {
            DB::table('users')->where('id', $this->user->id)->increment('attemps', 1);
        }
    }
}
