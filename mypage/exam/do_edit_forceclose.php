<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

$author = basename($_POST["author"]);
$id = basename($_POST["id"]);
$editid = basename($_POST["edit"]);

//回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt'), true);
if ($answerdata["_state"] != 0) die();

$submitmem = file(DATAROOT . 'exammember_' . $answerdata["_membermode"] . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$echoforceclose = FALSE;
if ($noprom) {
    if (!($nopermission and !$bymyself) and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($_SESSION["state"] == 'p') {
    if (isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if (!$echoforceclose) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


if ($author == "" or $id == "" or $editid == "") die('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt')) die('ファイルが存在しません。');
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//〆処理
$result = exam_totalization_edit($author . '_' . $id . '_' . $editid, TRUE);

switch ($result){
    case 0:
        register_alert("投票を強制的に締め切りました。<br><br>既に投票されていたデータを集計しました。<br>承認しても問題無いという意見で一致したため、<b>この変更を承認しました</b>。<br>作品の提出者に承認の通知をしました。", "success");
    break;
    case 1:
        register_alert("投票を強制的に締め切りました。<br><br>既に投票されていたデータを集計しました。<br>承認しても問題無いという意見で一致したため、<b>この共通情報を承認しました</b>。<br>情報の提出者に承認の通知をしました。", "success");
    break;
    case 2:
        register_alert("投票を強制的に締め切りました。<br><br>既に投票されていたデータを集計しました。<br>問題があるという意見で一致したため、<b>この変更を拒否しました</b>。<br>作品の提出者に拒否の通知をしました。", "success");
    break;
}

redirect("./index.php");
