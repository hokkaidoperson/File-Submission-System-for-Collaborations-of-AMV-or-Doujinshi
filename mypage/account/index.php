<?php
require_once('../../set.php');
setup_session();
$titlepart = 'アカウント情報編集';
require_once(PAGEROOT . 'mypage_header.php');
?>

<h1>アカウント情報編集</h1>
<p>アカウント登録時に入力した情報を変更出来ます。</p>
<div class="system-carditems">
<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="pw_unit.php" class="stretched-link">パスワードを変更する</a>
</div>
</div>
<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="others_unit.php" class="stretched-link">ニックネーム・メールアドレスを変更する</a>
</div>
</div>
<?php
if ($_SESSION["state"] == 'p') echo '<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="state_unit.php" class="stretched-link">主催者を辞退する（代わりの主催者を任命する必要があります）</a>
</div>
</div>';
else if ($_SESSION["state"] == 'c') echo '<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="state_unit.php" class="stretched-link">共同運営者を辞退する</a>
</div>
</div>';
else if ($_SESSION["admin"]) {
    if ($_SESSION["state"] == 'o') echo '<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="state_unit.php" class="stretched-link">非参加者→一般参加者　に変更する</a>
</div>
</div>';
    if ($_SESSION["state"] == 'g') echo '<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="state_unit.php" class="stretched-link">一般参加者→非参加者　に変更する</a>
</div>
</div>';
}
?>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
