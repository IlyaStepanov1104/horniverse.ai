<x-layouts.admin.app>
    <body class="bg-primary">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-5 col-lg-7 col-md-8 mt-5 pt-5">
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <!-- <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
                        <strong>Holy guacamole!</strong> You should check in on some of those fields below.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div> -->

                    <div class="card o-hidden border-0 shadow-lg my-5">
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="p-5">
                                        <div class="text-center">
                                            <h1 class="h4 text-gray-900 mb-4">Добро пожаловать!</h1>
                                        </div>
                                        <form class="user" method="POST" action="{{ route('login') }}">
                                            @csrf
                                            <div class="form-group">
                                                <input type="email" name="email" class="form-control form-control-user" placeholder="Введите ваш e-mail...">
                                            </div>
                                            <div class="form-group">
                                                <input type="password" name="password" required autocomplete="current-password" class="form-control form-control-user" placeholder="Пароль">
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check form-check-inline">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" value="" name="remember" id="remember_me">
                                                            <span class="cr"><i class="cr-icon fas fa-check"></i></span>
                                                                Запомнить меня
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary shadow-sm w-100">
                                                Войти
                                            </button>                             
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</x-layouts.admin.app>
        