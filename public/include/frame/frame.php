<?php
    require_once __DIR__ . '/../../app/sections.php';
    
    require_once __DIR__ . '/../config.php';
    $config = loadConstants();
    $pageMetas = loadPageMetas();

    $companyName = $config["COMPANY_NAME"];
    $twitterAccount = $config["TWITTER_ACCOUNT"];
    $configDomain = $config["DOMAIN"];
    //var_dump($config);

    //$pageSlug = 'privacy';  //読み込みフレームから渡してくる

    $currentMetas = $pageMetas["pages"][$pageSlug]["Metas"];
    //var_dump($currentMetas);

    $formatTitle = $pageSlug === 'top' ? $config["COMPANY_NAME"] . ' | ' . $config["SITE_NAME"] : $config["COMPANY_NAME"];

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $domain   = $_SERVER['HTTP_HOST'];
    $path     = $_SERVER['REQUEST_URI'];

    $currentURL = $protocol . $domain . $path;

    $currentTitle = $currentMetas["title"];

    $pageTitle = $pageSlug === "top" ? $formatTitle : $currentTitle . ' | ' . $formatTitle;
    $pageDescription = $currentMetas["description"];
    $pageKeywords = $currentMetas["keywords"];
    $pageH1 = $currentMetas["pageH1"];
    $pageID = $pageSlug === 'top' ? 'top' : 'sec';
    $sectionWrapperClass = $currentMetas["sectionWrapperClass"];

    $pageOGTitle = $currentMetas["og"]["title"];
    $pageOGDescription = $currentMetas["og"]["description"];
    $pageOGURL = $currentMetas["og"]["url"];
    $pageOGType = $currentMetas["og"]["type"];
    $pageOGImage = $currentMetas["og"]["image"];

    $pageTwitterCard = $currentMetas["twitter"]["card"];
    $pageTwitterTitle = $currentMetas["twitter"]["title"];
    $pageTwitterDescription = $currentMetas["twitter"]["description"];
    $pageTwitterImage = $currentMetas["twitter"]["image"];
    
    
?>

<html lang="en">    
    <head>
		<meta charset="utf-8" />
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
		<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
		<meta name="viewport" content="width=device-width" />

		<title><?= $pageTitle; ?></title>
        <meta name="description" content="<?= $pageDescription; ?>" />
        <meta name="keywords" content="<?= $pageKeywords; ?>" />

        <!-- OGP -->
        <meta property="og:site_name" content="<?= $companyName; ?>" />
        <meta property="og:title" content="<?= $pageOGTitle; ?>" />
        <meta property="og:type" content="<?= $pageOGType; ?>" />
        <meta property="og:url" content="<?= $pageOGURL; ?>" />
        <meta property="og:image" content="<?= $pageOGImage; ?>" />
        <meta property="og:description" content="<?= $pageOGDescription; ?>" />
        <meta property="og:locale" content="ja_JP" />

        <meta name="twitter:card" content="<?= $pageTwitterCard; ?>" />
        <meta name="twitter:site" content="@<?= $twitterAccount; ?>" />
        <meta name="twitter:domain" content="<?= $configDomain; ?>" />
        <meta name="twitter:title" content="<?= $pageTwitterTitle; ?>"/>
        <meta name="twitter:description" content="<?= $pageTwitterDescription; ?>" />
        <meta name="twitter:image" content="<?= $pageTwitterImage; ?>" />
		

        <?php
            $cssFiles = glob(__DIR__ . '/../../_astro/index.*.css');

            if (!empty($cssFiles)) {
                // :rootを含むCSSを先に並べる
                usort($cssFiles, function($a, $b) {
                    $aHasRoot = preg_match('/:root\s*{/', file_get_contents($a));
                    $bHasRoot = preg_match('/:root\s*{/', file_get_contents($b));
                    if ($aHasRoot && !$bHasRoot) return -1;
                    if (!$aHasRoot && $bHasRoot) return 1;
                    return filemtime($a) <=> filemtime($b);
                });

                foreach ($cssFiles as $cssFile) {
                    $fileName = basename($cssFile);
                    echo '<link rel="stylesheet" href="/_astro/' . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
                }
            }
        ?>

        <?= section('head') ?>   <!-- ← head スロット -->
	</head>



	<body id="<?= $pageID; ?>">


		<?php 
            // require('./header.php'); 
            require_once __DIR__ . '/header.php';    
        ?>
		
		<main>
			

			<section class="unitWrap" id="<?= $sectionWrapperClass; ?>">
				<div class="cont">
					<div class="titleWrap commonH2">
						<p class="bgText"><?= $pageH1; ?></p>
						<h2><?= $pageH1; ?></h2>
					</div>


                    <?= section('before_main') ?> <!-- ← mainの前に差し込みたい時用 -->
                    <?= section('content') ?><!-- ← ここが本文スロット -->
                    <?= section('after_main') ?>  <!-- ← mainの後に差し込みたい時用 -->


				</div>
			</section>



		</main>

        <?php 
            // require('./footer.php');
            require_once __DIR__ . '/footer.php';    
        ?>
		
        <?= section('scripts') ?>     <!-- ← body閉じタグ直前のJSなど -->
	</body>
</html>
