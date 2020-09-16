<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "FAQに載っていない事について問い合わせたいのですが。";
$pron = "エフエーキューにのっていないことについてといあわせたいのですが";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般",
    "お問い合わせ"
);

$content = <<<EOT
<p>システム制作者へのお問い合わせは下記よりお願い致します。</p>
<p class="small text-muted">※イベントの規則等に関しては、システム制作者でなく、イベントの主催者に直接お問い合わせ願います。</p>
<h3>メール</h3>
<p>システム制作者の個人メールアドレスは以下の通りです。<br>
<code>dosankomali【アットマーク】yahoo.co.jp</code>（<code>【アットマーク】</code> を <code>@</code> に直して下さい。）</p>
<h3>Twitter</h3>
<p>システム制作者の個人Twitterアカウントはこちらです。<br>
<a href="https://twitter.com/YukkuriDosanko" target="_blank" rel="noopener">@YukkuriDosanko</a></p>
<p class="small text-muted">※Twitterのリプライは他人に公開されます。お問い合わせの内容を他人に知られたくない場合は、Twitterのダイレクトメッセージもしくはメールでお問い合わせ願います。</p>
EOT;
