<body>
	<div id="cursor"></div>
	<div id="cursor__circle"></div>
	<header class="header">
		<div class="container">
			<div class="header__wrap">
				<a href="/game/" class="header__logo">
					<img src="/game/front/img/fav-4.png" alt="">
				</a>
				<div class="header__menu">
					<ul>
						<li><a href="/game/">Главная</a></li>
						<li><a href="/game/catalog">Каталог</a></li>
						<li><a href="/game/about">О компании</a></li>
						<li><a href="/game/#projects">Проекты</a></li>
						<li><a href="/game/help">Сервис</a></li>
						<li><a href="/game/pages/news">Новости</a></li>
						<li><a href="/game/contacts">Контакты</a></li>
					</ul>
				</div>
				<!--<a href="tel:+7(999)999-99-99" class="header__tel">+7(999) 999-99-99</a>-->
				<div class="header__social contact__social">
					<a href="#">
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1"  width="512" height="512" x="0" y="0" viewBox="0 0 176 176" style="enable-background:new 0 0 512 512" xml:space="preserve"><g><g data-name="Layer 2"><g data-name="01.facebook"><circle cx="88" cy="88" r="88" fill="#3a559f" data-original="#3a559f"></circle><path fill="#ffffff" d="m115.88 77.58-1.77 15.33a2.87 2.87 0 0 1-2.82 2.57h-16l-.08 45.45a2.05 2.05 0 0 1-2 2.07H77a2 2 0 0 1-2-2.08V95.48H63a2.87 2.87 0 0 1-2.84-2.9l-.06-15.33a2.88 2.88 0 0 1 2.84-2.92H75v-14.8C75 42.35 85.2 33 100.16 33h12.26a2.88 2.88 0 0 1 2.85 2.92v12.9a2.88 2.88 0 0 1-2.85 2.92h-7.52c-8.13 0-9.71 4-9.71 9.78v12.81h17.87a2.88 2.88 0 0 1 2.82 3.25z" data-original="#ffffff"></path></g></g></g></svg>
					</a>
					<a href="#">
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="512" height="512" x="0" y="0" viewBox="0 0 152 152" style="enable-background:new 0 0 512 512" xml:space="preserve"><g><g data-name="Layer 2"><g data-name="04.Twitter"><circle cx="76" cy="76" r="76" fill="#03a9f4" data-original="#03a9f4"></circle><path fill="#ffffff" d="M125.23 45.47a42 42 0 0 1-11.63 3.19 20.06 20.06 0 0 0 8.88-11.16 40.32 40.32 0 0 1-12.8 4.89 20.18 20.18 0 0 0-34.92 13.8 20.87 20.87 0 0 0 .47 4.6 57.16 57.16 0 0 1-41.61-21.11 20.2 20.2 0 0 0 6.21 27 19.92 19.92 0 0 1-9.12-2.49v.22a20.28 20.28 0 0 0 16.17 19.82 20.13 20.13 0 0 1-5.29.66 18 18 0 0 1-3.83-.34 20.39 20.39 0 0 0 18.87 14.06 40.59 40.59 0 0 1-25 8.61 36.45 36.45 0 0 1-4.83-.28 56.79 56.79 0 0 0 31 9.06c37.15 0 57.46-30.77 57.46-57.44 0-.89 0-1.75-.07-2.61a40.16 40.16 0 0 0 10.04-10.48z" data-original="#ffffff"></path></g></g></g></svg>
					</a>
					<a href="#">
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="512" height="512" x="0" y="0" viewBox="0 0 473.931 473.931" style="enable-background:new 0 0 512 512" xml:space="preserve"><g><circle cx="236.966" cy="236.966" r="236.966" style="" fill="#d42428" data-original="#d42428"></circle><path d="M404.518 69.38c92.541 92.549 92.549 242.593 0 335.142-92.541 92.541-242.593 92.545-335.142 0L404.518 69.38z" style="" fill="#cc202d" data-original="#cc202d"></path><path d="M469.168 284.426 351.886 167.148l-138.322 15.749-83.669 129.532 156.342 156.338c91.92-19.445 164.185-92.155 182.931-184.341z" style="" fill="#ba202e" data-original="#ba202e"></path><path d="M360.971 191.238c0-19.865-16.093-35.966-35.947-35.966H156.372c-19.85 0-35.94 16.105-35.94 35.966v96.444c0 19.865 16.093 35.966 35.94 35.966h168.649c19.858 0 35.947-16.105 35.947-35.966v-96.444h.003zM216.64 280.146v-90.584l68.695 45.294-68.695 45.29z" style="" fill="#ffffff" data-original="#ffffff"></path></g></svg>
					</a>
				</div>
				<div class="header__burger"><span></span></div>
			</div>
		</div>
	</header>
		<main class="main">
			<div class="bg"></div>
			    {{$slot}}
		</main>
	<div class="btn-slide-to-top">
		<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="512" height="512" x="0" y="0" viewBox="0 0 451.846 451.847" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M345.441 248.292 151.154 442.573c-12.359 12.365-32.397 12.365-44.75 0-12.354-12.354-12.354-32.391 0-44.744L278.318 225.92 106.409 54.017c-12.354-12.359-12.354-32.394 0-44.748 12.354-12.359 32.391-12.359 44.75 0l194.287 194.284c6.177 6.18 9.262 14.271 9.262 22.366 0 8.099-3.091 16.196-9.267 22.373z" fill="#000000" data-original="#000000"></path></g></svg>
	</div>
	<footer class="footer">
		<div class="container">
			<p>ВСЕ ПРАВА ЗАЩИЩЕНЫ</p>
		</div>
	</footer>
</body>