<?php
require_once('../set.php');

$deny = FALSE;

if (file_exists(DATAROOT . 'mail/state/co_' . $_GET["id"] . '.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/state/co_' . $_GET["id"] . '.txt'), true);
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/state/co_' . $_GET["id"] . '.txt');
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
<title>共同運営者辞退 承認手続 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probid = 0;
  probpw = 0;

//必須の場合のパターン・文字種・文字数
  if(document.form.userid.value === ""){
    problem = 1;
    probid = 1;
  }

//必須の場合のパターン・文字数・一致確認
  if(document.form.password.value === ""){
    problem = 1;
    probpw = 1;
  }

//問題ありの場合はエラー表示　ない場合は移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probid == 1 && probpw == 1) {
    alert( "ユーザーIDとパスワードを入力して下さい。" );
  } else if ( probid == 1) {
    alert( "ユーザーIDを入力して下さい。" );
  } else if ( probpw == 1) {
    alert( "パスワードを入力して下さい。" );
  }
  return false;
}

  if(window.confirm('手続きを開始します。よろしいですか？')){
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
<h1>共同運営者辞退 承認手続</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
この共同運営者の辞退を承認します。<br><br>
よろしければ、確認の為、あなたのユーザーIDとパスワードを入力して下さい。
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="co_handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="sectok" value="<?php echo $_GET["sectok"]; ?>">
<input type="hidden" name="id" value="<?php echo $_GET["id"]; ?>">
<div class="form-group">
<label for="userid">ユーザーID</label>
<input type="text" name="userid" class="form-control" id="userid">
</div>
<div class="form-group">
<label for="password">パスワード</label>
<input type="password" name="password" class="form-control" id="password">
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">主催者の交代を実行する</button>
</form>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
