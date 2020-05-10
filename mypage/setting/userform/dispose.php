<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


if (!file_exists(DATAROOT . 'form/userinfo/draft/')) redirect("../../index.php");

//一時ファイルを消す
remove_directory(DATAROOT . 'form/userinfo/draft');

unset($_SESSION["userformdata"]);

register_alert("ファイル提出に関する設定を中止しました。", "warning");

redirect("../../index.php");
