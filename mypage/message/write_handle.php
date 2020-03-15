<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["msg_content"] == "") $invalid = TRUE;
else if(length_with_lb($_POST["msg_content"]) > 1000) $invalid = TRUE;

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
    //認証文字列（参考：https://qiita.com/suin/items/c958bcca90262467f2c0）
    $randomchar128 = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 128)), 0, 128);
    $messagedata["sectok_$userid"] = $randomchar128;
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
    $readurl = $siteurl . 'message_read_shortcut/index.php?name=' . $id . '&userid=' . $userid . '&sectok=' . $messagedata["sectok_$userid"];
    $nickname = nickname($userid);
    $content = "$nickname 様

$authornick 様が、あなた宛てにメッセージを送信しました。


【件名】
$subject

【メッセージ内容】
$message

【メッセージに既読を付ける（ログイン不要）】
このメッセージに既読を付けるには、以下の開封確認URLにアクセスして下さい。
その際、ログインは不要です。
　開封確認URL：$readurl

【詳細確認・返信をする（要ログイン）】
このメッセージの詳細を確認したり、返信したりするには、ログインした状態で以下のURLに
アクセスして下さい。
　メッセージ詳細・返信用URL：$pageurl
";
    sendmail(email($userid), 'メッセージ通知（' . $subject . '）', $content);
}

$filedatajson = json_encode($messagedata);
if (file_put_contents(DATAROOT . 'messages/' . $id . '.txt', $filedatajson) === FALSE) die('メッセージデータの書き込みに失敗しました。');

$_SESSION['situation'] = 'message_sent';

redirect("./index.php");
