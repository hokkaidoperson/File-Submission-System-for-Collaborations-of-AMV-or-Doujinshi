<?php
require_once('../../set.php');
setup_session();
session_validation();

//主催者が登録されていないかつシステム管理者
if (!file_exists(DATAROOT . 'users/_promoter.txt') and $_SESSION["admin"]) {
    if (file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) {
        unlink(DATAROOT . 'mail/invitation/_promoter.txt');
        register_alert("招待リンクをリセットしました。正しい送信先にリンクを送り直して下さい。", "success");
    }
}

redirect("./index.php");
