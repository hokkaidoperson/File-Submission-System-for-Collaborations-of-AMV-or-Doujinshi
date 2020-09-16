<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

$deny = FALSE;
$userid = $_SESSION["userid"];

if (file_exists(DATAROOT . 'mail/state/promoter.txt')) {
    $filedata = json_decode(file_get_contents_repeat(DATAROOT . 'mail/state/promoter.txt'), true);
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/state/promoter.txt');
        $deny = TRUE;
    }
    if ($filedata["new"] != $userid) $deny = TRUE;
} else $deny = TRUE;

if ($deny) redirect("./prom_unit.php");

//前の主催者
$prom = id_state("p");
$oldprom = id_array($prom[0]);

//新しい主催者
$newprom = id_array($userid);
//…の前の立場
$oldstate = $newprom["state"];


//前の主催者の状態を一般参加者にして保存
$oldprom["state"] = "g";
$userdatajson =  json_encode($oldprom);
if (file_put_contents_repeat(DATAROOT . 'users/' . $prom[0] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//新しい人を主催者に
$newprom["state"] = "p";
$userdatajson =  json_encode($newprom);
if (file_put_contents_repeat(DATAROOT . 'users/' . $userid . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//立場別一覧の書き換え
$statedata = $userid . "\n";
$statedtp = DATAROOT . 'users/_promoter.txt';
if (file_put_contents_repeat($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

if ($oldstate == "c") $statedtp = DATAROOT . 'users/_co.txt';
else if ($oldstate == "g") $statedtp = DATAROOT . 'users/_general.txt';
else $statedtp = DATAROOT . 'users/_outsider.txt';
$array = file($statedtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search($userid, $array);
unset($array[$key]);
$statedata = implode("\n", $array) . "\n";
if (file_put_contents_repeat($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

$statedata = $prom[0] . "\n";
$statedtp = DATAROOT . 'users/_general.txt';
if (file_put_contents_repeat($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');



//新しい主催者がファイル確認メンバーにいたら、専用の名前「_promoter」に書き換える（「_promoter」がいたら消すだけ）
$settingfile = json_unpack(DATAROOT . 'examsetting.txt');
$array = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search($userid, $array);
if ($key !== FALSE) {
    unset($array[$key]);
    if (array_search("_promoter", $array) === FALSE) $array[] = "_promoter";
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents_repeat(DATAROOT . 'exammember_submit.txt', $statedata) === FALSE) die('システムデータの書き込みに失敗しました。');
    if ($settingfile["submit_leader"] == $userid) $settingfile["submit_leader"] = "_promoter";
}
$array = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search($userid, $array);
if ($key !== FALSE) {
    unset($array[$key]);
    if (array_search("_promoter", $array) === FALSE) $array[] = "_promoter";
    $statedata = implode("\n", $array) . "\n";
    if (file_put_contents_repeat(DATAROOT . 'exammember_edit.txt', $statedata) === FALSE) die('システムデータの書き込みに失敗しました。');
    if ($settingfile["edit_leader"] == $userid) $settingfile["edit_leader"] = "_promoter";
}


//ファイル確認関連ファイルも書き換え
exam_totalization_new("_all", FALSE);
exam_totalization_edit("_all", FALSE);


//招待リンクを消す
unlink(DATAROOT . 'mail/state/promoter.txt');

//両者に事後報告
$date = date('Y/m/d H:i:s');
$nicknameo = nickname($prom[0]);
$nicknamen = nickname($userid);

$content = "$nicknameo 様

$eventname の主催者の交代が完了しました。
$nicknamen 様が新たな主催者となり、あなたは一般参加者となりました。

　実行日時：$date
";
//内部関数で送信
sendmail(email($prom[0]), '主催者の交代が完了しました', $content);

$content = "$nicknamen 様

$eventname の主催者の交代が完了しました。
以前の主催者 $nicknameo 様は一般参加者となり、あなたは新たな主催者となりました。

　実行日時：$date
";
//内部関数で送信
sendmail(email($userid), '主催者の交代が完了しました', $content);

register_alert("手続きが完了し、あなたが新たな主催者となりました。", "success");

redirect("../index.php");
