<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "メッセージ機能の使い方について知りたいです。";
$pron = "メッセージきのうのつかいかたについてしりたいです";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "マイページ",
    "メッセージ機能"
);

$content = <<<EOT
<p><a href="guide/message.php">こちらのガイド</a>をご覧下さい。</p>
EOT;
