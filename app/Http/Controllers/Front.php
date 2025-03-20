<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Prize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ShareLink;

use Illuminate\Support\Str;
use Session;


class Front extends Controller
{
    // То же что и type script только все переменные не нужно заранее объявлять и они обозначаются значком доллара $ а методы вызываются через стрелочку

    public function sendTelegram($request) {
        $user = Auth::user();
        $user->telegram_id = $request->get('telegram_username');
        $user->save();
        return response()->json(['message' => 'Success']);
    }

    public function getLeaderboard()
    {
        $topPlayers = DB::table('users')
            ->select('users.wallet_address', DB::raw('COUNT(prizes.id) as wins'))
            ->leftJoin('prizes', 'users.id', '=', 'prizes.user_id')
            ->groupBy('users.wallet_address')
            ->orderByDesc('wins')
            ->limit(10)
            ->get();

        return response()->json($topPlayers, 200);
    }

    public function getConfigValueCallback(Request $request)
    {
        $key = $request->input('key');
        return response()-> json(['value' => AdminController::getConfigValue($key)]);
    }

    public function getLastPrize() {
        $user = Auth::user();
        $lastPrize = Prize::where(['user_id' => $user->id])->orderBy('id', 'desc')->first();
        return response()-> json(['lastPrize' => $lastPrize]);
    }

    public function generatePrize($user)
    {
        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        // Генерация уникального кода для приза
        do {
            $prizeCode = (string) Str::uuid(); // Генерация UUID
        } while (Prize::where('prize_code', $prizeCode)->exists()); // Проверка на уникальность

        // Создание записи в таблице призов
        $prize = Prize::create([
            'user_id' => $user->id,
            'prize_code' => $prizeCode,
            'received_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Приз успешно сгенерирован!',
            'prize' => $prize,
        ], 201);
    }

