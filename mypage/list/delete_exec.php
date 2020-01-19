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
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/invite/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>
');


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//ファイル提出者のユーザーID
$author = $_POST["author"];

//提出ID
$id = $_POST["id"];

if ($author == "" or $id == "") die_mypage('パラメーターエラー');


//自分のファイルのみ編集可
if ($author != $_SESSION['userid']) die_mypage('ご自身のファイルのみ、編集が可能です。');

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die_mypage('現在、ファイル提出期間外のため、ファイル操作は行えません。');

//今のパスワードで認証
$userdata = json_decode(file_get_contents(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt'), true);
if (!password_verify($_POST["password"], $userdata["pwhash"])) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>認証エラー</title>
</head>
<body>
<p>現在のパスワードが誤っています。お手数ですが、入力をやり直して下さい。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、設定画面にお戻り下さい。</a></p>
</body>
</html>');


//色々削除
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');
$filedata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
unlink(DATAROOT . "submit/" . $author . "/" . $id . ".txt");

if (file_exists(DATAROOT . 'files/' . $author . '/' . $id)) unlink(DATAROOT . 'files/' . $author . '/' . $id);
foreach(glob(DATAROOT . 'submit_attach/' . $author . '/' . $id . '_*') as $filename) {
    unlink($filename);
}

if (file_exists(DATAROOT . "edit/" . $author . "/" . $id . ".txt")) unlink(DATAROOT . "edit/" . $author . "/" . $id . ".txt");
if (file_exists(DATAROOT . 'edit_files/' . $author . '/' . $id)) unlink(DATAROOT . 'edit_files/' . $author . '/' . $id);
foreach(glob(DATAROOT . 'edtt_attach/' . $author . '/' . $id . '_*') as $filename) {
    unlink($filename);
}

//検査関連で通知する人
$noticeto = array();
if (file_exists(DATAROOT . 'exam/' . $author . '_' . $id . '.txt')) {
    $examdata = json_decode(file_get_contents(DATAROOT . 'exam/' . $author . '_' . $id . '.txt'), true);
    unlink(DATAROOT . 'exam/' . $author . '_' . $id . '.txt');
    $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }

    if ($examdata["_state"] == 0 or $examdata["_state"] == 1) {
        foreach ($submitmem as $key) {
            $data = $examdata[$key];
            if ($data["opinion"] == -1) continue;
            $noticeto[$key] = 1;
        }
    }
}
if (file_exists(DATAROOT . 'exam_discuss/' . $author . '_' . $id . '.txt')) unlink(DATAROOT . 'exam_discuss/' . $author . '_' . $id . '.txt');
foreach(glob(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_*.txt') as $filename) {
    $examdata = json_decode(file_get_contents($filename), true);
    unlink($filename);
    $memberfile = DATAROOT . 'exammember_' . $examdata["_membermode"] . '.txt';
    $submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }

    if ($examdata["_state"] == 0 or $examdata["_state"] == 1) {
        foreach ($submitmem as $key) {
            $data = $examdata[$key];
            if ($data["opinion"] == -1) continue;
            $noticeto[$key] = 1;
        }
    }
}
foreach(glob(DATAROOT . 'exam_edit_discuss/' . $author . '_' . $id . '_*.txt') as $filename) {
    unlink($filename);
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
sendmail(email($promoter[0]), '作品が削除されました（' . $filedata["title"] . '）', $content);


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
$_SESSION['situation'] = 'edit_deleted';


?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
