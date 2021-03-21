<?php
require_once('../../set.php');
setup_session();
$titlepart = '権限コントロール';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

?>

<h1>権限コントロール</h1>
<p>ここでは、共同運営者の他者ファイル閲覧、提出期間外のファイル提出・編集許可といった、特殊な権限について操作出来ます。</p>
<div class="system-carditems">
<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="fileacl.php" class="stretched-link">共同運営者の他者ファイル閲覧権限を操作</a>
</div>
</div>
<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="outofterm.php" class="stretched-link">提出期間外のファイル提出・情報編集権限を操作<?php
if (file_exists(DATAROOT . 'form/submit/done.txt')) {
    $general = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
    if ($general["from"] <= time() and $general["until"] > time()) echo '（現在、提出期間中につき使用不可）';
}
?></a>
</div>
</div>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
