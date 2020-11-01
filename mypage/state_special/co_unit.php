<?php
require_once('../../set.php');
setup_session();
$titlepart = '共同運営者辞退 承認手続';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

$deny = FALSE;

$id = basename($_GET["id"]);

if (file_exists(DATAROOT . 'mail/state/co_' . $id . '.txt')) {
    $filedata = json_decode(file_get_contents_repeat(DATAROOT . 'mail/state/co_' . $id . '.txt'), true);
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/state/co_' . $id . '.txt');
        $deny = TRUE;
    }
} else $deny = TRUE;

if ($deny) die_mypage('<h1>申請が見付かりません</h1>
<p>このユーザーは存在しないか、共同運営者辞退の手続きを行っていません。以下が原因として考えられます。</p>
<ol>
<li>手続用リンクの有効期限が切れている。</li>
<li>手続きが既に済んでいる。</li>
<li>手続用リンクのURLのうち一部分だけが切り取られ、サーバー側で正常に認識されなかった。</li>
</ol>
<p><a href="../index.php">マイページトップに戻る</a></p>');

?>

<h1>共同運営者辞退 承認手続</h1>
<p><?php echo nickname($id); ?> が共同運営者から辞退する事を承認します。</p>
<p>よろしければ、以下の「共同運営者の辞退を実行する」を押して下さい。</p>
<form name="form" action="co_handle.php" method="post" onSubmit="$('#confirmmodal').modal(); return false;" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<button type="submit" class="btn btn-primary">共同運営者の辞退を実行する</button>
<?php echo_modal_confirm("<p>手続きを開始してもよろしければ「OK」を押して下さい。この操作を取りやめる場合は「戻る」を押して下さい。</p><p><b>一旦OKボタンを押下すると、この操作を取り消す事が出来なくなりますので、ご注意下さい</b>。</p>", "最終確認", null, null, "OK"); ?>
</form>

<?php
require_once(PAGEROOT . 'mypage_footer.php');
