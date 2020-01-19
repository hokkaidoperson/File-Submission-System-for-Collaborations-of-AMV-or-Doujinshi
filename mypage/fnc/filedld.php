<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

//ファイル提出者のユーザーID
$author = $_GET["author"];

//区分（userform→ユーザー登録フォームの添付ファイル
//　　　submitform→提出フォームの添付ファイル（メインのやつじゃなく）
//　　　submitmain→提出フォームのメインファイル
//　　　submitform_edit→提出フォームの添付ファイル（メインのやつじゃなく）
//　　　submitmain_edit→提出フォームのメインファイル）
$genre = $_GET["genre"];

//登録・提出フォームもしくはメインファイルのID
$id = $_GET["id"];

//提出フォームの添付ファイルの時は項目番号も
$partid = $_GET["partid"];

//編集中の時は編集番号も
$editid = $_GET["edit"];

if ($author == "" or $genre == "" or $id == "") die ('パラメーターエラー');
if ($genre == "submitform" and $partid == "") die ('パラメーターエラー');
if ($genre == "submitform_edit" and ($partid == "" or $editid == "")) die ('パラメーターエラー');
if ($genre == "submitmain_edit" and $editid == "") die ('パラメーターエラー');


//ファイルのダウンロード権があるか確認
$allowed = FALSE;

switch($_SESSION["state"]) {
    case 'p':
        //主催者は基本的にアクセスおｋ
        $allowed = TRUE;
    break;
    case 'c':
        //主催がアクセス権を与えていたらおｋ（あとファイル確認時）
        $aclplace = DATAROOT . 'fileacl/' . $_SESSION["userid"] . '.txt';
        if (file_exists($aclplace)) {
            $acldata = json_decode(file_get_contents($aclplace), true);
            if ($genre == "userform") {
                if (array_search($author . '_userform', $acldata) !== FALSE) $allowed = TRUE;
            }
            else if (array_search($author . '_' . $id, $acldata) !== FALSE) $allowed = TRUE;
        }
        if ($genre == "submitform" or $genre == "submitmain") {
            $examplace = DATAROOT . 'exam/' . $author . '_' . $id . '.txt';
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents($examplace), true);
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and array_key_exists($_SESSION["userid"], $examdata)) $allowed = TRUE;
            }
        }
        if ($genre == "submitform_edit" or $genre == "submitmain_edit") {
            $examplace = DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt';
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents($examplace), true);
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and array_key_exists($_SESSION["userid"], $examdata)) $allowed = TRUE;
            }
        }
    case 'g':
        //自分のファイルだけ（共同運営者も同じく）
        if ($author == $_SESSION['userid']) $allowed = TRUE;
    break;
}

if (!$allowed) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>権限エラー</title>
</head>
<body>
<p>ファイルへのアクセス権がありません。</p>
<p><a href="#" onClick="window.close();">クリックしてタブを閉じて下さい。</a></p>
</body>
</html>');

//参考　https://notepad-blog.com/content/14/　https://www.php.net/manual/ja/function.header.php

switch ($genre) {
    case 'userform':
        $filedir = "users_attach/";
        $formdataplace = "users/" . $author . ".txt";
    break;
    case 'submitform':
        $filedir = "submit_attach/";
        $formdataplace = "submit/" . $author . "/" . $id . ".txt";
    break;
    case 'submitmain':
        $filedir = "files/";
        $formdataplace = "submit/" . $author . "/" . $id . ".txt";
    break;
    case 'submitform_edit':
        $filedir = "edit_attach/";
        $formdataplace = "edit/" . $author . "/" . $id . ".txt";
    break;
    case 'submitmain_edit':
        $filedir = "edit_files/";
        $formdataplace = "submit/" . $author . "/" . $id . ".txt";
    break;
}

if ($genre == 'submitform' or $genre == 'submitform_edit') $fileplace = DATAROOT . $filedir . $author . "/" . $id . "_" . $partid;
else $fileplace = DATAROOT . $filedir . $author . "/" . $id;


if (!file_exists(DATAROOT . $formdataplace)) die('ファイルが存在しません。');
if (!file_exists($fileplace)) die('添付ファイルが存在しません。');

$formdata = json_decode(file_get_contents(DATAROOT . $formdataplace), true);

switch ($genre) {
    case 'userform':
        $ext = $formdata[$id];
        for ($i = 0; $i <= 9; $i++) {
            if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
            $formsetting = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
            if ($formsetting["id"] == $id) $filename = $formsetting["title"];
        }
    break;
    case 'submitform':
    case 'submitform_edit':
        $ext = $formdata[$partid];
        for ($i = 0; $i <= 9; $i++) {
            if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
            $formsetting = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
            if ($formsetting["id"] == $partid) $filename = $formsetting["title"];
        }
    break;
    case 'submitmain':
    case 'submitmain_edit':
        $ext = $formdata["submit"];
        $filename = $formdata["title"];
    break;
}

//ファイルダウンロードさせる
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Length: '. filesize($fileplace));
header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');

readfile($fileplace);
