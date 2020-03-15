<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

//主催者が登録されていないかつシステム管理者
if (!file_exists(DATAROOT . 'users/_promoter.txt') and $_SESSION["admin"]) {
    if (file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) {
        unlink(DATAROOT . 'mail/invitation/_promoter.txt');
        $_SESSION["situation"] = 'invite_forceexpire';
    }
}

redirect("./index.php");
