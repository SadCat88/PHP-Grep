<? // ====================================================== ?>
<? // === PHP ?>
<? // ====================================================== ?>

<? // === Variables ?>
<?
$PHP_Grep_version = "1.0.5";
$PHP_Grep_github = "https://github.com/SadCat88/PHP-Grep";
$PHP_Grep_script_url = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
?>

<? // === Settings ?>
<?
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
?>

<? // === Update 

$versionJson =  file_get_contents('https://sadcat88.github.io/PHP-Grep/version.json');

$versionJson = json_decode($versionJson, true);

echo "<pre class='prevar' style='display:none; background-color:#000000; color:#ffffff;'>";
var_dump($versionJson);
echo "<br>";
echo "</pre>";	
?>


<? // === Script ?>
<?
// === время запуска скрипта
$GLOBALS['arPhpGrepParams']['startTime'] = microtime( true );


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

	$arResult = array();
	$arResult['searchString'] = array();
	$arResult['searchOption'] = array();
	$arResult['searchPath'] = array();
	$arResult['itemsForSearch'] = array();
	$arResult['error'] = '';

	// === экранирование указанного регулярного выражения
	$arResult['searchString'] = preg_quote( $searchString );


	// === тип поиска по умолчанию
	if ( empty( $searchOption ) ) {
		$searchOption = 'file-content';
	}
	$arResult['searchOption'] = $searchOption;

	// === массив путей для поиска
	$searchDirectories = array();

	// === если указан путь для поиска
	if ( !empty( $searchPath ) ) {

		// === получить массив путей из строки по разделителю ','
		$searchDirectories = explode( ',', $searchPath );
		$arResult['searchPath'] = $searchDirectories;

		// === для каждого указанного пути
		// вычислить абсолютный путь от корня файловой системы до указанной директории
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
		$arResult['searchPath'] = $searchDirectories;
	}


	// === поиск по содержимому файлов
	// =======================================

	// === массив include
	if ( !empty( $_GET['search-include'] ) ) {
		$include_ext = explode( ' ', trim( $_GET['search-include'] ) );
	}

	// === массив exclude
	if ( !empty( $_GET['search-exclude'] ) ) {
		$exclude_ext = explode( ' ', trim( $_GET['search-exclude'] ) );
	}

	$i = 0;
	// === перебрать все пути для поиска
	while ( count( $searchDirectories ) ) {

		$dir = array_pop( $searchDirectories );

		// === прочитать директорию
		if ( $handle = opendir( $dir ) ) {

			while ( ( $file = readdir( $handle ) ) !== false ) {

				// === пропустить из массива файлов ссылки на себя и родителя
				if ( $file == '.' || $file == '..' ) {
					continue;
				}

				$fileName = $file;
				$filePath = $dir . DIRECTORY_SEPARATOR . $file;

				$dirName = $file;
				$dirPath = $dir . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR;

				// === пропустить файл PHP-Grep
				if ( $_SERVER["SCRIPT_FILENAME"] === $filePath ) {
					continue;
				}


				// === поиск по содержимому файла или его имени
				if ( $searchOption === 'file-content' || $searchOption === 'file-name' ) {

					// === если текущая сущность директория
					// добавить ее в массив перебираемых путей для поиска
					// чтобы в ней на последующих итерациях найти все файлы
					if ( is_dir( $filePath ) ) {
						array_unshift( $searchDirectories, $filePath );
					}

					// === если текущая сущность файл
					elseif ( is_file( $filePath ) ) {

						// === если файл в списке разрешенных расширений
						// и не в списке запрещенных
						if (
							( empty( $include_ext ) || in_array( pathinfo( $file, PATHINFO_EXTENSION ), $include_ext ) )
							&& ( empty( $exclude_ext ) || !in_array( pathinfo( $file, PATHINFO_EXTENSION ), $exclude_ext ) )
						) {
							$arResult['itemsForSearch'][$i]['filePath'] = $filePath;
							$arResult['itemsForSearch'][$i]['fileName'] = $fileName;
							$i++;
						}

					}

				}

				// === поиск по названию директории
				elseif ( $searchOption === 'folder-name' ) {

					// === если текущая сущность директория
					// добавить ее в массив перебираемых путей для поиска
					// чтобы в ней на последующих итерациях найти все файлы
					if ( is_dir( $filePath ) ) {
						array_unshift( $searchDirectories, $filePath );

						$arResult['itemsForSearch'][$i]['dirPath'] = $dirPath;
						$arResult['itemsForSearch'][$i]['dirName'] = $dirName;
						$i++;

					}


					// === если текущая сущность файл
					elseif ( is_file( $filePath ) ) {
						continue;
					}

				}

			}

			closedir( $handle );
		}

	}


	return $arResult;

}


