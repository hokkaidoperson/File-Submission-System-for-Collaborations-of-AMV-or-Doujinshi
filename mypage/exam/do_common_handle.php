<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p", "c"))) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");

$subject = basename($_POST["subject"]);

csrf_prevention_validate();
if (!file_exists(DATAROOT . 'exam_edit/' . $subject . '.txt')) die('ファイルが存在しません。');
$answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $subject . '.txt'), true);

list($author, $id, $editid) = explode("/", $answerdata["_realid"]);
if ($author == "" or $id == "" or $editid == "") die('内部パラメーターエラー');
if ($id != "common") die('内部パラメーターエラー');
if (!file_exists(DATAROOT . "users/" . $author . ".txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

switch ($_POST["ans"]) {
    case "1": break;
    case "2": break;
    default: $invalid = TRUE;
}

if($_POST["reason"] == ""){
  if($_POST["ans"] == "2") $invalid = TRUE;
} else if(length_with_lb($_POST["reason"]) > 500) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$memberfile = DATAROOT . 'exammember_edit.txt';
$submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
if (json_pack(DATAROOT . 'exam_edit/' . $subject . '.txt', $answerdata) === FALSE) die("回答内容の書き込みに失敗しました。");

//回答完了確認・〆処理
$result = exam_totalization_edit($subject, FALSE);

switch ($result){
    case 0:
        if ($result === FALSE) register_alert("確認結果を送信しました。<br>他のメンバーが確認を終えるまでしばらくお待ち願います。", "success");
        else register_alert("<p>確認結果を送信しました。</p><p>全てのメンバーが共通情報の確認を終えました。<br>メンバー間で意見が分かれたため、<b>この内容の承認・拒否について議論する必要があります</b>。<br>以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。</p>", "success");
    break;
    case 1:
        register_alert("<p>確認結果を送信しました。</p><p>全てのメンバーが共通情報の確認を終えました。<br>承認しても問題無いという意見で一致したため、<b>この内容を承認しました</b>。<br>情報の提出者に承認の通知をしました。</p>", "success");
    break;
    case 2:
        register_alert("<p>確認結果を送信しました。</p><p>全てのメンバーが共通情報の確認を終えました。<br>問題があるという意見で一致したため、<b>この内容を拒否しました</b>。<br>情報の提出者に拒否の通知をします。</p>", "success");
    break;
}

redirect("./index.php");
