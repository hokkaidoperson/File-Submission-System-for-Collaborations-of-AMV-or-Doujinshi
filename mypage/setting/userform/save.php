<?php
require_once('../../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../../index.php");
}

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') redirect("./index.php");


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["number"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9]$/', $_POST["number"])) $invalid = TRUE;
switch ($_POST["type"]) {
    case "textbox": break;
    case "textbox2": break;
    case "textarea": break;
    case "radio": break;
    case "check": break;
    case "dropdown": break;
    case "attach": break;
    default: $invalid = TRUE;
}
if($_POST["id"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9]*$/', $_POST["id"])) $invalid = TRUE;
switch ($_POST["required"]) {
    case "0": break;
    case "1": break;
    case "2": break;
    default: $invalid = TRUE;
}

//必須の場合のパターン 文字数
if($_POST["title"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["title"]) > 50) $invalid = TRUE;

//文字数 必須でない
if($_POST["detail"] == ""){
} else if(length_with_lb($_POST["detail"]) > 500) $invalid = TRUE;

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

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


$number = $_POST['number'];

//基本的にはPOSTの内容をそのまま保存する
$_SESSION["userformdata"][$number] = $_POST;

//いらないやつをunset
unset($_SESSION["userformdata"][$number]["successfully"]);
unset($_SESSION["userformdata"][$number]["number"]);

//ファイル内容
$filedata = $_SESSION["userformdata"][$number];

$filedatajson = json_encode($filedata);

if (!file_exists(DATAROOT . 'form/userinfo/draft/')) {
    if (!mkdir(DATAROOT . 'form/userinfo/draft/')) die_mypage('ディレクトリの作成に失敗しました。');
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
        copy(DATAROOT . 'form/userinfo/' . "$i" . '.txt', DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt');
    }
}


$fileplace = DATAROOT . 'form/userinfo/draft/' . $number . '.txt';

if (file_put_contents($fileplace, $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');

$_SESSION['situation'] = 'userform_saved';

redirect("./index.php");
