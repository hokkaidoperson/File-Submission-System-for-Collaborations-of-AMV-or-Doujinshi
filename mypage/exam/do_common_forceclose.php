<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

$submitmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$author = basename($_GET["author"]);
$editid = basename($_GET["edit"]);

//回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $author . '_common_' . $editid . '.txt'), true);
if ($answerdata["_state"] != 0) die();

$echoforceclose = FALSE;
if ($noprom) {
    if (!($nopermission and !$bymyself) and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($_SESSION["state"] == 'p') {
    if (isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if (!$echoforceclose) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


if ($author == "" or $editid == "") die('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam_edit/' . $author . '_common_' . $editid . '.txt')) die('ファイルが存在しません。');
if (!file_exists(DATAROOT . "users/$author.txt")) die('ファイルが存在しません。');

//〆処理
$result = exam_totalization_edit($author . '_common_' . $editid, TRUE);

switch ($result){
    case 0:
        $_SESSION['situation'] = 'exam_common_forceclose_discuss';
    break;
    case 1:
        $_SESSION['situation'] = 'exam_common_forceclose_accept';
    break;
    case 2:
        $_SESSION['situation'] = 'exam_common_forceclose_reject';
    break;
}

redirect("./index.php");
