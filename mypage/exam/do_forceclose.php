<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$author = basename($_GET["author"]);
$id = basename($_GET["id"]);


//回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam/' . $author . '_' . $id . '.txt'), true);
if ($answerdata["_state"] != 0) die();

$echoforceclose = FALSE;
if ($noprom) {
    if (!($nopermission and !$bymyself) and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($_SESSION["state"] == 'p') {
    if (isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if (!$echoforceclose) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


if ($author == "" or $id == "") die('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam/' . $author . '_' . $id . '.txt')) die('ファイルが存在しません。');
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//〆処理
$result = exam_totalization_new($author . '_' . $id, TRUE);

switch ($result){
    case 0:
        $_SESSION['situation'] = 'exam_forceclose_discuss';
    break;
    case 1:
        $_SESSION['situation'] = 'exam_forceclose_accept';
    break;
    case 2:
        $_SESSION['situation'] = 'exam_forceclose_reject_m';
    break;
    case 3:
        $_SESSION['situation'] = 'exam_forceclose_reject';
    break;
}

redirect("./index.php");
