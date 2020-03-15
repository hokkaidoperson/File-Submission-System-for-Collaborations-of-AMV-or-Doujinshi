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


if (!isset($_GET['number']) or !isset($_GET['type'])) {
    redirect("./index.php");
}

$number = htmlspecialchars(basename($_GET['number']));
$type = basename($_GET['type']);

switch ($type) {
    case "textbox": break;
    case "textbox2": break;
    case "textarea": break;
    case "radio": break;
    case "check": break;
    case "dropdown": break;
    case "attach": break;
    default: die();
}

$_SESSION["submitformdata"][$number] = array(
    "id" => time(),
    "type" => $type,
);

redirect("./$type.php?number=$number");
