<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

$accessok = 'none';

//主催or共同運営でないシステム管理者
if ($_SESSION["state"] != 'p' and $_SESSION["state"] != 'c' and $_SESSION["admin"]) $accessok = 'ok';

if ($accessok == 'none') redirect("./index.php");


$userdata = id_array($_SESSION["userid"]);

if ($userdata["state"] == "g") {
    $userdata["state"] = "o";
    $userdatajson =  json_encode($userdata);
    if (file_put_contents_repeat(DATAROOT . 'users/' . $_SESSION["userid"] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    //立場別一覧の書き換え
    $statedtp = DATAROOT . 'users/_general.txt';
    $array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search($_SESSION["userid"], $array);
    unset($array[$key]);
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents_repeat($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    $statedata = $_SESSION["userid"] . "\n";
    $statedtp = DATAROOT . 'users/_outsider.txt';
    if (file_put_contents_repeat($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    register_alert("非参加者に切り替えました。", "success");
} else {
    $userdata["state"] = "g";
    $userdatajson =  json_encode($userdata);
    if (file_put_contents_repeat(DATAROOT . 'users/' . $_SESSION["userid"] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    //立場別一覧の書き換え
    $statedtp = DATAROOT . 'users/_outsider.txt';
    $array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search($_SESSION["userid"], $array);
    unset($array[$key]);
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents_repeat($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

    $statedata = $_SESSION["userid"] . "\n";
    $statedtp = DATAROOT . 'users/_general.txt';
    if (file_put_contents_repeat($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    register_alert("一般参加者に切り替えました。", "success");

}

redirect("./index.php");
