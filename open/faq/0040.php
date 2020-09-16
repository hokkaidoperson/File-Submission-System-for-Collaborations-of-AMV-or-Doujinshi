<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "システムの利用条件（クレジット表記、利用料など）はありますか？";
$pron = "システムのりようじょうけんクレジットひょうきりようりょうなどはありますか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般",
    "お問い合わせ"
);

$content = <<<EOT
<p>いいえ。クレジット表記は任意です。また、本システムの利用にあたって、システム制作者が利用料を徴収する事はございません。</p>
<h3>クレジット表記について（任意）</h3>
<p>イベントの成果物（動画や合同誌など）に本システムをクレジット表記して下さる場合、提出システムの紹介ページのURL <code><a href="https://www.hkdyukkuri.space/filesystem/" target="_blank" rel="noopener">https://www.hkdyukkuri.space/filesystem/</a></code> を併記して下さると幸いです。</p>
<h3>システム制作者へのプレゼントについて（任意）</h3>
<p>イベントの成果物（合同誌など）や、その他物品、金券等をプレゼントして下さると、システム開発の励みになります。</p>
<h4>投げ銭・ギフトカード</h4>
<p>投げ銭・ギフトカードをプレゼントして下さる場合は、以下のいずれかの方法でお送り下さい。<br>
金額は幾らでも構いません。また、事前連絡は不要です。<br>
システム制作者の連絡先については<a href="faq_read.php?id=0039">こちらのFAQ</a>をご覧下さい。</p>
<ul>
    <li><b><a href="https://paypay.ne.jp/guide/send/" target="_blank" rel="noopener">PayPayの送金機能</a></b><br>
    上記リンク先のページに記載の「2. 携帯電話番号/PayPay ID宛てに送る」もしくは「3. 受け取りリンクを作成して送る」のいずれかの方法をご利用下さい（PayPay IDは <code>hokkaidoyukkuri</code> です）。</li>
    <li><b><a href="https://pay.rakuten.co.jp/guide/send_receive/" target="_blank" rel="noopener">楽天ペイの送金機能</a></b><br>
    上記リンク先のページに記載の「連絡先に入っていない相手に送る場合」の方法をご利用下さい。</li>
    <li><b><a href="https://www.amazon.co.jp/%E3%82%AE%E3%83%95%E3%83%88%E5%88%B8/b?ie=UTF8&node=2351652051" target="_blank" rel="noopener">Amazonギフト券</a></b><br>
    Eメールタイプのギフト券をお送り下さい。</li>
</ul>
<h4>イベントの成果物（合同誌など）や、その他の贈答品</h4>
<p>イベントの成果物やその他の贈答品をプレゼントして下さる場合は、事前にシステム制作者宛にご連絡・ご相談願います。<br>
システム制作者の連絡先については<a href="faq_read.php?id=0039">こちらのFAQ</a>をご覧下さい。</p>
EOT;
