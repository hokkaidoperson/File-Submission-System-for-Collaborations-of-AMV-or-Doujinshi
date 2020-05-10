<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "Internet Explorerは、なぜ推奨環境外になっているのですか？";
$pron = "インターネットエクスプローラーはなぜすいしょうかんきょうがいになっているのですか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般"
);

$content = <<<EOT
<p>昨今のインターネットブラウザを取り巻く情勢を踏まえ、Internet Explorerをサポート対象外とする事にしました。</p>
<p>Windows 10には標準ブラウザとしてMicrosoft Edgeが搭載されており、Microsoft側も、Internet Explorerでない他のブラウザを利用する事を推奨しています。<br>
また、Windows 7、Windows 8（8.1）をご利用の方も、Microsoft Edgeやその他ブラウザをインストールして利用する事が出来ます。</p>
EOT;
