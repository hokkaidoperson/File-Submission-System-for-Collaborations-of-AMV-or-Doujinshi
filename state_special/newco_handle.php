<?php
require_once('../set.php');

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

$invalid = FALSE;

//sectokをもっかいチェック
if (file_exists(DATAROOT . 'mail/co_add/' . basename($_POST["userid"]) . '.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/co_add/' . basename($_POST["userid"]) . '.txt'), true);
    if ($filedata["sectok"] !== $_POST["sectok"]) $invalid = TRUE;
} else $invalid = TRUE;

$userid = basename($_POST["userid"]);

if (!file_exists(DATAROOT . 'users/' . $userid . '.txt')) $invalid = TRUE;
else {
    $userdata = json_decode(file_get_contents(DATAROOT . 'users/' . $userid . '.txt'), true);
    if (!password_verify($_POST["password"], $userdata["pwhash"])) $invalid = TRUE;
    if ($userdata["deleted"]) $invalid = TRUE;
}

//認証失敗の時
if ($invalid) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>認証エラー</title>
</head>
<body>
<p>認証に失敗しました。ユーザーIDとパスワードが誤っている可能性があります。</p>

<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、前の画面にお戻り下さい。</a></p>
</body>
</html>');
}

//状態を共催にして保存
$oldstate = $userdata["state"];
$userdata["state"] = "c";
$userdatajson =  json_encode($userdata);
if (file_put_contents(DATAROOT . 'users/' . $userid . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//立場別一覧の書き換え
$statedata = "$userid\n";
$statedtp = DATAROOT . 'users/_co.txt';
if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

if ($oldstate == "g") $statedtp = DATAROOT . 'users/_general.txt';
else if ($oldstate == "o") $statedtp = DATAROOT . 'users/_outsider.txt';
$array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search(basename($_POST["userid"]), $array);
unset($array[$key]);
$statedata = implode("\n", $array) . "\n";
if (file_put_contents($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');


//この人をファイル確認メンバーに入れる？
if (file_exists(DATAROOT . 'examsetting.txt')) {
    $examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

    if ($examsetting["submit_add"] == "1") {
        $statedata = "$userid\n";
        $statedtp = DATAROOT . 'exammember_submit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
    if ($examsetting["edit_add"] == "1") {
        $statedata = "$userid\n";
        $statedtp = DATAROOT . 'exammember_edit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
}


//招待リンクを消す
unlink(DATAROOT . 'mail/co_add/' . basename($_POST["userid"]) . '.txt');

//主催者に事後報告
$date = date('Y/m/d H:i:s');
$nicknameo = nickname(id_promoter());
$nicknamen = nickname(basename($_POST["userid"]));

$content = "$nicknameo 様

$eventname の共同運営者の追加が完了しました。
$nicknamen 様が共同運営者として新たに加わりました。

　実行日時：$date
";
//内部関数で送信
sendmail(email(id_promoter()), '共同運営者の追加が完了しました', $content);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../css/bootstrap.css">
<title>共同運営者登録完了 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
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
<h1>共同運営者登録完了</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
あなたは共同運営者として新たに登録されました。<br><br>
<a href="../index.php">ログインページへ</a>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
