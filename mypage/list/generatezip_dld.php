<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}


if (!file_exists(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip')) die('ZIPファイルが存在しません。');

//ファイルダウンロードさせる
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Length: '. filesize(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip'));
header('Content-Disposition: attachment; filename="' . $eventname . ' - 一括ダウンロード.zip"');

readfile(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip');
