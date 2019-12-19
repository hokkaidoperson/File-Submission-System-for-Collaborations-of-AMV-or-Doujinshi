<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/invite/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$accessok = 'none';

//主催or共同運営でないシステム管理者
if ($_SESSION["state"] != 'p' and $_SESSION["state"] != 'c' and $_SESSION["admin"]) $accessok = 'ok';

if ($accessok == 'none') die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
');


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

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
