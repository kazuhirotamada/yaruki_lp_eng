<?php
// confirm.php
require_once __DIR__ . '/../app/sections.php';  // slotを利用してframe.phpの中にコンテンツだけを埋め込む

session_start();

// 1) 受け取り & サニタイズ（必要に応じて厳格化）
function val($key){ return isset($_POST[$key]) ? trim($_POST[$key]) : ''; }
function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$honeypot = val('website');
if ($honeypot !== '') { http_response_code(400); exit('Spam detected.'); }

$data = [
  'topic'      => val('topic'),
  'name'       => val('name'),
  'company'    => val('company'),
  'email'      => val('email'),
  'tel'        => val('tel'),
  'in_japan'   => val('in_japan'),
  'zipcode'    => val('zipcode'),
  'prefecture' => val('prefecture'),
  'city'       => val('city'),
  'street'     => val('street'),
  'subject'    => val('subject'),
  'message'    => val('message'),
  'privacy'    => val('privacy'),
];

// 配列（checkbox）
$reply_via = isset($_POST['reply_via']) && is_array($_POST['reply_via']) ? $_POST['reply_via'] : [];

// 簡易バリデーション（足りなければ追加）
$errors = [];
if ($data['topic'] === '')     $errors['topic'] = 'Please select inquiry type.';
if ($data['name'] === '')      $errors['name'] = 'Please enter your name.';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email is invalid.';
if ($data['subject'] === '')   $errors['subject'] = 'Please enter subject.';
if ($data['message'] === '')   $errors['message'] = 'Please enter message.';
if ($data['privacy'] !== 'agree') $errors['privacy'] = 'You must agree to the privacy policy.';

if ($data['tel'] !== '' && !preg_match('/^[0-9()+\- ]{8,20}$/', $data['tel'])) {
  $errors['tel'] = 'Enter a valid phone number.';
}

if ($data['in_japan'] === 'yes') {
  if (!preg_match('/^[0-9]{3}-?[0-9]{4}$/', $data['zipcode'])) { $errors['zipcode'] = 'Enter a valid Japanese postal code.'; }
  if ($data['prefecture'] === '') { $errors['prefecture'] = 'Enter prefecture.'; }
  if ($data['city'] === '')       { $errors['city'] = 'Enter city/ward.'; }
}

if ($errors) {
  $_SESSION['form_data']   = $data;       // ← $data を保存（サニタイズ済）
  $_SESSION['form_data']['reply_via'] = $reply_via;
  $_SESSION['form_errors'] = $errors;
  header('Location: /contact/edit.php');
  exit;
}

// CSRFトークン（確認→送信の二段階間で利用）
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];
?>




<?php 
/****

ここからテンプレート

******/


    $pageSlug = 'contact';
?>

<?php
  // head 追記があれば（任意）
  start_section('head'); ?>
  <meta name="robots" content="noindex">
<?php end_section('head'); ?>

<?php
// ★ ここからが本文スロット（frame.php の <main> に入る）★
start_section('content'); ?>

  <link rel="stylesheet" href="/contact/css/form.css">

  <div class="confirmWrapper">
    <h2>Confirm your submission</h2>
    <table>
      <tr><th>Inquiry Type</th><td><?=esc($data['topic'])?></td></tr>
      <tr><th>Your Name</th><td><?=esc($data['name'])?></td></tr>
      <tr><th>Company</th><td><?=esc($data['company'])?></td></tr>
      <tr><th>Email</th><td><?=esc($data['email'])?></td></tr>
      <tr><th>Phone</th><td><?=esc($data['tel'])?></td></tr>
      <tr><th>Current Residence</th><td><?=esc($data['in_japan']==='yes'?'Japan':'Outside Japan')?></td></tr>
      <tr><th>Address</th><td>
        <?=esc($data['zipcode'])?><br>
        <?=esc($data['prefecture'])?> <?=esc($data['city'])?><br>
        <?=esc($data['street'])?>
      </td></tr>
      <tr><th>Subject</th><td><?=esc($data['subject'])?></td></tr>
      <tr><th>Message</th><td><pre style="white-space:pre-wrap; margin:0;"><?=esc($data['message'])?></pre></td></tr>
      <tr><th>Preferred Contact</th><td><?=esc(implode(', ', $reply_via))?></td></tr>
    </table>

    <form method="post"
        action="/contact/contact-submit.php"
        class="actions"
        onsubmit="if(event.submitter?.dataset.role==='send'){ this.querySelector('[data-role=send]').disabled=true; }"
        enctype="multipart/form-data"
        accept-charset="UTF-8">


    <?php foreach ($data as $k=>$v): ?>
      <?php if ($k === 'message'): ?>
        <input type="hidden" name="message"
          value="<?= esc(str_replace(["\r","\n"], ['&#13;','&#10;'], $v)) ?>">
      <?php else: ?>
        <input type="hidden" name="<?= esc($k) ?>" value="<?= esc($v) ?>">
      <?php endif; ?>
    <?php endforeach; ?>


    <?php foreach ($reply_via as $v): ?>
      <input type="hidden" name="reply_via[]" value="<?= esc($v) ?>">
    <?php endforeach; ?>

    <!-- honeypot と CSRF -->
    <input type="hidden" name="website" value="">
    <input type="hidden" name="csrf_token" value="<?= esc($csrf) ?>">

      <div class="btnWrapper">
        <!-- 戻る（edit.phpへPOST、バリデーション無視） -->
        <button class="returnBtn" type="submit"
                formaction="/contact/edit.php"
                formmethod="post"
                formnovalidate>
          Back / Edit
        </button>
      
        <!-- 送信（既定の action=/contact/contact-submit.php） -->
        <button class="submitBtn" type="submit" data-role="send">Send</button>

        
      </div>
    
    </form>
  </div>
<?php end_section('content'); ?>

<?php
// 必要なら scripts スロットも
start_section('scripts'); ?>
  <script>/* confirm page only js */</script>
<?php end_section('scripts'); ?>


<?php
// 最後にレイアウトを読んで描画
require __DIR__ . '/../include/frame/frame.php';