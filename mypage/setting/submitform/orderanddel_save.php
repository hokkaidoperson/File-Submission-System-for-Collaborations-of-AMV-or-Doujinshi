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


//一瞬リセット
if (file_exists(DATAROOT . 'form/submit/draft')) remove_directory(DATAROOT . 'form/submit/draft');

if (!mkdir(DATAROOT . 'form/submit/draft/', true)) die('ディレクトリの作成に失敗しました。');

for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/submit/draft/' . $i . '.txt';
    if (isset($_SESSION["submitformdata"][$i])) {
        //ファイル内容
        $filedata = $_SESSION["submitformdata"][$i];

        $filedatajson = json_encode($filedata);

        if (file_put_contents($fileplace, $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}
//ファイル内容
$filedata = $_SESSION["submitformdata"]["general"];
$filedatajson = json_encode($filedata);
if (file_put_contents(DATAROOT . 'form/submit/draft/general.txt', $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');

$_SESSION['situation'] = 'submitform_saved';

redirect("./index.php");
