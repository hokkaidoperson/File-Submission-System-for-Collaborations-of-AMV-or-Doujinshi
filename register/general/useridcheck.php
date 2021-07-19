<?php
//ユーザーIDチェック・アカウント情報入力画面跡地　兼　ユーザーID重複チェックAPI
require_once('../../set.php');

$returnarray = array();

$invalid = FALSE;

if($_GET["userid"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9a-zA-Z]*$/', $_GET["userid"])) $invalid = TRUE;
else if(mb_strlen($_GET["userid"]) > 20) $invalid = TRUE;

if ($invalid) $returnarray["idresult"] = 0;
else {
    $userid = $_GET["userid"];
    $IP = getenv("REMOTE_ADDR");

    $conflict = FALSE;

    //登録中のユーザーID横取り阻止（保証期間は30分）
    if (file_exists(DATAROOT . 'users_reserve/')) {
        foreach (glob(DATAROOT . 'users_reserve/*.txt') as $filename) {
            $filedata = json_decode(file_get_contents_repeat($filename), true);
            if ($filedata["expire"] <= time()) {
                unlink($filename);
                continue;
            }
            // 自分自身だったら通してあげる
            if (basename($filename, ".txt") == $IP) continue;
            if ($filedata["userid"] == $userid) {
                $conflict = TRUE;
                break;
            }
        }
    }

    //登録済みの中から探す
    foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
        if (basename($filename, ".txt") == $userid) {
            $conflict = TRUE;
            break;
        }
    }

    if ($conflict) $returnarray["idresult"] = 0;
    else {
        $returnarray["idresult"] = 1;
        if (!file_exists(DATAROOT . 'users_reserve/')) mkdir(DATAROOT . 'users_reserve/');

        $reserve = array(
            "expire" => time() + (30 * 60),
            "userid" => $userid,
        );

        $fileplace = DATAROOT . 'users_reserve/' . $IP . '.txt';
        json_pack($fileplace, $reserve);
    }
}

//------------------------------------------

$invalid = FALSE;

if($_GET["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_GET["email"])) $invalid = TRUE;

if ($invalid) $returnarray["emailresult"] = 0;
else {
    $email = $_GET["email"];

    $conflict = 0;

    //登録済みの中から探す
    foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents_repeat($filename), true);
        if ($filedata["email"] == $email) {
            $conflict++;
        }
    }

    if ($conflict >= ACCOUNTS_PER_ADDRESS) $returnarray["emailresult"] = 0;
    else if ($conflict > 0) $returnarray["emailresult"] = 2;
    else $returnarray["emailresult"] = 1;
}

echo json_encode($returnarray);
