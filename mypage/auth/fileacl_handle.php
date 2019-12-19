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
if ($_POST["userid"] == "") $invalid = TRUE;
if ($_POST["users"] != "" and !is_array($_POST["users"])) $invalid = TRUE;
if ($_POST["files"] != "" and !is_array($_POST["files"])) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$userid = $_POST["userid"];

//ディレクトリ作成
if (!file_exists(DATAROOT . 'fileacl/')) {
    if (!mkdir(DATAROOT . 'fileacl/')) die('ディレクトリの作成に失敗しました。');
}

$acldata = array_merge((array)$_POST["users"], (array)$_POST["files"]);
$acldatajson =  json_encode($acldata);
if (file_put_contents(DATAROOT . 'fileacl/' . $userid . '.txt', $acldatajson) === FALSE) die('ACLデータの書き込みに失敗しました。');

//対象者にメール
$nickname = nickname($userid);
$pageurl = $siteurl . 'mypage/list/index.php';
$content = "$nickname 様

$eventname のポータルサイトにて、主催者がファイルへのアクセス権を編集しました。
あなた以外の他者が提出したファイルや、他者のユーザー情報にアクセス可能に（あるいは不可能に）なった可能性があります。

現在アクセス可能なファイルにつきましては、マイページの「提出済み作品一覧・編集」から確認出来ます。


　提出済み作品一覧・編集：$pageurl
";
        //内部関数で送信
        sendmail(email($userid), 'ファイルへのアクセス権が変更されました', $content);
        $_SESSION['situation'] = 'auth_fileacl';


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
