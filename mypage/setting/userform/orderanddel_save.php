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
if (file_exists(DATAROOT . 'form/userinfo/draft')) remove_directory(DATAROOT . 'form/userinfo/draft');

if (!mkdir(DATAROOT . 'form/userinfo/draft/')) die('ディレクトリの作成に失敗しました。');

for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/userinfo/draft/' . $i . '.txt';
    if (isset($_SESSION["userformdata"][$i])) {
        //ファイル内容
        $filedata = $_SESSION["userformdata"][$i];

        $filedatajson = json_encode($filedata);

        if (file_put_contents($fileplace, $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}

$_SESSION['situation'] = 'userform_saved';

redirect("./index.php");
