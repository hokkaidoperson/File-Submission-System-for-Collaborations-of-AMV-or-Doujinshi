<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "ユーザーIDは何に使うのですか？ なぜユーザーIDには半角英数字しか使えないのですか？";
$pron = "ユーザーアイディーはなににつかうのですかなぜユーザーアイディーにははんかくえいすうじしかつかえないのですか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般",
    "ユーザー登録"
);

$content = <<<EOT
<p>ユーザーIDは、システム上で個人を識別するために用います。<br>
プログラム内で使用する文字列であるため、使える文字種に制限を設けており、半角英数字しか使えない仕様となっています。</p>
EOT;
