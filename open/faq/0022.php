<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "ファイル提出の方法を教えて下さい。";
$pron = "ファイルていしゅつのほうほうをおしえてください";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "マイページ",
    "ファイル提出"
);

$content = <<<EOT
<p><a href="guide/submission.php">こちらのガイド</a>をご覧下さい。</p>
EOT;
