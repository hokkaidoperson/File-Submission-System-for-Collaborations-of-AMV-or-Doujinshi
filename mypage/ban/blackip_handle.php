<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') redirect("./index.php");


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

$blplace = DATAROOT . 'blackip.txt';

if (file_put_contents($blplace, $_POST["setting"]) === FALSE) die('リストデータの書き込みに失敗しました。');

$_SESSION['situation'] = 'ban_ip';

redirect("./index.php");