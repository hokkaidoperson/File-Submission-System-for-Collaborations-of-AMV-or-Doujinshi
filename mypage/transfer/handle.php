<?php
require_once('../../set.php');
setup_session();
session_validation();

if (!$_SESSION["admin"]) redirect("./index.php");

//zipモジュールチェック
if (!extension_loaded('zip')) die_error_html('拡張機能エラー', '<p>大変申し訳ございませんが、現在の状況ではZIPファイルを生成出来ません。<br>
この引継ぎ用データ作成機能では、PHPの拡張機能 <strong>zip</strong> を利用しますが、現在の環境では無効になっています。<br>
システム管理者にお問い合わせ下さい。<br>
もしあなた自身がシステム管理者であれば、PHPの設定を確認し、拡張機能を有効化もしくはインストールして下さい。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、前の画面にお戻り下さい。</a></p>');


csrf_prevention_validate();


$invalid = FALSE;

//必須の場合のパターン 文字数
if($_POST["eventname"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["eventname"]) > 50) $invalid = TRUE;

if($_POST["filesize"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9]*$/', $_POST["filesize"])) $invalid = TRUE;
else if((int)$_POST["filesize"] < 1) $invalid = TRUE;

if($_POST["accounts"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9]*$/', $_POST["accounts"])) $invalid = TRUE;
else if((int)$_POST["accounts"] < 1 or (int)$_POST["accounts"] > 10) $invalid = TRUE;

//メールアドレス形式確認　必須でない
if($_POST["system"] == ""){
} else if(!preg_match('/.+@.+\..+/', $_POST["system"])) $invalid = TRUE;

//文字数 必須でない
if($_POST["systemfrom"] == ""){
} else if(mb_strlen($_POST["systemfrom"]) > 30) $invalid = TRUE;

//文字数 必須でない
if($_POST["systempre"] == ""){
} else if(mb_strlen($_POST["systempre"]) > 15) $invalid = TRUE;

//文字種　どっちかかたっぽだけはNG
  if($_POST["recaptcha_sec"] == "" && $_POST["recaptcha_site"] == ""){
  } else if($_POST["recaptcha_sec"] == "" || $_POST["recaptcha_site"] == "") $invalid = TRUE;
  else if(!preg_match('/^[0-9a-zA-Z-_]*$/', $_POST["recaptcha_sec"]) || !preg_match('/^[0-9a-zA-Z-_]*$/', $_POST["recaptcha_site"])) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


mkdir(DATAROOT . 'tmp/');

dir_copy(DATAROOT . 'form/', DATAROOT . 'tmp/form/');
copy(DATAROOT . 'exammember_edit.txt', DATAROOT . 'tmp/exammember_edit.txt');
copy(DATAROOT . 'exammember_submit.txt', DATAROOT . 'tmp/exammember_submit.txt');
copy(DATAROOT . 'examsetting.txt', DATAROOT . 'tmp/examsetting.txt');
dir_copy(DATAROOT . 'users/', DATAROOT . 'tmp/users/');

foreach (users_array() as $userid => $data) {
    dir_copy(DATAROOT . 'files/' . $userid . '/common/', DATAROOT . 'tmp/files/' . $userid . '/common/');
    $data["transferred"] = $eventname;
    json_pack(DATAROOT . 'tmp/users/' . $userid . '.txt', $data);
}

$init = array(
    "eventname" => $_POST["eventname"],
    "maxsize" => $_POST["filesize"],
    "accounts" => $_POST["accounts"],
    "robot" => $_POST["robot"]
);

if (json_pack(DATAROOT . 'tmp/init.txt', $init) === FALSE) die('初期設定関連のデータの書き込みに失敗しました。');

$maildata = array(
    "from" => $_POST["system"],
    "sendonly" => $_POST["systemsend"],
    "fromname" => $_POST["systemfrom"],
    "pre" => $_POST["systempre"]
);

if (json_pack(DATAROOT . 'tmp/mail.txt', $maildata) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//reCAPTCHA
$recdata = array(
    "site" => $_POST["recaptcha_site"],
    "sec" => $_POST["recaptcha_sec"],
);

if (json_pack(DATAROOT . 'tmp/rec.txt', $recdata) === FALSE) die('reCAPTCHA関連のデータの書き込みに失敗しました。');

$zip = new ZipArchive;
$zip->open(DATAROOT . 'data-transfer.zip', ZipArchive::CREATE|ZipArchive::OVERWRITE);
zipSub($zip, DATAROOT . 'tmp/');
$zip->close();

remove_directory(DATAROOT . 'tmp/');

register_alert('引継ぎ用ZIPファイルの生成が完了しました。<br>
データディレクトリの最下層に、「data-transfer.zip」を生成しました。<strong>このZIPファイルを、引継ぎ先のシステムのデータディレクトリの最下層に設置した上で、初期設定を行って下さい</strong>。', "success");

redirect("./index.php");
