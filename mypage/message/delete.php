<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

//メッセージID
$id = basename($_GET["name"]);

if ($id == "") die('パラメーターエラー');
list($from, $time) = explode('_', $id);
if ($from != $_SESSION["userid"]) die("他人のメッセージは削除出来ません。");

unlink(DATAROOT . 'messages/' . $id . '.txt');

$_SESSION['situation'] = 'message_deleted';

redirect("./index.php");
