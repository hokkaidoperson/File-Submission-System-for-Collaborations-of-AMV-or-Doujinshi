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
$_SESSION["userformdata"] = array();
if (file_exists(DATAROOT . 'form/userinfo/draft/')) {
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt')) break;
        $_SESSION["userformdata"][$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt'), true);
    }
} else {
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
        $_SESSION["userformdata"][$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
    }
}

redirect("./index.php");
