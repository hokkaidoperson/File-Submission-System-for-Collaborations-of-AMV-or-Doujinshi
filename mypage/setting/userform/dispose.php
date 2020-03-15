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


if (!file_exists(DATAROOT . 'form/userinfo/draft/')) redirect("../../index.php");

//一時ファイルを消す
remove_directory(DATAROOT . 'form/userinfo/draft');

unset($_SESSION["userformdata"]);

$_SESSION['situation'] = 'userform_cancelled';

redirect("../../index.php");
