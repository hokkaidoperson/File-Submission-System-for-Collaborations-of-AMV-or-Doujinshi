<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt')) redirect("./index.php");


csrf_prevention_validate();

if (!file_exists(DATAROOT . 'mail_schedule/')) {
    if (!mkdir(DATAROOT . 'mail_schedule/')) die('ディレクトリの作成に失敗しました。');
}

//日時の計算
$general = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

$date = array();

//開始日時
$date['from_just'] = (int)$general["from"];

//開始日時の3，2，1日前
$date['from_1'] = $date['from_just'] - 24 * 60 * 60;
$date['from_2'] = $date['from_1'] - 24 * 60 * 60;
$date['from_3'] = $date['from_2'] - 24 * 60 * 60;

//締切日時
$date['until_just'] = (int)$general["until"];

//開始日時の3，2，1日前
$date['until_1'] = $date['until_just'] - 24 * 60 * 60;
$date['until_2'] = $date['until_1'] - 24 * 60 * 60;
$date['until_3'] = $date['until_2'] - 24 * 60 * 60;

//ループ処理用
$roop = array('from_3', 'from_2', 'from_1', 'from_just', 'until_3', 'until_2', 'until_1', 'until_just');
$current = time();

//設定内容保存
foreach ($roop as $value) {
    if (array_search($value, (array)$_POST["schedule"]) !== FALSE and $date[$value] > $current) {
        if (file_put_contents_repeat(DATAROOT . 'mail_schedule/' . $value . '.txt', "1") === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists(DATAROOT . 'mail_schedule/' . $value . '.txt')) unlink(DATAROOT . 'mail_schedule/' . $value . '.txt');
    }
}


register_alert("自動配信設定を保存しました。", "success");

redirect("./index.php");
