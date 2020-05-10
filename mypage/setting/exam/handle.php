<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");

csrf_prevention_validate();

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

$f = $_POST["submit"];
if ($f == "") $f = array();
if((array)$f == array()) $invalid = TRUE;
foreach($f as $value) {
    if (!user_exists($value)) {
        $invalid = TRUE;
        break;
    }
    $dtl = id_array($value);
    if ($dtl["state"] == 'o' or $dtl["state"] == 'g') {
        $invalid = TRUE;
        break;
    }
}

$f = $_POST["edit"];
if ($f == "") $f = array();
if((array)$f == array()) $invalid = TRUE;
foreach($f as $value) {
    if (!user_exists($value)) {
        $invalid = TRUE;
        break;
    }
    $dtl = id_array($value);
    if ($dtl["state"] == 'o' or $dtl["state"] == 'g') {
        $invalid = TRUE;
        break;
    }
}

switch ($_POST["reason"]) {
    case "notice": break;
    case "dont-a": break;
    case "dont-b": break;
    default: $invalid = TRUE;
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//設定内容保存
$savedata = implode("\n", (array)$_POST["submit"]) . "\n";
$fileplace = DATAROOT . 'exammember_submit.txt';
if (file_put_contents($fileplace, $savedata) === FALSE) die('設定内容の書き込みに失敗しました。');

$savedata = implode("\n", (array)$_POST["edit"]) . "\n";
$fileplace = DATAROOT . 'exammember_edit.txt';
if (file_put_contents($fileplace, $savedata) === FALSE) die('設定内容の書き込みに失敗しました。');

$savedata = array(
    "submit_add" => $_POST["submit_add"],
    "edit_add" => $_POST["edit_add"],
    "reason" => $_POST["reason"]
);
$savedata = json_encode($savedata);
$fileplace = DATAROOT . 'examsetting.txt';
if (file_put_contents($fileplace, $savedata) === FALSE) die('設定内容の書き込みに失敗しました。');


//ファイル確認関連ファイルも書き換え
exam_totalization_new("_all", FALSE);
exam_totalization_edit("_all", FALSE);

register_alert("ファイル確認に関する設定変更が完了しました。", "success");

redirect("../../index.php");
