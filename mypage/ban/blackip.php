<?php
require_once('../../set.php');
session_start();
$titlepart = 'IPアドレス・リモートホストによるアクセス制限';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

?>
<h1>IPアドレス・リモートホストによるアクセス制限</h1>
<p>ここで指定したIPアドレスもしくはリモートホストからの、<b>一般参加者の新規登録</b>が制限されます。<br>
登録済みユーザーのログイン時、もしくは共同運営者の新規登録時にはこの制限は適用されません。</p>
<p>リモートホストは、IPアドレスからの逆引き（PHPの gethostbyaddr 関数）によって取得します。</p>
<p>制限の対象にするIPやリモートホストを、1行ごとに1つ入力して下さい。<br>次のワイルドカードが使えます。</p>
<ul>
<li><code>?</code> = 1文字（半角英数字、ドット"."、ハイフン"-"）</li>
<li><code>*</code> = 1文字以上（半角英数字、ドット"."、ハイフン"-"）</li>
<li><code>!</code> = 1文字（半角数字のみ）</li>
<li><code>~</code> = 1文字以上（半角数字のみ）</li>
</ul>
<p>例：<code>123.456.???.123</code>⇒123.456.789.123 など（123.456.78.123は除外されません）<br>
例：<code>*.example.com</code>⇒123.456.789.123.example.com、1-2-3-4.rooter.example.com など</p>

<form name="form" action="blackip_handle.php" method="post" onSubmit="return check()" style="margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<textarea id="setting" name="setting" rows="4" cols="80" class="form-control"><?php
if (file_exists(DATAROOT . 'blackip.txt')) echo htmlspecialchars(file_get_contents(DATAROOT . 'blackip.txt'));
?></textarea>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">設定変更</button>
</form>
<script language="JavaScript" type="text/javascript">
<!--
function check(){

  if(window.confirm('現在の入力内容を送信します。よろしいですか？')){
  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";

    return true;
  } else{
    return false;
  }
}
// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
