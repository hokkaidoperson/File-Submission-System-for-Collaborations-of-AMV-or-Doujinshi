<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


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
register_alert("ファイル提出に関する設定変更が完了しました。<br>ご自身の入力内容を変更する場合は、「参加者・作品の一覧・編集」から一覧に移動し、あなたの作品を選択して下さい。<br><br><b>必須項目を新たに追加したりした場合、メッセージ機能を用いて参加者にその旨を通知し、設定変更を促して下さい。</b>", "success");

redirect("../../index.php");
