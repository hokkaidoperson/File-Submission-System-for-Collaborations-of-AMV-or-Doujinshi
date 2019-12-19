<?php
require_once('../../set.php');
session_start();
$titlepart = 'パスワード変更';
require_once(PAGEROOT . 'mypage_header.php');

?>

<h1>パスワード変更</h1>
<div class="border border-primary" style="padding:10px;">
<form name="form" action="pw_handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<label for="oldpassword">現在のパスワード（本人確認の為ご入力願います）</label>
<input type="password" name="oldpassword" class="form-control" id="oldpassword">
</div>
<div class="form-group">
<label for="password">新しいパスワード（8文字以上30文字以内）</label>
<input type="password" name="password" class="form-control" id="password">
<font size="2">※パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</font>
</div>
<div class="form-group">
<label for="passwordagn">新しいパスワード（確認の為再入力）</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn">
</div>
<button type="submit" class="btn btn-primary" id="submitbtn">パスワードを変更する</button>
</form>
</div>
<script type="text/javascript">
<!--
function check(){

  problem = 0;

  probold = 0;
  probpw = 0;

  if(document.form.oldpassword.value === ""){
    problem = 1;
    probold = 1;
  }

  if(document.form.password.value === ""){
    problem = 1;
    probpw = 1;
  } else if(document.form.password.value.length > 30){
    problem = 1;
    probpw = 2;
  } else if(document.form.password.value.length < 8){
    problem = 1;
    probpw = 3;
  } else if(document.form.password.value !== document.form.passwordagn.value){
    problem = 1;
    probpw = 4;
  }

//問題ありの場合はエラー表示　ない場合は移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probold == 1) {
    alert( "【現在のパスワード】\n入力されていません。" );
  }
  if ( probpw == 1) {
    alert( "【新しいパスワード】\n入力されていません。" );
  }
  if ( probpw == 2) {
    alert( "【新しいパスワード】\n文字数が多すぎます（現在" + document.form.password.value.length + "文字）。30文字以内に抑えて下さい。" );
  }
  if ( probpw == 3) {
    alert( "【新しいパスワード】\n文字数が少なすぎます（現在" + document.form.password.value.length + "文字）。8文字以上のパスワードにして下さい。" );
  }
  if ( probpw == 4) {
    alert( "【新しいパスワード】\n再入力のパスワードが違います。もう一度入力して下さい。パスワードは間違っていませんか？" );
  }
  return false;
}

if(window.confirm('このパスワードを登録します。よろしいですか？')){
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

