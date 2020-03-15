<?php
require_once('../../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../../index.php");
}

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') redirect("./index.php");

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
        $_SESSION["submitformdata"][$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
    }
    if (file_exists(DATAROOT . 'form/submit/general.txt')) $_SESSION["submitformdata"]["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);
}

redirect("./index.php");
