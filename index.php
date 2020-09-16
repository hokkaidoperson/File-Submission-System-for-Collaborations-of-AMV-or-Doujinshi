<?php
require_once('set.php');
setup_session();
//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("./mypage/index.php");
}

//recaptcha周りの参考URL https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents_repeat(DATAROOT . 'rec.txt'), true);

$userec = FALSE;
if ($recdata["site"] != "" and $recdata["sec"] != "" and extension_loaded('curl')) $userec = TRUE;

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php
if (META_NOFOLLOW) echo '<meta name="robots" content="noindex, nofollow, noarchive">';
?>
<link rel="stylesheet" href="css/bootstrap.css?<?php echo urlencode(VERSION); ?>">
<link rel="stylesheet" href="css/style.css?<?php echo urlencode(VERSION); ?>">
<title><?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<?php if ($userec) echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>"; ?>
<script type="text/javascript">
<!--
function check_individual(id){
  if (id === "userid") {
      document.getElementById("userid-errortext").innerHTML = "";
      if(document.form.userid.value === ""){
        problem = 1;
        document.getElementById("userid-errortext").innerHTML = "入力されていません。";
        document.form.userid.classList.add("is-invalid");
        document.form.userid.classList.remove("is-valid");
      } else {
        document.form.userid.classList.add("is-valid");
        document.form.userid.classList.remove("is-invalid");
      }
      return;
  }

  if (id === "password") {
      document.getElementById("password-errortext").innerHTML = "";
      if(document.form.password.value === ""){
        problem = 1;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
        document.form.password.classList.add("is-invalid");
        document.form.password.classList.remove("is-valid");
      } else {
        document.form.password.classList.add("is-valid");
        document.form.password.classList.remove("is-invalid");
      }
      return;
  }
}

function check(){

  var problem = 0;
  document.getElementById("neterrortext").style.display = "none";
  document.getElementById("userid-errortext").innerHTML = "";
  if(document.form.userid.value === ""){
    problem = 1;
    document.getElementById("userid-errortext").innerHTML = "入力されていません。";
    document.form.userid.classList.add("is-invalid");
    document.form.userid.classList.remove("is-valid");
  } else {
    document.form.userid.classList.add("is-valid");
    document.form.userid.classList.remove("is-invalid");
  }

  document.getElementById("password-errortext").innerHTML = "";
  if(document.form.password.value === ""){
    problem = 1;
    document.getElementById("password-errortext").innerHTML = "入力されていません。";
    document.form.password.classList.add("is-invalid");
    document.form.password.classList.remove("is-valid");
  } else {
    document.form.password.classList.add("is-valid");
    document.form.password.classList.remove("is-invalid");
  }

  if ( problem == 1 ) {
    return false;
  }

  <?php if ($userec) echo "grecaptcha.execute(); return false;"; else echo "return true;"; ?>

}

function recSubmit(token) {
  document.form.submit();
}

function recError(token) {
  document.getElementById("neterrortext").style.display = "block";
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
<body<?php if ($userec) echo ' style="margin-bottom: 90px;"'; ?>>
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
<p>上記を有効にしてもこの画面が表示される場合、ご利用のブラウザは当サイトが使用するJavascriptの機能を提供していない、もしくは充分にサポートしていない可能性がありますので、ブラウザを変えて再度お試し下さい（推奨環境のブラウザでこの画面が表示される場合、システム管理者までご連絡下さい）。</p>
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1><?php echo $eventname; ?>　ファイル提出用ポータルサイト</h1>
<p class="text-right"><a href='open/index.php' target="_blank" class="system-link-helpicon">ヘルプ</a></p>
<?php if (isset($_SESSION['guest_redirto']) and $_SESSION['guest_redirto'] != "") echo '<div class="border border-primary system-border-spacer">
このページの利用にはログインが必要です。
</div>'; ?>
<div class="border system-border-spacer">
当サイトではJavascript及びCookieを使用します。現在は有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。
</div>
<div class="border border-primary system-border-spacer">
<h2>ログイン</h2>
<form name="form" action="login.php" method="post" onSubmit="return check()">
<?php
csrf_prevention_in_form();
?>
<div class="form-row">
<div class="form-group col-md-6">
<label for="userid">ユーザーID</label>
<input type="text" name="userid" class="form-control" id="userid" autofocus onBlur="check_individual(&quot;userid&quot;);">
<div id="userid-errortext" class="system-form-error"></div>
</div>
<div class="form-group col-md-6">
<label for="password">パスワード</label>
<input type="password" name="password" class="form-control" id="password" onBlur="check_individual(&quot;password&quot;);">
<div id="password-errortext" class="system-form-error"></div>
</div>
</div>
<?php
if ($userec) echo '<div id=\'recaptcha\' class="g-recaptcha" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit" data-error-callback="recError" data-size="invisible"></div>
<button class="btn btn-primary" type="submit">ログイン</button><div class="small text-muted mb-2">※ログインボタン押下直後、あなたがスパムやボットでない事を確かめるために画像認証画面が表示される場合があります。</div>';
else echo '<button type="submit" class="btn btn-primary">ログイン</button>';
?>
<div id="neterrortext" class="small text-danger" style="display: none;">ユーザーの認証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「ログイン」を押して下さい。</div>
</form>
<div class="small"><a href='reset_pw/index.php' class="system-link-helpicon">パスワードを忘れてしまった方はこちらから再発行して下さい。</a><br><a href='search_id/index.php' class="system-link-helpicon">ユーザーID・ニックネームを忘れてしまいパスワード再発行が行えない方はこちらから再送信して下さい。</a></div>
</div>
<div class="border border-primary system-border-spacer">
<a href='register/general/index.php'><?php echo $eventname; ?>のポータルサイトに未登録の参加者はこちらから登録して下さい。</a>
</div>
<div class="border border-success system-border-spacer">
<?php echo $eventname; ?>では、<a href='https://www.hkdyukkuri.space/filesystem/' target="_blank" rel="noopener">MAD合作・合同誌向けファイル提出システム</a>を利用しています。<br>
また、本システムでは、ウェブデザインの調整に<a href="https://getbootstrap.jp/" target="_blank" rel="noopener">Bootstrap4</a>を利用しています。<br>
システム内のアイコンには<a href="https://icooon-mono.com/" target="_blank" rel="noopener">icooon-mono</a>による素材を利用しています。
</div>
<div class="border border-success system-border-spacer">
バージョン情報：<?php echo VERSION; ?>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="js/bootstrap.bundle.js?<?php echo urlencode(VERSION); ?>"></script>
</body>
</html>
