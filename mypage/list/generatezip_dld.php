<?php
require_once('../../set.php');
setup_session();
session_validation();


if (!file_exists(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip')) die('ZIPファイルが存在しません。');

//ファイルダウンロードさせる（readfileは大容量ファイル時にエラーになる）
mb_http_output('pass');
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Length: '. filesize(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip'));
header('Content-Disposition: attachment; filename="' . $eventname . ' - 一括ダウンロード.zip"');

$path = DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip';
$handle = fopen($path, "rb");
while(!feof($handle))
{
    print fread($handle, 4096);
    ob_flush();
    flush();
}
fclose();