function phpGrep( $arSearchData ) {

//	$arSearchData = array();
//	$arSearchData['searchString'] = array();
//	$arSearchData['searchOption'] = array();
//  $arResult['searchPath'] = array();
//	$arSearchData['itemsForSearch'] = array();
//  $arSearchData['error'] = '';
	$arResult = $arSearchData;
	$arResult['itemsFound'] = array();

	// === обработчик ошибок
	if ( !empty( $arSearchData['error'] ) ) {
		return $arSearchData['error'];
	}


	// === поиск по содержимому файлов
	if ( $arSearchData['searchOption'] === 'file-content' ) {

		foreach ( $arSearchData['itemsForSearch'] as $key => $item ) {

			$fileContent = file_get_contents( $item['filePath'] );

			if ( preg_match_all( '#' . $arSearchData['searchString'] . '#simU', $fileContent, $matches ) ) {
				array_push( $arResult['itemsFound'], $item['filePath'] );
			}

		}

	}

	// === поиск по названию файла
	if ( $arSearchData['searchOption'] === 'file-name' ) {

		foreach ( $arSearchData['itemsForSearch'] as $key => $item ) {

			$fileName = $item['fileName'];

			if ( preg_match_all( '#' . $arSearchData['searchString'] . '#simU', $fileName, $matches ) ) {
				array_push( $arResult['itemsFound'], $item['filePath'] );
			}

		}

	}

	// === поиск по названию директории
	if ( $arSearchData['searchOption'] === 'folder-name' ) {

		foreach ( $arSearchData['itemsForSearch'] as $key => $item ) {

			$dirName = $item['dirName'];

			if ( preg_match_all( '#' . $arSearchData['searchString'] . '#simU', $dirName, $matches ) ) {
				array_push( $arResult['itemsFound'], $item['dirPath'] );
			}

		}

	}


	return $arResult;

}


function outputResult( $arSearchResult = '' ) {


	// === строка для поиска пустая
	if ( $arSearchResult === '' ) {

		$output = '<h2 class="heading">Результат поиска:</h2>';

		$output .= '<p class="error">Меня просили найти ничего и я нашёл ничто...</p>';

	}

	// === обработка ошибок
	elseif ( !empty( $arSearchResult['error'] ) ) {

		$output = '<h2 class="heading">Результат поиска:</h2>';

		$output .= $arSearchResult['error'];

	}

	// === есть результаты поиска
	elseif ( !empty( $arSearchResult['itemsFound'] ) ) {

		$endTime = microtime( true ) - $GLOBALS['arPhpGrepParams']['startTime'];

		$output = '<h2 class="result-heading">Результат поиска:</h2>';

		$output .= '<ul class="result-list">';
		foreach ( $arSearchResult['itemsFound'] as $key => $item ) {
			$output .= '<li class="result-item">' . $item . '</li>';
		}
		$output .= '</ul>';

		$output .= '<p class="result-subheading">Общее количество элементов попавших в поиск: '
							 . count( $arSearchResult['itemsForSearch'] ) . '</p>';
		$output .= '<p class="result-subheading">Количество элементов удовлетворяющих запросу: '
							 . count( $arSearchResult['itemsFound'] ) . '</p>';
		$output .= '<p class="result-subheading">Время поиска: ' . round( $endTime, 4 ) . ' сек.</p>';


	}

	// === результатов поиска нет
	else {

		$endTime = microtime( true ) - $GLOBALS['arPhpGrepParams']['startTime'];

		$output = '<h2 class="result-heading">Результат поиска:</h2>';

		$output .= '<ul class="result-list">';
		$output .= '<li class="result-item">Ничего не найдено...</li>';
		$output .= '</ul>';

		$output .= '<p class="result-subheading">Общее количество элементов попавших в поиск: '
							 . count( $arSearchResult['itemsForSearch'] ) . '</p>';
		$output .= '<p class="result-subheading">Количество элементов удовлетворяющих запросу: '
							 . count( $arSearchResult['itemsFound'] ) . '</p>';
		$output .= '<p class="result-subheading">Время поиска: ' . round( $endTime, 4 ) . ' сек.</p>';

	}

	echo $output;

}

