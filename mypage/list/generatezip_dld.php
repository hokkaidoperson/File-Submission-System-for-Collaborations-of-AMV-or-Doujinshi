<?php
require_once('../../set.php');
setup_session();
session_validation();


if (!file_exists(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip')) die('ZIPファイルが存在しません。');

//ファイルダウンロードさせる
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Length: '. filesize(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip'));
header('Content-Disposition: attachment; filename="' . $eventname . ' - 一括ダウンロード.zip"');

readfile(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip');
