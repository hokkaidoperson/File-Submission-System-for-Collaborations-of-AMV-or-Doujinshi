<?php
require_once('../../set.php');
session_start();
$titlepart = 'ブラックリスト・アクセス制限';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'ban_user_rem') {
    echo '<p><div class="border border-success" style="padding:10px;">
選択したユーザーのアカウントの凍結を解除しました。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'ban_user_add') {
    echo '<p><div class="border border-success" style="padding:10px;">
選択したユーザーのアカウントを凍結しました。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'ban_ip') {
    echo '<p><div class="border border-success" style="padding:10px;">
アクセス制限の設定を変更しました。
</div></p>';
    $_SESSION["situation"] = '';
}

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

?>

<h1>ブラックリスト・アクセス制限</h1>
<p>不適切な作品投稿を繰り返す、イベント運営の妨害をする、などの行為があり、解決しない場合は、本機能を用いてアカウントを凍結出来ます。<br>
また、凍結されたアカウントとは別のアカウントを作成していたずらを続ける者がいる場合は、当該ユーザーのIPアドレス（もしくはリモートホスト）を基にアクセス制限を行う事が可能です。</p>
<p><b>この機能は無闇に使わず、最終手段としてご利用下さい。</b></p>
<div class="row">
<a href="blackuser.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
アカウントの凍結・凍結解除
</div>
</div>
</a>
<a href="blackip.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
IPアドレス・リモートホストによるアクセス制限
</div>
</div>
</a>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
