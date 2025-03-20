<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Front;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});
Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/admin', [AdminAuthController::class, 'redirectToAdmin']);
Route::get('/get-config', [Front::class, 'getConfigValueCallback']);
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['admin'])->group(function () {
    Route::get('/admin/config', [AdminController::class, 'showConfigForm'])->name('admin.config.form');
    Route::post('/admin/config', [AdminController::class, 'updateConfig'])->name('admin.config.update');
});


Route::get('/', [Front::class, 'home']);
Route::any('/validate-user', [Front::class, 'validateUser']);
Route::any('/choice', [Front::class, 'choice']);
Route::get('/chat-gpt', [Front::class, 'chatGPT']);
Route::get('/game', [Front::class, 'game']);

Route::post('/wallet-login', [Front::class, 'walletLogin']);
Route::get('/logout', [Front::class, 'logout']);
Route::get('/user', [Front::class, 'getUser']);
Route::any('/token-login', [Front::class, 'walletLogin']);
Route::post('/store-link', [Front::class, 'storeLink']);
Route::post('/user/add_coins', [Front::class, 'addCoins']);
Route::post('/subscribe', [Front::class, 'subscribe']);
Route::post('/repost', [Front::class, 'storeLink']);
Route::post('/start', [Front::class, 'startGame']);
Route::get('/last-prize', [Front::class, 'getLastPrize']);

Route::get('/solana-balance', [Front::class, 'getSolanaTokenBalance']);

Route::get('/walletconnect-login', [Front::class, 'login']);
Route::post('/login/walletconnect/callback', 'AuthController@handleWalletConnectCallback');

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('backup:clean');
    return "Кэш очищен.";
});
Route::get('/optimize', function () {
    Artisan::call('artisan:optimize');
    return "Route";
});
require __DIR__ . '/auth.php';
