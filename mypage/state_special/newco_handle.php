<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

$deny = FALSE;

$id = $_SESSION["userid"];

if (file_exists(DATAROOT . 'mail/co_add/' . $id . '.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/co_add/' . $id . '.txt'), true);
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/co_add/' . $id . '.txt');
        $deny = TRUE;
    }
} else $deny = TRUE;

if ($deny) redirect("./newco_unit.php");

//状態を共催にして保存
$userdata = id_array($id);
$oldstate = $userdata["state"];
$userdata["state"] = "c";
$userdatajson =  json_encode($userdata);
if (file_put_contents(DATAROOT . 'users/' . $id . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//立場別一覧の書き換え
$statedata = "$id\n";
$statedtp = DATAROOT . 'users/_co.txt';
if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

if ($oldstate == "g") $statedtp = DATAROOT . 'users/_general.txt';
else if ($oldstate == "o") $statedtp = DATAROOT . 'users/_outsider.txt';
$array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search($id, $array);
unset($array[$key]);
$statedata = implode("\n", $array) . "\n";
if (file_put_contents($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');


//この人をファイル確認メンバーに入れる？
if (file_exists(DATAROOT . 'examsetting.txt')) {
    $examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

    if ($examsetting["submit_add"] == "1") {
        $statedata = "$id\n";
        $statedtp = DATAROOT . 'exammember_submit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
    if ($examsetting["edit_add"] == "1") {
        $statedata = "$id\n";
        $statedtp = DATAROOT . 'exammember_edit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
}


//招待リンクを消す
unlink(DATAROOT . 'mail/co_add/' . $id . '.txt');

//主催者に事後報告
$date = date('Y/m/d H:i:s');
$nicknameo = nickname(id_promoter());
$nicknamen = nickname($id);

$content = "$nicknameo 様

$eventname の共同運営者の追加が完了しました。
$nicknamen 様が共同運営者として新たに加わりました。

　実行日時：$date
";
//内部関数で送信
sendmail(email(id_promoter()), '共同運営者の追加が完了しました', $content);

register_alert("手続きが完了し、あなたは共同運営者となりました。", "success");

redirect("../index.php");
