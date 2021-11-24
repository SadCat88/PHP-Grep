<? // === PHP ?>
<?
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );


$GLOBALS['arParams']['startTime'] = microtime( true );

// === универсальная функция strpos()
function u_strpos( $haystack, $needle, $offset = 0 ) {

	// === проверка наличия модуля mbstring
	$isMbString = extension_loaded( 'mbstring' );

	if ( $isMbString ) {
		$result = mb_strpos( $haystack, $needle, $offset );
	}
	else {
		$result = substr( $haystack, $needle, $offset );
	}

	return $result;

}

// === универсальная функция substr()
function u_substr( $string, $offset, $length = null ) {

	// === проверка наличия модуля mbstring
	$isMbString = extension_loaded( 'mbstring' );

	if ( $isMbString ) {
		$result = mb_substr( $string, $offset, $length );
	}
	else {
		$result = substr( $string, $offset, $length );
	}

	return $result;

}

// === универсальная функция strlen()
function u_strlen( $string ) {

	// === проверка наличия модуля mbstring
	$isMbString = extension_loaded( 'mbstring' );

	if ( $isMbString ) {
		$result = mb_strlen( $string );
	}
	else {
		$result = strlen( $string );
	}

	return $result;

}

function getArSearchData( $searchString, $searchPath, $searchOption ) {

	// === экранирование указанного регулярного выражения
	$searchString = escapeshellcmd( $searchString );

	echo "<pre>";
	var_dump( $searchString );
	echo "<br><br>";
	echo "</pre>";


	// === массив путей для поиска
	$searchDirectories = array();

	// === если указан путь для поиска
	if ( !empty( $searchPath ) ) {

		// === получить массив путей из строки по разделителю ','
		$searchDirectories = explode( ',', $searchPath );

		// === для каждого указанного пути
		// вычислить абсолютный путь от корня фавйловой системы до указанной директории

		foreach ( $searchDirectories as $keyDir => $dir ) {
			// === отрезать пробельные символы в конце и начале строки
			$dir = trim( $dir );

			$userPath = $dir;

			// === если путь является абсолютным - от корня файловой системы
			if ( u_strpos( $userPath, $_SERVER["DOCUMENT_ROOT"] ) === 0 ) {
				$rootPath = $userPath;
			}
			// === если путь является относительным
			else {

				$firstLetter = u_substr( $userPath, 0, 1 );

				// === если относительный путь не начинается с сепаратора, добавить его
				$userPath = ( $firstLetter === '\\' || $firstLetter === '/' ) ? $userPath : DIRECTORY_SEPARATOR . $userPath;
				$rootPath = $_SERVER["DOCUMENT_ROOT"] . $userPath;
			}


			$searchDirectories[$keyDir] = $rootPath;
		}

	}

	// === если путь для поиска не указан
	else {
		$rootPath = $_SERVER["DOCUMENT_ROOT"];
		$searchDirectories[0] = $rootPath;
	}


	echo "<pre>";
	var_dump( $searchDirectories );
	echo "<br>";

	echo "</pre>";


	while ( count( $searchDirectories ) ) {

		$dir = array_pop( $searchDirectories );
		echo "<pre>";
		var_dump( $dir );
		echo "<br>";

		echo "</pre>";
	}

	if ( $searchOption === 'file-content' ) {

	}

}

function phpGrep( $searchString, $searchPath ) {


}

?>

<? // === DOCUMENT ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta
		name="viewport"
		content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
	>
	<meta
		http-equiv="X-UA-Compatible"
		content="ie=edge"
	>
	<link
		rel="shortcut icon"
		href="#"
	>
	<title>PHP-Grep</title>
</head>
<body>

<? // === CSS ?>
<style type="text/css">
	body {
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
		font-size: 14px;
	}


</style>


