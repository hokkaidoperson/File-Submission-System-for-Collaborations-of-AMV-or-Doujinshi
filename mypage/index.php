<?php
require_once('../set.php');
session_start();
$titlepart = 'マイページ トップ';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'registered') {
    echo '<p><div class="border border-primary" style="padding:10px;">
ユーザー登録が完了しました。<br><br>
登録メールアドレス宛に、確認の為のメールを送信しました（「迷惑メール」「プロモーション」などに振り分けられている可能性もあるため、メールが見当たらない場合はそちらもご確認下さい）。メールアドレスが誤っている場合は、速やかに変更をお願いします（「アカウント情報編集」から変更出来ます）。</div></p>';
    echo '<p><div class="border border-warning" style="padding:10px;">
当サイトに30分以上アクセスが無い場合は、セキュリティの観点から自動的にログアウトします。<br>
特に、情報入力画面など、同じページにしばらく留まり続ける場面ではご注意願います。</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'userform_cancelled') {
    echo '<p><div class="border border-warning" style="padding:10px;">
ユーザー登録画面の設定を中止しました。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'userform_applied') {
    echo '<p><div class="border border-success" style="padding:10px;">
ユーザー登録画面の設定変更が完了しました。<br>ご自身の入力内容を変更する場合は、「アカウント情報編集」から設定画面に推移して下さい。<br><br>
<b>必須項目を新たに追加したりした場合、メッセージ機能を用いて参加者にその旨を通知し、設定変更を促して下さい。</b>
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'submitform_cancelled') {
    echo '<p><div class="border border-warning" style="padding:10px;">
ファイル提出に関する設定を中止しました。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'submitform_applied') {
    echo '<p><div class="border border-success" style="padding:10px;">
ファイル提出に関する設定変更が完了しました。<br>ご自身の入力内容を変更する場合は、「参加者・作品の一覧・編集」から一覧に移動し、あなたの作品を選択して下さい。<br><br>
<b>必須項目を新たに追加したりした場合、メッセージ機能を用いて参加者にその旨を通知し、設定変更を促して下さい。</b>
</div></p>';
    $_SESSION["situation"] = '';
}

//主催者の登録が完了していない場合（招待リンクの期限チェック）
if (file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/invitation/_promoter.txt'), true);
    if ($filedata["expire"] <= time()) unlink(DATAROOT . 'mail/invitation/_promoter.txt');
}
if (!file_exists(DATAROOT . 'users/_promoter.txt') and $_SESSION["admin"]) {
    if (!file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) echo '<p><div class="border border-danger" style="padding:10px;">
<b>主催者が登録されていません。</b><br><a href="invite/index.php">こちらのリンクをクリックして、主催者にアカウント登録の招待メールを送信して下さい。</a>
</div></p>';
    else echo '<p><div class="border border-warning" style="padding:10px;">
現在、主催者の登録を待機しています。<br><a href="invite/index.php">メールの送付先を誤った事に気付いた場合は、こちらのリンクをクリックして、正しい送付先にリンクを送り直して下さい。</a>
</div></p>';
}

//フォーム登録してない場合
if ($_SESSION["state"] == 'p' and !file_exists(DATAROOT . 'form/userinfo/done.txt')) echo '<p><div class="border border-danger" style="padding:10px;">
<b>ユーザー登録時の記入事項が登録されていません。</b><br><a href="setting/userform/index.php">こちらのリンクをクリックして、記入事項を登録して下さい。</a>
</div></p>';
if ($_SESSION["state"] == 'p' and !file_exists(DATAROOT . 'form/submit/done.txt')) echo '<p><div class="border border-danger" style="padding:10px;">
<b>ファイル提出時の記入事項や、ファイル提出期間が登録されていません。</b><br><a href="setting/submitform/index.php">こちらのリンクをクリックして、記入事項と提出期間を登録して下さい。</a>
</div></p>';

if (file_exists(DATAROOT . 'form/submit/done.txt')) {
    //受付3日前・受付中・締め切り後の表示
    $general = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);
    $fromdisp = date('Y年n月j日G時i分s秒', $general["from"]);
    $untildisp = date('Y年n月j日G時i分s秒', $general["until"]);
    if ($general["from"] > time()) {
        if ($general["from"] <= time() + 3 * 24 * 60 * 60) echo '<p><div class="border border-primary" style="padding:10px;">
ファイル提出受付は、' . $fromdisp . 'より始まります。
</div></p>';
    }
    else if ($general["until"] <= time()) {
        if ($_SESSION["state"] == 'p') echo '<p><div class="border border-danger" style="padding:10px;">
ファイル提出受付は、' . $untildisp . 'に締め切りました。<br>
また、提出済みのファイル情報や、ユーザー情報（メールアドレス、パスワード以外）の編集機能も停止しました。<br><br>
「参加者・作品の一覧・編集」ページから、提出されたファイルや入力内容を一括ダウンロード出来ます。<br><br>
ただし、主催者が認めれば、締め切り後の提出・編集が可能です（主催者自身の情報編集は常時可能です）。<br>
参加者からの要請を受諾して提出・編集機能を限定的に解禁する場合は、「権限コントロール」ページから設定して下さい。
</div></p>';
        else echo '<p><div class="border border-danger" style="padding:10px;">
ファイル提出受付は、' . $untildisp . 'に締め切りました。<br>
また、提出済みのファイル情報や、ユーザー情報（メールアドレス、パスワード以外）の編集も出来なくなりました。<br>
もし事情があって提出・編集したい場合は、主催者にご相談下さい。主催者が認めれば、締め切り後の提出・編集が可能です。
</div></p>';
    }
    else {
        if ($general["until"] <= time() + 3 * 24 * 60 * 60) echo '<p><div class="border border-warning" style="padding:10px;">
ファイル提出受付は、' . $untildisp . 'に締め切られます。<br>
締切と同時に、提出済みのファイル情報や、ユーザー情報（メールアドレス、パスワード以外）の編集も出来なくなります（ただし、主催者が認めれば、締め切り後の提出・編集が可能です）。<br>
ファイル提出や情報編集が必要な方は、お早めにお願い致します。
</div></p>';
        else echo '<p><div class="border border-success" style="padding:10px;">
現在、ファイル提出受付中です（' . $untildisp . 'まで）。
</div></p>';
    }
}

