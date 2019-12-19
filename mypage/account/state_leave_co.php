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
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/invite/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$accessok = 'none';

//共催
if ($_SESSION["state"] == 'c') $accessok = 'ok';

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

$prom = id_state("p");

$userdata = id_array($prom[0]);
$email = $userdata["email"];

if (!file_exists(DATAROOT . 'mail/state/')) {
    if (!mkdir(DATAROOT . 'mail/state/', 0777, true)) die('ディレクトリの作成に失敗しました。');
}

//認証文字列（参考：https://qiita.com/suin/items/c958bcca90262467f2c0）
$randomchar128 = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 128)), 0, 128);

$fileplace = DATAROOT . 'mail/state/co_' . $_SESSION["userid"] . '.txt';

//2日後に有効期限切れ
$expire = time() + (2 * 24 * 60 * 60);

//ファイル内容
$filedata = array(
    "sectok" => $randomchar128,
    "expire" => $expire
);

$filedatajson = json_encode($filedata);

if (file_put_contents($fileplace, $filedatajson) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//メール本文形成
$expireformat = date('Y年n月j日G時i分s秒', $expire);
$pageurl = $siteurl . 'state_special/co_unit.php?id=' . $_SESSION["userid"] . '&sectok=' . $randomchar128;
$nickname = $userdata["nickname"];
$conick = nickname($_SESSION["userid"]);
$content = "$nickname 様

$conick 様から、$eventname の共同運営者を辞退するという申請がありました。
これについて$conick 様から事情を聞いており、辞退を承認する場合は、以下の手続用URLから手続して下さい。
もし事情を聞いていない場合は、$conick 様に直接お問い合わせ下さい。

　手続用URL　　　　：$pageurl
　上記URLの有効期限：$expireformat

※URLは一部だけ切り取られると認識されません。
　改行されているなどの理由により正常にURLをクリック出来ない場合は、URLを直接コピーしてブラウザに貼り付けて下さい。
※有効期限は24時間表記です。
※手続前に有効期限が切れてしまった場合は、$conick 様にURLの再送を依頼して下さい。
";
//内部関数で送信
sendmail($email, '共同運営者辞退の申請がありました', $content);

$_SESSION['situation'] = 'state_switcher_mail';

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
