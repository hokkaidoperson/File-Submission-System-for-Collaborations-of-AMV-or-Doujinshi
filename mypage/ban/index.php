<?php
require_once('../../set.php');
setup_session();
$titlepart = 'ブラックリスト・アカウント作成制限';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

?>

<h1>ブラックリスト・アカウント作成制限</h1>
<p>不適切な作品投稿を繰り返す、イベント運営の妨害をする、などの行為があり、解決しない場合は、本機能を用いてアカウントを凍結出来ます。<br>
また、凍結されたアカウントとは別のアカウントを作成していたずらを続ける者がいる場合は、当該ユーザーのIPアドレス（もしくはリモートホスト名）を基にアカウント作成制限を行う事が可能です。</p>
<p><b>この機能は無闇に使わず、最終手段としてご利用下さい。</b></p>
<div class="row" style="padding:10px;">
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
IPアドレス・リモートホスト名によるアカウント作成制限
</div>
</div>
</a>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
