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
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/ban/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

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

$invalid = FALSE;
if ($_POST["subject"] == "") $invalid = TRUE;
if ($_POST["message_mail"] == "") {
} else if (mb_strlen($_POST["message_mail"]) > 500) $invalid = TRUE;
if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$id = $_POST["subject"];

if (!user_exists($id)) die ("ユーザーが存在しません。");

$blplace = DATAROOT . 'blackuser.txt';
if (file_exists($blplace)) $bldata = json_decode(file_get_contents($blplace), true);
else $bldata = array();

$key = array_search($id, $bldata);
if ($key !== FALSE) {
    unset($bldata[$key]);
    $rem = TRUE;
    $title = "アカウント凍結解除のお知らせ";
}
else {
    $bldata[] = $id;
    $rem = FALSE;
    $title = "アカウント凍結のお知らせ";
}

$datajson =  json_encode($bldata);
if (file_put_contents($blplace, $datajson) === FALSE) die('リストデータの書き込みに失敗しました。');

//対象者にメール
$nickname = nickname($id);
if ($_POST["message_mail"] == "") $message = "（コメント無し）";
else $message = $_POST["message_mail"];
if (!$rem) $content = "$nickname 様

$eventname のポータルサイトにて、主催者があなたのアカウントを凍結しました。
現在、あなたのアカウントでログインしても何も操作を行う事が出来ません。

もし、誤って凍結された可能性がある場合、或いはこのアカウント凍結を不服とする場合は、
主催者に直接ご相談下さい。


【主催者からのメッセージ（凍結理由など）】
$message
";
else $content = "$nickname 様

$eventname のポータルサイトにて、主催者があなたのアカウントの凍結を解除しました。
これで、あなたのアカウントでログインして各種操作を行う事が可能になりました。

この度のアカウント凍結に関して不明点などあれば、主催者に直接お尋ね下さい。


【主催者からのメッセージ（凍結解除理由など）】
$message
";
//内部関数で送信
sendmail(email($id), $title, $content);
if ($rem) $_SESSION['situation'] = 'ban_user_rem';
else $_SESSION['situation'] = 'ban_user_add';


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
