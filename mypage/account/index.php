<?php
require_once('../../set.php');
setup_session();
$titlepart = 'アカウント情報編集';
require_once(PAGEROOT . 'mypage_header.php');
?>

<h1>アカウント情報編集</h1>
<p>アカウント登録時に入力した情報を変更出来ます。</p>
<div class="row system-carditems">
<a href="pw_unit.php">
<div class="card system-cardindv">
<div class="card-body">
パスワードを変更する
</div>
</div>
</a>
<a href="others_unit.php">
<div class="card system-cardindv">
<div class="card-body">
ニックネーム・メールアドレスを変更する
</div>
</div>
</a>
<?php
if ($_SESSION["state"] == 'p') echo '<a href="state_unit.php">
<div class="card system-cardindv">
<div class="card-body">
主催者を辞退する（代わりの主催者を任命する必要があります）
</div>
</div>
</a>';
else if ($_SESSION["state"] == 'c') echo '<a href="state_unit.php">
<div class="card system-cardindv">
<div class="card-body">
共同運営者を辞退する
</div>
</div>
</a>';
else if ($_SESSION["admin"]) {
    if ($_SESSION["state"] == 'o') echo '<a href="state_unit.php">
<div class="card system-cardindv">
<div class="card-body">
非参加者→一般参加者　に変更する
</div>
</div>
</a>';
    if ($_SESSION["state"] == 'g') echo '<a href="state_unit.php">
<div class="card system-cardindv">
<div class="card-body">
一般参加者→非参加者　に変更する
</div>
</div>
</a>';
}
?>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
