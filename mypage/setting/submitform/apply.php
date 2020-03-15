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


if (!file_exists(DATAROOT . 'form/submit/draft/')) {
    if (!mkdir(DATAROOT . 'form/submit/draft/')) die_mypage('ディレクトリの作成に失敗しました。');
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
        copy(DATAROOT . 'form/submit/' . "$i" . '.txt', DATAROOT . 'form/submit/draft/' . "$i" . '.txt');
    }
    copy(DATAROOT . 'form/submit/general.txt', DATAROOT . 'form/submit/draft/general.txt');
}

//一時ファイルを実際の設定ファイルにする
for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/submit/' . $i . '.txt';
    if (file_exists(DATAROOT . 'form/submit/draft/' . "$i" . '.txt')) {
        //ファイル内容
        $filedata = file_get_contents(DATAROOT . 'form/submit/draft/' . "$i" . '.txt');

        if (file_put_contents($fileplace, $filedata) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}

$fileplace = DATAROOT . 'form/submit/general.txt';
//ファイル内容
$filedata = file_get_contents(DATAROOT . 'form/submit/draft/general.txt');

if (file_put_contents($fileplace, $filedata) === FALSE) die('設定内容の書き込みに失敗しました。');


//一時ファイルを消す
remove_directory(DATAROOT . 'form/submit/draft');

unset($_SESSION["submitformdata"]);

//勝利宣言（？）
if (!file_exists(DATAROOT . 'form/submit/done.txt')){
    if (file_put_contents(DATAROOT . 'form/submit/done.txt', "1") === FALSE) die('設定内容の書き込みに失敗しました。');
}
$_SESSION['situation'] = 'submitform_applied';

redirect("../../index.php");
