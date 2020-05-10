<?php
require_once('../set.php');

$deny = FALSE;

$name = basename($_GET["name"]);
$userid = basename($_GET["userid"]);
$sectok = basename($_GET["sectok"]);

if ($name == "" or $userid == "" or $sectok == "") die("パラメーターエラー");
if (!user_exists($userid)) die();

if (file_exists(DATAROOT . 'messages/' . $name . '.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'messages/' . $name . '.txt'), true);
    if (!isset($filedata["sectok_$userid"]) or $filedata["sectok_$userid"] !== $sectok) $deny = TRUE;
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
1. 送信者がメッセージを削除した。<br>
2. メッセージの閲覧権が無い。<br>
3. 開封確認URLのうち一部分だけが切り取られ、サーバー側で正常に認識されなかった。</p>
</body>
</html>');

//既読の処理
if (isset($filedata[$userid]) and $filedata[$userid] == 0) {
    $filedata[$userid] = 1;
    $filedatajson = json_encode($filedata);
    if (file_put_contents(DATAROOT . 'messages/' . $name . '.txt', $filedatajson) === FALSE) die('メッセージデータの書き込みに失敗しました。');
    $print = "メッセージ「" . hsc($filedata["_subject"]) . "」に既読を付けました。<br>ブラウザを閉じても構いません。";
} else $print = "メッセージ「" . hsc($filedata["_subject"]) . "」には既に既読が付いています。<br>ブラウザを閉じても構いません。";

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../css/bootstrap.css">
<title>メッセージ開封確認 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
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
<body>
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1>メッセージ開封確認</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<?php echo $print; ?>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
