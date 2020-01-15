<?php
require_once('../set.php');

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
<link rel="stylesheet" href="../css/bootstrap.css">
<title>パスワード再発行 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<?php if ($userec) echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>"; ?>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  if(document.form.userid.value === ""){
    problem = 1;
  }

  if(document.form.nickname.value === ""){
    problem = 1;
  }

  if(document.form.email.value === ""){
    problem = 1;
  }

//問題ありの場合はエラー表示　ない場合は移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  alert( "全ての項目を入力して下さい。" );
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
パスワードの再発行を行うためのURLを、お使いのアカウントのメールアドレスに送信します。<br><br>
確認の為、お使いのアカウントの情報を以下に入力して下さい。
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="auth.php" method="post"<?php if ($userec == FALSE) echo 'onSubmit="return check()"'; ?>>
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<label for="userid">ユーザーID</label>
<input type="text" name="userid" class="form-control" id="userid">
</div>
<div class="form-group">
<label for="nickname">ニックネーム</label>
<input type="text" name="nickname" class="form-control" id="nickname">
</div>
<div class="form-group">
<label for="email">メールアドレス</label>
<input type="email" name="email" class="form-control" id="email">
</div>
<?php
if ($userec) echo '<button class="g-recaptcha btn btn-primary" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit">URLを送信</button>';
else echo '<button type="submit" class="btn btn-primary" id="submitbtn">URLを送信</button>';
?>
</form>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