?>



<? // ====================================================== ?>
<? // === DOCUMENT ?>
<? // ====================================================== ?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta
		http-equiv="Content-Type"
		name="viewport"
		charset="utf-8"
		content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
	>
	<link
		rel="shortcut icon"
		href="#"
	>
	<title>PHP-Grep</title>

	<? // ====================================================== ?>
	<? // === STYLE ?>
	<? // ====================================================== ?>

	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
			font-size: 14px;
		}
	</style>

	<style>

		.c-expand-block.--js-start-short .c-expand-block-body {
			height: 0;
		}

		/* === reset */
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

		/* === variables */
		:root {
			--color-main_1: rgba(197, 226, 255, 1);

			--color-second_1: rgba(218, 108, 17, 1);

			--color-text_1: rgba(126, 74, 0, 1);
			--color-text_2: rgb(255, 91, 0);
			--color-btn-hover_1: rgba(237, 132, 0, 0.2);

			--color-main_gradient_1: rgba(77, 166, 255, 1);
			--color-main_gradient_2: rgba(197, 226, 255, 1);
			--color-main_gradient_3: rgba(255, 228, 210, 1);

			--color-form-bg: rgba(255, 255, 255, 0.25);
		}

		/* === c-expand-block */
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

		/* === base */
		body {
			min-height: 100vh;
			overflow-x: scroll;

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

		a {
			color: var(--color-text_1);
		}


		.l-container {
			display: flex;
			flex-direction: column;
			width: 100%;
			margin: 0 auto;
			max-width: calc(1560px + 5vw * 2);
			padding: 0 5vw;
		}

		@media (max-width: 440px) {
			.l-container {
				padding: 0 3vw;
			}
		}

		.l-container.--result {
			display: flex;
			flex-direction: column;
			width: 100%;
			margin: 0 auto;
			max-width: 100%;
			padding: 1em 2vw;
		}

		/* === blocks */
		.header {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
		}


		.copyright {
			padding: 0.5vh 0.5vw;
			font-size: 16px;
		}

		h1 {
			line-height: 2em;
			margin-bottom: 0.4em;
		}


		.l-row {
			display: flex;
			align-items: flex-end;
			justify-content: flex-start;
		}

		.form {
			margin-bottom: 1em;
		}

		.form-item {
			margin-bottom: 0.5em;
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

		.form-item-subtitle {
			font-size: 14px;
			margin-bottom: 0.4em;
		}

		.form-item-label {
			display: inline-block;
			margin-right: 3em;
		}

		.w-input.--row {
			display: flex;
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
			padding: 0.4em;
			width: 100%;
			min-height: 2em;
			height: 2em;

			font-size: 16px;
			font-weight: 700;
			line-height: 1;

			border: 1px solid var(--color-text_1);
			border-radius: 6px;
			background-color: var(--color-form-bg);

			resize: none;
		}

		.btn {
			display: inline-flex;
			justify-content: center;
			align-items: center;

			padding: 0.4em 1em;
			min-height: 2em;

			cursor: pointer;
			background: none;
			border: 1px solid var(--color-text_1);
			border-radius: 6px;

			font-size: 16px;
			font-weight: 700;
			line-height: 1;
			color: var(--color-text_1);
			text-decoration: none;

			transition: all 0.3s ease 0s;
		}

		.btn:hover {
			color: var(--color-text_2);
			border: 1px solid var(--color-text_2);
			background-color: var(--color-btn-hover_1);
		}

		.btn.--help {
			flex-shrink: 0;
			padding: 0.4em;
			width: 2em;
			height: 2em;
		}

		.btn.--help .content {
			font-size: 16px;
			font-weight: 700;
		}


		.c-expand-block.--js-change-state .btn.--help .content.--close {
			display: block;
		}


		.result-list {
			padding: 1em 1em;
		}

		.result-subheading {
			font-weight: 600;
		}


	</style>

</head>
<body>

<? // ====================================================== ?>
<? // === HTML ?>
<? // ====================================================== ?>
<div class="l-container">

	<div class="header">

		<h1>PHP-Grep</h1>

		<div class="copyright">
			<p>Version: <? echo $PHP_Grep_version ?></p>
			<p>Developed: SadCat88</p>
			<p><a
					href="<? echo $PHP_Grep_github ?>"
					target="_blank"
				> GitHub</a></p>
		</div>

	</div>

	<form class="form">

		<? // === RegExp ?>
		<div class="form-item --regexp c-expand-block --js-start-short">

			<div class="l-row">
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
			</div>

			<div class="form-item-description --after c-expand-block-body">
				<p>Поиск без учета регистра.</p>
				<br>
				<p>Поиск осуществляется с помощью функции preg_match_all( ).</p>
				<p>Все метасимволы в строке экранируются функцией preg_quote( ).</p>
			</div>

		</div>

		<? // === Path ?>
		<div class="form-item --path c-expand-block --js-start-short">
			<div class="l-row">
				<label class="form-item-label">
					<p class="form-item-title">
						Path
					</p>
					<p class="form-item-subtitle"><?php echo $_SERVER["DOCUMENT_ROOT"] ?>/</p>


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
			</div>

			<div class="form-item-description --after c-expand-block-body">
				<p>Уточнение пути для поиска</p>
				<br>
				<p>Если форма пустая, поиск будет произведен в корневой директории сайта, иначе по указанному пути.</p>
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

			<div class="l-row">
				<label class="form-item-label">
					<p class="form-item-title">
						Include .ext
					</p>


					<div class="w-input --row">

						<? $thisContent = !empty( $_GET['search-include'] )
							? @urldecode( $_GET['search-include'] )
							: 'php tpl html htm js css';
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
			</div>

			<div class="form-item-description --after c-expand-block-body">
				<p>Включить в поиск файлы с указанным расширением.</p>
			</div>

		</div>

		<? // === Exclude ?>
		<div class="form-item --exclude c-expand-block --js-start-short">

			<div class="l-row">
				<label class="form-item-label">
					<p class="form-item-title">
						Exclude .ext
					</p>


					<div class="w-input --row">

						<? $thisContent = !empty( $_GET['search-exclude'] )
							? @urldecode( $_GET['search-exclude'] )
							: 'jpg png gif jpeg xml ini cfg';
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
			</div>


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
				isset( $_GET['search-option'] )
				&& $_GET['search-option'] !== 'file-content'
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
			$thisChecked = '';
			if (
				isset( $_GET['search-option'] )
				&& $_GET['search-option'] === 'file-name'
			) {
				$thisChecked = 'checked';
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
			$thisChecked = '';
			if (
				isset( $_GET['search-option'] )
				&& $_GET['search-option'] === 'folder-name'
			) {
				$thisChecked = 'checked';
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
				class="btn --reset --js-reset-all-forms"
				type="button"
			>
				Reset all forms
			</button>

			<a
				class="btn --search"
				href="<? echo $PHP_Grep_script_url ?>"
				target="_blank"
			>Open new tab</a>
		</div>

	</form>

</div>

<div class="result">
	<hr>
	<div class="l-container --result">
		<? // ====================================================== ?>
		<? // === RUN PHP-GREP ?>
		<? // ====================================================== ?>
		<? if ( !empty( $_GET['search-string'] ) ) {

			$arSearchData = getArSearchData( $_GET['search-string'], $_GET['search-path'], $_GET['search-option'] );

			$arSearchResult = phpGrep( $arSearchData );

			outputResult( $arSearchResult );

		}
		else {
			outputResult();
		} ?>

	</div>
</div>


<? // ====================================================== ?>
<? // === SCRIPT ?>
<? // ====================================================== ?>
<script>
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		// ===========================================================================
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
							element:              element,
							primaryHeighDropdown: thisComponentBody.clientHeight,
							maxHeighDropdown:     thisComponentBody.scrollHeight,
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

		// ===========================================================================
		// === .PHP-Grep
		// ===========================================================================
		(
			function () {

				// === очистка форм
				let btnRestAllForms = document.querySelector( '.--js-reset-all-forms' );

				btnRestAllForms.addEventListener( 'click', ( event ) => {
					let thisLocation = window.location;
					window.location.href = thisLocation.origin + thisLocation.pathname;
				} );


				// === обработчик нажатия Enter внутри формы
				let allInputText = document.querySelectorAll( '.form-item-input.--text' );
				if ( allInputText.length !== 0 ) {
					allInputText.forEach( ( element, index ) => {
						element.addEventListener( 'keydown', ( event ) => {

							if ( event.keyCode == 13 ) {
								event.preventDefault();

								document.querySelector( '.form' ).submit();
							}

						} );
					} );
				}

			}
		)();


	} );

</script>
</body>
</html>