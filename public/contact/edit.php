<?php
// confirm.php
require_once __DIR__ . '/../app/sections.php';  // slotを利用してframe.phpの中にコンテンツだけを埋め込む

//configを取得
require_once __DIR__ . '/../include/config.php';
$config = loadConstants();

$companyName = $config["COMPANY_NAME"];
$mailAddress = $config["EMAIL"];
$zipcode = $config["ZIPCODE"];
$prefEng = $config["PREF_ENG"];
$wardEng = $config["WARD_ENG"];
$addr2Eng = $config["ADDR_2_ENG"];
$addr1Eng = $config["ADDR_1_ENG"];

//セッションスタート
session_start();

$defaults = [
  'topic'=>'','name'=>'','company'=>'','email'=>'','tel'=>'',
  'in_japan'=>'yes','zipcode'=>'','prefecture'=>'','city'=>'','street'=>'',
  'subject'=>'','message'=>'','privacy'=>''
];

// 1) POSTがあればそれを採用（confirmからの戻りや手打ち遷移に対応）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = array_merge($defaults, $_POST);
  $data['reply_via'] = isset($_POST['reply_via']) && is_array($_POST['reply_via']) ? $_POST['reply_via'] : [];
  // 次の遷移用にセッションにも保存しておく（任意）
  $_SESSION['form_data'] = $data;
  $errors = []; // POSTで戻った時点ではエラーは無し
} else {
  // 2) セッションに残っていれば復元
  $saved = $_SESSION['form_data'] ?? [];
  $data  = array_merge($defaults, $saved);
  $errors = $_SESSION['form_errors'] ?? [];
  unset($_SESSION['form_errors']); // 使い切り
}


