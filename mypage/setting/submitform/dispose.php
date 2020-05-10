<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


if (!file_exists(DATAROOT . 'form/submit/draft/')) redirect("../../index.php");

//一時ファイルを消す
remove_directory(DATAROOT . 'form/submit/draft');

unset($_SESSION["submitformdata"]);

register_alert("ファイル提出に関する設定を中止しました。", "warning");

redirect("../../index.php");
