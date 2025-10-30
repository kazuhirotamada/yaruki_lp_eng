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

<footer>
    <div class="cont">
        <p class="copyright">© <?= date('Y'); ?> <?= $companyName; ?>. All rights reserved.</p>
    </div>
</footer>