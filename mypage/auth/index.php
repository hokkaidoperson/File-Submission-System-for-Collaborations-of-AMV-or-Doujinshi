<?php
require_once('../../set.php');
setup_session();
$titlepart = '権限コントロール';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

?>

<h1>権限コントロール</h1>
<p>ここでは、共同運営者の他者ファイル閲覧、提出期間外のファイル提出・編集許可といった、特殊な権限について操作出来ます。</p>
<div class="row" style="padding:10px;">
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
