<?php
// php/contact-submit.php
// 文字コードは UTF-8 で保存してください。

declare(strict_types=1);

// ── 設定 ───────────────────────────────────────
$TO_EMAILS = [
  'tamada@dmn-co.com',
  'mementomori@benri-yaruki.com',
  'info@shonan-anshin.com'
];            // 受信先
$FROM_EMAIL = 'info@shonan-anshin.com'; // envelope & From
$SITE_NAME  = 'Yaruki Group Co., Ltd.';
$THANKS_URL = '/contact/thanks';
$ALLOW_TOPICS = ['quote','support','general','moving','disposal','cleaning','storage','other'];

// ── 前処理（送信制御）────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /', true, 303); exit;
}
session_start();

// 簡易レート制限（同一セッション 30秒）
$now = time();
if (isset($_SESSION['last_submit_ts']) && ($now - (int)$_SESSION['last_submit_ts']) < 30) {
  http_response_code(429);
  echo 'Please wait a moment before submitting again.'; exit;
}
$_SESSION['last_submit_ts'] = $now;

// Content-Type チェック（任意：マルチパートのみ許可）
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === false) {
  http_response_code(400);
  echo 'Invalid content type.'; exit;
}

// ── 受け取り & サニタイズ ───────────────────────
$raw = fn(string $k) => isset($_POST[$k]) ? (string)$_POST[$k] : '';
$stripCRLF = fn(string $s) => preg_replace("/[\r\n]+/", ' ', $s); // header injection 対策

$topic      = $stripCRLF(trim($raw('topic')));
$name       = $stripCRLF(trim($raw('name')));
$company    = $stripCRLF(trim($raw('company')));
$email      = $stripCRLF(trim($raw('email')));
$tel        = $stripCRLF(trim($raw('tel')));
$in_japan   = $raw('in_japan') === 'no' ? 'no' : 'yes'; // 既定 yes
$zipcode    = $stripCRLF(trim($raw('zipcode')));
$prefecture = $stripCRLF(trim($raw('prefecture')));
$city       = $stripCRLF(trim($raw('city')));
$street     = $stripCRLF(trim($raw('street')));
$subjectIn  = $stripCRLF(trim($raw('subject')));

// ★ 先に message を受け取ってから、hiddenで積んだ改行エンティティを復元
$messageIn  = trim($raw('message'));
$messageIn  = str_replace(['&#13;','&#10;'], ["\r","\n"], $messageIn);

$replyVia   = isset($_POST['reply_via']) && is_array($_POST['reply_via']) ? array_map('strval', $_POST['reply_via']) : [];
$honeypot   = trim($raw('website')); // 画面に見えないハニーポット


/// ── CSRF 検証 ───────────────────────────────────
$postedToken = $raw('csrf_token');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
  http_response_code(419);
  echo 'Invalid session token.'; exit;
}
unset($_SESSION['csrf_token']); // 使い切り


// ── バリデーション ──────────────────────────────
$errors = [];

// ハニーポット
if ($honeypot !== '') { $errors[] = 'Spam detected.'; }

// 必須
if (!in_array($topic, $ALLOW_TOPICS, true)) { $errors[] = 'Select a valid inquiry type.'; }
if ($name === '')                            { $errors[] = 'Enter your name.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Enter a valid email.'; }
if ($subjectIn === '')                       { $errors[] = 'Enter subject.'; }
if ($messageIn === '')                       { $errors[] = 'Enter message.'; }

// 文字数（過剰入力抑止）
if (mb_strlen($name) > 100)       { $errors[] = 'Name is too long.'; }
if (mb_strlen($company) > 120)    { $errors[] = 'Company is too long.'; }
if (mb_strlen($subjectIn) > 160)  { $errors[] = 'Subject is too long.'; }
if (mb_strlen($messageIn) > 5000) { $errors[] = 'Message is too long.'; }


// 任意項目の形式
if ($tel !== '' && !preg_match('/^[0-9+\-() ]{8,20}$/', $tel)) {
  $errors[] = 'Enter a valid phone number.';
}

