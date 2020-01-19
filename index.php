<?php
require_once('set.php');
session_start();
//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'mypage/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

//recaptcha周りの参考URL https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents(DATAROOT . 'rec.txt'), true);

$userec = FALSE;
if ($recdata["site"] != "" and $recdata["sec"] != "") $userec = TRUE;

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="css/bootstrap.css">
<title><?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<?php if ($userec) echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>"; ?>
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

return true;

}

function recSubmit(token) {
  if (check() === true) document.form.submit();
}

//Cookie判定（参考：https://qiita.com/tatsuyankmura/items/8e09cbd5ee418d35f169）
var setCookie = function(cookieName, value){
  var cookie = cookieName + "=" + value + ";";
  document.cookie = cookie;
};

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
};

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
<h1><?php echo $eventname; ?>　ファイル提出用ポータルサイト</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
当サイトではJavascript及びCookieを使用します。現在は有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<h2>ログイン</h2>
<form name="form" action="login.php" method="post"<?php if ($userec == FALSE) echo 'onSubmit="return check()"'; ?>>
<?php if (isset($_GET['redirto'])) echo '<input type="hidden" name="redirto" value="' . htmlspecialchars($_GET['redirto']) . '">'; ?>
<div class="form-group">
<label for="userid">ユーザーID</label>
<input type="text" name="userid" class="form-control" id="userid">
</div>
<div class="form-group">
<label for="password">パスワード</label>
<input type="password" name="password" class="form-control" id="password">
</div>
<?php
if ($userec) echo '<button class="g-recaptcha btn btn-primary" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit">ログイン</button>';
else echo '<button type="submit" class="btn btn-primary">ログイン</button>';
?>
</form>
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<a href='reset_pw/index.php'>パスワードを忘れてしまった方はこちらから再発行して下さい。</a>
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<a href='register/general/index.php'>ポータルサイトに未登録の参加者はこちらから登録して下さい。</a>
</div>
<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<?php echo $eventname; ?>では、<a href='https://www.hkdyukkuri.space/filesystem/' target="_blank">MAD合作・合同誌向けファイル提出システム</a>を利用しています。<br>
また、本システムでは、ウェブデザインの調整に<a href="https://getbootstrap.jp/" target="_blank">Bootstrap4</a>を利用しています。<br>
マイページのアイコンには<a href="https://icooon-mono.com/" target="_blank">icooon-mono</a>による素材を利用しています。
</div>
<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
バージョン情報：<?php echo VERSION; ?>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="js/bootstrap.bundle.js"></script>
</body>
</html>
