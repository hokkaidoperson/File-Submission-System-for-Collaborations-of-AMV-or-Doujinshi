<?php
require_once('../../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../../index.php?redirto=mypage/setting/submitform/index.php\'" />
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

//変更前の内容を再読み込み
$_SESSION["submitformdata"] = array();
if (file_exists(DATAROOT . 'form/submit/draft/')) {
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/submit/draft/' . "$i" . '.txt')) break;
        $_SESSION["submitformdata"][$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/draft/' . "$i" . '.txt'), true);
    }
    if (file_exists(DATAROOT . 'form/submit/draft/general.txt')) $_SESSION["submitformdata"]["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/draft/general.txt'), true);
} else {
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
        $_SESSION["submitformdata"][$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/draft/' . "$i" . '.txt'), true);
    }
    if (file_exists(DATAROOT . 'form/submit/general.txt')) $_SESSION["submitformdata"]["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);
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
