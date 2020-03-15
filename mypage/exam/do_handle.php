<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

$accessok = 'none';

//主催・共同運営
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') $accessok = 'ok';

if ($accessok == 'none') redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");

$subject = basename($_POST["subject"]);

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");
if (!file_exists(DATAROOT . 'exam/' . $subject . '.txt')) die('ファイルが存在しません。');
list($author, $id) = explode('_', $subject);
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

switch ($_POST["ans"]) {
    case "1": break;
    case "2": break;
    case "3": break;
    default: $invalid = TRUE;
}

if($_POST["reason"] == ""){
  if($_POST["ans"] == "2" || $_POST["ans"] == "3") $invalid = TRUE;
} else if(length_with_lb($_POST["reason"]) > 500) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

//回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam/' . $subject . '.txt'), true);
$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
}

if ($answerdata["_state"] != 0) die();
if (array_search($_SESSION["userid"], $submitmem) === FALSE) die();

if ($author == $_SESSION["userid"]) die();

//データを記録する
$answerdata[$_SESSION["userid"]] = array(
    "opinion" => $_POST["ans"],
    "reason" => $_POST["reason"]
);
if (json_pack(DATAROOT . 'exam/' . $subject . '.txt', $answerdata) === FALSE) die("回答内容の書き込みに失敗しました。");

//回答完了確認・〆処理
$result = exam_totalization_new($subject, FALSE);

switch ($result){
    case 0:
        $_SESSION['situation'] = 'exam_submitted_discuss';
    break;
    case 1:
        $_SESSION['situation'] = 'exam_submitted_accept';
    break;
    case 2:
        $_SESSION['situation'] = 'exam_submitted_reject_m';
    break;
    case 3:
        $_SESSION['situation'] = 'exam_submitted_reject';
    break;
}
if ($result === FALSE) $_SESSION['situation'] = 'exam_submitted';

redirect("./index.php");
