<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./co_unit.php");

csrf_prevention_validate();

$deny = FALSE;

$id = basename($_POST["id"]);

if (file_exists(DATAROOT . 'mail/state/co_' . $id . '.txt')) {
    $filedata = json_decode(file_get_contents_repeat(DATAROOT . 'mail/state/co_' . $id . '.txt'), true);
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/state/co_' . $id . '.txt');
        $deny = TRUE;
    }
} else $deny = TRUE;

if ($deny) redirect("./co_unit.php");

//辞退する人
$oldco = id_array($id);

//辞退する人の状態を一般参加者にして保存
$oldco["state"] = "g";
$userdatajson =  json_encode($oldco);
if (file_put_contents_repeat(DATAROOT . 'users/' . $id . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');


//立場別一覧の書き換え
$statedtp = DATAROOT . 'users/_co.txt';
$array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search($id, $array);
unset($array[$key]);
$statedata = implode("\n", $array) . "\n";
if (file_put_contents_repeat($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

$statedata = $id . "\n";
$statedtp = DATAROOT . 'users/_general.txt';
if (file_put_contents_repeat($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//ファイル確認メンバーにいたら除外
$settingfile = json_unpack(DATAROOT . 'examsetting.txt');
$array = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search($id, $array);
if ($key !== FALSE) {
    unset($array[$key]);
    if ($array == array()) $array = array("_promoter");
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents_repeat(DATAROOT . 'exammember_submit.txt', $statedata) === FALSE) die('システムデータの書き込みに失敗しました。');
    if ($settingfile["submit_leader"] == $id) $settingfile["submit_leader"] = "";
}
$array = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search($id, $array);
if ($key !== FALSE) {
    unset($array[$key]);
    if ($array == array()) $array = array("_promoter");
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents_repeat(DATAROOT . 'exammember_edit.txt', $statedata) === FALSE) die('システムデータの書き込みに失敗しました。');
    if ($settingfile["edit_leader"] == $id) $settingfile["edit_leader"] = "";
}
json_pack(DATAROOT . 'examsetting.txt', $settingfile);


//ファイル確認関連ファイルも書き換え
exam_totalization_new("_all", FALSE);
exam_totalization_edit("_all", FALSE);


//招待リンクを消す
unlink(DATAROOT . 'mail/state/co_' . $id . '.txt');

//事後報告
$date = date('Y/m/d H:i:s');
$nicknameo = nickname($id);
$nicknamep = nickname(id_promoter());

$content = "$nicknameo 様

$eventname の共同運営者からの辞退が完了しました。
主催者・$nicknamep 様が辞退を承認し、あなたは一般参加者となりました。

　実行日時：$date
";
//内部関数で送信
sendmail(email(basename($_POST["id"])), '共同運営者からの辞退が完了しました', $content);

register_alert("手続きが完了し、{$nicknameo}は一般参加者となりました。", "success");

redirect("../index.php");
