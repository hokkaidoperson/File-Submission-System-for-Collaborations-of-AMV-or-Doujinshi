<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");

//変更前の内容を再読み込み
$_SESSION["userformdata"] = array();
if (file_exists(DATAROOT . 'form/userinfo/draft/')) {
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt')) break;
        $_SESSION["userformdata"][$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt'), true);
    }
} else {
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
        $_SESSION["userformdata"][$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
    }
}

redirect("./index.php");
