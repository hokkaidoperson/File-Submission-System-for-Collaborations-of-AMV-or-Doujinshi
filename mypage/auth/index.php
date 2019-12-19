<?php
require_once('../../set.php');
session_start();
$titlepart = '権限コントロール';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'auth_fileacl') {
    echo '<p><div class="border border-success" style="padding:10px;">
アクセス権の変更が完了しました。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'auth_outofterm') {
    echo '<p><div class="border border-success" style="padding:10px;">
操作権の変更が完了しました。
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

<h1>権限コントロール</h1>
<p>ここでは、共同運営者の他者ファイル閲覧、提出期間外のファイル提出・編集許可といった、特殊な権限について操作出来ます。</p>
<div class="row">
<a href="fileacl.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
共同運営者の他者ファイル閲覧権限を操作
</div>
</div>
</a>
<a href="outofterm.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
提出期間外のファイル提出・情報編集権限を操作<?php
if (file_exists(DATAROOT . 'form/submit/done.txt')) {
    $general = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);
    if ($general["from"] <= time() and $general["until"] > time()) echo '（現在、提出期間中につき使用不可）';
}
?>
</div>
</div>
</a>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
