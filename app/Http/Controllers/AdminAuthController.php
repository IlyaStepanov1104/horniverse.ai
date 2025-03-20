<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    // Показываем форму входа
    public function redirectToAdmin(Request $request) {
        return redirect('/admin/login');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/admin/config');
        }
        return view('admin.login');
    }

    // Обработка формы входа
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Пытаемся найти администратора по email
        $admin = User::where('email', $request->email)->first();

        if ($admin && \Hash::check($request->password, $admin->password)) {
            // Авторизуем администратора
            Auth::login($admin);
            return redirect()->route('admin.config.form');
        }

        // Если не совпадает email или пароль, возвращаем ошибку
        return back()->with('error', 'Неверный email или пароль');
    }

    // Метод для выхода
    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
