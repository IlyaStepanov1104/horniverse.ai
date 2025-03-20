<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Exception\NotFoundException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'subscribed'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getSolanaBalanceAttribute()
    {
        $rpcUrl = "https://api.mainnet-beta.solana.com";
        $walletAddress = $this->wallet_address; // Берем из текущего пользователя

        if (!$walletAddress) {
            return "Ошибка: не указан адрес кошелька";
        }

        $postData = [
            "jsonrpc" => "2.0",
            "id" => 1,
            "method" => "getTokenAccountsByOwner",
            "params" => [
                $walletAddress,
                ["programId" => "TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA"],
                ["encoding" => "jsonParsed"]
            ]
        ];

        $response = Http::post($rpcUrl, $postData);

        if ($response->failed()) {
            return 0;
        }

        $data = $response->json();

        if (!isset($data["result"]["value"])) {
            return 0;
        }

        return $data["result"]["value"][0]["account"]["data"]["parsed"]["info"]["tokenAmount"]["uiAmount"];
    }

}
