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


if (!file_exists(DATAROOT . 'form/submit/draft/')) {
    if (!mkdir(DATAROOT . 'form/submit/draft/', true)) die('ディレクトリの作成に失敗しました。');
}

for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/submit/draft/' . $i . '.txt';
    if (isset($_SESSION["submitformdata"][$i])) {
        //ファイル内容
        $filedata = $_SESSION["submitformdata"][$i];

        $filedatajson = json_encode($filedata);

        if (file_put_contents($fileplace, $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}

$_SESSION['situation'] = 'submitform_saved';

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
