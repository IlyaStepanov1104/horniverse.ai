<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/game/images/Fav.png">
    <title>Admin - Unicorn</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/game/admin/css/sb-admin-2.css">
</head>
<body>
<div class="container">
    <h1>Настройки конфигурации</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.config.update') }}" method="POST">
        @csrf
        @foreach($configurations as $config)
            <div class="form-group">
                <label for="{{ $config->key }}">{{ $config->name }}</label>
                <input type="text" class="form-control" id="{{ $config->key }}" name="{{ $config->key }}"
                       value="{{ $config->value }}">
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary">Сохранить</button>
    </form>
</div>
<hr>
<div class="container">
    <h2>Сундук</h2>
    <ul>
        <li>Количество монет - {{$chest->attemps}}</li>
        <li>Победителей - {{$chest->wins}}</li>
    </ul>
</div>
<hr>
<div class="container">
    <h2>Призовые ссылки</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Дата создания</th>
            <th>Код</th>
            <th>ID пользователя</th>
            <th>Telegram</th>
            <th>Кошелек</th>
            <th>IP</th>
        </tr>
        </thead>
        <tbody>
        @foreach($prizes as $prize)
            <tr>
                <td>{{ $prize->id }}</td>
                <td>{{ $prize->created_at }}</td>
                <td>{{ $prize->prize_code }}</td>
                <td>{{ $prize->user_id }}</td>
                <td>{{ optional($prize->user)->telegram_id }}</td>
                <td>{{ optional($prize->user)->wallet_address }}</td>
                <td>{{ optional($prize->user)->ip }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<hr>
<div class="container">
    <form action="{{ route('admin.logout') }}" method="POST" class="form-inline pos-a">
        @csrf
        <button class="btn btn-outline-danger" type="submit">Выйти</button>
    </form>
</div>
</body>
</html>
