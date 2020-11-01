<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


if (!file_exists(DATAROOT . 'form/userinfo/draft/')) {
    if (!mkdir(DATAROOT . 'form/userinfo/draft/')) die('ディレクトリの作成に失敗しました。');
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
        copy(DATAROOT . 'form/userinfo/' . "$i" . '.txt', DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt');
    }
}

//一時ファイルを実際の設定ファイルにする
for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/userinfo/' . $i . '.txt';
    if (file_exists(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt')) {
        //ファイル内容
        $filedata = file_get_contents_repeat(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt');

        if (file_put_contents_repeat($fileplace, $filedata) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}


//一時ファイルを消す
remove_directory(DATAROOT . 'form/userinfo/draft');

unset($_SESSION["userformdata"]);

//勝利宣言（？）
if (!file_exists(DATAROOT . 'form/userinfo/done.txt')){
    if (file_put_contents_repeat(DATAROOT . 'form/userinfo/done.txt', "1") === FALSE) die('設定内容の書き込みに失敗しました。');
}
register_alert("<p>共通情報入力画面の設定変更が完了しました。<br>ご自身の入力内容を変更する場合は、「共通情報の入力・編集」から設定画面に推移して下さい。</p><p><b>必須項目を新たに追加したりした場合、メッセージ機能を用いて参加者にその旨を通知し、設定変更を促して下さい。</b></p>", "success");

redirect("../../index.php");
