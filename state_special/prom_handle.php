<?php
require_once('../set.php');

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

$invalid = FALSE;

//sectokをもっかいチェック
if (file_exists(DATAROOT . 'mail/state/promoter.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/state/promoter.txt'), true);
    if ($filedata["sectok"] !== $_POST["sectok"]) $invalid = TRUE;
    if ($filedata["new"] != $_POST["userid"]) $invalid = TRUE;
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

//前の主催者
$prom = id_state("p");
$oldprom = id_array($prom[0]);

//新しい主催者
$newprom = id_array($_POST["userid"]);
//…の前の立場
$oldstate = $newprom["state"];


//前の主催者の状態を一般参加者にして保存
$oldprom["state"] = "g";
$userdatajson =  json_encode($oldprom);
if (file_put_contents(DATAROOT . 'users/' . $prom[0] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//新しい人を主催者に
$newprom["state"] = "p";
$userdatajson =  json_encode($newprom);
if (file_put_contents(DATAROOT . 'users/' . basename($_POST["userid"]) . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//立場別一覧の書き換え
$statedata = basename($_POST["userid"]) . "\n";
$statedtp = DATAROOT . 'users/_promoter.txt';
if (file_put_contents($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

if ($oldstate == "c") $statedtp = DATAROOT . 'users/_co.txt';
else if ($oldstate == "g") $statedtp = DATAROOT . 'users/_general.txt';
else $statedtp = DATAROOT . 'users/_outsider.txt';
$array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search(basename($_POST["userid"]), $array);
unset($array[$key]);
$statedata = implode("\n", $array) . "\n";
if (file_put_contents($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

$statedata = $prom[0] . "\n";
$statedtp = DATAROOT . 'users/_general.txt';
if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');



//新しい主催者がファイル確認メンバーにいたら、専用の名前「_promoter」に書き換える（「_promoter」がいたら消すだけ）
$ismember_submit = FALSE;
$ismember_edit = FALSE;
$array = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search(basename($_POST["userid"]), $array);
if ($key !== FALSE) {
    $ismember_submit = TRUE;
    unset($array[$key]);
    if (array_search("_promoter", $array) === FALSE) $array[] = "_promoter";
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents(DATAROOT . 'exammember_submit.txt', $statedata) === FALSE) die('システムデータの書き込みに失敗しました。');
}
$array = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search(basename($_POST["userid"]), $array);
if ($key !== FALSE) {
    $ismember_edit = TRUE;
    unset($array[$key]);
    if (array_search("_promoter", $array) === FALSE) $array[] = "_promoter";
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents(DATAROOT . 'exammember_edit.txt', $statedata) === FALSE) die('システムデータの書き込みに失敗しました。');
}


//ファイル確認関連ファイルも書き換え
exam_totalization_new("_all", FALSE);
exam_totalization_edit("_all", FALSE);


//招待リンクを消す
unlink(DATAROOT . 'mail/state/promoter.txt');

//両者に事後報告
$date = date('Y/m/d H:i:s');
$nicknameo = nickname($prom[0]);
$nicknamen = nickname(basename($_POST["userid"]));

$content = "$nicknameo 様

$eventname の主催者の交代が完了しました。
$nicknamen 様が新たな主催者となり、あなたは一般参加者となりました。

　実行日時：$date
";
//内部関数で送信
sendmail(email($prom[0]), '主催者の交代が完了しました', $content);

$content = "$nicknamen 様

$eventname の主催者の交代が完了しました。
以前の主催者 $nicknameo 様は一般参加者となり、あなたは新たな主催者となりました。

　実行日時：$date
";
//内部関数で送信
sendmail(email(basename($_POST["userid"])), '主催者の交代が完了しました', $content);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../css/bootstrap.css">
<title>主催者交代完了 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
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
<h1>主催者交代完了</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
主催者が交代し、あなたが新しい主催者となりました。<br><br>
<a href="../index.php">ログインページへ</a>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
