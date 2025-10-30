<?php
    require_once __DIR__ . '/../config.php';
    $config = loadConstants();
    
    $siteName = $config["SITE_NAME"];
    $companyName = $config["COMPANY_NAME"];
    $tel = $config["TEL"];
    $telHyphen = $config["TEL_HYPHEN"];
    $lineURL = $config["LINE_URL"];
    $mailURL = $config["MAIL_URL"];
?>
        <header>
			<div class="logoWrap">
				<h1><a href="/"><img src="/assets/images/head/head_logo.svg" alt="<?= $siteName; ?> | <?= $companyName; ?>"></a></h1>
			</div>

			<div class="menuWrap">
				<div class="contactWrap">
					<div class="title">
						<p>Get a Free Consultation</p>
					</div>
					<div class="telWrap boxWrap">
						<a href="<?= $tel; ?>"><span class="icon"><img src="/assets/images/head/icon_tel.svg" alt="TEL"></span><span class="text"><?= $telHyphen; ?></span></a>
					</div>
					<div class="mailWrap boxWrap">
						<a href="<?= $mailURL; ?>"><span class="icon"><img src="/assets/images/head/icon_mail.svg" alt="Mail"></span><span class="text">Mail</span></a>
					</div>
					<div class="lineWrap boxWrap">
						<a href="<?= $lineURL; ?>" target="_blank"><span class="icon"><img src="/assets/images/head/icon_line.svg" alt="LINE"></span></a>
					</div>
				</div>
			</div>
		</header>