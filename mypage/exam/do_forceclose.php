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

//主催のみ
if ($_SESSION["state"] == 'p') $accessok = 'ok';

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

$author = $_GET["author"];
$id = $_GET["id"];

if ($author == "" or $id == "") die('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam/' . $author . '_' . $id . '.txt')) die('ファイルが存在しません。');
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam/' . $author . '_' . $id . '.txt'), true);
if ($answerdata["_state"] != 0) die();

//フォームgeneralデータ（理由通知の設定呼び出し）
$formsetting = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

//意見が一致したのか？（resultが0のままだったら対立してる）
$result = 0;

//計測用変数
$op1 = 0;
$op2 = 0;
$op3 = 0;
$count = 0;
foreach ($answerdata as $key => $data) {
    if (strpos($key, '_') !== FALSE) continue;
    if ($data["opinion"] == 0) continue;
    switch ($data["opinion"]){
        case 1:
            $op1++;
        break;
        case 2:
            $op2++;
        break;
        case 3:
            $op3++;
        break;
    }
    $count++;
}
if ($op1 == $count) $result = 1;
else if ($op2 == $count) $result = 2;
else if ($op3 == $count) $result = 3;

//計測結果を保存
if ($result == 0) $answerdata["_state"] = 1;
else {
    $answerdata["_state"] = 3;
    $answerdata["_result"] = $result;
}

$filedatajson = json_encode($answerdata);
if (file_put_contents(DATAROOT . 'exam/' . $author . '_' . $id . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

//入力内容を読み込んで書き換え
$formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
$formdata["exam"] = $result;
$filedatajson =  json_encode($formdata);
if (file_put_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');

$authornick = nickname($author);

if ($result == 0) {
    $pageurl = $siteurl . 'mypage/exam/discuss.php?author=' . $author . '&id=' . $id;
    //内部関数で送信
    foreach ($answerdata as $key => $data) {
        if (strpos($key, '_') !== FALSE) continue;
        $nickname = nickname($key);
        $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
メンバー間で意見が分かれたため、この作品の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
        sendmail(email($key), 'ファイル確認の結果（議論の必要あり・' . $formdata["title"] . '）', $content);
    }
} else {
    switch ($result){
        case 1:
            $contentpart = '承認しても問題無いという意見で一致したため、この作品を承認しました。
作品の提出者に承認の通知をしました。';
            $subject = 'ファイル確認の結果（承認・' . $formdata["title"] . '）';
            $authorsubject = '作品を承認しました（' . $formdata["title"] . '）';
        break;
        case 2:
            $contentpart = '軽微な修正が必要であるという意見で一致したため、この作品を修正待ち状態にしました。
作品の提出者に、修正依頼の通知をしました。';
            $subject = 'ファイル確認の結果（修正待ち・' . $formdata["title"] . '）';
            $authorsubject = '作品を修正して下さい（' . $formdata["title"] . '）';
        break;
        case 3:
            $contentpart = '内容上問題があるという意見で一致したため、この作品を拒否しました。
作品の提出者に拒否の通知をしました。';
            $subject = 'ファイル確認の結果（拒否・' . $formdata["title"] . '）';
            $authorsubject = '作品の承認が見送られました（' . $formdata["title"] . '）';
        break;
    }

    //内部関数で送信
    foreach ($answerdata as $key => $data) {
        if (strpos($key, '_') !== FALSE) continue;
        if ($author == $key) continue;
        $nickname = nickname($key);
        $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
        sendmail(email($key), $subject, $content);
    }

    //提出者向け
    $reasons = "";
    if ($formsetting["reason"] == "notice") {
        foreach ($answerdata as $key => $data) {
            if (strpos($key, '_') !== FALSE) continue;
            if ($data["reason"] != "") $reasons = $reasons . "◇" . $data["reason"] . "\n\n";
        }
    }
    else if ($formsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
    else if ($formsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
    switch ($result){
        case 1:
            $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイル内容に問題が無いと判断されたため、この作品は承認されました。

$eventname にご参加頂き、ありがとうございます。


【提出内容の修正・削除をしたい場合や、作品を追加提出したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、提出内容の修正・削除や、追加提出を行えます。
提出内容を修正・削除する場合は、マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
追加提出をする場合は、「作品を提出する」をクリックし、改めて作品の提出を行って下さい。
";
        break;
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

}

switch ($result){
    case 0:
        $_SESSION['situation'] = 'exam_forceclose_discuss';
    break;
    case 1:
        $_SESSION['situation'] = 'exam_forceclose_accept';
    break;
    case 2:
        $_SESSION['situation'] = 'exam_forceclose_reject_m';
    break;
    case 3:
        $_SESSION['situation'] = 'exam_forceclose_reject';
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
