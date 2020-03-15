<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

$accessok = 'none';

//主催or共同運営でないシステム管理者
if ($_SESSION["state"] != 'p' and $_SESSION["state"] != 'c' and $_SESSION["admin"]) $accessok = 'ok';

if ($accessok == 'none') redirect("./index.php");


$userdata = id_array($_SESSION["userid"]);

if ($userdata["state"] == "g") {
    $userdata["state"] = "o";
    $userdatajson =  json_encode($userdata);
    if (file_put_contents(DATAROOT . 'users/' . $_SESSION["userid"] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    //立場別一覧の書き換え
    $statedtp = DATAROOT . 'users/_general.txt';
    $array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search($_SESSION["userid"], $array);
    unset($array[$key]);
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    $statedata = $_SESSION["userid"] . "\n";
    $statedtp = DATAROOT . 'users/_outsider.txt';
    if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    $_SESSION['situation'] = 'state_switcher_admin_to_o';
} else {
    $userdata["state"] = "g";
    $userdatajson =  json_encode($userdata);
    if (file_put_contents(DATAROOT . 'users/' . $_SESSION["userid"] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    //立場別一覧の書き換え
    $statedtp = DATAROOT . 'users/_outsider.txt';
    $array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search($_SESSION["userid"], $array);
    unset($array[$key]);
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    $statedata = $_SESSION["userid"] . "\n";
    $statedtp = DATAROOT . 'users/_general.txt';
    if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    $_SESSION['situation'] = 'state_switcher_admin_to_g';

}

redirect("./index.php");
