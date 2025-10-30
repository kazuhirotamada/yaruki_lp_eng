<?php
// app/contact_common.php
declare(strict_types=1);

// ▼ フォーム定義
$TOPIC_LABELS = [
  'quote'    => 'Request a Quote',
  'support'  => 'Support',
  'general'  => 'General Inquiry',
  'moving'   => 'Moving to Japan',
  'disposal' => 'Unwanted Items / Disposal',
  'cleaning' => 'Move-out Cleaning',
  'storage'  => 'Short-term Storage',
  'other'    => 'Other',
];
$ALLOW_TOPICS = array_keys($TOPIC_LABELS);


function topic_label(string $topic, array $labels): string {
  return $labels[$topic] ?? $topic;
}