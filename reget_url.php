<?php
require_once('set.php');
//サイトのURLを取得
$url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$url = str_replace('reget_url.php', '', $url);

//サイト名を保管
if (file_get_contents(DATAROOT . 'siteurl.txt') != $url) {
    if (file_put_contents(DATAROOT . 'siteurl.txt', $url) === FALSE) die('サイトURLの書き込みに失敗しました。');
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
