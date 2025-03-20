<?php
namespace App\Http\Controllers;

use App\Models\Prize;
use Illuminate\Http\Request;
use App\Models\Configuration;

class AdminController extends Controller
{
    public function __construct()
    {
        // Добавляем middleware для проверки администратора
        $this->middleware('admin');
    }

    public function showConfigForm()
    {
        // Получаем все конфигурационные параметры
        $configurations = Configuration::all();
        $prizes = Prize::with('user')->get();
        return view('admin.config', compact('configurations', 'prizes'));
    }

    public function updateConfig(Request $request)
    {
        // Обновление конфигурационных параметров
        foreach ($request->except('_token') as $key => $value) {
            $config = Configuration::where('key', $key)->first();
            if ($config) {
                $config->value = $value;
                $config->save();
            }
        }

        return redirect()->route('admin.config.form')->with('success', 'Конфигурация обновлена!');
    }

    // Публичный метод получения значения конфигурации
    public static function getConfigValue($key): string | null
    {
        $config = Configuration::where('key', $key)->first();
        return $config ? $config->value : null;
    }
}
