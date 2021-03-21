<?php
require_once('../../set.php');
setup_session();
$titlepart = 'IPアドレス・リモートホスト名によるアカウント作成制限';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

?>
<h1>IPアドレス・リモートホスト名によるアカウント作成制限</h1>
<p>ここで指定したIPアドレスもしくはリモートホスト名からの、<strong>一般参加者の新規登録</strong>が制限されます。<br>
登録済みユーザーのログイン時、もしくは共同運営者の新規登録時にはこの制限は適用されません。</p>
<p>リモートホスト名は、IPアドレスからの逆引き（PHPの gethostbyaddr 関数）によって取得します。</p>
<p>制限の対象にするIPやホスト名を、1行ごとに1つ入力して下さい。<br>次のワイルドカードが使えます。</p>
<ul>
<li><code>?</code> = 1文字（半角英数字、ドット"."、ハイフン"-"）</li>
<li><code>*</code> = 1文字以上（半角英数字、ドット"."、ハイフン"-"）</li>
<li><code>!</code> = 1文字（半角数字のみ）</li>
<li><code>~</code> = 1文字以上（半角数字のみ）</li>
</ul>
<p>例：<code>123.456.???.123</code>⇒123.456.789.123 など（123.456.78.123は除外されません）<br>
例：<code>*.example.com</code>⇒123.456.789.123.example.com、1-2-3-4.rooter.example.com など</p>

<form name="form" action="blackip_handle.php" method="post" onSubmit="return check()" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<textarea id="setting" name="setting" rows="5" class="form-control"><?php
if (file_exists(DATAROOT . 'blackip.txt')) echo hsc(file_get_contents_repeat(DATAROOT . 'blackip.txt'));
?></textarea>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">設定変更</button>
</form>
<script language="JavaScript" type="text/javascript">

function check(){

  if(window.confirm('現在の入力内容を送信します。よろしいですか？')){
  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";

    return true;
  } else{
    return false;
  }
}

</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
