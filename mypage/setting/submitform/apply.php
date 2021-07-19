<?php
require_once('../../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

if (no_access_right(array("p"))) redirect("./index.php");

if (!isset($_SESSION["submitformdata"])) redirect("./index.php");
if (!isset($_SESSION["submitformdata"]["general"])) redirect("./index.php");


for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/submit/' . $i . '.txt';
    if (isset($_SESSION["submitformdata"][$i])) {
        //ファイル内容
        $filedata = $_SESSION["submitformdata"][$i];

        if (json_pack($fileplace, $filedata) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}
$fileplace = DATAROOT . 'form/submit/general.txt';
$filedata = $_SESSION["submitformdata"]["general"];
if (json_pack($fileplace, $filedata) === FALSE) die('設定内容の書き込みに失敗しました。');


unset($_SESSION["submitformdata"]);

//勝利宣言（？）
if (!file_exists(DATAROOT . 'form/submit/done.txt')){
    if (file_put_contents_repeat(DATAROOT . 'form/submit/done.txt', "1") === FALSE) die('設定内容の書き込みに失敗しました。');
}
register_alert("<p>ファイル提出に関する設定変更が完了しました。<br>ご自身の入力内容を変更する場合は、「参加者・作品の一覧・編集」から一覧に移動し、あなたの作品を選択して下さい。</p><p><strong>必須項目を新たに追加したりした場合、メッセージ機能を用いて参加者にその旨を通知し、設定変更を促して下さい。</strong></p>", "success");

redirect("../../index.php");