// 日本在住のときだけ住所必須
if ($in_japan === 'yes') {
  if (!preg_match('/^[0-9]{3}-?[0-9]{4}$/', $zipcode)) { $errors[] = 'Enter a valid Japanese postal code.'; }
  if ($prefecture === '') { $errors[] = 'Enter prefecture.'; }
  if ($city === '')       { $errors[] = 'Enter city/ward.'; }
}

// CSRF（簡易：Referer/Originを自ドメインに限定：必要に応じて強化）
$originOk = false;
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host) {
  $origin  = $_SERVER['HTTP_ORIGIN']  ?? '';
  $referer = $_SERVER['HTTP_REFERER'] ?? '';
  if (($origin && str_contains($origin, $host)) || ($referer && str_contains($referer, $host))) {
    $originOk = true;
  }
}
// 本番で厳しくするなら以下を有効化
// if (!$originOk) { $errors[] = 'Cross-site submission not allowed.'; }

// エラー返却
if ($errors) {
  // POSTデータをセッションに保存して edit.php で復元
  $_SESSION['form_data'] = [
    'topic'      => $topic,
    'name'       => $name,
    'company'    => $company,
    'email'      => $email,
    'tel'        => $tel,
    'in_japan'   => $in_japan,
    'zipcode'    => $zipcode,
    'prefecture' => $prefecture,
    'city'       => $city,
    'street'     => $street,
    'subject'    => $subjectIn,
    'message'    => $messageIn,
    'privacy'    => $_POST['privacy'] ?? '',
    'reply_via'  => $replyVia,
  ];
  $_SESSION['form_errors'] = $errors;

  // JSON 送信（フェッチ等）の場合は即返却
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
     (($_SERVER['HTTP_ACCEPT'] ?? '') && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(400);
    echo json_encode(['ok' => false, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 通常フォーム送信は edit.php に戻す
  header('Location: /contact/edit.php', true, 303);
  exit;
}

// ── メール作成 ─────────────────────────────────
$nl = "\r\n";
$date = date('Y-m-d H:i:s');

$envelopeParam = "-f{$FROM_EMAIL}";

$addressBlock = ($in_japan === 'yes')
  ? "Zip: {$zipcode}{$nl}Prefecture: {$prefecture}{$nl}City/Ward: {$city}{$nl}Street/Building: {$street}{$nl}"
  : "(User currently outside Japan){$nl}";

$replyViaStr = $replyVia ? implode(', ', $replyVia) : '—';

$body =
"New contact from {$SITE_NAME}{$nl}{$nl}" .
"Date: {$date}{$nl}" .
"Topic: {$topic}{$nl}" .
"Name: {$name}{$nl}" .
"Company: {$company}{$nl}" .
"Email: {$email}{$nl}" .
"Tel: {$tel}{$nl}{$nl}" .
"Currently in Japan: {$in_japan}{$nl}" .
$addressBlock .
"Preferred contact: {$nl}{$replyViaStr}{$nl}{$nl}" .
"Subject: {$subjectIn}{$nl}{$nl}" .
"Message:{$nl}{$messageIn}{$nl}";

// 件名
$subject = "[Contact] {$subjectIn}";

// ヘッダ
$from    = $FROM_EMAIL; // 表示名を付けるなら: "\"{$SITE_NAME}\" <{$FROM_EMAIL}>"
$replyTo = $email;
$headers = [];
$headers[] = "From: {$from}";
$headers[] = "Reply-To: {$replyTo}";
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-Type: text/plain; charset=UTF-8";
$headers[] = "Content-Transfer-Encoding: 8bit";
$headerStr = implode($nl, $headers);

// ── 複数宛先にループ送信 ───────────────────────
if (function_exists('mb_language')) { @mb_language('uni'); }
if (function_exists('mb_internal_encoding')) { @mb_internal_encoding('UTF-8'); }

$sentAll = true;
foreach ($TO_EMAILS as $to) {
  $to = trim($to);
  if (!filter_var($to, FILTER_VALIDATE_EMAIL)) continue; // 不正アドレススキップ

  $ok = false;
  if (function_exists('mb_send_mail')) {
    $ok = @mb_send_mail($to, $subject, $body, $headerStr, "-f{$FROM_EMAIL}");
  } else {
    $ok = @mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $body, $headerStr, "-f{$FROM_EMAIL}");
  }

  if (!$ok) {
    error_log("[CONTACT] failed to send to {$to}");
    $sentAll = false;
  }
}

if (!$sentAll) {
  http_response_code(500);
  echo 'Failed to send to one or more recipients.'; exit;
}



/* =========================
   オート返信（送信者側）
   - From/Reply-To ともに自ドメイン
   - Auto-Submitted, Message-ID, Date を付与
========================= */
$autoSubject = "Thank you for contacting {$SITE_NAME}";
$autoBody =
"Dear {$name},{$nl}{$nl}"
."Thank you for reaching out to us. We have received your inquiry as follows:{$nl}{$nl}"
."--------------------------------------{$nl}"
."Topic: {$topic}{$nl}"
."Name: {$name}{$nl}"
."Company: {$company}{$nl}"
."Email: {$email}{$nl}"
."Tel: {$tel}{$nl}"
."Subject: {$subjectIn}{$nl}{$nl}"
."Message:{$nl}{$messageIn}{$nl}"
."--------------------------------------{$nl}{$nl}"
."Our representative will get back to you shortly.{$nl}"
."If this message was sent in error, please disregard it.{$nl}{$nl}"
."Best regards,{$nl}"
."{$SITE_NAME}{$nl}"
."{$FROM_EMAIL}{$nl}"
."--------------------------------------{$nl}";

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $autoHeaders = [];
  $displayName = '=?UTF-8?B?' . base64_encode($SITE_NAME) . '?=';
  $autoHeaders[] = "From: {$displayName} <{$FROM_EMAIL}>";
  $autoHeaders[] = "Reply-To: {$FROM_EMAIL}";
  $autoHeaders[] = "MIME-Version: 1.0";
  $autoHeaders[] = "Content-Type: text/plain; charset=UTF-8";
  $autoHeaders[] = "Content-Transfer-Encoding: 8bit";
  $autoHeaders[] = "Date: " . date('r');
  $autoHeaders[] = "Message-ID: <" . uniqid('', true) . "@shonan-anshin.com>";
  $autoHeaders[] = "Auto-Submitted: auto-replied";
  $autoHeaderStr = implode($nl, $autoHeaders);

  $autoSubjectEnc = '=?UTF-8?B?' . base64_encode($autoSubject) . '?=';

  $sentAuto = false;
  if (function_exists('mb_send_mail')) {
    $sentAuto = @mb_send_mail($email, $autoSubject, $autoBody, $autoHeaderStr, $envelopeParam);
    if (!$sentAuto) { $sentAuto = @mb_send_mail($email, $autoSubject, $autoBody, $autoHeaderStr); }
  } else {
    $sentAuto = @mail($email, $autoSubjectEnc, $autoBody, $autoHeaderStr, $envelopeParam);
    if (!$sentAuto) { $sentAuto = @mail($email, $autoSubjectEnc, $autoBody, $autoHeaderStr); }
  }
  /*
  サーバーにログを残す
  if ($sentAuto) {
    error_log('[CONTACT] autoresponder sent OK to '.$email);
  } else {
    error_log('[CONTACT] autoresponder send failed to '.$email);
  } */
}


// 後片付け
unset($_SESSION['form_data'], $_SESSION['form_errors']);

// ── 完了レスポンス ───────────────────────────────
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (($_SERVER['HTTP_ACCEPT'] ?? '') && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE); exit;
}

// ふつうのフォーム送信はサンクスページへ
header('Location: ' . $THANKS_URL, true, 303);
exit;