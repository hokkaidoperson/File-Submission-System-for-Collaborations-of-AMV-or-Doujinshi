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
<meta http-equiv="refresh" content="0; URL=\'../../index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}


if (!file_exists(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip')) die('ZIPファイルが存在しません。');

//ファイルダウンロードさせる
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Length: '. filesize(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip'));
header('Content-Disposition: attachment; filename="' . $eventname . ' - 一括ダウンロード.zip"');

readfile(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip');
