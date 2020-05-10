<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "パスワードを忘れてしまいログイン出来なくなりました。";
$pron = "パスワードをわすれてしまいログインできなくなりました";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "ログイン"
);

$content = <<<EOT
<p>パスワード等入力画面の下の「パスワードを忘れてしまった方はこちらから再発行して下さい。」を選択し、パスワードの再発行を行って下さい。</p>
<p><font size="2" class="text-muted">※パスワード再発行に必要な情報を忘れた方は<a href="faq_read.php?id=0011">こちらのFAQ</a>をご覧下さい。</font></p>
EOT;