<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;
$examsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);

$examfilename = basename($_POST["examname"]);
if ($examfilename == "") die('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam/' . $examfilename . '.txt')) die('ファイルが存在しません。');
$answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam/' . $examfilename . '.txt'), true);

list($author, $id) = explode("/", $answerdata["_realid"]);

if ($author == "" or $id == "") die('内部パラメーターエラー');


if ($answerdata["_state"] != 0) die();

$echoforceclose = FALSE;
$leader = id_leader("submit");
if ($leader != NULL) {
    if ($leader == $_SESSION["userid"] and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($noprom) {
    if (!($nopermission and !$bymyself) and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($_SESSION["state"] == 'p') {
    if (isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if (!$echoforceclose) redirect("../list/index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("../list/index.php");

if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//〆処理
$result = exam_totalization_new($examfilename, TRUE);

switch ($result){
    case 0:
        register_alert("<p>投票を強制的に締め切りました。</p><p>既に投票されていたデータを集計しました。<br>メンバー間で意見が分かれたため、<strong>この作品の承認可否について議論する必要があります</strong>。<br>以下の「確認未完了の提出物」の項目から、簡易チャット画面に移って下さい。</p>", "success");
    break;
    case 1:
        register_alert("<p>投票を強制的に締め切りました。</p><p>既に投票されていたデータを集計しました。<br>承認しても問題無いという意見で一致したため、<strong>この作品を承認しました</strong>。<br>作品の提出者に承認の通知をしました。</p>", "success");
    break;
    case 2:
        register_alert("<p>投票を強制的に締め切りました。</p><p>既に投票されていたデータを集計しました。<br>軽微な修正が必要であるという意見で一致したため、<strong>この作品を修正待ち状態にしました</strong>。<br>作品の提出者に、修正依頼の通知をします。</p>", "success");
    break;
    case 3:
        register_alert("<p>投票を強制的に締め切りました。</p><p>既に投票されていたデータを集計しました。<br>このイベントに相応しくないという意見で一致したため、<strong>この作品の承認を見送りました</strong>。<br>作品の提出者に承認見送りの通知をします。</p>", "success");
    break;
}

redirect("../list/index.php");
