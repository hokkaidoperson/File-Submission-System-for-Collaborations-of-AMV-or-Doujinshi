<?php
require_once('../set.php');

//recaptcha周りの参考URL https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents(DATAROOT . 'rec.txt'), true);

$userec = FALSE;
if ($recdata["site"] != "" and $recdata["sec"] != "" and extension_loaded('curl')) $userec = TRUE;

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../css/bootstrap.css">
<title>ユーザーID・ニックネーム再送信 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<?php if ($userec) echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>"; ?>
<script type="text/javascript">
<!--
function check_individual() {
    var valid = 1;
    document.getElementById("email-errortext").innerHTML = "";
    document.getElementById("email-searchinfo").innerHTML = "";
    if(document.form.email.value === ""){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.email.value.match(/.+@.+\..+/)){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
    } else {
        fetch('../register/general/useridcheck.php?email=' + document.form.email.value)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                throw new Error();
            }
        })
        .then((result) => {
            if (result.emailresult == 1) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスと紐づいているアカウントが見つかりませんでした。";
                document.form.email.classList.add("is-invalid");
                document.form.email.classList.remove("is-valid");
            } else {
                document.getElementById("email-searchinfo").innerHTML = "このメールアドレスと紐づいているアカウントが見つかりました。「再発行情報を送信」を押すと、アカウント情報を再送します。";
                document.form.email.classList.add("is-valid");
                document.form.email.classList.remove("is-invalid");
            }
        })
    }
    if (valid) {
        document.form.email.classList.add("is-valid");
        document.form.email.classList.remove("is-invalid");
    } else {
        document.form.email.classList.add("is-invalid");
        document.form.email.classList.remove("is-valid");
    }
    return;
}

function check(){
    document.getElementById("neterrortext").style.display = "none";
    var valid = 1;

    document.getElementById("email-searchinfo").innerHTML = "";
    document.getElementById("email-errortext").innerHTML = "";
    if(document.form.email.value === ""){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.email.value.match(/.+@.+\..+/)){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
    } else {
        fetch('../register/general/useridcheck.php?email=' + document.form.email.value)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                document.getElementById("email-errortext").innerHTML = "入力内容の検証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「再発行情報を送信」を押して下さい。";
                throw new Error();
            }
        })
        .catch((error) => {
            document.getElementById("email-errortext").innerHTML = "入力内容の検証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「再発行情報を送信」を押して下さい。";
            throw new Error();
        })
        .then((result) => {
            if (result.emailresult == 1) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスと紐づいているアカウントが見つかりませんでした。";
                document.form.email.classList.add("is-invalid");
                document.form.email.classList.remove("is-valid");
            } else {
                <?php if ($userec) echo "grecaptcha.execute();"; else echo "document.form.submit();"; ?>
            }
        })
    }
    if (valid) {
        document.form.email.classList.add("is-valid");
        document.form.email.classList.remove("is-invalid");
    } else {
        document.form.email.classList.add("is-invalid");
        document.form.email.classList.remove("is-valid");
    }
    return false;

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
<h1>ユーザーID・ニックネーム再送信</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<b>パスワードの再発行に必要な情報（ユーザーID・ニックネーム）は、ユーザー登録完了時にお送りしているメールに記載しています</b>ので、まずはそちらをご確認願います。<br>
もし見当たらない場合や、ニックネームが変更されていて分からない場合は、パスワードの再発行に必要な情報をお使いのアカウントのメールアドレスに再送します。<br><br>
お使いのアカウントのメールアドレスを以下に入力して下さい。<br><br>
※無暗に大量のメールが送信されるのを防ぐため、この機能でアカウント情報を再送出来るのは、1アカウントにつき、24時間に1回のみとさせて頂きます。
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="auth.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<label for="email">メールアドレス</label>
<input type="email" name="email" class="form-control" id="email" onBlur="check_individual()">
<div id="email-errortext" class="invalid-feedback" style="display: block;"></div>
<div id="email-searchinfo" class="valid-feedback" style="display: block;"></div>
</div>
<?php
if ($userec) echo '<div id=\'recaptcha\' class="g-recaptcha" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit" data-error-callback="recError" data-size="invisible"></div>
<button class="btn btn-primary" type="submit">再発行情報を送信</button><br><font size="2"><span class="text-muted">※「再発行情報を送信」を押下した直後、あなたがスパムやボットでない事を確かめるために画像認証画面が表示される場合があります。</span></font>';
else echo '<button type="submit" class="btn btn-primary" id="submitbtn">再発行情報を送信</button>';
?>
<div id="neterrortext" style="display: none;"><font size="2"><span class="text-danger">ユーザーの認証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「再発行情報を送信」を押して下さい。</span></font></div>
</form>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
