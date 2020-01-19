<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
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

//主催・共同運営
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') $accessok = 'ok';

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


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");
if (!file_exists(DATAROOT . 'exam/' . $_POST["subject"] . '.txt')) die('ファイルが存在しません。');
list($author, $id) = explode('_', $_POST["subject"]);
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
} else if(mb_strlen($_POST["reason"]) > 500) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

//回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam/' . $_POST["subject"] . '.txt'), true);
$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
}

if ($answerdata["_state"] != 0) die();
if (array_search($_SESSION["userid"], $submitmem) === FALSE) die();
if (isset($answerdata[$_SESSION["userid"]]["opinion"]) and $answerdata[$_SESSION["userid"]]["opinion"] == -1) die();

if ($author == $_SESSION["userid"]) die();

//理由通知の設定呼び出し
$examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

//データを記録する
$answerdata[$_SESSION["userid"]] = array(
    "opinion" => $_POST["ans"],
    "reason" => $_POST["reason"]
);

//全員の回答終わった？
$complete = TRUE;
foreach ($submitmem as $key) {
    if (!isset($answerdata[$key])) {
        $complete = FALSE;
        continue;
    }
    $data = $answerdata[$key];
    if ($data["opinion"] == 0) $complete = FALSE;
}

//回答終わってなければ帰す
if ($complete == FALSE) {
    $filedatajson =  json_encode($answerdata);
    if (file_put_contents(DATAROOT . 'exam/' . $_POST["subject"] . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');
    $_SESSION['situation'] = 'exam_submitted';
    die('<!DOCTYPE html>
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
</html>');
}

//以下、全員の回答が終わった時の処理

//意見が一致したのか？（resultが0のままだったら対立してる）
$result = 0;

//計測用変数
$op1 = 0;
$op2 = 0;
$op3 = 0;
$count = 0;
foreach ($submitmem as $key) {
    $data = $answerdata[$key];
    if ($data["opinion"] == -1) continue;
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
if (file_put_contents(DATAROOT . 'exam/' . $_POST["subject"] . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

//入力内容を読み込んで書き換え
$formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
$formdata["exam"] = $result;
$filedatajson =  json_encode($formdata);
if (file_put_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');

$authornick = nickname($author);

if ($result == 0) {
    $pageurl = $siteurl . 'mypage/exam/discuss.php?author=' . $author . '&id=' . $id;
    //内部関数で送信
    foreach ($submitmem as $key) {
        $data = $answerdata[$key];
        if ($data["opinion"] == -1) continue;
        $nickname = nickname($key);
        $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
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
    foreach ($submitmem as $key) {
        $data = $answerdata[$key];
        if ($author == $key) continue;
        if ($data["opinion"] == -1) continue;
        $nickname = nickname($key);
        $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
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
