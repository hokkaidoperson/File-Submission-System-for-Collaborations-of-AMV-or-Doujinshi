<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p", "c"))) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");

$subject = basename($_POST["subject"]);

csrf_prevention_validate();
if (!file_exists(DATAROOT . 'exam_edit/' . $subject . '.txt')) die('ファイルが存在しません。');
$answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $subject . '.txt'), true);

list($author, $id) = explode("/", $answerdata["_realid"]);
if ($author == "" or $id == "") die('内部パラメーターエラー');
if ($id == "common") die('内部パラメーターエラー');
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["add"] == "") $invalid = TRUE;
else if(length_with_lb($_POST["add"]) > 500) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

if ($answerdata["_state"] != 1) die();

$memberfile = DATAROOT . 'exammember_' . $answerdata["_membermode"] . '.txt';

$submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
}

if (array_search($_SESSION["userid"], $submitmem) === FALSE) die();

//議論ログ
if (!file_exists(DATAROOT . 'exam_edit_discuss/' . $subject . '.txt')) die('ファイルが存在しません。');
$discussdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit_discuss/' . $subject . '.txt'), true);

//入力内容を読み込む（作品名はなんじゃろな）
$formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);


//ログにデータ追加
$discussdata["comments"][$_SESSION["userid"] . "_" . time()] = $_POST["add"];

//既読を未読にする＆通知飛ばす
$authornick = nickname($author);
$pageurl = $siteurl . 'mypage/exam/discuss_edit.php?examname=' . $subject;
foreach ($submitmem as $key) {
    if ((string)$key === $_SESSION["userid"]) continue;
    if (isset($discussdata["read"][$key]) and $discussdata["read"][$key] == 1) {
        $discussdata["read"][$key] = 0;
        $nickname = nickname($key);
        $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更に関する議論について、コメントが追加されました。
簡易チャットページを再確認し、必要に応じてコメントして下さい。

※この通知は、あなたが簡易チャットページを最後に確認した後にコメントが追加された際に、それを通知するためのものです。
　あなたが簡易チャットページを再確認するまでは、コメントが追加されても通知されません。


　簡易チャットページ：$pageurl
";
        sendmail(email($key), 'コメント追加のお知らせ（「' . $formdata["title"] . '」の内容変更に関する議論）', $content);
    }
}

$filedatajson = json_encode($discussdata);
if (file_put_contents_repeat(DATAROOT . 'exam_edit_discuss/' . $subject . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');

register_alert("コメントを追加しました。", "success");

redirect("./discuss_edit.php?examname=$subject");
