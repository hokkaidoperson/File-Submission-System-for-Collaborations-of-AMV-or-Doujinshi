<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");

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

register_alert("設定内容を一時ファイルに保存しました。設定を完了する場合は、「変更内容を保存し適用する」ボタンを押して実際の入力画面に反映させて下さい。");

redirect("./index.php");
