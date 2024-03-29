<?php
require_once('../../set.php');
setup_session();
session_validation();

//ファイル提出者のユーザーID
$author = isset($_GET["author"]) ? basename($_GET["author"]) : "";
//登録・提出フォームもしくはメインファイルのID
$id = isset($_GET["id"]) ? basename($_GET["id"]) : "";
//提出フォームの添付ファイルの時は項目番号も
$partid = isset($_GET["partid"]) ? basename($_GET["partid"]) : "";
//編集中の時は編集番号も
$editid = isset($_GET["edit"]) ? basename($_GET["edit"]) : "";

if (strpos($author, "_exam-s-") !== FALSE) {
    $exam = TRUE;
    $examplace = DATAROOT . 'exam/' . str_replace("_exam-s-", "", $author) . '.txt';
    if (!file_exists($examplace)) die('ファイルが存在しません。');
    $filedata = json_unpack($examplace);
    list($author, $id) = explode("/", $filedata["_realid"]);
} else if (strpos($author, "_exam-e-") !== FALSE) {
    $exam = TRUE;
    $examplace = DATAROOT . 'exam_edit/' . str_replace("_exam-e-", "", $author) . '.txt';
    if (!file_exists($examplace)) die('ファイルが存在しません。');
    $filedata = json_unpack($examplace);
    list($author, $id, $editid) = explode("/", $filedata["_realid"]);
    if ($id == "common") die('内部パラメーターエラー');
} else if (strpos($author, "_exam-c-") !== FALSE) {
    $exam = TRUE;
    $examplace = DATAROOT . 'exam_edit/' . str_replace("_exam-c-", "", $author) . '.txt';
    if (!file_exists($examplace)) die('ファイルが存在しません。');
    $filedata = json_unpack($examplace);
    list($author, $internal, $editid) = explode("/", $filedata["_realid"]);
    if ($internal != "common") die('内部パラメーターエラー');
} else $exam = FALSE;

//区分（userform→共通情報の添付ファイル
//　　　submitform→提出フォームの添付ファイル（メインのやつじゃなく）
//　　　submitmain→提出フォームのメインファイル
//　　　submitform_edit→提出フォームの添付ファイル（メインのやつじゃなく）
//　　　submitmain_edit→提出フォームのメインファイル）
$genre = $_GET["genre"];

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
        if ($genre == "userform_edit" and $exam) {
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents_repeat($examplace), true);
                if ($examdata["_commonmode"] == "new") $genre = "userform";
            }
        }
    break;
    case 'c':
        //主催がアクセス権を与えていたらおｋ（あとファイル確認時）
        $aclplace = DATAROOT . 'fileacl/' . $_SESSION["userid"] . '.txt';
        if (file_exists($aclplace)) {
            $acldata = json_decode(file_get_contents_repeat($aclplace), true);
            if ($genre == "userform") {
                if (array_search($author . '_userform', $acldata) !== FALSE) $allowed = TRUE;
            }
            else if (array_search($author . '_' . $id, $acldata) !== FALSE) $allowed = TRUE;
        }
        if ($genre == "submitform" or $genre == "submitmain" and $exam) {
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents_repeat($examplace), true);
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and is_exammember($_SESSION["userid"], "submit")) $allowed = TRUE;
            }
        }
        if ($genre == "submitform_edit" or $genre == "submitmain_edit" and $exam) {
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents_repeat($examplace), true);
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and is_exammember($_SESSION["userid"], $examdata["_membermode"])) $allowed = TRUE;
            }
        }
        if ($genre == "userform_edit" and $exam) {
            if (file_exists($examplace)) {
                $examdata = json_decode(file_get_contents_repeat($examplace), true);
                if ($examdata["_state"] != 2 and $examdata["_state"] != 3 and is_exammember($_SESSION["userid"], "edit")) $allowed = TRUE;
                if ($examdata["_commonmode"] == "new") $genre = "userform";
            }
        }
    case 'g':
        //自分のファイルだけ（共同運営者も同じく）
        if ($author == $_SESSION['userid']) $allowed = TRUE;
    break;
}

if (!$allowed) die_error_html('権限エラー', '<p>ファイルへのアクセス権がありません。</p>
<p><a href="#" onClick="window.close();">クリックしてタブを閉じて下さい。</a></p>');

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

$formdata = json_decode(file_get_contents_repeat(DATAROOT . $formdataplace), true);

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

if ($exam and exam_anonymous()) {
    preg_match('/\.([0-9a-zA-Z]+)$/i', $ext, $tmp);
    $ext = 'ダウンロード.' . $tmp[1];
}

//ファイルダウンロードさせる
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Length: '. filesize($fileplace));
header('Content-Disposition: attachment; filename="' . $ext . '"');

readfile($fileplace);