    public function getSolanaTokenBalance()
    {
        $balance = Auth::user()->solana_balance;
        return response()->json(['balance' => $balance]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $_COOKIE['token'] = null;
        return response()->json(['success' => true])
            ->withCookie(cookie()->forget('token'));
    }

    public function startGame()
    {
        $user = Auth::user();

        if ($user->attemps < 1) {
            return response()->json(['type' => 'error'], 400);
        }

        $user->attemps -= 1;

        $user->save();

        return response()->json(['type' => 'started']);
    }

    public function repost(Request $request)
    {
        $user = Auth::user();

        $user->subscribed = true;
        $user->attemps += 1;
        $user->save();
    }

    public function subscribe(Request $request)
    {
        $user = Auth::user();

        $user->subscribed = true;
        $user->attemps += 1;
        $user->save();
    }

    public function storeLink(Request $request)
    {
        $url = $request->input('url');

        if (empty($url) || ShareLink::where('link', $url)->count() || !preg_match('/^https?:\/\/(www\.)?x\.com(\/.*)?$/i', $url)) {
            return response()->json(['status' => 'error'], 400);
        }

        $referral = ShareLink::create([
            'user_id' => Auth::id(),
            'link' => $url,
        ]);

        return response()->json(['status' => 'ok', 'data' => $referral], 201);
    }

    function walletLogin(Request $request)
    {
        // Получаем IP
        $clientIp = request()->getClientIp();

        // Получаем данные запроса
        $data = $request->all();

        // Проверяем, передан ли кошелёк
        if (!isset($data['addresses'][0])) {
            return response()->json(['error' => 'Missing address'], 400);
        }

        $walletAddress = $data['addresses'][0];

        // Проверяем, есть ли пользователь в БД
        $user = DB::table('users')->where('wallet_address', $walletAddress)->first();

        // Генерируем API-токен если нужео для авторизации через API и токен
        $token = bin2hex(random_bytes(32)); // 32-байтовый случайный токен

        if (!$user) {
            // Если пользователя нет – создаём нового
            DB::table('users')->insert([
                'wallet_address' => $walletAddress,
                'name' => 'New User',
                'created_at' => now(),
                'updated_at' => now(),
                'ip' => $clientIp,
                'token' => $token
            ]);

            // Получаем только что созданного пользователя
            $user = DB::table('users')->where('wallet_address', $walletAddress)->first();
        } else {
            DB::table('users')->where('id', $user->id)->update(['token' => $token]);
        }

        // Возвращаем ответ c токеном
        // return response()->json(['message' => 'Login successful', 'token' => $token, 'user_id' => $user->id]);

        // Редиректим на залогинивание через токен
//        return redirect('/token-login?token=' . $token . '&id=' . $user->id);
        // Либо возваращаем на главную страницу c залогиниванием через laravel auth
        Auth::loginUsingId($user->id);

        $_COOKIE['token'] = $token;

        return response()->json(['token' => $token, 'check' => Auth::check(), 'user' => Auth::user()], 200);
    }

    function loginWithToken(Request $request)
    {
        // Проверяем, есть ли пользователь в БД с таким токеном
        if (DB::table('users')->where('token', $request->token)->exists()) {
            // Получаем пользователя
            $user = DB::table('users')->where('token', $request->token)->first();
            // Логиним пользователя
            Auth::loginUsingId($user->id);
            // Редиректим на главную страницу
            return redirect('/');
        } else {
            return redirect('/');
        }
    }

    public function handleWalletConnectCallback(Request $request)
    {
        $accounts = $request->input('accounts');

        // Logic to authorize or register user in Laravel
        $user = User::firstOrCreate(['wallet_address' => $accounts[0]]);
        Auth::login($user);

        return response()->json(['status' => 'success']);
    }

    function addattemps(Request $request)
    {
        $tokens = $request->input('tokens');
        $userAddress = $_POST['userAdress'];

        $user = User::where(['token' => $userAddress])->get()[0];

        $user->attemps += $tokens;
        $user->save();

        return $user;
    }

    function getUser(Request $request)
    {
        $token = $request->input('token');

        $user = User::firstOrCreate(['token' => $token]);
        Auth::login($user);
        $user->balance = $user->solana_balance;
        $user->get_attemps = $user->will_get_attemps;
        $user->time_update = $user->time_to_next_reset;

        return $user;
    }

    function home()
    {
        if (!Auth::check()) {
            return redirect('../wallet?redirect_url=https://horniverse.ai/game');
        }
        $user = Auth::user();
        DB::table('users')->where('id', $user->id)->update(['scene' => 0]);
        DB::table('users')->where('id', $user->id)->update(['magic' => '']);
        DB::table('users')->where('id', $user->id)->update(['weapons' => '']);
        DB::table('users')->where('id', $user->id)->update(['combination' => '']);
        DB::table('users')->where('id', $user->id)->update(['boss' => implode('', [rand(0, 2), rand(0, 5), rand(0, 2), rand(0, 2)])]);
        $chest = DB::table('chest')->first()->attemps;
        $wins = DB::table('chest')->where('id', 1)->first()->wins;
        $message = '';
        $canPlay =  true;
        if ($wins >= AdminController::getConfigValue('max_winners_count')) {
            $message = 'For today, all griffons have been defeated. Come back tomorrow to try again!';
            $canPlay =  false;
        }

        $now = Carbon::now();
        $yesterday = $now->subDay();
        $timer = $user->time_to_next_reset;
        $prizeCount = Prize::where('user_id', $user->id)
            ->where('received_at', '>=', $yesterday)
            ->count();

        if ($prizeCount >= AdminController::getConfigValue('max_prize_count')) {
            $message = 'You\'ve already won '.$prizeCount.' times.'.PHP_EOL.PHP_EOL.'All the griffons run away at the sight of you. Come back tomorrow to try again!';
            $canPlay = false;
        }

        return view('front.home', compact('chest', 'wins', 'canPlay', 'message', 'timer'));
    }

    function chatGPT()
    {
        $boss = DB::table('chest')->first()->boss;
        $bossStates = substr($boss, 0, 3);
        $bossStatesArr = array(
            array(
                'Потерял броню',
                'status',
                'sd',
            ),
            array(
                'Потерял броню',
                'status',
                'sd',
            ),
            array(
                'Потерял броню',
                'status',
                'sd',
            ),
        );

        // dd('OK');
        $search = "
                Создай описание места преступления в атакованном грифонами царстве единорогов, где похитили детей единорожков и сундук с драгоценностями. Описание должно содержать:
                1. Подсказку которая указыала бы что босс " . $bossStatesArr[0][$bossStates[0]] . " во время битвы.
                2. Подсказку которая указыала бы что босс " . $bossStatesArr[1][$bossStates[1]] . " во время битвы.
                3. Подсказку которая указыала бы что босс " . $bossStatesArr[2][$bossStates[2]] . " во время битвы.
                Каждая подсказка должна быть очевидной, но не прямолинейной. Добавь атмосферные детали.
                Не более 300 символов. На английском языке.
        ";
        dd($search);
        $data = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])
            ->post("https://api.openai.com/v1/chat/completions", [
                "model" => "gpt-4",
                'messages' => [
                    [
                        "role" => "user",
                        "content" => $search
                    ]
                ],
                "temperature" => 0.7,
                "max_tokens" => 500,
                "top_p" => 1.0,
                "frequency_penalty" => 0.5,
                "presence_penalty" => 0.8,
                // "stop" => ["11."],
            ]);
        dd($data['choices'][0]['message']['content']);
        $message = " Here is you can map key status true in array of object.
            array = array.map(item => {
                return {
                  ...item,
                  status: true
                };
              });";
        $message = $data['choices'][0]['message'];
        return response()->json($message, 200, array(), JSON_PRETTY_PRINT);
    }

    function choice()
    {
        if (request('choice') == 'Начать заново') {
            return response()->json([
                'scene' => 0,
            ], 200);
        };
        $user = Auth::user();
        $scene = $user->scene;
        $help = '';
        $boss = $user->boss;
        if ($scene == 2) {
            $boss = implode('', [rand(0, 2), rand(0, 5), rand(0, 2), rand(0, 2)]);
            DB::table('users')->where('id', $user->id)->update(['magic' => '']);
            DB::table('users')->where('id', $user->id)->update(['weapons' => '']);
            DB::table('users')->where('id', $user->id)->update(['combination' => '']);
            DB::table('users')->where('id', $user->id)->update(['boss' => $boss]);

            $bossStates = substr($boss, 0, 3);
            $bossStatesArr = array(
                array(
                    'Потерял броню',
                    'Был средне бронирован',
                    'Был тяжело бронирован',
                ),
                array(
                    'Остался сухим',
                    'намок',
                    'вляпался в масло',
                    'в порезах',
                    'боялся света',
                    'боялся тьмы'
                ),
                array(
                    'Не был ранен',
                    'Был ранен в голову',
                    'Был ранен в туловище',
                ),
            );
            $search = "
                Создай описание места преступления в атакованном грифонами царстве единорогов, где похитили детей единорожков и сундук с драгоценностями. Описание должно содержать:
                1. Подсказку которая указыала бы что босс " . $bossStatesArr[0][$bossStates[0]] . " во время битвы.
                2. Подсказку которая указыала бы что босс " . $bossStatesArr[1][$bossStates[1]] . " во время битвы.
                3. Подсказку которая указыала бы что босс " . $bossStatesArr[2][$bossStates[2]] . " во время битвы.
                Каждая подсказка должна быть очевидной, но не прямолинейной. Добавь атмосферные детали.
                Не более 300 символов. На английском языке.
            ";
            $dataHelp = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ])
                ->post("https://api.openai.com/v1/chat/completions", [
                    "model" => "gpt-4",
                    'messages' => [
                        [
                            "role" => "user",
                            "content" => $search
                        ]
                    ],
                    "temperature" => 0.7,
                    "max_tokens" => 500,
                    "top_p" => 1.0,
                    "frequency_penalty" => 0.5,
                    "presence_penalty" => 0.8,
                    // "stop" => ["11."],
                ]);
            logger()->info($dataHelp);
            $help = $dataHelp['choices'][0]['message']['content'];
        }
        $texts = array(
            array(
                'Are you ready to help her bring peace back to Maemlandia and defeat the griffons?',
            ),
            array(
                'Horniverse is in danger. The griffons took my children, took the Treasury and destroyed our world. We need to work together, and only you can help us. First, we need to find traces of the griffons. They could not have disappeared without a trace. Your task is to explore the territory and find their location. I will help you with clues, but you must decide how to act. I am waiting for your help!',
            ),
            array(
                'We know that griffons leave behind them a trail of fiery magic and destruction.',
                'You can use your skills to watch for these signs or perhaps gather information from locals.',
                'We need to look for vulnurabilities in the griffons.',
            ),
            array(
                $help
            ),
            array(
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
            ),
            array(
                'Choose your secondary weapon',
                'Choose your secondary weapon',
                'Choose your secondary weapon',
                'Choose your secondary weapon',
                'Choose your secondary weapon',
                'Choose your secondary weapon',
            ),
            array(
                'Choose your magic',
                'Choose your magic',
                'Choose your magic',
                'Choose your magic',
                'Choose your magic',
                'Choose your magic',
            ),
            array(
                'Choose your secondary magic',
                'Choose your secondary magic',
                'Choose your secondary magic',
                'Choose your secondary magic',
                'Choose your secondary magic',
                'Choose your secondary magic',
            ),
            array(
                'Choose your weapon to Attack',
                'Choose your weapon to Attack',
                'Choose your weapon to Attack',
                'Choose your weapon to Attack',
                'Choose your weapon to Attack',
                'Choose your weapon to Attack',
            ),
            array(
                'Choose your magic to Attack',
                'Choose your magic to Attack',
                'Choose your magic to Attack',
                'Choose your magic to Attack',
                'Choose your magic to Attack',
                'Choose your magic to Attack',
            ),
            array(
                'Choose your style to Attack',
                'Choose your style to Attack',
                'Choose your style to Attack',
                'Choose your style to Attack',
                'Choose your style to Attack',
                'Choose your style to Attack',
            ),
            array(
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
                'Choose your weapon',
            ),
            // array(
            //     'Плюс магией',
            //     'Плюс магией',
            //     'Плюс магией',
            // ),
            // array(
            //     'Типом',
            //     'Типом',
            //     'Типом',
            // ),
            array(
                'You won!',
                'You won!',
                'You won!',
            ),
            array(
                'You died!',
                'You died!',
                'You died!',
            )
        );

        $weapons = array('0', '0');
        if (strlen($user->weapons) > 1) {
            $weapons[0] = $user->weapons[0];
            $weapons[1] = $user->weapons[1];
        }
        $magic = array('0', '0');
        if (strlen($user->magic) > 1) {
            $magic[0] = $user->magic[0];
            $magic[1] = $user->magic[1];
        }
        $choiсes = array(
            array(
                "Yes, I'm ready!",
                "I'll try... I'm afraid of griffons!"
            ),
            array(
                'How do I start searching?',
                'What options do I have?',
                'What should I do?'
            ),
            array(
                'Look for vulnerabilities'
            ),
            array(
                'Go to the weapons room',
            ),
            array(
                'Sword',
                'Axe',
                'Halberd'
            ),
            array(
                'Sword',
                'Axe',
                'Halberd'
            ),
            array(
                'Physical',
                'Electricity',
                'Fire',
                'Poison',
                'Light',
                'Dark'
            ),
            array(
                'Physical',
                'Electricity',
                'Fire',
                'Poison',
                'Light',
                'Dark'
            ),
            array(
                $weapons[0],
                $weapons[1]
            ),
            array(
                $magic[0],
                $magic[1]
            ),
            array(
                '0',
                '1',
                '2'
            ),
            array(
                $weapons[0],
                $weapons[1]
            ),
            // array(
            //     $magic[0],
            //     $magic[1]
            // ),
            // array(
            //     '0',
            //     '1',
            //     '2'
            // ),
            array(
                'Взять сундук'
            ),
            array(
                'Начать заново'
            )
        );
        // Slava >
        $background = array(
            'url("/game/images/game/environment/ruins/Bg.png")|url(/game/images/game/environment/ruins/Layer-0.png)|url(/game/images/game/environment/ruins/Layer-1.png)|url(/game/images/game/environment/ruins/Layer-2.png)|url(/game/images/game/environment/ruins/Layer-3.png)|url(/game/images/game/environment/ruins/Layer-4.png)',
            'url("/game/images/game/environment/ruins/Bg.png")|url(/game/images/game/environment/ruins/Layer-0.png)|url(/game/images/game/environment/ruins/Layer-1.png)|url(/game/images/game/environment/ruins/Layer-2.png)|url(/game/images/game/environment/ruins/Layer-3.png)|url(/game/images/game/environment/ruins/Layer-4.png)',
            'url("/game/images/game/environment/ruins/Bg.png")|url(/game/images/game/environment/ruins/Layer-0.png)|url(/game/images/game/environment/ruins/Layer-1.png)|url(/game/images/game/environment/ruins/Layer-2.png)|url(/game/images/game/environment/ruins/Layer-3.png)|url(/game/images/game/environment/ruins/Layer-4.png)',
            'url("/game/images/game/environment/ruins/Bg.png")|url(/game/images/game/environment/ruins/Layer-0.png)|url(/game/images/game/environment/ruins/Layer-1.png)|url(/game/images/game/environment/ruins/Layer-2.png)|url(/game/images/game/environment/ruins/Layer-3.png)|url(/game/images/game/environment/ruins/Layer-4.png)',
            'url("/game/images/game/environment/weaponry/Bg.png")|url(/game/images/game/environment/weaponry/Layer-0.png)|url(/game/images/game/environment/weaponry/Layer-1.png)|url(/game/images/game/environment/weaponry/Layer-2.png)|url(/game/images/game/environment/weaponry/Layer-3.png)|url(/game/images/game/environment/weaponry/Layer-4.gif)',
            'url("/game/images/game/environment/weaponry/Bg.png")|url(/game/images/game/environment/weaponry/Layer-0.png)|url(/game/images/game/environment/weaponry/Layer-1.png)|url(/game/images/game/environment/weaponry/Layer-2.png)|url(/game/images/game/environment/weaponry/Layer-3.png)|url(/game/images/game/environment/weaponry/Layer-4.gif)',
            'url("/game/images/game/environment/weaponry/Bg.png")|url(/game/images/game/environment/weaponry/Layer-0.png)|url(/game/images/game/environment/weaponry/Layer-1.png)|url(/game/images/game/environment/weaponry/Layer-2.png)|url(/game/images/game/environment/weaponry/Layer-3.png)|url(/game/images/game/environment/weaponry/Layer-4.gif)',
            'url("/game/images/game/environment/weaponry/Bg.png")|url(/game/images/game/environment/weaponry/Layer-0.png)|url(/game/images/game/environment/weaponry/Layer-1.png)|url(/game/images/game/environment/weaponry/Layer-2.png)|url(/game/images/game/environment/weaponry/Layer-3.png)|url(/game/images/game/environment/weaponry/Layer-4.gif)',
            'url("/game/images/game/environment/end/Bg.png")|url("/game/images/game/environment/end/Layer-0.gif")|none|none|none|none',
            'url("/game/images/game/environment/end/Bg.png")|url("/game/images/game/environment/end/Layer-0.gif")one|none|none|none|none',
            'url("/game/images/game/environment/end/Bg.png")|url("/game/images/game/environment/end/Layer-0.gif")|none|none|none|none',
            'url("/game/images/game/environment/end/Bg.png")|url("/game/images/game/environment/end/Layer-0.gif")|none|none|none|none',
            'url("/game/images/game/environment/end/Bg.png")|url("/game/images/game/environment/end/Layer-0.gif")|none|none|none|none',
            'url("/game/images/game/environment/end/Bg.png")|url("/game/images/game/environment/end/Layer-0.gif")|none|none|none|none',
            // 'url("/images/game/environment/end/Bg.png")',
            // 'url("/images/game/environment/end/Bg.png")',
        );
        // Slava <

        if ($user->scene == 4 || $user->scene == 5) {
            $weapons = $user->weapons;
            DB::table('users')->where('id', $user->id)->update(['weapons' => $weapons . request('choice')]);
        }
        if ($user->scene == 6 || $user->scene == 7) {
            $magic = $user->magic;
            DB::table('users')->where('id', $user->id)->update(['magic' => $magic . request('choice')]);
        }
        $combination = '';
        if ($user->scene > 7) {
            $combination = $user->combination;
            $combination = $combination . request('choice');
            DB::table('users')->where('id', $user->id)->update(['combination' => $combination]);
        }

        $scene++;
        DB::table('users')->where('id', $user->id)->update(['scene' => $scene]);

        $acces = true;
        if ($scene == 11) {
            DB::table('users')->where('id', $user->id)->update(['log' => substr($boss, 0, 3) . '/' . $combination]);
            if (substr($boss, 0, 3) == $combination) {
                $acces = true;
            } else {
                $acces = false;
            }
        }
        if ($scene == 12) {
            $wins = DB::table('chest')->where('id', 1)->first()->wins;
            if ($boss == $combination && $wins < AdminController::getConfigValue('max_winners_count')) {
                $acces = true;
                DB::table('users')->where('id', $user->id)->update(['win' => now()]);
                DB::table('chest')->where('id', 1)->increment('wins');

//                DB::table('users')->where('id', $user->id)->update(['scene' => 0]);
//                DB::table('users')->where('id', $user->id)->update(['magic' => '']);
//                DB::table('users')->where('id', $user->id)->update(['weapons' => '']);
//                DB::table('users')->where('id', $user->id)->update(['combination' => '']);
//                DB::table('users')->where('id', $user->id)->update(['boss' => implode('', [rand(0, 2), rand(0, 5), rand(0, 2), rand(0, 2)])]);

                $this->generatePrize($user);
                // $scene = 0;
            } else {
                $acces = false;
            }
        }

        if ($acces != true) {
//            DB::table('users')->where('id', $user->id)->update(['scene' => 0]);
//            DB::table('users')->where('id', $user->id)->update(['magic' => '']);
//            DB::table('users')->where('id', $user->id)->update(['weapons' => '']);
//            DB::table('users')->where('id', $user->id)->update(['combination' => '']);
//            DB::table('users')->where('id', $user->id)->update(['boss' => implode('', [rand(0, 2), rand(0, 5), rand(0, 2), rand(0, 2)])]);
            $scene = 13;
        }
        return response()->json([
            'scene' => $scene,
            'choice' => request('choice'),
            'weapons' => $user->weapons,
            'magic' => $user->magic,
            'attemps' => $user->attemps,
            'chest' => DB::table('chest')->first()->attemps,

            'text' => $texts[$scene][request('choice')],
            'choiсes' => $choiсes[$scene],
            'background' => $background[$scene],
            'combination' => $combination,

            'success' => true
        ], 200);
    }

    public function validateUser()
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), TRUE);
        if ($data) {
            $data_check_string = $data[0];
            $hash = $data[1];

            $secret_key = hash('sha256', '7599312407:AAFrKI5akY0HgoPyHcUZ8IlQ_BEJ2iidQ5I', TRUE);
            $check_hash = hash_hmac('sha256', $data_check_string, $secret_key);

            if (hash_equals($check_hash, $hash)) {
                preg_match('/id=(\d+)/', $data_check_string, $matches);

                $user_id = $matches[1] ?? null;

                if (DB::table('users')->where('telegram_id', $user_id)->first()) {
                    Auth::loginUsingId(DB::table('users')->where('telegram_id', $user_id)->value('id'));
                } else {
                    DB::table('users')->insert(['telegram_id' => $user_id, 'attemps' => 1000, 'scene' => 0]);
                    Auth::loginUsingId(DB::table('users')->where('telegram_id', $user_id)->value('id'));
                }
                echo json_encode(['success' => TRUE, 'data_check_string' => $check_hash, 'matches' => $hash, 'user_id' => $user_id]);
            } else {
                echo json_encode(['success' => FALSE, 'data_check_string' => $check_hash, 'matches' => $hash]);
            }
        }
    }

    function game()
    {
        return view('front.game');
    }
}

