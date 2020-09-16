<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "JavascriptとCookieはどのような用途で使用しますか？";
$pron = "ジャバスクリプトエイジャックスとクッキーはどのようなようとでしようしますか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般"
);

$content = <<<EOT
<h3>Javascript</h3>
<p>画面表示、入力内容の検証等に使用します。また、使用済みユーザーIDの検索等、サーバーとのやり取りを円滑にする目的もあります。</p>
<h3>Cookie</h3>
<p>ログイン中のユーザーの識別に使用します。当サイトが他サイトのCookie情報を読み書きする事はありません。</p>
<p class="small text-muted">※reCAPTCHAが有効になっている場合、reCAPTCHAの機能のためにGoogleのCookie情報が読み書きされる事があります。</p>
EOT;
