<?php
require_once('../../set.php');
session_start();
$titlepart = 'アカウント情報編集';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'pw_changed') {
    echo '<p><div class="border border-success" style="padding:10px;">
パスワードの変更が完了しました。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'others_changed') {
    echo '<p><div class="border border-success" style="padding:10px;">
次の通り、登録情報を変更しました。<br>' . $_SESSION["situation2"] . '</div></p>';
    $_SESSION["situation"] = '';
    $_SESSION["situation2"] = '';
}
if ($_SESSION["situation"] == 'others_nochange') {
    echo '<p><div class="border border-success" style="padding:10px;">
登録情報の変更はありませんでした。</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'state_switcher_mail') {
    echo '<p><div class="border border-primary" style="padding:10px;">
メールを送信しました。手続き完了までしばらくお待ち下さい。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'state_switcher_admin_to_g') {
    echo '<p><div class="border border-primary" style="padding:10px;">
一般参加者に切り替えました。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'state_switcher_admin_to_o') {
    echo '<p><div class="border border-primary" style="padding:10px;">
非参加者に切り替えました。
</div></p>';
    $_SESSION["situation"] = '';
}
?>

<h1>アカウント情報編集</h1>
<p>アカウントに登録されているメールアドレスやパスワードなどを変更出来ます。</p>
<div class="row">
<a href="pw_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
パスワードを変更する
</div>
</div>
</a>
<a href="others_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
パスワード以外（ニックネーム、メールアドレスなど）を変更する
</div>
</div>
</a>
<?php
if ($_SESSION["state"] == 'p') echo '<a href="state_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
主催者を辞退する（代わりの主催者を任命する必要があります）
</div>
</div>
</a>';
else if ($_SESSION["state"] == 'c') echo '<a href="state_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
共同運営者を辞退する
</div>
</div>
</a>';
else if ($_SESSION["admin"]) {
    if ($_SESSION["state"] == 'o') echo '<a href="state_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
非参加者→一般参加者　に変更する
</div>
</div>
</a>';
    if ($_SESSION["state"] == 'g') echo '<a href="state_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
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
