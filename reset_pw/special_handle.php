<?php
require_once('../set.php');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン・文字数・一致確認
if($_POST["password"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["password"]) > 72) $invalid = TRUE;
else if(mb_strlen($_POST["password"]) < 8) $invalid = TRUE;
else if($_POST["password"] != $_POST["passwordagn"]) $invalid = TRUE;

//sectokをもっかいチェック
$fileplace = DATAROOT . 'mail/reset_pw/' . basename($_POST["userid"]) . '.txt';

if (file_exists($fileplace)) {
    $filedata = json_decode(file_get_contents_repeat($fileplace), true);
    if ($filedata["sectok"] !== $_POST["sectok"]) $invalid = TRUE;
} else $invalid = TRUE;


if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//パスワードハッシュ化
$hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$userdata = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . basename($_POST["userid"]) . '.txt'), true);
$userdata["pwhash"] = $hash;

$userdatajson =  json_encode($userdata);

if (file_put_contents_repeat(DATAROOT . 'users/' . basename($_POST["userid"]) . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');


//リンクを消す
unlink($fileplace);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php
if (META_NOFOLLOW) echo '<meta name="robots" content="noindex, nofollow, noarchive">';
?>
<link rel="stylesheet" href="../css/bootstrap.css?<?php echo urlencode(VERSION); ?>">
<link rel="stylesheet" href="../css/style.css?<?php echo urlencode(VERSION); ?>">
<title>パスワード再発行完了 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
<script type="text/javascript">
<!--
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
</head>
<body>
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
<p>上記を有効にしてもこの画面が表示される場合、ご利用のブラウザは当サイトが使用するJavascriptの機能を提供していない、もしくは充分にサポートしていない可能性がありますので、ブラウザを変えて再度お試し下さい（推奨環境のブラウザでこの画面が表示される場合、システム管理者までご連絡下さい）。</p>
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1>パスワード再発行完了</h1>
<div class="border system-border-spacer">
<p>パスワードの再発行が完了しました。新しいパスワードでログインして下さい。</p>
<p><a href="../index.php">ログインページへ</a></p>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js?<?php echo urlencode(VERSION); ?>"></script>
</body>
</html>
