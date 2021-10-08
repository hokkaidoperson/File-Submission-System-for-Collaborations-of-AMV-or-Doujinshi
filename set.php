<?php
//デバッグ用　リリース時にはコメントアウト---------------------
//ini_set("display_errors", 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//ini_set("log_errors", "On");
//ini_set("error_log", "error.log.txt");
//-----------------------------------------------------------


require_once 'functions/fileRW.php';
require_once 'functions/input_output.php';
require_once 'functions/input_validation.php';
require_once 'functions/submittion_exam.php';
require_once 'functions/userdata_session.php';

require_once 'plugins/getid3/getid3.php';
$getid3 = new getID3();

//各種定義・初期設定チェック----------------------------------
mb_language("Japanese");
mb_internal_encoding("UTF-8");
if (file_exists('dataplace.php')) require_once('dataplace.php'); else define('DATAROOT', dirname(__FILE__).'/data/');
if (!file_exists(DATAROOT . 'init.txt')) die('初期設定が済んでいません。');

define('PAGEROOT', dirname(__FILE__).'/');

//バージョン情報
define('VERSION', 'Gamma-4E-1');

$initdata = json_decode(file_get_contents_repeat(DATAROOT . 'init.txt'), true);
define('FILE_MAX_SIZE', (int)$initdata["maxsize"]);
if (isset($initdata["accounts"])) define('ACCOUNTS_PER_ADDRESS', (int)$initdata["accounts"]);
else define('ACCOUNTS_PER_ADDRESS', 1);
define('META_NOFOLLOW', (isset($initdata["robot"]) and $initdata["robot"] == 1));

$eventname = $initdata["eventname"];
$siteurl = file_get_contents_repeat(DATAROOT . 'siteurl.txt');

$sendmaildata = json_decode(file_get_contents_repeat(DATAROOT . 'mail.txt'), true);
//----------------------------------------------------------


//メール配信制御
require_once('mail_scheduler.php');


//不要ファイル浄化-------------------------------------------
//バージョンアップで要らなくなった各種ファイルを自動除去

//下のfile_remover変数に順次追加（定義時にPAGEROOT定数は不要）
$file_remover = array(
    "register/invitation/co_useridcheck.php",
    "register/invitation/prom_useridcheck.php",
    "state_special/",
    "images/",
    "css/bootstrap.css",
    "css/bootstrap.css.map"
);

foreach ($file_remover as $filename) {
    $filename = PAGEROOT . $filename;
    if (file_exists($filename)) {
        if (is_dir($filename)) remove_directory($filename);
        else unlink($filename);
    }
}
//----------------------------------------------------------


//その他ユーザー関数----------------------------------------

//保存している規定値（接頭辞とか）を使ってメール送信
function sendmail($email, $subject, $content) {
    global $sendmaildata;
    global $eventname;
    global $siteurl;
    if ($sendmaildata["pre"] == '') $mailpre = mb_substr($eventname, 0, 15);
    else $mailpre = $sendmaildata["pre"];
    if ($sendmaildata["from"] == '') {
        $sendmaildata["from"] = ini_get('sendmail_from');
        if ($sendmaildata["from"] === FALSE or $sendmaildata["from"] == "") die("メール：From情報が欠落しています。");
    }
    if ($sendmaildata["fromname"] != '') $from = mb_encode_mimeheader($sendmaildata["fromname"], "UTF-8") . " <" . $sendmaildata["from"] . ">";
    else $from = $sendmaildata["from"];
    $from = str_replace(["\r\n", "\r", "\n"], "", $from);
    $subject = '【' . $mailpre . '】' . $subject;
    if ($sendmaildata["sendonly"] == 1 ) $content = "※このメールは、$eventname に関する自動送信メールです。
　あなたが $eventname に関わっている覚えが無い場合は、このまま本メールを破棄して下さい。
※このメールアドレスは送信専用です。
　こちらに返信頂いても受信出来ませんのでご了承下さい。
------------------------------
$content
------------------------------
$eventname
$siteurl";
    else $content = "※このメールは、$eventname に関する自動送信メールです。
　あなたが $eventname に関わっている覚えが無い場合は、このまま本メールを破棄して下さい。
------------------------------
$content
------------------------------
$eventname
$siteurl";

    if (!mb_send_mail($email, $subject, $content, "Content-Type: text/plain; charset=UTF-8 \nX-Mailer: PHP/" . phpversion() . " \nFrom: $from \nContent-Transfer-Encoding: BASE64", "-f " . $sendmaildata["from"])) die("メール送信に失敗しました。");

}

//メッセージ機能トップで、メッセージを日付順に並べ替える用の関数
function msg_callback_fnc($a, $b) {
    list($dummy, $atime) = explode('_', $a);
    list($dummy, $btime) = explode('_', $b);
    if ((int)$atime > (int)$btime) return -1;
    else if  ((int)$atime == (int)$btime) return 0;
    else return 1;
}

//FAQで、結果をヒット数順に並べ替える用の関数
function faq_callback_fnc($a, $b) {
    if ((int)$a["hits"] > (int)$b["hits"]) return -1;
    else if ((int)$a["hits"] == (int)$b["hits"]) return 0;
    else return 1;
}
