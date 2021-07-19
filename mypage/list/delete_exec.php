<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p", "c", "g"))) redirect("./index.php");


csrf_prevention_validate();

//ファイル提出者のユーザーID
$author = basename($_POST["author"]);

//提出ID
$id = basename($_POST["id"]);

if ($author == "" or $id == "") die('パラメーターエラー');


//自分のファイルのみ編集可
if ($author != $_SESSION['userid']) die('ご自身のファイルのみ、編集が可能です。');

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die('現在、ファイル提出期間外のため、ファイル操作は行えません。');

//今のパスワードで認証
$userdata = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt'), true);
if (!password_verify($_POST["password"], $userdata["pwhash"])) die();


//色々削除
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');
$filedata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
unlink(DATAROOT . "submit/" . $author . "/" . $id . ".txt");

if (file_exists(DATAROOT . 'files/' . $author . '/' . $id . '/')) remove_directory(DATAROOT . 'files/' . $author . '/' . $id . '/');

if (file_exists(DATAROOT . "edit/" . $author . "/" . $id . ".txt")) unlink(DATAROOT . "edit/" . $author . "/" . $id . ".txt");
if (file_exists(DATAROOT . 'edit_files/' . $author . '/' . $id . '/')) remove_directory(DATAROOT . 'edit_files/' . $author . '/' . $id . '/');

//合計再生時間
$userprofile = new JsonRW(user_file_path());
$userprofile->array["length_sum"] -= $filedata["length_sum"];
$userprofile->write();

//検査関連で通知する人
$noticeto = array();
if (isset($filedata["related_exams"])) foreach ($filedata["related_exams"] as $examname) {
    if (strpos($examname, "exam/") !== FALSE) {
        $exambasename = strpos("exam/", "", $examname);
        $examdata = json_decode(file_get_contents_repeat(DATAROOT . $examname . '.txt'), true);
        unlink(DATAROOT . $examname . '.txt');
        $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $key = array_search("_promoter", $submitmem);
        if ($key !== FALSE) {
            $submitmem[$key] = id_promoter();
        }

        if ($examdata["_state"] == 0 or $examdata["_state"] == 1) {
            foreach ($submitmem as $key) {
                $data = $examdata[$key];
                $noticeto[$key] = 1;
            }
        }
        if (file_exists(DATAROOT . 'exam_discuss/' . $exambasename . '.txt')) unlink(DATAROOT . 'exam_discuss/' . $exambasename . '.txt');
    } else if (strpos($examname, "exam_edit/") !== FALSE) {
        $exambasename = strpos("exam_edit/", "", $examname);
        $examdata = json_decode(file_get_contents_repeat(DATAROOT . $examname . '.txt'), true);
        unlink(DATAROOT . $examname . '.txt');
        $memberfile = DATAROOT . 'exammember_' . $examdata["_membermode"] . '.txt';
        $submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $key = array_search("_promoter", $submitmem);
        if ($key !== FALSE) {
            $submitmem[$key] = id_promoter();
        }

        if ($examdata["_state"] == 0 or $examdata["_state"] == 1) {
            foreach ($submitmem as $key) {
                $data = $examdata[$key];
                $noticeto[$key] = 1;
            }
        }
        if (file_exists(DATAROOT . 'exam_edit_discuss/' . $exambasename . '.txt')) unlink(DATAROOT . 'exam_edit_discuss/' . $exambasename . '.txt');
    }
}

$userid = $_SESSION["userid"];


//主催者に事後報告
$promoter = id_state('p');
$nicknamep = nickname($promoter[0]);
$nickname = nickname($userid);
$date = date('Y/m/d H:i:s');

$content = "$nicknamep 様

$eventname のポータルサイトで、 $nickname 様が作品「" . $filedata["title"] . "」を削除しましたのでお知らせします。

　削除日時：$date
";

//内部関数で送信
if ($_SESSION["userid"] != $promoter[0]) sendmail(email($promoter[0]), '作品が削除されました（' . $filedata["title"] . '）', $content);


//ファイル確認をお願いしてた人
foreach($noticeto as $noticeid => $dummy) {
    if ($noticeid == $promoter[0]) continue;
    $nicknamec = nickname($noticeid);

    $content = "$nicknamec 様

$nickname 様の作品「" . $filedata["title"] . "」の確認（もしくは議論）をお願いしておりましたが、提出者が作品を削除しました。
従いまして、当該作品の確認作業（もしくは議論）を中止致します。
ファイル確認へのご協力、ありがとうございます。

　削除日時：$date
";

//内部関数で送信
sendmail(email($noticeid), 'ファイル確認（議論）中止のお知らせ（作品削除・' . $filedata["title"] . '）', $content);
}


//本人に通知
$email = $_SESSION["email"];

$content = "$nickname 様

$eventname のポータルサイトにて、作品「" . $filedata["title"] . "」を削除しました。

この通知は、$eventname のポータルサイトであなたの登録情報に変更があった際（ファイルの新規提出など）に、
それが不正ログインによる変更でないかどうか確認するためにお送りしているものです。


【あなた自身の操作で提出した場合】
ご確認ありがとうございます。引き続きポータルサイトをご利用下さい。
このメールは削除しても構いません。


【提出した覚えが無い場合】
第三者があなたのアカウントに不正ログインした可能性があります。直ちに、パスワードの変更を行って下さい。

あなたの操作でログインした後、「アカウント情報編集」→「パスワード変更」の順に選択して下さい。
";
//内部関数で送信
sendmail($email, '作品を削除しました（' . $filedata["title"] . '）', $content);
register_alert("作品を削除しました。", "success");

redirect("./index.php");
