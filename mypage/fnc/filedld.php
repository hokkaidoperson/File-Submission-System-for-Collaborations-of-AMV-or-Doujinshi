<?php
require_once('../../set.php');
setup_session();
session_validation();

//ファイル提出者のユーザーID
$author = basename($_GET["author"]);

//区分（userform→共通情報の添付ファイル
//　　　submitform→提出フォームの添付ファイル（メインのやつじゃなく）
//　　　submitmain→提出フォームのメインファイル
//　　　submitform_edit→提出フォームの添付ファイル（メインのやつじゃなく）
//　　　submitmain_edit→提出フォームのメインファイル）
$genre = $_GET["genre"];

//登録・提出フォームもしくはメインファイルのID
$id = basename($_GET["id"]);

//提出フォームの添付ファイルの時は項目番号も
$partid = basename($_GET["partid"]);

//編集中の時は編集番号も
$editid = basename($_GET["edit"]);

if ($author == "" or $genre == "" or $id == "") die ('パラメーターエラー');
if ($genre == "submitform" and $partid == "") die ('パラメーターエラー');
if ($genre == "submitmain" and $partid == "") die ('パラメーターエラー');
if ($genre == "submitform_edit" and ($partid == "" or $editid == "")) die ('パラメーターエラー');
if ($genre == "submitmain_edit" and ($partid == "" or $editid == "")) die ('パラメーターエラー');
if ($genre == "userform_edit" and $editid == "") die ('パラメーターエラー');


//ファイルのダウンロード権があるか確認
$allowed = FALSE;

switch($_SESSION["state"]) {
    case 'p':
        //主催者は基本的にアクセスおｋ
        $allowed = TRUE;
        if ($genre == "userform_edit") {
            $examplace = DATAROOT . 'exam_edit/' . $author . '_common_' . $editid . '.txt';
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents($examplace), true);
                if ($examdata["_commonmode"] == "new") $genre = "userform";
            }
        }
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
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and is_exammember($_SESSION["userid"], "submit")) $allowed = TRUE;
            }
        }
        if ($genre == "submitform_edit" or $genre == "submitmain_edit") {
            $examplace = DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt';
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents($examplace), true);
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and is_exammember($_SESSION["userid"], $examdata["_membermode"])) $allowed = TRUE;
            }
        }
        if ($genre == "userform_edit") {
            $examplace = DATAROOT . 'exam_edit/' . $author . '_common_' . $editid . '.txt';
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents($examplace), true);
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and is_exammember($_SESSION["userid"], "edit")) $allowed = TRUE;
                if ($examdata["_commonmode"] == "new") $genre = "userform";
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
        $filedir = "files/$author/common/";
        $formdataplace = "users/" . $author . ".txt";
    break;
    case 'submitform':
        $filedir = "files/$author/$id/";
        $formdataplace = "submit/" . $author . "/" . $id . ".txt";
    break;
    case 'submitmain':
        $filedir = "files/$author/$id/";
        $formdataplace = "submit/" . $author . "/" . $id . ".txt";
    break;
    case 'userform_edit':
        $filedir = "edit_files/$author/common/";
        $formdataplace = "edit/" . $author . "/common.txt";
    break;
    case 'submitform_edit':
        $filedir = "edit_files/$author/$id/";
        $formdataplace = "edit/" . $author . "/" . $id . ".txt";
    break;
    case 'submitmain_edit':
        $filedir = "edit_files/$author/$id/";
        $formdataplace = "edit/" . $author . "/" . $id . ".txt";
    break;
}

if ($genre == 'submitform' or $genre == 'submitform_edit') $fileplace = DATAROOT . $filedir . $partid;
else if ($genre == 'submitmain' or $genre == 'submitmain_edit') $fileplace = DATAROOT . $filedir . "main_" . $partid;
else $fileplace = DATAROOT . $filedir . $id;


if (!file_exists(DATAROOT . $formdataplace)) die('ファイルが存在しません。');
if (!file_exists($fileplace)) die('添付ファイルが存在しません。');

$formdata = json_decode(file_get_contents(DATAROOT . $formdataplace), true);

switch ($genre) {
    case 'userform':
        $tmp = explode("_", $id, 2);
        $ext = $formdata[$tmp[0]][$tmp[1]];
    break;
    case 'userform_edit':
        $tmp = explode("_", $id, 2);
        $ext = $formdata[$tmp[0] . "_add"][$tmp[1]];
    break;
    case 'submitform':
        $tmp = explode("_", $partid, 2);
        $ext = $formdata[$tmp[0]][$tmp[1]];
    break;
    case 'submitform_edit':
        $tmp = explode("_", $partid, 2);
        $ext = $formdata[$tmp[0] . "_add"][$tmp[1]];
    break;
    case 'submitmain':
        $ext = $formdata["submit"][$partid];
    break;
    case 'submitmain_edit':
        $ext = $formdata["submit_add"][$partid];
    break;
}

//ファイルダウンロードさせる
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Length: '. filesize($fileplace));
header('Content-Disposition: attachment; filename="' . $ext . '"');

readfile($fileplace);
