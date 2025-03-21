<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/game/images/Fav.png">
    <title>Unicorn</title>

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
    <link rel="stylesheet" href="/game/css/style.css">
</head>
<body>
<script>
    function copyCode() {
        const codeValue = $('.code-input').val();
        const tempTextarea = $('<textarea>');
        $('body').append(tempTextarea);
        tempTextarea.val(codeValue).select();
        document.execCommand('copy');
        tempTextarea.remove();
        alert('Значение скопировано в буфер обмена!');
        return false;
    }

    function sendTelegram() {
        const val = $('.tg-input').val();
        if (!val) {
            $('.tg-input').css('border-color', 'red');
            setTimeout(() => {
                $('.tg-input').css('border-color', '#08262a');
            }, 2000);
            return false;
        }
        $.ajax({
            url: '/game/send_telegram',
            method: 'POST',
            data: {telegram_username: val},
            success: () => {
                alert('Telegram username accepted. You will be contacted within 24 hours.');
            }
        });
        return false;
    }
</script>
<section id="game" style="background-image: url(/game/images/game/environment/forest/Bg.png)">
    <div class="game-container">
        @if ($canPlay)
            <div class="cutscences" style="transition: opacity 2s ease-in-out;">
                <div class="flex js-win-form win-form" style="display: none;">
                    <h3 style="color: white">Your prize code:</h3>
                    <input class="action code-input" placeholder="Loading code..." disabled/>
                    <input class="action tg-input" placeholder="Enter your telegram @username"
                           name="telegram_username"/>
                    <button class="btn action" onclick="copyCode()">Copy prize code</button>
                    <button class="btn action" onclick="sendTelegram()">Confirm</button>
                </div>
                <a href="../" class="btn action to-site" style="display: none;">Go back to
                    site</a>
                <a class="btn action start">Start the game ({{Auth::user()->attemps}} attempts)</a>
                <a class="btn action buy" href="/wallet">Get more attempts</a>
                <video id="intro" src="/game/images/game/cutscenes/Intro.mp4" style="display: none;"></video>
                <video id="win" src="/game/images/game/cutscenes/Win.mp4" style="display: none;"></video>
                <video id="loose" src="/game/images/game/cutscenes/Loose.mp4" style="display: none;"></video>
                <video id="loose-2" src="/game/images/game/cutscenes/Loose-2.mp4" style="display: none;"></video>
                <a class="btn skip" style="display: none;">Skip</a>
            </div>

            <div class="bg">
                <div class="layer-0"></div>
                <div class="layer-1"></div>
                <div class="layer-2"></div>
                <div class="layer-3"></div>
                <div class="layer-4"></div>
            </div>

            <div class="front">
                <div class="player">
                    <img id="player" src="/game/images/game/player/Static.gif">
                    <img id="weapon" src="/game/images/game/weapons/sword/Static.gif">
                    <img id="element" src="/game/images/game/elements/none/Static.gif">
                </div>
            </div>

            <div class="ui">
                <div class="chest">
                    <img src="/game/images/game/ui/ChestIcon.png">
                    <p>
                        @if (Auth::check())
                            Chest: {{ $chest }}
                        @endif
                        <img src="/game/images/game/ui/CoinAnimated.gif">
                    </p>
                </div>
                <div class="description" id="userName">
                    <div class="ai" style="display: none;">
                        <big>Loading...</big>
                        <img src="/game/images/game/ui/AiLoading.webp">
                        <small>Horniverse AI</small>
                    </div>
                    <p id="text">Are you ready to help her bring peace to Memelandia and defeat the griffons?</p>
                </div>


                <div class="bottom">
                    <div class="col" id="choices">
                        <button class="btn action" data-choice="0" onmouseup="choice('0')">Yes, I'm ready!</button>
                        <button class="btn action" data-choice="0" onmouseup="choice('0')">I'll try... I'm afraid of
                            griffons!
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div
                style="color: white; display: flex; justify-content: center; align-items: center; text-align: center; height: 100%; flex-direction: column; gap: 30px;">
                <h2 style="white-space: pre-wrap">{{$message}}</h2>
                <div>
                    <h3 style="font-size: 30px">Come back to try in:</h3>
                    <div id="timer" style="font-size: 40px; font-weight: bold; color: #EF4A82;"></div>
                </div>
                <a href="../" class="btn action">Back to site</a>
            </div>

            <script>
                $(document).ready(function () {
                    // Получаем значение таймера из PHP
                    let timerString = "{{ $timer }}"; // формат "H:i:s"

                    // Преобразуем строку в массив [час, минуты, секунды]
                    let timeArray = timerString.split(':');
                    let hours = parseInt(timeArray[0]);
                    let minutes = parseInt(timeArray[1]);
                    let seconds = parseInt(timeArray[2]);

                    // Функция для обновления времени
                    function updateTimer() {
                        if (seconds === 0) {
                            if (minutes === 0) {
                                if (hours === 0) {
                                    clearInterval(timerInterval);
                                    // Время закончилось, обновляем или что-то другое
                                    $("#timer").text("Время вышло!");
                                } else {
                                    hours--;
                                    minutes = 59;
                                    seconds = 59;
                                }
                            } else {
                                minutes--;
                                seconds = 59;
                            }
                        } else {
                            seconds--;
                        }

                        // Отображаем оставшееся время
                        let formattedTime = String(hours).padStart(2, '0') + ':' +
                            String(minutes).padStart(2, '0') + ':' +
                            String(seconds).padStart(2, '0');
                        $("#timer").text(formattedTime);
                    }

                    // Обновляем таймер каждую секунду
                    let timerInterval = setInterval(updateTimer, 1000);

                    // Изначально запускаем обновление
                    updateTimer();
                });
            </script>

        @endif
    </div>
