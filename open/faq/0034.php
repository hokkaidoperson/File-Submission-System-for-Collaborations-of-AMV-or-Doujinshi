<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "ポータルサイトの推奨環境を教えて下さい。";
$pron = "ポータルサイトのすいしょうかんきょうをおしえてください";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般"
);

$content = <<<EOT
<p>ポータルサイトを利用するには、<b>Javascript、Cookie</b>がブラウザ上でサポート・有効化されている必要があります。<br>
また、以下のブラウザを、推奨環境としてサポートしています（いずれも最新版の利用を想定しています）。</p>
<ul>
<li>Google Chrome</li>
<li>Microsoft Edge</li>
<li>Mozilla Firefox</li>
<li>Opera</li>
<li>Safari</li>
</ul>
<p class="small text-muted">※上記ブラウザであっても、サポートを終了した端末・ブラウザの組み合わせ（Windows版Safariなど）においては動作を保証しません。<br>※Operaは<a href="https://support.google.com/recaptcha/?hl=en#6223828" target="_blank" rel="noopener">reCAPTCHAのサポート対象</a>外となっているため、reCAPTCHAがOpera上で正常に動作しない場合、お手数ですが他のブラウザをご利用願います。<br>※<b>Internet Explorerは推奨環境外とさせて頂きます</b>。詳しくは<a href="faq_read.php?id=0036">こちらのFAQ</a>をご覧下さい。</p>
<p>推奨環境のブラウザで正常に動作しない場合は、システムの不具合として対応致します。推奨環境以外のブラウザで正常に動作しない場合は、システムの不具合とは見做さない場合があります。</p>
EOT;
