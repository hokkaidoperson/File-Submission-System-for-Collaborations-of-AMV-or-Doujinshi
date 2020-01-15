<?php
require_once('../../set.php');

if (!file_exists(DATAROOT . 'form/userinfo/done.txt')) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>準備中です</title>
</head>
<body>
<p>必要な設定が済んでいないため、只今、ユーザー登録を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
</body>
</html>');

if (blackip(0, "g")) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>アクセスが制限されています</title>
</head>
<body>
<p>現在ご利用のアクセス元（IPアドレス）からのユーザー登録が制限されているため、ユーザー登録出来ません。<br>
あなた、もしくは同じアクセス元を利用する他の誰かが、イベントの運営を妨害するなどしたために主催者により制限されています。<br>
もしそのような事をした覚えが無い場合は、以下のブロック情報を添えて主催者にご相談下さい。</p>
<p>【ブロック情報】<br>
IPアドレス：' . getenv("REMOTE_ADDR") . '<br>
リモートホスト：' . gethostbyaddr(getenv("REMOTE_ADDR")) . '</p>
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
<link rel="stylesheet" href="../../css/bootstrap.css">
<title>アカウント登録 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<?php if ($userec) echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>"; ?>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probid = 0;

//必須の場合のパターン・文字種・文字数
  if(document.form.userid.value === ""){
    problem = 1;
    probid = 1;
  } else if(!document.form.userid.value.match(/^[0-9a-zA-Z]*$/)){
    problem = 1;
    probid = 2;
  } else if(document.form.userid.value.length > 20){
    problem = 1;
    probid = 3;
  }

//問題ありの場合はエラー表示　ない場合は移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probid == 1) {
    alert( "【ユーザーID】\n入力されていません。" );
  }
  if ( probid == 2) {
    alert( "【ユーザーID】\n半角英数字以外の文字が含まれています（記号も使用出来ません）。別のIDを指定して下さい。" );
  }
  if ( probid == 3) {
    alert( "【ユーザーID】\n文字数が多すぎます（現在" + document.form.userid.value.length + "文字）。20文字以内に抑えて下さい。" );
  }
  return false;
}

<?php
if ($userec == FALSE) echo 'submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";
';
?>
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
<h1>アカウント登録</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
本イベントのポータルサイトで使用するアカウントを登録します。登録したアカウントを使用して、ファイルを提出して下さい。<br><br>
当サイトではJavascript及びCookieを使用します。現在は有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。<br><br>
※この登録画面は、イベントに参加される一般の方向けのものです。<b>このイベントを共に運営される方（共同運営者）は、この画面から登録出来ません</b>（主催者に依頼して、登録用のURLを送付してもらって下さい）。
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="useridcheck.php" method="post"<?php if ($userec == FALSE) echo 'onSubmit="return check()"'; ?>>
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<label for="userid">ユーザーID（半角英数字のみ　20文字以内　<b>後から変更出来ません</b>）</label>
<input type="text" name="userid" class="form-control" id="userid">
<font size="2">※ログインの際にこのユーザーIDを使用します。</font>
</div>
※入力頂いたユーザーIDが既に使われていないかどうか、チェックを行います。<br>
<?php
if ($userec) echo '<button class="g-recaptcha btn btn-primary" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit">ユーザーIDをチェック</button>';
else echo '<button type="submit" class="btn btn-primary" id="submitbtn">ユーザーIDをチェック</button>';
?>
</form>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../../js/bootstrap.bundle.js"></script>
</body>
</html>
