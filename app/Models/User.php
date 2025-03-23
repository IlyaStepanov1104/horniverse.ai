<?php

namespace App\Models;

use App\Http\Controllers\AdminController;
use Carbon\Carbon;
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



    public function getTimeToNextResetAttribute()
    {
        $now = Carbon::now();
        $midnight = Carbon::tomorrow()->startOfDay(); // 00:00:00 следующего дня
        $noon = Carbon::today()->setHour(12)->setMinute(0)->setSecond(0); // Сегодня в 12:00:00

        if ($now->greaterThanOrEqualTo($noon)) {
            $noon = $noon->addDay(); // Если уже прошло 12:00, берем следующее
        }

        $timeToMidnight = $midnight->diffInSeconds($now);
        $timeToNoon = $noon->diffInSeconds($now);

        return gmdate("H:i:s", min($timeToMidnight, $timeToNoon));
    }
}
