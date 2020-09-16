<?php
require_once('../../set.php');
setup_session();
session_validation();

$subject = basename($_POST["subject"]);

if (!file_exists(DATAROOT . 'exam_edit/' . $subject . '.txt')) die('ファイルが存在しません。');
$answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $subject . '.txt'), true);
if ($answerdata["_state"] != 4) die();

list($author, $id, $editid) = explode('/', $answerdata["_realid"]);
if ($author == "" or $id == "" or $editid == "") die('内部パラメーターエラー');
if ($id != "common") die('内部パラメーターエラー');
if (!file_exists(DATAROOT . "users/$author.txt")) die('ファイルが存在しません。');

if ($_SESSION["state"] == 'g' or $_SESSION["state"] == 'o') die();

$leader = id_leader("edit");
if ($leader != NULL) {
    if ($leader != $_SESSION["userid"]) redirect("./index.php");
} else redirect("./index.php");

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


csrf_prevention_validate();

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["reason"] == "") $invalid = TRUE;
else if(length_with_lb($_POST["reason"]) > 500) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//結果を保存
$answerdata["_state"] = 3;
$answerdata["_result"]["reason"] = $_POST["reason"];

$filedatajson = json_encode($answerdata);
if (file_put_contents_repeat(DATAROOT . 'exam_edit/' . $subject . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

//入力内容を読み込んで書き換え
$formdata = json_decode(file_get_contents_repeat(DATAROOT . "users/$author.txt"), true);
$formdata["common_editing"] = 0;
if ($answerdata["_commonmode"] === "new") $formdata["common_acceptance"] = $answerdata["_result"]["opinion"];
$filedatajson = json_encode($formdata);
if (file_put_contents_repeat(DATAROOT . "users/$author.txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');

$authornick = nickname($author);


$authorsubject = '内容の承認が見送られました（共通情報）';

$reasons = "◇" . $_POST["reason"] . "\n\n";


if ($answerdata["_commonmode"] === "new") $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。";
else $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。\n現在は、変更前の内容を維持したままの状態となっています。";
$content = "$authornick 様

あなたの共通情報について、イベントの運営メンバーが確認しました。
確認の結果、その内容に問題があると判断されました。
$changeinfo


【問題があると判断された理由（ファイル確認者によるコメント）】
$reasons

【再編集をするには】
問題があるとされる内容を修正の上、共通情報を再編集する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「共通情報の入力・編集」をクリックして下さい。
";
sendmail(email($author), $authorsubject, $content);
unlink(DATAROOT . "edit/$author/common.txt");

register_alert("提出者への通知が完了しました。", "success");

redirect("./index.php");
