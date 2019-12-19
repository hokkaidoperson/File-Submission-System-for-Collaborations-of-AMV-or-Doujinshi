<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/exam/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$accessok = 'none';

//主催だけ（条件付きで共催も）
if ($_SESSION["state"] == 'p') $accessok = 'ok';


$prom = id_state('p');
//議論ログ
if (!file_exists(DATAROOT . 'exam_edit_discuss/' . $_POST["subject"] . '.txt')) die('ファイルが存在しません。');
$discussdata = json_decode(file_get_contents(DATAROOT . 'exam_edit_discuss/' . $_POST["subject"] . '.txt'), true);
if (!isset($discussdata["read"][$prom[0]])) $accessok = 'ok';


if ($accessok == 'none') die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
');

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");
if (!file_exists(DATAROOT . 'exam_edit/' . $_POST["subject"] . '.txt')) die('ファイルが存在しません。');
list($author, $id, $editid) = explode('_', $_POST["subject"]);
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

switch ($_POST["ans"]) {
    case "1": break;
    case "2": break;
    default: $invalid = TRUE;
}

if($_POST["reason"] == ""){
  if($_POST["ans"] == "2") $invalid = TRUE;
  else if(mb_strlen($_POST["reason"]) > 500) $invalid = TRUE;
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

//投票の回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $_POST["subject"] . '.txt'), true);
if ($answerdata["_state"] != 1) die();

if (!isset($answerdata[$_SESSION["userid"]])) die();
else if ($answerdata[$_SESSION["userid"]]["opinion"] == -1) die();

//フォームgeneralデータ（理由通知の設定呼び出し）
$formsetting = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);


//結果を保存
$answerdata["_state"] = 2;
$answerdata["_result"] = $_POST["ans"];
$discussdata["comments"]["-system_" . time()] = "最終結論の入力が完了し、議論を終了しました。";

$filedatajson = json_encode($answerdata);
if (file_put_contents(DATAROOT . 'exam_edit/' . $_POST["subject"] . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

$filedatajson = json_encode($discussdata);
if (file_put_contents(DATAROOT . 'exam_edit_discuss/' . $_POST["subject"] . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');

//入力内容を読み込んで書き換え
$formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
$formdata["editing"] = 0;
if ($_POST["ans"] == 1) {
    $formdata["exam"] = 1;
    $changeddata = json_decode(file_get_contents(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
    foreach($changeddata as $key => $data) {
        $formdata[$key] = $data;
    }
    if (file_exists(DATAROOT . 'edit_files/' . $author . '/' . $id)) rename(DATAROOT . 'edit_files/' . $author . '/' . $id, DATAROOT . 'files/' . $author . '/' . $id);
    foreach(glob(DATAROOT . 'edit_attach/' . $author . '/' . $id . '_*') as $filename) {
        $name = basename($filename);
        rename($filename, DATAROOT . 'submit_attach/' . $author . '/' . $name);
    }
}
$filedatajson =  json_encode($formdata);
if (file_put_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');

$authornick = nickname($author);

switch ($_POST["ans"]){
    case 1:
        $contentpart = '承認しても問題無いという結論になったため、この作品を承認しました。
作品の提出者に承認の通知をしました。';
        $subject = '議論の結果（承認・内容変更・' . $formdata["title"] . '）';
        $authorsubject = '内容変更を承認しました（' . $formdata["title"] . '）';
        break;
    case 2:
        $contentpart = '問題があるという結論になったため、この作品を拒否しました。
作品の提出者に拒否の通知をしました。';
        $subject = '議論の結果（拒否・内容変更・' . $formdata["title"] . '）';
        $authorsubject = '内容変更の承認が見送られました（' . $formdata["title"] . '）';
    break;
}

//内部関数で送信
foreach ($answerdata as $key => $data) {
    if (strpos($key, '_') !== FALSE) continue;
    if ($author == $key) continue;
    $nickname = nickname($key);
    $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更について、主催者が最終的な結論を入力し、議論を終了しました。
$contentpart

ファイル確認および議論へのご協力、ありがとうございます。
";
    sendmail(email($key), $subject, $content);
}

//提出者向け
if ($formsetting["reason"] == "notice") {
    $reasons = "◇" . $_POST["reason"] . "\n\n";
}
else if ($formsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
else if ($formsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
switch ($_POST["ans"]){
    case 1:
        $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」の内容変更について、イベントの運営メンバーが確認しました。
確認の結果、変更内容に問題が無いと判断されたため、この変更は承認されました。

$eventname にご参加頂き、ありがとうございます。


【提出内容の修正・削除をしたい場合や、作品を追加提出したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、提出内容の修正・削除や、追加提出を行えます。
提出内容を修正・削除する場合は、マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
追加提出をする場合は、「作品を提出する」をクリックし、改めて作品の提出を行って下さい。
";
    break;
    case 2:
        $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」の内容変更について、イベントの運営メンバーが確認しました。
確認の結果、変更後の内容に問題があると判断されました。
そのため、大変恐れ入りますが、この変更の承認を見送らせて頂きます。
現在は、変更前の内容を維持したままの状態となっています。


【問題があると判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには】
問題があるとされる内容を修正の上、ファイルを再提出する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
    break;
}
sendmail(email($author), $authorsubject, $content);
unlink(DATAROOT . "edit/" . $author . "/" . $id . ".txt");

switch ($_POST["ans"]){
    case 1:
        $_SESSION['situation'] = 'exam_edit_discuss_closed_accept';
    break;
    case 2:
        $_SESSION['situation'] = 'exam_edit_discuss_closed_reject';
    break;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
