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

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["msg_content"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["msg_content"]) > 1000) $invalid = TRUE;

if($_POST["msg_subject"] == "") {}
else if(mb_strlen($_POST["msg_subject"]) > 50) $invalid = TRUE;

if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') {
    $canshow = users_array();
    unset($canshow[$_SESSION["userid"]]);
} else {
    $list = id_state("p");
    $canshow = array(
        $list[0] => id_array($list[0])
    );
}
foreach ((array)$_POST["to"] as $userid) {
    if (!isset($canshow[$userid])) $invalid = TRUE;
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//ディレクトリ作成
if (!file_exists(DATAROOT . 'messages/')) {
    if (!mkdir(DATAROOT . 'messages/')) die('ディレクトリの作成に失敗しました。');
}

//メッセージデータ
$id = $_SESSION["userid"] . "_" . time();
if ($_POST["msg_subject"] == '') $subject = mb_substr($_POST["msg_content"], 0, 30);
else $subject = $_POST["msg_subject"];
$messagedata = array(
    "_subject" => $subject,
    "_content" => $_POST["msg_content"]
);
foreach ((array)$_POST["to"] as $userid) {
    $messagedata[$userid] = 0;
}

$authornick = "";
if (state($_SESSION["userid"]) == "p") {
    if (id_admin() == $_SESSION["userid"]) $authornick .= '主催者兼システム管理者・';
    else $authornick .= '主催者・';
}
else if (state($_SESSION["userid"]) == "c") {
    if (id_admin() == $_SESSION["userid"]) $authornick .= '共同運営者兼システム管理者・';
    else $authornick .= '共同運営者・';
}
else if (id_admin() == $_SESSION["userid"]) $authornick .= 'システム管理者・';
$authornick .= nickname($_SESSION["userid"]);
$message = $_POST["msg_content"];
$pageurl = $siteurl . 'mypage/message/read.php?name=' . $id;
foreach ((array)$_POST["to"] as $userid) {
    $nickname = nickname($userid);
    $content = "$nickname 様

$authornick 様が、あなた宛てにメッセージを送信しました。


【件名】
$subject

【メッセージ内容】
$message


　詳細確認・返信はこちら：$pageurl
";
    sendmail(email($userid), 'メッセージ通知（' . $subject . '）', $content);
}

$filedatajson = json_encode($messagedata);
if (file_put_contents(DATAROOT . 'messages/' . $id . '.txt', $filedatajson) === FALSE) die('メッセージデータの書き込みに失敗しました。');

$_SESSION['situation'] = 'message_sent';

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