</section>
<style>

</style>

</body>
<script>
    let interval;

    $('.start').click(function () {
        $.ajax({
            url: '/game/start',
            method: 'POST',
            success: () => {
                $('.cutscences').removeClass('hide');
                $('.skip').show();
                $('.cutscences #intro').show();
                $('.cutscences #intro')[0].play();
                $('.cutscences #win').hide();
                interval = setTimeout(() => {
                    $('.cutscences').addClass('hide');
                }, 59000);
                $(this).fadeOut(1000);
                $('.buy').fadeOut(1000);
            },
            error: () => {
                $(this).text('Error: Not enough attempts');
                $(this).prop('disabled', true);
                $('.skip').hide();
                setTimeout(() => {
                    $(this).text('Start the game ({{Auth::user()->attemps ? Auth::user()->attemps - 1 : 0}} attempts)');
                    $(this).prop('disabled', false);
                }, 3000);
            }
        });


    });

    $('.skip').click(function () {
        $(this).fadeOut(1000);
        $('.cutscences').addClass('hide');
        $('.cutscences #intro')[0].pause();
        clearInterval(interval);
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let combination, scene;

    function choice(choiceNumber) {
        console.log(combination + choiceNumber);
        if (scene == 10 || scene == 11) {
            combination = combination + choiceNumber;
            // Play animation and AJAX;

            // SLAVA >
            let state = {
                weapon: combination.charAt(0) == '0' ? 'sword' : combination.charAt(0) == '1' ? 'axe' : 'halberd',
                element: combination.charAt(1) == '0' ? 'none' : combination.charAt(1) == '1' ? 'electricity' : combination.charAt(1) == '2' ? 'fire' : combination.charAt(1) == '3' ? 'poison' : combination.charAt(1) == '4' ? 'light' : 'dark',
                attack: combination.charAt(2) == '0' ? 'Light' : combination.charAt(2) == '1' ? 'Heavy' : 'Pierce'
            };

            $('#element').show();
            $('#weapon').attr('src', `/game/images/game/weapons/${state.weapon}/${state.attack}.gif`);
            $('#element').attr('src', `/game/images/game/elements/${state.element}/${state.attack}.gif`);
            $('#player').attr('src', `/game/images/game/player/${state.attack}.gif`);

            let ajaxDelay = combination.charAt(2) == '0' ? 1100 : combination.charAt(2) == '1' ? 800 : 800;

            setTimeout(() => {
                postAJAX(choiceNumber);
                $('#weapon').attr('src', `/game/images/game/weapons/${state.weapon}/Static.gif`);
                $('#element').hide();
                $('#player').attr('src', `/game/images/game/player/Static.gif`);
            }, ajaxDelay);

            // setTimeout(() => {
            //     postAJAX(choiceNumber);
            //     $('.layer-0').css('background-image', `url(https://horniverse.ai/game/images/game/environment/end/Layer-0-B.gif)`);
            //     setTimeout(() => {
            //         postAJAX(choiceNumber);
            //         $('.layer-0').css('background-image', `url(https://horniverse.ai/game/images/game/environment/end/Layer-0.gif)`);
            //     }, 720);
            // }, 200);


            // < SLAVA
        } else {
            if (scene == 2) {
                if (window.typeWriter) {
                    clearInterval(window.typeWriter);
                }
                $('#text').html('');
                $('.ai').show();
            }
            postAJAX(choiceNumber);
        }
    };
    let readableName = {
        0: {
            0: 'Choose sword',
            1: 'Choose axe',
            2: 'Choose halberd'
        },
        1: {
            0: 'No magic',
            1: 'Electrify',
            2: 'Set on fire',
            3: 'Poison',
            4: 'Blind',
            5: 'Use Darkness'
        },
        2: {
            0: 'Make a quick strike',
            1: 'Make a powerful strike',
            2: 'Make a piercing strike'
        },
    }

    function postAJAX(choiceNumber) {
        $.ajax({
            url: '/game/choice',
            method: 'POST',
            data: {choice: choiceNumber},
            success: async function (response) {

                if (response.success) {

                    // Slava >
                    let backgrounds = response.background.split('|');
                    console.log('Checking background:', backgrounds[0]);
                    console.log('Current background:', $('#game').css('background-image'));
                    if ($('#game').css('background-image') != backgrounds[0]) {
                        $('#game').addClass('hide');
                        $('.layer-0').addClass('hide');
                        $('.layer-1').addClass('hide');
                        $('.layer-2').addClass('hide');
                        $('.layer-3').addClass('hide');
                        $('.layer-4').addClass('hide');
                        setTimeout(() => {
                            $('#game').css('background-image', backgrounds[0]);
                            $('.layer-0').css('background-image', backgrounds[1]);
                            $('.layer-1').css('background-image', backgrounds[2]);
                            $('.layer-2').css('background-image', backgrounds[3]);
                            $('.layer-3').css('background-image', backgrounds[4]);
                            $('.layer-4').css('background-image', backgrounds[5]);
                        }, 1000);
                        setTimeout(() => {
                            $('#game').addClass('showing');
                            $('.layer-0').addClass('showing');
                            $('.layer-1').addClass('showing');
                            $('.layer-2').addClass('showing');
                            $('.layer-3').addClass('showing');
                            $('.layer-4').addClass('showing');
                            $('#game').removeClass('hide');
                            $('.layer-0').removeClass('hide');
                            $('.layer-1').removeClass('hide');
                            $('.layer-2').removeClass('hide');
                            $('.layer-3').removeClass('hide');
                            $('.layer-4').removeClass('hide');
                        }, 2000);

                        setTimeout(() => {
                            $('#game').removeClass('showing');
                            $('.layer-0').removeClass('showing');
                            $('.layer-1').removeClass('showing');
                            $('.layer-2').removeClass('showing');
                            $('.layer-3').removeClass('showing');
                            $('.layer-4').removeClass('showing');
                        }, 3000);
                    }

                    let text = response.text;
                    let index = 0;
                    $('#text').html('');
                    if (window.typeWriter) {
                        clearInterval(window.typeWriter);
                    }
                    window.typeWriter = setInterval(() => {
                        if (index < text.length) {
                            $('#text').html($('#text').html() + `<span class="symbol">${text.charAt(index)}</span>`);
                            index++;
                        } else {
                            clearInterval(window.typeWriter);
                        }
                    }, 20);

                    if (response.scene == 3) {
                        $('.ai').hide();
                    } else if (response.scene == 4) {
                        $('.player').addClass('weaponry');
                    } else if (response.scene == 8) {
                        $('.player').removeClass('weaponry');
                        $('.player').addClass('boss');
                    } else if (response.scene == 12) {
                        $('.cutscences').removeClass('hide');
                        $('.cutscences #intro').hide();
                        $('.cutscences #win').show();
                        $('.cutscences #win')[0].play();
                        $('.btn.start').addClass('end-hide-button').show().attr('href', '/game/').text('Restart the game ({{Auth::user()->attemps ? Auth::user()->attemps - 1 : 0}} attempts)').addClass('restart');
                        $('.buy').addClass('end-hide-button').show();
                        $('.btn.to-site').addClass('end-hide-button').show();
                        $('.js-win-form').addClass('end-hide-button').show();
                        setTimeout(() => {
                            $('.btn.start').removeClass('end-hide-button').addClass('end-show-button');
                            $('.buy').removeClass('end-hide-button').addClass('end-show-button');
                            $('.btn.to-site').removeClass('end-hide-button').addClass('end-show-button');
                            $('.js-win-form').removeClass('end-hide-button').addClass('end-show-button');
                        }, 5000);
                        fetch(`/game/last-prize`)
                            .then(res => res.json())
                            .then(({lastPrize}) => {
                                const code = lastPrize.prize_code;
                                $('.code-input').val(code);
                            });
                    } else if (response.scene == 13) {
                        $('.cutscences').removeClass('hide');
                        $('.cutscences #intro').hide();
                        $('.cutscences #win').hide();

                        let looseScene = Math.floor(Math.random() * 2) + 1;
                        if (looseScene == 1) {
                            $('.cutscences #loose').show();
                            $('.cutscences #loose')[0].play();
                        } else {
                            $('.cutscences #loose-2').show();
                            $('.cutscences #loose-2')[0].play();
                        }
                        $('.btn.start').addClass('end-hide-button').show().attr('href', '/game/').text('Restart the game ({{Auth::user()->attemps ? Auth::user()->attemps - 1 : 0}} attempts)').addClass('restart');
                        $('.buy').addClass('end-hide-button').show();
                        $('.btn.to-site').addClass('end-hide-button').show();
                        setTimeout(() => {
                            $('.btn.start').removeClass('end-hide-button').addClass('end-show-button');
                            $('.buy').removeClass('end-hide-button').addClass('end-show-button');
                            $('.btn.to-site').removeClass('end-hide-button').addClass('end-show-button');
                        }, 5000);
                    }
                    // < Slava


                    scene = response.scene;
                    if (scene == 8 || scene == 11) {
                        $readableType = 0;
                    }
                    if (scene == 9) {
                        $readableType = 1;
                    }
                    if (scene == 10) {
                        $readableType = 2;
                    }
                    combination = response.combination;

                    let choicesHtml = '';
                    if (response.scene < 8) {
                        response.choiсes.forEach((value, key) => {
                            choicesHtml += `<button class="btn action" data-choice="${key}" onmouseup="choice('${key}')">${value}</button>`;
                        });
                    } else {
                        response.choiсes.forEach((value, key) => {
                            choicesHtml += `<button class="btn action" data-choice="${value}" onmouseup="choice('${value}')">${readableName[$readableType][value]}</button>`;
                        });
                    }

                    $('#choices').html(choicesHtml);
                    $('#chest').html(response.chest);
                    console.log(response.scene);
                } else {
                    console.error('Error:', response.error);
                }
            }
        });
    }
</script>
</html>





