<?php
//デバッグ用　リリース時にはコメントアウト---------------------
//ini_set("display_errors", 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//ini_set("log_errors", "On");
//ini_set("error_log", "***********/error.log.txt");
//----------------------------------------------------------

if (file_exists('dataplace.php')) require_once('dataplace.php'); else define('DATAROOT', dirname(__FILE__).'/data/');
if (!file_exists(DATAROOT . 'init.txt')) die('初期設定が済んでいません。');

define('PAGEROOT', dirname(__FILE__).'/');

//バージョン情報
define('VERSION', 'Gamma-1');

define('FILE_MAX_SIZE', file_get_contents(DATAROOT . 'maxsize.txt'));

$eventname = file_get_contents(DATAROOT . 'eventname.txt');
$siteurl = file_get_contents(DATAROOT . 'siteurl.txt');

//ユーザー関数

//保存している規定値（接頭辞とか）を使ってメール送信
function sendmail($email, $subject, $content) {
    $sendmaildata = json_decode(file_get_contents(DATAROOT . 'mail.txt'), true);
    global $eventname;
    global $siteurl;
    if ($sendmaildata["pre"] == '') $mailpre = mb_substr($eventname, 0, 15);
    else $mailpre = $sendmaildata["pre"];
    if ($sendmaildata["fromname"] != '') $from = "From: " . $sendmaildata["fromname"] . " <" . $sendmaildata["from"] . ">";
    else $from = "From: " . $sendmaildata["from"];
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

    if ($sendmaildata["from"] != '') {
      if (!mb_send_mail($email, $subject, $content, $from)) die("メール送信に失敗しました。");
    } else {
      if (!mb_send_mail($email, $subject, $content)) die("メール送信に失敗しました。");
    }

}


//全ユーザーの情報を配列に収める　$array["userid"]["nickname"など]
function users_array() {
    $array = array();
    foreach (glob(DATAROOT . 'users/[!_]*.txt') as $filename) {
        $key = basename($filename, ".txt");
        $array[$key] = json_decode(file_get_contents($filename), true);
    }
    return $array;
}

//立場に合うユーザーIDを配列に収める
//p...主催者（1人だけのはず）　c...共催　g...一般参加者　o...非参加者（1人だけ＆システム管理者だけのはず）
function id_state($state) {
    switch ($state){
        case 'p':
            $filename = 'promoter';
        break;
        case 'c':
            $filename = 'co';
        break;
        case 'g':
            $filename = 'general';
        break;
        case 'o':
            $filename = 'outsider';
        break;
        default:
            return FALSE;
    }
    if (!file_exists(DATAROOT . 'users/_' . $filename . '.txt')) return array();
    return file(DATAROOT . 'users/_' . $filename . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

//システム管理者のID
function id_admin() {
    foreach (glob(DATAROOT . 'users/[!_]*.txt') as $filename) {
        $key = basename($filename, ".txt");
        $array = json_decode(file_get_contents($filename), true);
        if ($array["admin"]) return $key;
    }
}

//ユーザーが存在するかどうか
function user_exists($id) {
    return file_exists(DATAROOT . 'users/' . $id . '.txt');
}

//ID→ユーザー情報の配列（IDが無ければFALSE）
function id_array($id) {
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array;
}

//ID→ニックネーム（IDが無ければFALSE）
function nickname($id) {
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array["nickname"];
}

//ID→メルアド（IDが無ければFALSE）
function email($id) {
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array["email"];
}

//ID→立場記号（IDが無ければFALSE）
function state($id) {
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array["state"];
}

//マイページ表示画面向けのdie関数（テキストを表示して、フッターをちゃんと出してからdieする）
//マイページ画面向けでないやつ（handle.phpとか）だったらdieで充分
function die_mypage($echo = "") {
    global $eventname;
    global $siteurl;
    echo $echo;
    require_once(PAGEROOT . 'mypage_footer.php');
    die();
}

//期間外操作が認められているか　認められている場合は期限を、認められていない場合はFALSEを返す
//$idは通常省略（自分のチェック）
//subjectに「userform」か「submit」か提出作品ID
function outofterm($subject, $id = "") {
    if ($id == "") $id = $_SESSION["userid"];
    $aclplace = DATAROOT . 'outofterm/' . $id . '.txt';
    if (file_exists($aclplace)) {
        $acldata = json_decode(file_get_contents($aclplace), true);
        if ($acldata["expire"] <= time()) return FALSE;
        if (array_search($subject, $acldata) !== FALSE) return $acldata["expire"];
        else return FALSE;
    } else return FALSE;
}

//提出期間中かどうか調べる（FALSE:そもそも設定してないor期間外　TRUE:期間中）
function in_term() {
    if (!file_exists(DATAROOT . 'form/submit/general.txt')) return FALSE;
    $generaldata = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);
    if ($generaldata["from"] > time()) return FALSE;
    else if ($generaldata["until"] <= time()) return FALSE;
    else return TRUE;
}

//締め切り前かどうか調べる（FALSE:期間外　TRUE:期間中or未設定）
function before_deadline() {
    if (!file_exists(DATAROOT . 'form/submit/general.txt')) return TRUE;
    $generaldata = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);
    if ($generaldata["until"] <= time()) return FALSE;
    else return TRUE;
}