//ファイル確認のタスクが残ってないか
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') {
    $remain_p = FALSE;
    $remain_d = FALSE;
    foreach(glob(DATAROOT . 'exam/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents($filename), true);
        if ($filedata["_state"] == 2 or $filedata["_state"] == 3) continue;
        else if ($filedata["_state"] == 1) $remain_d = TRUE;
        else if (isset($filedata[$_SESSION["userid"]]) and $filedata[$_SESSION["userid"]]["opinion"] == 0) $remain_p = TRUE;
   }
    foreach(glob(DATAROOT . 'exam_edit/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents($filename), true);
        if ($filedata["_state"] == 2 or $filedata["_state"] == 3) continue;
        else if ($filedata["_state"] == 1) $remain_d = TRUE;
        else if (isset($filedata[$_SESSION["userid"]]) and $filedata[$_SESSION["userid"]]["opinion"] == 0) $remain_p = TRUE;
    }
    if ($remain_d and $remain_p) echo '<p><div class="border border-primary" style="padding:10px;">
確認を行っていないファイルがあります。また、議論中のファイルがあります。<br>
「提出作品の確認・承認」からご確認願います。</div></p>';
   else if ($remain_d) echo '<p><div class="border border-primary" style="padding:10px;">
議論中のファイルがあります。<br>
「提出作品の確認・承認」からご確認願います。</div></p>';
   else if ($remain_p) echo '<p><div class="border border-primary" style="padding:10px;">
確認を行っていないファイルがあります。<br>
「提出作品の確認・承認」からご確認願います。</div></p>';
}

//未読メッセージ
$unread = FALSE;
foreach(glob(DATAROOT . 'messages/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents($filename), true);
    if (isset($filedata[$_SESSION["userid"]]) and $filedata[$_SESSION["userid"]] == 0) $unread = TRUE;
}
if ($unread) echo '<p><div class="border border-primary" style="padding:10px;">
未読のメッセージがあります。<br>
「メッセージ機能」からご確認願います。</div></p>';

?>

<h1>マイページ　トップ</h1>

<h2>作品提出・閲覧・管理</h2>
<div class="row">
<?php
if ($_SESSION["state"] == 'p') echo '<div style="width: 20rem; margin: 0.5rem;"><a href="submit/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/upload.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>作品を提出する</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="list/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/list.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>参加者・作品の一覧・編集</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="exam/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/exam.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>提出作品の確認・承認</h5>
</div></div></div></div>
</a></div>
';
if ($_SESSION["state"] == 'c') echo '<div style="width: 20rem; margin: 0.5rem;"><a href="submit/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/upload.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>作品を提出する</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="list/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/list.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>提出済み作品一覧・編集</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="exam/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/exam.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>提出作品の確認・承認</h5>
</div></div></div></div>
</a></div>';
if ($_SESSION["state"] == 'g') echo '<div style="width: 20rem; margin: 0.5rem;"><a href="submit/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/upload.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>作品を提出する</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="list/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/list.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>提出済み作品一覧・編集</h5>
</div></div></div></div>
</a></div>';
if ($_SESSION["state"] == 'o') echo '表示可能な項目がありません。';
?>
</div>
<h2>やり取り</h2>
<div class="row">
<?php
echo '<div style="width: 20rem; margin: 0.5rem;"><a href="message/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/message.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>メッセージ機能</h5>
</div></div></div></div>
</a></div>';
?>
</div>
<h2>各種設定・その他</h2>
<div class="row">
<?php
if ($_SESSION["state"] == 'p') echo '<div style="width: 20rem; margin: 0.5rem;"><a href="auth/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/acl.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>権限コントロール</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="setting/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/edit.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>イベント情報編集</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="invite/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/invite.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>共同運営者の招待</h5>
</div></div></div></div>
</a></div>
<div style="width: 20rem; margin: 0.5rem;"><a href="ban/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/ban.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>ブラックリスト・アクセス制限</h5>
</div></div></div></div>
</a></div>';

if ($_SESSION["admin"]) echo '<div style="width: 20rem; margin: 0.5rem;"><a href="system/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/setting.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>システム設定</h5>
</div></div></div></div>
</a></div>';
echo '<div style="width: 20rem; margin: 0.5rem;"><a href="account/index.php">
<div class="card"><div class="card-body"><div class="media">
<img class="align-self-center mr-3" src="../images/account.svg" style="width: 70px; height: 70px;">
<div class="media-body">
<h5>アカウント情報編集</h5>
</div></div></div></div>
</a></div>';
?>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
