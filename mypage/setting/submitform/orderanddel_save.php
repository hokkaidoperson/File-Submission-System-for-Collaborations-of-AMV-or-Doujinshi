<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


//一瞬リセット
if (file_exists(DATAROOT . 'form/submit/draft')) remove_directory(DATAROOT . 'form/submit/draft');

if (!mkdir(DATAROOT . 'form/submit/draft/', true)) die('ディレクトリの作成に失敗しました。');

for ($i = 0; $i <= 9; $i++) {
    $fileplace = DATAROOT . 'form/submit/draft/' . $i . '.txt';
    if (isset($_SESSION["submitformdata"][$i])) {
        //ファイル内容
        $filedata = $_SESSION["submitformdata"][$i];

        $filedatajson = json_encode($filedata);

        if (file_put_contents_repeat($fileplace, $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');
    } else {
        if (file_exists($fileplace)) unlink($fileplace);
    }
}
//ファイル内容
$filedata = $_SESSION["submitformdata"]["general"];
$filedatajson = json_encode($filedata);
if (file_put_contents_repeat(DATAROOT . 'form/submit/draft/general.txt', $filedatajson) === FALSE) die('設定内容の書き込みに失敗しました。');

register_alert("設定内容を一時ファイルに保存しました。設定を完了する場合は、「変更内容を保存し適用する」ボタンを押して実際の入力画面に反映させて下さい。");

redirect("./index.php");
