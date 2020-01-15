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

$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$author = $_GET["author"];
$id = $_GET["id"];
$editid = $_GET["edit"];

//回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt'), true);
if ($answerdata["_state"] != 0) die();

$echoforceclose = FALSE;
if ($noprom) {
    if (!($nopermission and !$bymyself) and isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($_SESSION["state"] == 'p') {
    if (isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if (!$echoforceclose) die('<!DOCTYPE html>
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

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die('<!DOCTYPE html>
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


if ($author == "" or $id == "" or $editid == "") die('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt')) die('ファイルが存在しません。');
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//理由通知の設定呼び出し
$examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

//意見が一致したのか？（resultが0のままだったら対立してる）
$result = 0;

//計測用変数
$op1 = 0;
$op2 = 0;
$count = 0;
foreach ($submitmem as $key) {
    if (!isset($answerdata[$key])) continue;
    $data = $answerdata[$key];
    if ($data["opinion"] == -1) continue;
    switch ($data["opinion"]){
        case 1:
            $op1++;
        break;
        case 2:
            $op2++;
        break;
    }
    $count++;
}
if ($op1 == $count) $result = 1;
else if ($op2 == $count) $result = 2;

//計測結果を保存
if ($result == 0) $answerdata["_state"] = 1;
else {
    $answerdata["_state"] = 3;
    $answerdata["_result"] = $result;
}

$filedatajson = json_encode($answerdata);
if (file_put_contents(DATAROOT . 'exam_edit/' . $_POST["subject"] . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

//議論入りしないなら入力内容を読み込んで書き換え
if ($result != 0) {
    $formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
    $formdata["editing"] = 0;
    if ($result == 1) {
        $formdata["exam"] = 1;
        $changeddata = json_decode(file_get_contents(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
        foreach($changeddata as $key => $data) {
            $formdata[$key] = $data;
        }
    }
    $filedatajson =  json_encode($formdata);
    if (file_put_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');
    if (file_exists(DATAROOT . 'edit_files/' . $userid . '/' . $id)) rename(DATAROOT . 'edit_files/' . $userid . '/' . $id, DATAROOT . 'files/' . $userid . '/' . $id);
    foreach(glob(DATAROOT . 'edit_attach/' . $id . '_*') as $filename) {
        $name = basename($filename);
        rename($filename, DATAROOT . 'submit_attach/' . $userid . '/' . $name);
    }
}

$authornick = nickname($author);

if ($result == 0) {
    $pageurl = $siteurl . 'mypage/exam/discuss_edit.php?author=' . $author . '&id=' . $id;
    //内部関数で送信
    foreach ($submitmem as $key) {
        $data = $answerdata[$key];
        $nickname = nickname($key);
        $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
メンバー間で意見が分かれたため、この変更の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
        sendmail(email($key), 'ファイル確認の結果（議論の必要あり・内容変更・' . $formdata["title"] . '）', $content);
    }
} else {
    switch ($result){
        case 1:
            $contentpart = '承認しても問題無いという意見で一致したため、この変更を承認しました。
作品の提出者に承認の通知をしました。';
            $subject = 'ファイル確認の結果（承認・内容変更・' . $formdata["title"] . '）';
            $authorsubject = '内容変更を承認しました（' . $formdata["title"] . '）';
        break;
        case 2:
            $contentpart = '問題があるという意見で一致したため、この変更を拒否しました。
作品の提出者に拒否の通知をしました。';
            $subject = 'ファイル確認の結果（拒否・内容変更・' . $formdata["title"] . '）';
            $authorsubject = '内容変更の承認が見送られました（' . $formdata["title"] . '）';
        break;
    }

    //内部関数で送信
    foreach ($submitmem as $key) {
        $data = $answerdata[$key];
        if ($author == $key) continue;
        $nickname = nickname($key);
        $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
        sendmail(email($key), $subject, $content);
    }

    //提出者向け
    $reasons = "";
    if ($examsetting["reason"] == "notice") {
        foreach ($answerdata as $key => $data) {
            if (strpos($key, '_') !== FALSE) continue;
            if ($data["reason"] != "") $reasons = $reasons . "◇" . $data["reason"] . "\n\n";
        }
    }
    else if ($examsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
    else if ($examsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
    switch ($result){
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

【再編集をするには】
問題があるとされる内容を修正の上、ファイルを再編集する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
        break;
    }
    sendmail(email($author), $authorsubject, $content);
    unlink(DATAROOT . "edit/" . $author . "/" . $id . ".txt");
}

switch ($result){
    case 0:
        $_SESSION['situation'] = 'exam_edit_forceclose_discuss';
    break;
    case 1:
        $_SESSION['situation'] = 'exam_edit_forceclose_accept';
    break;
    case 2:
        $_SESSION['situation'] = 'exam_edit_forceclose_reject';
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
