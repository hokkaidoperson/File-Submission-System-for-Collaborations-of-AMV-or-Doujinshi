<?php
require_once('../../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../../index.php?redirto=mypage/setting/userform/index.php\'" />
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


if (!file_exists(DATAROOT . 'form/userinfo/draft/')) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
');

//一時ファイルを実際の設定ファイルにする
for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/userinfo/' . $i . '.txt';
    if (file_exists(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt')) {
        //ファイル内容
        $filedata = file_get_contents(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt');

        if (file_put_contents($fileplace, $filedata) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}


//一時ファイルを消す
remove_directory(DATAROOT . 'form/userinfo/draft');

unset($_SESSION["userformdata"]);

//勝利宣言（？）
if (!file_exists(DATAROOT . 'form/userinfo/done.txt')){
    if (file_put_contents(DATAROOT . 'form/userinfo/done.txt', "1") === FALSE) die('設定内容の書き込みに失敗しました。');
}
$_SESSION['situation'] = 'userform_applied';

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='../../index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