<style>
	*,
	*::before,
	*::after {
		box-sizing: border-box;
		margin: 0;
		padding: 0;
	}

	*:focus,
	button:focus {
		outline: none;
	}

	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
		margin: 0;
	}

	ul,
	li {
		list-style: none;
	}

	fieldset {
		border: none;
	}

	mark {
		background: none;
	}


	:root {
		--color-main_1: rgba(197, 226, 255, 1);

		--color-second_1: rgba(218, 108, 17, 1);

		--color-text_1: rgba(126, 74, 0, 1);
		--color-text_2: rgb(255, 91, 0);
		--color-btn-hover_1: rgba(237, 132, 0, 0.2);

		--color-main_gradient_1: rgba(77, 166, 255, 1);
		--color-main_gradient_2: rgba(197, 226, 255, 1);
		--color-main_gradient_3: rgba(255, 228, 210, 1);
	}

	body {
		min-height: 100vh;

		background-color: var(--color-main_1);
		background: linear-gradient(
			200deg,
			var(--color-main_gradient_1) 0%,
			var(--color-main_gradient_2) 33%,
			var(--color-main_gradient_2) 69%,
			var(--color-main_gradient_3) 100%
		);

		color: var(--color-text_1);
		font-size: 14px;
	}

	.l-container {
		display: flex;
		flex-direction: column;
		width: 100%;
		margin: 0 auto;
		max-width: calc(1560px + 5vw * 2);
		padding: 0 5vw;
	}


	.copyright {
		position: absolute;
		top: 0;
		right: 0;
		padding: 1em 2em;
		font-size: 16px;
	}

	h1 {
		line-height: 2em;
		margin-bottom: 0.4em;
	}

	.form-item {
		margin-bottom: 2em;
	}

	.form-item.--options {
		display: flex;
		flex-direction: column;
	}

	.form-item.--options .form-item-label {
		padding: 0.3em 0;
		cursor: pointer;
		transition: all 0.3s ease 0s;
	}

	.form-item.--options .form-item-label:hover {
		color: var(--color-text_2);
	}


	.form-item-title {
		font-size: 16px;
		font-weight: 700;
		margin-bottom: 0.4em;
	}

	.form-item-label {
		display: inline-block;
		margin-right: 3em;
	}

	.w-input.--row {
		display: block;
		padding: 0 1em;
		position: relative;
	}

	.line-regular {
		position: absolute;
		font-size: 16px;
		font-weight: 700;
		line-height: 1;
		top: 50%;
		transform: translate(0, -50%);
	}

	.line-regular.--left {
		left: 0;
	}

	.line-regular.--right {
		right: 0;
	}

	.form-item-input.--text {
		padding: 0.4em 0.6em;
		width: 100%;

	}

	.btn {
		display: inline-flex;
		justify-content: center;
		align-items: center;

		padding: 0.4em 1em;
		min-width: 200px;

		cursor: pointer;
		margin-right: 0.5em;
		background: none;
		border: 1px solid var(--color-text_1);
		border-radius: 6px;

		font-size: 16px;
		font-weight: 700;
		color: var(--color-text_1);

		transition: all 0.3s ease 0s;
	}

	.btn:hover {
		color: var(--color-text_2);
		border: 1px solid var(--color-text_2);
		background-color: var(--color-btn-hover_1);
	}

	.btn.--help {
		min-width: 3em;
		min-height: 3em;
		transform: translate(0, 1em);
		margin-bottom: 0.5em;
	}

	.btn.--help .content {
		position: absolute;
		font-size: 24px;
		font-weight: 700;
	}


	.c-expand-block-body {
		transition: all 0.5s ease 0s;
		overflow: hidden;
		padding-top: 0.5em;
	}

	.c-expand-block.--js-start-short .c-expand-block-body {
		height: 0;
	}

	.btn.--help .content.--open {
		display: block;
	}


	.btn.--help .content.--close {
		display: none;
	}

	.c-expand-block.--js-change-state .btn.--help .content.--open {
		display: none;
	}

	.c-expand-block.--js-change-state .btn.--help .content.--close {
		display: block;
	}


</style>


<? // === HTML ?>
<div class="header">
	<div class="l-container">
		<h1>PHP-Grep</h1>

		<div class="copyright">
			<p>Version: 1.0.4</p>
			<p>Developed: SadCat88</p>
			<p><a
					href="<?php echo $PHP_Grep_github ?>"
					target="_blank"
				>GitHub</a></p>
		</div>
	</div>
</div>


