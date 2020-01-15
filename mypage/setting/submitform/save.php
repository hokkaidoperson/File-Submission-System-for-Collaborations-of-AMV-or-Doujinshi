<?php
require_once('../../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../../index.php?redirto=mypage/setting/submitform/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
');


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if ($_POST["type"] != "general") {
    if($_POST["number"] == "") $invalid = TRUE;
    else if(!preg_match('/^[0-9]$/', $_POST["number"])) $invalid = TRUE;
}
switch ($_POST["type"]) {
    case "textbox": break;
    case "textbox2": break;
    case "textarea": break;
    case "radio": break;
    case "check": break;
    case "dropdown": break;
    case "attach": break;
    case "general": break;
    default: $invalid = TRUE;
}
if ($_POST["type"] != "general") {
    if($_POST["id"] == "") $invalid = TRUE;
    else if(!preg_match('/^[0-9]*$/', $_POST["id"])) $invalid = TRUE;
    switch ($_POST["required"]) {
        case "0": break;
        case "1": break;
        case "2": break;
        default: $invalid = TRUE;
    }
}

//必須の場合のパターン 文字数
if ($_POST["type"] != "general") {
    if($_POST["title"] == "") $invalid = TRUE;
    else if(mb_strlen($_POST["title"]) > 50) $invalid = TRUE;
}

//文字数 必須でない
if($_POST["detail"] == ""){
} else if(mb_strlen($_POST["detail"]) > 500) $invalid = TRUE;

//文字種・数の範囲 必須でない
if($_POST["max"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["max"])) $invalid = TRUE;
else if((int)$_POST["max"] < 1 or (int)$_POST["max"] > 9999) $invalid = TRUE;
if($_POST["min"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["min"])) $invalid = TRUE;
else if((int)$_POST["min"] < 1 or (int)$_POST["min"] > 9999) $invalid = TRUE;
if($_POST["max2"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["max2"])) $invalid = TRUE;
else if((int)$_POST["max2"] < 1 or (int)$_POST["max2"] > 9999) $invalid = TRUE;
if($_POST["min2"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["min2"])) $invalid = TRUE;
else if((int)$_POST["min2"] < 1 or (int)$_POST["min2"] > 9999) $invalid = TRUE;

//文字数 必須でない
if($_POST["prefix_a"] == ""){
} else if(mb_strlen($_POST["prefix_a"]) > 50) $invalid = TRUE;
if($_POST["prefix_b"] == ""){
} else if(mb_strlen($_POST["prefix_b"]) > 50) $invalid = TRUE;
if($_POST["suffix_a"] == ""){
} else if(mb_strlen($_POST["suffix_a"]) > 50) $invalid = TRUE;
if($_POST["suffix_b"] == ""){
} else if(mb_strlen($_POST["suffix_b"]) > 50) $invalid = TRUE;

switch ($_POST["arrangement"]) {
    case "": break;
    case "h": break;
    default: $invalid = TRUE;
}

if($_POST["list"] == "") {
    if ($_POST["type"] == "radio" or $_POST["type"] == "check" or $_POST["type"] == "dropdown") $invalid = TRUE;
}

//必須の場合のパターン 文字数
if($_POST["type"] == "attach" and $_POST["ext"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9a-z,]*$/', $_POST["ext"])) $invalid = TRUE;
if($_POST["type"] == "general" and $_POST["ext"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9a-z,]*$/', $_POST["ext"])) $invalid = TRUE;

//文字種 必須でない
if($_POST["width"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["width"])) $invalid = TRUE;
if($_POST["width2"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["width2"])) $invalid = TRUE;
if($_POST["height"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["height"])) $invalid = TRUE;

if($_POST["size"] == ""){
} else if(!preg_match('/^[0-9]*$/', $_POST["size"])) $invalid = TRUE;
else if((int)$_POST["size"] < 1 or (int)$_POST["size"] > FILE_MAX_SIZE) $invalid = TRUE;

switch ($_POST["recheck"]) {
    case "": break;
    case "auto": break;
    default: $invalid = TRUE;
}

//日付・時刻
if ($_POST["type"] == "general") {
    list($Y, $m, $d) = explode('-', $_POST["from_date"]);
    if (checkdate($m, $d, $Y) !== true) $invalid = TRUE;
    list($Y, $m, $d) = explode('-', $_POST["until_date"]);
    if (checkdate($m, $d, $Y) !== true) $invalid = TRUE;

    list($hr, $mn) = explode(':', $_POST["from_time"]);
    if ($hr < 0 and $hr > 23) $invalid = TRUE;
    if ($mn < 0 and $mn > 59) $invalid = TRUE;
    list($hr, $mn) = explode(':', $_POST["until_time"]);
    if ($hr < 0 and $hr > 23) $invalid = TRUE;
    if ($mn < 0 and $mn > 59) $invalid = TRUE;
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


if (isset($_POST['number'])) $number = $_POST['number'];
else $number = "general";

//基本的にはPOSTの内容をそのまま保存する
$_SESSION["submitformdata"][$number] = $_POST;

//日付だけunixスタンプに変換
if ($number == "general") {
    list($Yf, $mf, $df) = explode('-', $_POST["from_date"]);
    list($hrf, $mnf) = explode(':', $_POST["from_time"]);
    list($Yu, $mu, $du) = explode('-', $_POST["until_date"]);
    list($hru, $mnu) = explode(':', $_POST["until_time"]);
    $_SESSION["submitformdata"][$number]["from"] = mktime($hrf, $mnf, 0, $mf, $df, $Yf);
    $_SESSION["submitformdata"][$number]["until"] = mktime($hru, $mnu, 0, $mu, $du, $Yu);
}


//いらないやつをunset
unset($_SESSION["submitformdata"][$number]["successfully"]);
unset($_SESSION["submitformdata"][$number]["number"]);
unset($_SESSION["submitformdata"][$number]["from_date"]);
unset($_SESSION["submitformdata"][$number]["from_time"]);
unset($_SESSION["submitformdata"][$number]["until_time"]);
unset($_SESSION["submitformdata"][$number]["until_date"]);

//ファイル内容
$filedata = $_SESSION["submitformdata"][$number];

$filedatajson = json_encode($filedata);

$fileplace = DATAROOT . 'form/submit/draft/' . $number . '.txt';

if (file_put_contents($fileplace, $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');

$_SESSION['situation'] = 'submitform_saved';

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
