<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    use HasFactory;

    protected $table = 'prizes';

    protected $fillable = [
        'user_id',
        'prize_code',
        'received_at',
    ];

    /**
     * Получить пользователя, который получил этот приз.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
