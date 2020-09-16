<?php
require_once('../../set.php');
setup_session();
$titlepart = '主催者交代手続';
require_once(PAGEROOT . 'mypage_header.php');

$deny = FALSE;

if (file_exists(DATAROOT . 'mail/state/promoter.txt')) {
    $filedata = json_decode(file_get_contents_repeat(DATAROOT . 'mail/state/promoter.txt'), true);
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/state/promoter.txt');
        $deny = TRUE;
    }
    if ($filedata["new"] != $_SESSION["userid"]) $deny = TRUE;
} else $deny = TRUE;

if ($deny) die_mypage('<h1>申請が見付かりません</h1>
<p>あなたが主催者交代の対象になっている事を確認出来ませんでした。以下が原因として考えられます。</p>
<ol>
<li>手続用リンクの有効期限が切れている。</li>
<li>手続きが既に済んでいる。</li>
</ol>
<p><a href="../index.php">マイページトップに戻る</a></p>');

?>

<h1>主催者交代手続</h1>
<p>主催者を交代し、あなたが新しい主催者となります。</p>
<p>よろしければ、以下の「主催者の交代を実行する」を押して下さい。</p>
<form name="form" action="prom_handle.php" method="post" onSubmit="$('#confirmmodal').modal(); return false;" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<button type="submit" class="btn btn-primary">主催者の交代を実行する</button>
<?php echo_modal_confirm("手続きを開始してもよろしければ「OK」を押して下さい。この操作を取りやめる場合は「戻る」を押して下さい。<br><br><b>一旦OKボタンを押下すると、この操作を取り消す事が出来なくなりますので、ご注意下さい</b>。", "最終確認", null, null, "OK"); ?>
</form>

<?php
require_once(PAGEROOT . 'mypage_footer.php');