//ユーザーブラックリスト
function blackuser($id) {
    $blplace = DATAROOT . 'blackuser.txt';
    if (file_exists($blplace)) $bldata = json_decode(file_get_contents($blplace), true);
    else $bldata = array();
    if (array_search($id, $bldata) !== FALSE) return TRUE;
    else return FALSE;
}

//IPブラックリスト
function blackip($admin, $state) {
    if ($admin) return FALSE;
    if ($state == "p" or $state == "c") return FALSE;
    $blplace = DATAROOT . 'blackip.txt';
    if (file_exists($blplace)) {
        $exlist = str_replace(array("\r\n", "\r", "\n"), "\n", file_get_contents($blplace));
        $exlist = preg_quote($exlist, '/');
        $exlist = str_replace('\*', '[0-9A-Za-z.-]+', $exlist);
        $exlist = str_replace('\?', '[0-9A-Za-z.-]', $exlist);
        $exlist = str_replace('~', '[0-9]+', $exlist);
        $exlist = str_replace('\!', '[0-9]', $exlist);
        $exlist = explode("\n", $exlist);
        $remotehost = gethostbyaddr(getenv("REMOTE_ADDR"));
        $excluded = FALSE;
        foreach ($exlist as $checking) {
            $prefix = '/^' . $checking . '$/';
            if (preg_match($prefix, getenv("REMOTE_ADDR"))) return TRUE;
            if (preg_match($prefix, $remotehost)) return TRUE;
        }
    }
    return FALSE;
}

//メッセージ機能トップで、メッセージを日付順に並べ替える用の関数
function msg_callback_fnc($a, $b) {
    list($dummy, $atime) = explode('_', $a);
    list($dummy, $btime) = explode('_', $b);
    if ((int)$atime > (int)$btime) return -1;
    else if  ((int)$atime == (int)$btime) return 0;
    else return 1;
}

// 再帰的にディレクトリを削除する関数（引用：https://www.sejuku.net/blog/78776）
function remove_directory($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        // ファイルかディレクトリによって処理を分ける
        if (is_dir("$dir/$file")) {
            // ディレクトリなら再度同じ関数を呼び出す
            remove_directory("$dir/$file");
        } else {
            // ファイルなら削除
            unlink("$dir/$file");
        }
    }
    // 指定したディレクトリを削除
    return rmdir($dir);
}

//------------------------------------

//チェック系関数　問題無ければ0を、そうでなければ1を返す（ユーザーフォームの入力事項確認に使う）
//必須・任意関連（テキストボックス、エリア）
function check_required($type, $item) {
  if ($type == "1" && $item === "") return 1;
  return 0;
}

//必須・任意関連（テキストボックス×2）
function check_required2($type, $item, $item2) {
  if ($type == "1") {
    if ($item == "" || $item2 == "")
    return 1;
  }
  if ($type == "2") {
    if ($item == "" && $item2 == "")
    return 1;
  }
  return 0;
}

//テキスト系の最大最小（0だとチェックしない）
function check_maxmin($max, $min, $item) {
  if ($max != 0) {
    if (mb_strlen($item) > $max) return 1;
  }
  if ($min != 0) {
    if (mb_strlen($item) < $min && mb_strlen($item) > 0) return 1;
  }
  return 0;
}
