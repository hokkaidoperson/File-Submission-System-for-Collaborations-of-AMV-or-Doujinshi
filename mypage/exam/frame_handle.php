<?php
require_once('../../set.php');
setup_session();
session_validation();

$subject = basename($_POST["subject"]);

if ($_SESSION["state"] == 'g' or $_SESSION["state"] == 'o') die();

$leader = id_leader("submit");
if ($leader != NULL) {
    if ($leader != $_SESSION["userid"]) redirect("./index.php");
} else redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


csrf_prevention_validate();
if (!file_exists(DATAROOT . 'exam/' . $subject . '.txt')) die('ファイルが存在しません。');
$answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam/' . $subject . '.txt'), true);
list($author, $id) = explode("/", $answerdata["_realid"]);
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["reason"] == "") $invalid = TRUE;
else if(length_with_lb($_POST["reason"]) > 500) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

if ($answerdata["_state"] != 4) die();


//結果を保存
$answerdata["_state"] = 3;
$answerdata["_result"]["reason"] = $_POST["reason"];

$filedatajson = json_encode($answerdata);
if (file_put_contents_repeat(DATAROOT . 'exam/' . $subject . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

//入力内容を読み込んで書き換え
$formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
$formdata["exam"] = $answerdata["_result"]["opinion"];
$filedatajson =  json_encode($formdata);
if (file_put_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');

$authornick = nickname($author);

switch ($answerdata["_result"]["opinion"]){
    case 2:
        $authorsubject = '作品を修正して下さい（' . $formdata["title"] . '）';
    break;
    case 3:
        $authorsubject = '作品の承認が見送られました（' . $formdata["title"] . '）';
    break;
}

$reasons = "◇" . $_POST["reason"] . "\n\n";

switch ($answerdata["_result"]["opinion"]){
    case 2:
        $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイルの軽微な修正が必要と判断されました。
お手数ですが、以下をご確認頂き、ファイルの再提出をして頂けますと幸いです。


【修正が必要と判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには（ファイル提出の締め切り前まで）】
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。


【既にファイル提出の締め切りを迎えている場合】
大変お手数ですが、主催者にご相談願います。
主催者が認めた場合は、締め切り後であっても入力内容の編集を行えます。
";
    break;
    case 3:
        $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、提出されたファイルは、内容などの観点上、本イベントに相応しくないと判断されました。
そのため、大変恐れ入りますが、この作品の承認を見送らせて頂きます。


【相応しくないと判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには】
本イベントに相応しくないとされる内容を修正の上、ファイルを再提出する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
    break;
}
sendmail(email($author), $authorsubject, $content);


register_alert("提出者への通知が完了しました。", "success");

redirect("./index.php");
