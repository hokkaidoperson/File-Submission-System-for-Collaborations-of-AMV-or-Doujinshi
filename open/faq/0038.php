<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "システムの利用中に不具合を発見しました。どうすればいいですか？";
$pron = "システムのりようちゅうにふぐあいをはっけんしましたどうすればいいですか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般",
    "お問い合わせ"
);

$faqcontent_ver = VERSION;

$content = <<<EOT
<p>この度はご不便をお掛けし、誠に申し訳ございません。<br>
システム制作者にて確認・修正させて頂きたく存じますので、お手数ですが不具合に関する詳細をお伝え頂けますと幸いです。</p>
<p>なお、不具合報告の際は、システム制作者の手元で状況の検証・再現を行いやすくするため、下記についてなるべく詳細な情報をご提供頂けますと幸いです。</p>
<ul>
<li>不具合発生前に行った操作</li>
<li>起こっている現象・表示されているエラーメッセージ</li>
<li>システムのバージョン（本ポータルサイトに使われているシステムのバージョンは <code>{$faqcontent_ver}</code> です。）</li>
<li>ご利用中の端末・OS・ブラウザ</li>
</ul>
<h2>不具合報告先</h2>
<h3>メール</h3>
<p>システム制作者の個人メールアドレスは以下の通りです。<br>
<code>dosankomali【アットマーク】yahoo.co.jp</code>（<code>【アットマーク】</code> を <code>@</code> に直して下さい。）</p>
<h3>Twitter</h3>
<p>システム制作者の個人Twitterアカウントはこちらです。<br>
<a href="https://twitter.com/YukkuriDosanko" target="_blank" rel="noopener">@YukkuriDosanko</a></p>
<h3>GitHub</h3>
<p>GitHubのアカウントをお持ちの方は、MAD合作・合同誌向けファイル提出システムのGitにトピックを作成して不具合報告して頂いても構いません。<br>
<a href="https://github.com/hokkaidoperson/File-Submission-System-for-Collaborations-of-AMV-or-Doujinshi/security" target="_blank" rel="noopener">セキュリティ面の不具合・脆弱性に関する件はこちら</a><br>
<a href="https://github.com/hokkaidoperson/File-Submission-System-for-Collaborations-of-AMV-or-Doujinshi/issues" target="_blank" rel="noopener">その他、一般的な不具合に関する件はこちら</a></p>
EOT;
