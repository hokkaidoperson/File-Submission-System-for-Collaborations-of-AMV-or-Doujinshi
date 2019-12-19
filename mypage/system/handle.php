<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/account/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

if (!$_SESSION["admin"]) die('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>システム管理者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//未入力の場合強制終了
if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

//必須の場合のパターン 文字数
if($_POST["eventname"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["eventname"]) > 50) $invalid = TRUE;

if($_POST["filesize"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9]*$/', $_POST["filesize"])) $invalid = TRUE;
else if((int)$_POST["filesize"] < 1) $invalid = TRUE;

//メールアドレス形式確認　必須でない
if($_POST["system"] == ""){
} else if(!preg_match('/.+@.+\..+/', $_POST["system"])) $invalid = TRUE;

//文字数 必須でない
if($_POST["systemfrom"] == ""){
} else if(mb_strlen($_POST["systemfrom"]) > 30) $invalid = TRUE;

//文字数 必須でない
if($_POST["systempre"] == ""){
} else if(mb_strlen($_POST["systempre"]) > 15) $invalid = TRUE;

//文字種　どっちかかたっぽだけはNG
  if($_POST["recaptcha_sec"] == "" && $_POST["recaptcha_site"] == ""){
  } else if($_POST["recaptcha_sec"] == "" || $_POST["recaptcha_site"] == "") $invalid = TRUE;
  else if(!preg_match('/^[0-9a-zA-Z-]*$/', $_POST["recaptcha_sec"]) || !preg_match('/^[0-9a-zA-Z-]*$/', $_POST["recaptcha_site"])) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//イベント名書き込み
$eventname = $_POST["eventname"];
if (file_put_contents(DATAROOT . 'eventname.txt', $eventname) === FALSE) die('イベント名の書き込みに失敗しました。');

//maxサイズ
if (file_put_contents(DATAROOT . 'maxsize.txt', $_POST["filesize"]) === FALSE) die('ファイルサイズの書き込みに失敗しました。');

$maildata = array(
    "from" => $_POST["system"],
    "sendonly" => $_POST["systemsend"],
    "fromname" => $_POST["systemfrom"],
    "pre" => $_POST["systempre"]
);

$maildatajson =  json_encode($maildata);

if (file_put_contents(DATAROOT . 'mail.txt', $maildatajson) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//reCAPTCHA
$recdata = array(
    "site" => $_POST["recaptcha_site"],
    "sec" => $_POST["recaptcha_sec"],
);

$recdatajson =  json_encode($recdata);

if (file_put_contents(DATAROOT . 'rec.txt', $recdatajson) === FALSE) die('reCAPTCHA関連のデータの書き込みに失敗しました。');


$_SESSION["situation"] = "system_setting";
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
