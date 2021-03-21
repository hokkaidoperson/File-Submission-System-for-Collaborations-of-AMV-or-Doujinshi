<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

$examfilename = basename($_POST["examname"]);
if ($examfilename == "") die('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam_edit/' . $examfilename . '.txt')) die('ファイルが存在しません。');
$answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $examfilename . '.txt'), true);

list($author, $id, $editid) = explode("/", $answerdata["_realid"]);
if ($author == "" or $id == "" or $editid == "") die('内部パラメーターエラー');
if ($id == "common") die('内部パラメーターエラー');

if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

if ($answerdata["_state"] != 0) die();

$submitmem = file(DATAROOT . 'exammember_' . $answerdata["_membermode"] . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$echoforceclose = FALSE;
$leader = id_leader($answerdata["_membermode"]);
if ($leader != NULL) {
    if ($leader == $_SESSION["userid"] and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($noprom) {
    if (!($nopermission and !$bymyself) and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($_SESSION["state"] == 'p') {
    if (isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if (!$echoforceclose) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


//〆処理
$result = exam_totalization_edit($examfilename, TRUE);

switch ($result){
    case 0:
        register_alert("<p>投票を強制的に締め切りました。</p><p>既に投票されていたデータを集計しました。<br>メンバー間で意見が分かれたため、<strong>この変更の承認・拒否について議論する必要があります</strong>。<br>以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。</p>", "success");
    break;
    case 1:
        register_alert("<p>投票を強制的に締め切りました。</p><p>既に投票されていたデータを集計しました。<br>承認しても問題無いという意見で一致したため、<strong>この変更を承認しました</strong>。<br>作品の提出者に承認の通知をしました。</p>", "success");
    break;
    case 2:
        register_alert("<p>投票を強制的に締め切りました。</p><p>既に投票されていたデータを集計しました。<br>問題があるという意見で一致したため、<strong>この変更を拒否しました</strong>。<br>作品の提出者に拒否の通知をします。</p>", "success");
    break;
}

redirect("./index.php");
