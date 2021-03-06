<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "ユーザーIDは後から変更出来ますか？";
$pron = "ユーザーアイディーはあとからへんこうできますか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般",
    "ユーザー登録",
    "アカウント情報編集"
);

$content = <<<EOT
<p>いいえ、ユーザーIDは後から変更出来ません。</p>
<p>システム内部で個人を確実に識別出来るように、ユーザー登録時に決めたユーザーIDは後から変更出来ない仕様となっております。</p>
EOT;