// --- サニタイズ関数 ---
function esc($v) {
  return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function err_has(string $key): bool {
  return !empty($GLOBALS['errors'][$key]);
}
function err_msg(string $key): string {
  return htmlspecialchars($GLOBALS['errors'][$key] ?? '', ENT_QUOTES, 'UTF-8');
}
function err_id(string $key): string {
  return 'err-' . $key;
}
function err_class(string $key): string {
  return err_has($key) ? ' is-invalid' : '';
}
function aria_invalid(string $key): string {
  return err_has($key) ? ' aria-invalid="true" aria-describedby="'.err_id($key).'"' : '';
}


/****

ここからテンプレート

******/

    $pageSlug = 'contact';

?>

<?php
  // head 追記があれば（任意）
  start_section('head'); ?>
  <meta name="robots" content="noindex">
  <link rel="stylesheet" href="/contact/css/form.css">
<?php end_section('head'); ?>

<?php
// ★ ここからが本文スロット（frame.php の <main> に入る）★
start_section('content'); ?>

<?php if ($errors): ?>
  <div class="errorBox">
    <p><strong>Please correct the following errors:</strong></p>
    <ul>
      <?php foreach ($errors as $err): ?>
        <li><?= esc($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

  
<div class="formWrapper">

    <div class="descWrap">
        <p class="bold">We welcome your inquiries.</p>
        <p>Please fill in all required fields marked with an asterisk (<span class="asterisk">*</span>).</p>
    </div>

  <form action="/contact/confirm.php" method="post" enctype="multipart/form-data" accept-charset="UTF-8" >
    
    <!-- 1) Inquiry Type -->
    <div class="<?= err_class('topic') ?>">
      <label for="topic">Inquiry Type <span aria-hidden="true" class="asterisk">*</span></label>
      <select id="topic" name="topic" required>
        <option value="" disabled <?= empty($data['topic']) ? 'selected' : '' ?>>Please select</option>
        <option value="quote" <?= ($data['topic'] ?? '')==='quote' ? 'selected' : '' ?>>Request a Quote</option>
        <option value="support" <?= ($data['topic'] ?? '')==='support' ? 'selected' : '' ?>>Support</option>
        <option value="general" <?= ($data['topic'] ?? '')==='general' ? 'selected' : '' ?>>General Inquiry</option>
        <option value="moving" <?= ($data['topic'] ?? '')==='moving' ? 'selected' : '' ?>>Moving to Japan</option>
        <option value="disposal" <?= ($data['topic'] ?? '')==='disposal' ? 'selected' : '' ?>>Unwanted Items / Disposal</option>
        <option value="cleaning" <?= ($data['topic'] ?? '')==='cleaning' ? 'selected' : '' ?>>Move-out Cleaning</option>
        <option value="storage" <?= ($data['topic'] ?? '')==='storage' ? 'selected' : '' ?>>Short-term Storage</option>
        <option value="other" <?= ($data['topic'] ?? '')==='other' ? 'selected' : '' ?>>Other</option>
      </select>
      <?php if (err_has('topic')): ?>
        <p id="<?= err_id('topic') ?>" class="errorText"><?= err_msg('topic') ?></p>
        <?php endif; ?>
    </div>

    <!-- 2) Name -->
    <div class="<?= err_class('name') ?>">
      <label for="name">Your Name <span aria-hidden="true" class="asterisk">*</span></label>
      <input id="name" name="name" type="text" inputmode="text" autocomplete="name" required
             value="<?= esc($data['name'] ?? '') ?>"  placeholder="e.g. John Smith" maxlength="100" />
        <?php if (err_has('name')): ?>
            <p id="<?= err_id('name') ?>" class="errorText"><?= err_msg('name') ?></p>
        <?php endif; ?>
    </div>

    <!-- 3) Company (optional) -->
    <div>
      <label for="company">Company (optional)</label>
      <input id="company" name="company" type="text" value="<?= esc($data['company'] ?? '') ?>" inputmode="text" autocomplete="organization"
             placeholder='e.g. <?= $companyName; ?>' maxlength="120" />
    </div>

    <!-- 4) Email -->
    <div class="<?= err_class('email') ?>">
      <label for="email">Email Address <span aria-hidden="true" class="asterisk">*</span></label>
      <input id="email" name="email" type="email" value="<?= esc($data['email'] ?? '') ?>" autocomplete="email" required
             placeholder='e.g. <?= $mailAddress; ?>' maxlength="254" />
        <?php if (err_has('email')): ?>
            <p id="<?= err_id('email') ?>" class="errorText"><?= err_msg('email') ?></p>
        <?php endif; ?>
    </div>

    <!-- 5) Phone (optional) -->
    <div class="<?= err_class('tel') ?>">
      <label for="tel">Phone Number (optional)</label>
      <input
        id="tel"
        name="tel"
        type="tel" 
        value="<?= esc($data['tel'] ?? '') ?>"
        inputmode="tel"
        placeholder="e.g. +81-90-1234-5678 or 090-1234-5678"
        pattern="^[0-9()+\- ]{8,20}$"
        title="Use only digits, +, ( ), hyphen, and spaces (8-20 characters)."
        maxlength="20"
        />
    
        <?php if (err_has('tel')): ?>
            <p id="<?= err_id('tel') ?>" class="errorText"><?= err_msg('tel') ?></p>
        <?php endif; ?>
      <small>Include country code if outside Japan.</small>
    </div>

    <div class="addressWrapper">
        <!-- Current Residence (controls address requirement) -->
        <fieldset class="<?= err_class('in_japan') ?>">
            <legend>Current Residence</legend>

            <div class="radios">
                <div class="radioUnit">
                    <input class="input-visually-hidden" id="inJP-yes" type="radio" name="in_japan" value="yes" <?= ($data['in_japan'] ?? 'yes')==='yes' ? 'checked' : '' ?> />
                    <label class="radio" for="inJP-yes"> I currently live in Japan</label>
                </div>

                <div class="radioUnit">
                    <input class="input-visually-hidden" id="inJP-no" type="radio" name="in_japan" value="no" <?= ($data['in_japan'] ?? '')==='no' ? 'checked' : '' ?> />
                    <label class="radio" for="inJP-no"> I currently live outside Japan</label>
                </div>
            </div>
            <?php if (err_has('in_japan')): ?>
                <p id="<?= err_id('in_japan') ?>" class="errorText"><?= err_msg('in_japan') ?></p>
            <?php endif; ?>
        </fieldset>

        <!-- 6) Address (shown/required only when in Japan = yes) -->
        <div id="addressFields" class="mt25" style="display:<?= ($data['in_japan'] ?? 'yes')==='yes' ? 'block' : 'none' ?>;">
        <div>
            <label for="zipcode">Zip Code (Japan) (optional)</label>
            <input id="zipcode" name="zipcode" type="text" 
                    value="<?= esc($data['zipcode'] ?? '') ?>"
                inputmode="numeric" pattern="^[0-9]{3}-?[0-9]{4}$"
                placeholder='e.g. <?= $zipcode; ?>' maxlength="8"
                autocomplete="postal-code" />
            <small>If you already have a Japanese address, please enter it.</small>
        </div>

        <div>
            <label for="prefecture">Prefecture (optional)</label>
            <input id="prefecture" name="prefecture" type="text" value="<?= esc($data['prefecture'] ?? '') ?>" placeholder='e.g. <?= $prefEng; ?>'
                autocomplete="address-level1" />
        </div>

        <div>
            <label for="city">City / Ward (optional)</label>
            <input id="city" name="city" type="text" value="<?= esc($data['city'] ?? '') ?>" placeholder='e.g. <?= $addr2Eng . ', ' . $wardEng; ?>'
                autocomplete="address-level2" />
        </div>

        <div>
            <label for="street">Street / Building (optional)</label>
            <input id="street" name="street" type="text" 
                    value="<?= esc($data['street'] ?? '') ?>"
                placeholder='e.g. <?= $addr1Eng; ?>'
                autocomplete="address-line1" />
        </div>
        </div>

    </div>


    <!-- 7) Subject -->
    <div class="<?= err_class('subject') ?>">
      <label for="subject">Subject <span aria-hidden="true" class="asterisk">*</span></label>
      <input id="subject" name="subject" type="text" value="<?= esc($data['subject'] ?? '') ?>" required placeholder="Enter subject" maxlength="120" />
      <?php if (err_has('subject')): ?>
            <p id="<?= err_id('subject') ?>" class="errorText"><?= err_msg('subject') ?></p>
        <?php endif; ?>
    </div>

    <!-- 8) Message -->
    <div class="<?= err_class('message') ?>">
      <label for="message">Message <span aria-hidden="true" class="asterisk">*</span></label>
      <textarea id="message" name="message" rows="8" required
                placeholder="Please describe your inquiry in detail" maxlength="5000"><?= esc($data['message'] ?? '') ?></textarea>
        <?php if (err_has('message')): ?>
            <p id="<?= err_id('message') ?>" class="errorText"><?= err_msg('message') ?></p>
        <?php endif; ?>
    </div>

    <!-- 9) Preferred Contact Method (optional) -->
    <fieldset>
      <legend>Preferred Contact Method (optional)</legend>

      <div class="checkboxes">
        <div class="checkUnit">
            <input type="checkbox" name="reply_via[]" value="Email" <?= in_array('Email', $data['reply_via'] ?? []) ? 'checked' : '' ?> id="via-email" class="input-visually-hidden" />
            <label class="checkbox" for="via-email"> Email</label>
        </div>

        <div class="checkUnit">
            <input type="checkbox" name="reply_via[]" value="Phone" <?= in_array('Phone', $data['reply_via'] ?? []) ? 'checked' : '' ?> id="via-phone" class="input-visually-hidden" />
            <label for="via-phone" class="checkbox"> Phone</label>
        </div>
        
        <div class="checkUnit">
            <input type="checkbox" name="reply_via[]" value="LINE" <?= in_array('LINE', $data['reply_via'] ?? []) ? 'checked' : '' ?> id="via-line" class="input-visually-hidden" />
            <label class="checkbox" for="via-line"> LINE</label>
        </div>
      </div>
    </fieldset>

    <!-- 10) Privacy Policy Agreement -->
    <div class="<?= err_class('privacy') ?>">

        <div class="checkUnit">
            <input type="checkbox" name="privacy" value="agree"  required  <?= ($data['privacy'] ?? '')==='agree' ? 'checked' : '' ?>  id="privacycheck" class="input-visually-hidden"/>
            <label for="privacycheck" class="checkbox">
                I agree to the handling of personal information <span aria-hidden="true" class="asterisk">*</span><br />
                <small><a href="/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a></small>

                <?php if (err_has('privacy')): ?>
                    <p id="<?= err_id('privacy') ?>" class="errorText"><?= err_msg('privacy') ?></p>
                <?php endif; ?>
            </label>
        
        </div>
    </div>

    <!-- 11) Spam Protection (Honeypot) -->
    <div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
      <label for="website">If you are human, leave this field empty.</label>
      <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" />
    </div>

    <!-- 12) Submit Button -->
    <div class="tc">
      <button type="submit">Confirm</button>
    </div>

    <!-- Recommended: CSRF token (handled on backend) -->
    <!-- <input type="hidden" name="csrf_token" value="..."> -->
  </form>
</div>

<script src="/contact/js/form-address.js" defer></script>



<?php end_section('content'); ?>

<?php
// 必要なら scripts スロットも
start_section('scripts'); ?>
  <script>/* confirm page only js */</script>
<?php end_section('scripts'); ?>


<?php
// 最後にレイアウトを読んで描画
require __DIR__ . '/../include/frame/frame.php';