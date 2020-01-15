<?php
require_once('../set.php');

$deny = FALSE;

$fileplace = DATAROOT . 'mail/reset_pw/' . $_GET["userid"] . '.txt';

if (file_exists($fileplace)) {
    $filedata = json_decode(file_get_contents($fileplace), true);
    if ($filedata["expire"] <= time()) {
        unlink($fileplace);
        $deny = TRUE;
    }
    if ($filedata["sectok"] !== $_GET["sectok"]) $deny = TRUE;
} else $deny = TRUE;

if ($deny) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>認証エラー</title>
</head>
<body>
<p>認証に失敗しました。以下が原因として考えられます。<br>
1. 設定リンクの有効期限が切れている。<br>
2. 設定リンクのURLのうち一部分だけが切り取られ、サーバー側で正常に認識されなかった。</p>
</body>
</html>');

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../css/bootstrap.css">
<title>パスワード再発行 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probpw = 0;

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
  if ( probpw == 1) {
    alert( "【パスワード】\n入力されていません。" );
  }
  if ( probpw == 2) {
    alert( "【パスワード】\n文字数が多すぎます（現在" + document.form.password.value.length + "文字）。30文字以内に抑えて下さい。" );
  }
  if ( probpw == 3) {
    alert( "【パスワード】\n文字数が少なすぎます（現在" + document.form.password.value.length + "文字）。8文字以上のパスワードにして下さい。" );
  }
  if ( probpw == 4) {
    alert( "【パスワード】\n再入力のパスワードが違います。もう一度入力して下さい。パスワードは間違っていませんか？" );
  }
  return false;
}

if(window.confirm('現在のパスワードを登録します。よろしいですか？')){
  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";
  return true;
} else{
  return false;
}

}

//Cookie判定（参考：https://qiita.com/tatsuyankmura/items/8e09cbd5ee418d35f169）
var setCookie = function(cookieName, value){
  var cookie = cookieName + "=" + value + ";";
  document.cookie = cookie;
}

var getCookie = function(cookieName){
  var l = cookieName.length + 1 ;
  var cookieAry = document.cookie.split("; ") ;
  var str = "" ;
  for(i=0; i < cookieAry.length; i++){
    if(cookieAry[i].substr(0, l) === cookieName + "="){
      str = cookieAry[i].substr(l, cookieAry[i].length) ;
      break ;
    }
  }
  return str;
}

setCookie('check_cookie', true);
var val = getCookie('check_cookie');
// -->
</script>
<body>
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1>パスワード再発行</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
新しいパスワードを入力して下さい。
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="special_handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="sectok" value="<?php echo $_GET["sectok"]; ?>">
<input type="hidden" name="userid" value="<?php echo $_GET["userid"]; ?>">
<div class="form-group">
<label for="password">パスワード（8文字以上30文字以内）</label>
<input type="password" name="password" class="form-control" id="password">
<font size="2">※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</font>
</div>
<div class="form-group">
<label for="passwordagn">パスワード（確認の為再入力）</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn">
</div>
<button type="submit" class="btn btn-primary" id="submitbtn">入力したパスワードで設定する</button>
</form>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
