<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


csrf_prevention_validate();

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["number"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9]$/', $_POST["number"])) $invalid = TRUE;
switch ($_POST["type"]) {
    case "textbox": break;
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

for ($i = 0; $i < 5; $i++) {
    //文字種・数の範囲 必須でない
    if($_POST["max"][$i] == ""){
    } else if(!preg_match('/^[0-9]*$/', $_POST["max"][$i])) $invalid = TRUE;
    else if((int)$_POST["max"][$i] < 1 or (int)$_POST["max"][$i] > 9999) $invalid = TRUE;
    if($_POST["min"][$i] == ""){
    } else if(!preg_match('/^[0-9]*$/', $_POST["min"][$i])) $invalid = TRUE;
    else if((int)$_POST["min"][$i] < 1 or (int)$_POST["min"][$i] > 9999) $invalid = TRUE;
    //文字数 必須でない
    if($_POST["prefix"][$i] == ""){
    } else if(mb_strlen($_POST["prefix"][$i]) > 50) $invalid = TRUE;
    if($_POST["suffix"][$i] == ""){
    } else if(mb_strlen($_POST["suffix"][$i]) > 50) $invalid = TRUE;
    //文字種 必須でない
    if($_POST["width"][$i] == ""){
    } else if(!preg_match('/^[0-9]*$/', $_POST["width"][$i])) $invalid = TRUE;
    if($_POST["height"][$i] == ""){
    } else if(!preg_match('/^[0-9]*$/', $_POST["height"][$i])) $invalid = TRUE;
}

switch ($_POST["arrangement"][0]) {
    case "": break;
    case "h": break;
    default: $invalid = TRUE;
}

if($_POST["list"] == "") {
    if ($_POST["type"] == "radio" or $_POST["type"] == "check" or $_POST["type"] == "dropdown") $invalid = TRUE;
}

if($_POST["type"] == "attach") {
    if ($_POST["ext"] == "") $invalid = TRUE;
    else if(!preg_match('/^[0-9a-z,]*[0-9a-z]$/', $_POST["ext"])) $invalid = TRUE;

    if($_POST["size"] == ""){
    } else if(!preg_match('/^[0-9]*$/', $_POST["size"])) $invalid = TRUE;
    else if((int)$_POST["size"] < 1 or (int)$_POST["size"] > FILE_MAX_SIZE) $invalid = TRUE;

    if($_POST["filenumber"] == ""){
    } else if(!preg_match('/^[0-9]*$/', $_POST["filenumber"])) $invalid = TRUE;
    else if((int)$_POST["filenumber"] < 1 or (int)$_POST["filenumber"] > 100) $invalid = TRUE;

    if($_POST["reso"][0] == "" and $_POST["reso"][1] == ""){
    } else if($_POST["reso"][0] == "" or $_POST["reso"][1] == "") $invalid = TRUE;
    else if(!preg_match('/^[0-9]*$/', $_POST["reso"][0]) or !preg_match('/^[0-9]*$/', $_POST["reso"][1])) $invalid = TRUE;
    else if((int)$_POST["reso"][0] < 1 or (int)$_POST["reso"][1] < 1) $invalid = TRUE;

    if($_POST["length"][0] == "" and $_POST["length"][1] == ""){
    } else if($_POST["length"][0] == "" or $_POST["length"][1] == "") $invalid = TRUE;
    else if(!preg_match('/^[0-9]*$/', $_POST["length"][0]) or !preg_match('/^[0-9]*$/', $_POST["length"][1])) $invalid = TRUE;
    else if((int)$_POST["length"][0] < 0 or (int)$_POST["length"][1] < 0 or (int)$_POST["length"][1] > 59) $invalid = TRUE;
}

switch ($_POST["recheck"][0]) {
    case "": break;
    case "auto": break;
    default: $invalid = TRUE;
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


$number = $_POST['number'];

//基本的にはPOSTの内容をそのまま保存する
$_SESSION["userformdata"][$number] = $_POST;

//分秒を秒数に変換
if($_POST["length"][0] != "" and $_POST["length"][1] != ""){
    $_SESSION["userformdata"][$number]["length"] = (int)$_POST["length"][0] * 60 + (int)$_POST["length"][1];
} else $_SESSION["userformdata"][$number]["length"] = "";

//いらないやつをunset
unset($_SESSION["userformdata"][$number]["csrf_prevention_token"]);
unset($_SESSION["userformdata"][$number]["number"]);

register_alert("設定内容を一時的に保存しました。<br>実際の入力欄にはまだ反映されていません。<strong>設定を完了する場合は、「変更内容を保存し適用する」ボタンを押して下さい</strong>。");

redirect("./index.php");