<form class="form l-container">

	<? // === RegExp ?>
	<div class="form-item --regexp c-expand-block --js-start-short">

		<label class="form-item-label">

			<p class="form-item-title">
				RegExp
			</p>

			<div class="w-input --row">

				<span class="line-regular --left">#</span>
				<? $thisContent = !empty( $_GET['search-string'] ) ? @urldecode( $_GET['search-string'] ) : ''; ?>
				<textarea
					class="form-item-input --text"
					name="search-string"
					cols="120"
					rows="1"
				><?=$thisContent?></textarea>
				<span class="line-regular --right">#</span>

			</div>
		</label>

		<button
			type="button"
			class="btn --help c-expand-block-btn"
		>
			<span class="content --open">?</span>
			<span class="content --close">X</span>
		</button>

		<div class="form-item-description --after c-expand-block-body">
			<p>Все спецсимволы в строке необходимо экранировать, например "\#" для символа "#"</p>
			<p>Спецсимволы нуждающиеся в экранировании "#", "\", "(", "[", "$" и т.д.</p>
			<br>
			<p>Поиск осуществляется с помощью функции preg_match_all( ).</p>
			<p>Соответственно регулярное выражение поддерживает группы( ), диапазоны[ ], квантификаторы{ } и т.д.</p>
		</div>

	</div>

	<? // === Path ?>
	<div class="form-item --path c-expand-block --js-start-short">

		<label class="form-item-label">
			<p class="form-item-title">
				Path
			</p>


			<div class="w-input --row">

				<? $thisContent = !empty( $_GET['search-path'] ) ? @urldecode( $_GET['search-path'] ) : ''; ?>
				<textarea
					class="form-item-input --text"
					name="search-path"
					cols="120"
					rows="1"
				><?=$thisContent?></textarea>

			</div>

		</label>

		<button
			type="button"
			class="btn --help c-expand-block-btn"
		>
			<span class="content --open">?</span>
			<span class="content --close">X</span>
		</button>

		<div class="form-item-description --after c-expand-block-body">
			<p>Если форма пустая, поиск будет произведен в корневой директории сайта, иначе в указанной директории.</p>
			<p>"/" в начале и конце ставить не обязательно.</p>
			<br>
			<p>Можно указать несколько путей через запятую.</p>
			<br>
			<p>Указывать можно, как абсолютный путь от корневой директории сервера:</p>
			<p>"<?=$_SERVER["DOCUMENT_ROOT"]?><b>/test-folder/"</b></p>
			<p>так и относительный от корня сайта: <b>"/test-folder/"</b></p>
		</div>

	</div>

	<? // === Include ?>
	<div class="form-item --include c-expand-block --js-start-short">

		<label class="form-item-label">
			<p class="form-item-title">
				Include .ext
			</p>


			<div class="w-input --row">

				<? $thisContent = !empty( $_GET['include'] )
					?
					@urldecode( $_GET['include'] )
					:
					'php tpl html htm js css';
				?>
				<textarea
					class="form-item-input --text"
					name="search-include"
					cols="120"
					rows="1"
				><?=$thisContent?></textarea>


			</div>

		</label>

		<button
			type="button"
			class="btn --help c-expand-block-btn"
		>
			<span class="content --open">?</span>
			<span class="content --close">X</span>
		</button>

		<div class="form-item-description --after c-expand-block-body">
			<p>Включить в результаты поиска только файлы с указанным расширением.</p>
		</div>

	</div>

	<? // === Exclude ?>
	<div class="form-item --exclude c-expand-block --js-start-short">

		<label class="form-item-label">
			<p class="form-item-title">
				Exclude .ext
			</p>


			<div class="w-input --row">

				<? $thisContent = !empty( $_GET['exclude'] )
					?
					@urldecode( $_GET['exclude'] )
					:
					'jpg png gif jpeg xml ini cfg';
				?>
				<textarea
					class="form-item-input --text"
					name="search-exclude"
					cols="120"
					rows="1"
				><?=$thisContent?></textarea>


			</div>

		</label>


		<button
			type="button"
			class="btn --help c-expand-block-btn"
		>
			<span class="content --open">?</span>
			<span class="content --close">X</span>
		</button>


		<div class="form-item-description --after c-expand-block-body">
			<p>Исключить из поиска файлы с указанным расширением.</p>
			<p>Файлы исключения не будут открываться для чтения, что ускорит поиск.</p>
		</div>

	</div>

	<? // === Options ?>
	<div class="form-item --options">

		<p class="form-item-title">
			Options
		</p>

		<?
		$thisChecked = 'checked';
		if (
			isset( $_GET['search-option'] ) && $_GET['search-option'] !== 'file-content'
		) {
			$thisChecked = '';
		}
		?>
		<label class="form-item-label">
			<input
				class="form-item-input"
				type="radio"
				name="search-option"
				value="file-content"
				<?=$thisChecked?>
			>
			Поиск по содержимому файла
		</label>

		<?
		$thisChecked = 'checked';
		if (
			isset( $_GET['search-option'] ) && $_GET['search-option'] !== 'file-name'
		) {
			$thisChecked = '';
		}
		?>
		<label class="form-item-label">
			<input
				class="form-item-input"
				type="radio"
				name="search-option"
				value="file-name"
				<?=$thisChecked?>
			>
			Поиск по названию файла
		</label>

		<?
		$thisChecked = 'checked';
		if (
			isset( $_GET['search-option'] ) && $_GET['search-option'] !== 'folder-namee'
		) {
			$thisChecked = '';
		}
		?>
		<label class="form-item-label">
			<input
				class="form-item-input"
				type="radio"
				name="search-option"
				value="folder-name"
				<?=$thisChecked?>
			>
			Поиск по названию директории
		</label>
	</div>

	<? // === Buttons ?>
	<div class="form-item --buttons">
		<button
			class="btn --search"
			type="submit"
			value="Search"
		>
			Search
		</button>

		<button
			class="btn --reset"
			type="button"
		>
			Reset all forms
		</button>
	</div>


</form>

<? // === RUN GREP ?>
<? if ( !empty( $_GET['search-string'] ) ) {
	$arSearchData = getArSearchData( $_GET['search-string'], $_GET['search-path'], $_GET['search-option'] );

} ?>

<div class="footer">
	<div class="l-container">

	</div>
</div>

<hr>

<? // === JS ?>
<script>
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		//
		//
		// === .c-expand-block
		// ===========================================================================
		(
			function () {

				// === коллекция всех компонентов
				let allComponents = document.querySelectorAll( '.c-expand-block' );

				// === массив для хранения всех параметров каждого компонента
				// allComponentParams[0] - индекс
				// allComponentParams[0].element - экземпляр компонента
				// allComponentParams[0].primaryHeighDropdown - изначальная высота дропдауна
				// allComponentParams[0].maxHeighDropdown - максимальная высота дропдауна
				let allComponentParams = [];

				// === считывание параметров каждого компонента
				let readParams = function () {
					allComponents.forEach( function ( element, index ) {
						let thisComponentBody = element.querySelector( '.c-expand-block-body' );

						allComponentParams[index] = {
							element: element,
							primaryHeighDropdown: thisComponentBody.clientHeight,
							maxHeighDropdown: thisComponentBody.scrollHeight,
						};
					} );
				};

				// === первичная инициализация
				readParams();

				// === перечитывание параметров после ресайза
				window.addEventListener( 'resize', function ( event ) {
					readParams();
				} );


				// === основная логика компонента
				allComponents.forEach( function ( element, index ) {
					let thisComponent = element;
					let thisIndex = index;
					let thisComponentBody = element.querySelector( '.c-expand-block-body' );
					let thisComponentBtnAction = element.querySelector( '.c-expand-block-btn' );

					thisComponentBody.style.height = `${allComponentParams[thisIndex].primaryHeighDropdown}px`;

					// === клик по кнопке
					thisComponentBtnAction.addEventListener( 'mousedown', function ( event ) {
						thisComponent.classList.toggle( '--js-change-state' );

						// === стартовое положение блока .--js-start-short
						if ( thisComponent.classList.contains( '--js-start-short' ) ) {
							if ( thisComponent.classList.contains( '--js-change-state' ) ) {
								// === растянуть блок
								thisComponentBody.style.height = `${allComponentParams[thisIndex].maxHeighDropdown}px`;
							}
							else {
								// === сжать блок
								thisComponentBody.style.height = '0';
							}
						}

						// === стартовое положение блока .--js-start-long
						if ( thisComponent.classList.contains( '--js-start-long' ) ) {
							if ( thisComponent.classList.contains( '--js-change-state' ) ) {
								// === сжать блок
								thisComponentBody.style.height = '0';
							}
							else {
								// === растянуть блок
								thisComponentBody.style.height = `${allComponentParams[thisIndex].maxHeighDropdown}px`;
							}
						}
					} );
				} );
			}
		)();
	} );

</script>
</body>
</html>