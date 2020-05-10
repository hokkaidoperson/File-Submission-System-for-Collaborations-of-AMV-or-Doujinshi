<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

$submitmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$author = basename($_POST["author"]);
$editid = basename($_POST["edit"]);

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
        register_alert("投票を強制的に締め切りました。<br><br>既に投票されていたデータを集計しました。<br>メンバー間で意見が分かれたため、<b>この変更の承認・拒否について議論する必要があります</b>。<br>以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。", "success");
    break;
    case 1:
        register_alert("投票を強制的に締め切りました。<br><br>既に投票されていたデータを集計しました。<br>承認しても問題無いという意見で一致したため、<b>この変更を承認しました</b>。<br>作品の提出者に承認の通知をしました。", "success");
    break;
    case 2:
        register_alert("投票を強制的に締め切りました。<br><br>既に投票されていたデータを集計しました。<br>問題があるという意見で一致したため、<b>この変更を拒否しました</b>。<br>作品の提出者に拒否の通知をしました。", "success");
    break;
}

redirect("./index.php");
