<?php
//デバッグ用　リリース時にはコメントアウト---------------------
//ini_set("display_errors", 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//ini_set("log_errors", "On");
//ini_set("error_log", "******error.log.txt");
//----------------------------------------------------------


//各種定義・初期設定チェック----------------------------------
if (file_exists('dataplace.php')) require_once('dataplace.php'); else define('DATAROOT', dirname(__FILE__).'/data/');
if (!file_exists(DATAROOT . 'init.txt')) die('初期設定が済んでいません。');

define('PAGEROOT', dirname(__FILE__).'/');

//バージョン情報
define('VERSION', 'Gamma-3E-0');

$initdata = json_decode(file_get_contents_repeat(DATAROOT . 'init.txt'), true);
define('FILE_MAX_SIZE', (int)$initdata["maxsize"]);
if (isset($initdata["accounts"])) define('ACCOUNTS_PER_ADDRESS', (int)$initdata["accounts"]);
else define('ACCOUNTS_PER_ADDRESS', 1);
define('META_NOFOLLOW', (isset($initdata["robot"]) and $initdata["robot"] == 1));

$eventname = $initdata["eventname"];
$siteurl = file_get_contents_repeat(DATAROOT . 'siteurl.txt');
//----------------------------------------------------------


//メール配信制御
require_once('mail_scheduler.php');


//不要ファイル浄化-------------------------------------------
//バージョンアップで要らなくなった各種ファイルを自動除去

//下のfile_remover変数に順次追加（定義時にPAGEROOT定数は不要）
$file_remover = array(
    "register/invitation/co_useridcheck.php",
    "register/invitation/prom_useridcheck.php",
    "state_special/"
);

foreach ($file_remover as $filename) {
    $filename = PAGEROOT . $filename;
    if (file_exists($filename)) {
        if (is_dir($filename)) remove_directory($filename);
        else unlink($filename);
    }
}
//----------------------------------------------------------


//ユーザー関数-----------------------------------------------

//保存している規定値（接頭辞とか）を使ってメール送信
function sendmail($email, $subject, $content) {
    $sendmaildata = json_decode(file_get_contents_repeat(DATAROOT . 'mail.txt'), true);
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
        $array[$key] = json_decode(file_get_contents_repeat($filename), true);
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
        $array = json_decode(file_get_contents_repeat($filename), true);
        if ($array["admin"]) return $key;
    }
}

//主催者のID
function id_promoter() {
    $array = id_state("p");
    return $array[0];
}

//ユーザーが存在するかどうか
function user_exists($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    return file_exists(DATAROOT . 'users/' . $id . '.txt');
}

//ID→ユーザー情報の配列（IDが無ければFALSE）
function id_array($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array;
}

//ID→ニックネーム（IDが無ければFALSE）
function nickname($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array["nickname"];
}

//ID→メルアド（IDが無ければFALSE）
function email($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array["email"];
}

//ID→立場記号（IDが無ければFALSE）
function state($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $id . '.txt'), true);
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
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    $aclplace = DATAROOT . 'outofterm/' . $id . '.txt';
    if (file_exists($aclplace)) {
        $acldata = json_decode(file_get_contents_repeat($aclplace), true);
        if ($acldata["expire"] <= time()) return FALSE;
        if (array_search($subject, $acldata) !== FALSE) return $acldata["expire"];
        else return FALSE;
    } else return FALSE;
}

//提出期間中かどうか調べる（FALSE:そもそも設定してないor期間外　TRUE:期間中）
function in_term() {
    if (!file_exists(DATAROOT . 'form/submit/general.txt')) return FALSE;
    $generaldata = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
    if ($generaldata["from"] > time()) return FALSE;
    else if ($generaldata["until"] <= time()) return FALSE;
    else return TRUE;
}

//締め切り前かどうか調べる（FALSE:期間外　TRUE:期間中or未設定）
function before_deadline() {
    if (!file_exists(DATAROOT . 'form/submit/general.txt')) return TRUE;
    $generaldata = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
    if ($generaldata["until"] <= time()) return FALSE;
    else return TRUE;
}

//ユーザーブラックリスト
function blackuser($id) {
    $blplace = DATAROOT . 'blackuser.txt';
    if (file_exists($blplace)) $bldata = json_decode(file_get_contents_repeat($blplace), true);
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
        $exlist = str_replace(array("\r\n", "\r", "\n"), "\n", file_get_contents_repeat($blplace));
        $exlist = preg_quote($exlist, '/');
        $exlist = str_replace('\*', '[0-9A-Za-z.-]+', $exlist);
        $exlist = str_replace('\?', '[0-9A-Za-z.-]', $exlist);
        $exlist = str_replace('~', '[0-9]+', $exlist);
        $exlist = str_replace('\!', '[0-9]', $exlist);
        $exlist = explode("\n", $exlist);
        $remotehost = gethostbyaddr(getenv("REMOTE_ADDR"));
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

//再帰的にディレクトリを削除する関数（引用：https://www.sejuku.net/blog/78776）
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

//配列をjsonにパックして保存（ファイルの場所、配列の順）
//file_put_contentsの結果をリターン
function json_pack($filename, $array) {
    $arrayjson =  json_encode($array);
    return file_put_contents_repeat($filename, $arrayjson);
}

//jsonのファイルをほどいた配列を返す
//ファイルが無い場合はFALSE
function json_unpack($filename) {
    if (!file_exists($filename)) return FALSE;
    return json_decode(file_get_contents_repeat($filename), true);
}

//ファイル確認メンバー？
function is_exammember($userid, $membermode) {
    $membermode = basename($membermode);
    $memberfile = DATAROOT . 'exammember_' . $membermode . '.txt';
    $submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }
    if (array_search($userid, $submitmem) === FALSE) return FALSE;
    else return TRUE;
}

//リーダー（設定無しの場合NULL）
function id_leader($membermode) {
    $settingfile = json_unpack(DATAROOT . 'examsetting.txt');
    if ($settingfile[$membermode . "_leader"] == "") return NULL;
    if ($settingfile[$membermode . "_leader"] === "_promoter") {
        $settingfile[$membermode . "_leader"] = id_promoter();
    }
    return $settingfile[$membermode . "_leader"];
}

//匿名モード？
function exam_anonymous() {
    $settingfile = json_unpack(DATAROOT . 'examsetting.txt');
    if ($settingfile["anonymous"] == "1") return TRUE;
    return FALSE;
}

//ファイル確認集計処理ショートカット（新規提出）
//意見の書き込み後、もしくは確認者リストの更新後に呼び出す
//現在の確認者リストに基づき意見を集計、回答が出揃っていれば〆処理
//$subjectは処理するファイル名、「_all」で全部について集計
//$forcecloseがTRUEで強制〆切
//$subjectが「_all」以外の時は、検査結果の数字（未回答者有の場合FALSE）を返す（「_all」の時は一番最後に検査した奴の結果を返すけどあまり意味が無い）
function exam_totalization_new($subject, $forceclose) {
    global $siteurl;
    global $eventname;
    if ($subject === "_all") $subjectarray = glob(DATAROOT . 'exam/*.txt');
    else $subjectarray = array("exam/$subject.txt");
    $formsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);

    $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }

    foreach($subjectarray as $filename) {
        $subject = basename($filename, '.txt');
        $answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam/' . $subject . '.txt'), true);
        list($author, $id) = explode("/", $answerdata["_realid"]);
        if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) continue;

        if ($answerdata["_state"] != 0 and $answerdata["_state"] != 4) continue;
        if ($answerdata["_state"] == 4) $dontnotice = TRUE;
        else $dontnotice = FALSE;

        //メンバーにいない人をファイルから外す
        foreach ($answerdata as $key => $data) {
            if (strpos($key, '_') !== FALSE) continue;
            if (array_search($key, $submitmem) === FALSE) unset($answerdata[$key]);
        }

        //全員の回答終わった？
        $complete = TRUE;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) {
                $complete = FALSE;
                continue;
            }
            $data = $answerdata[$key];
            if ($data["opinion"] == 0) $complete = FALSE;
        }
        if ($forceclose) $complete = TRUE;

        //回答終わってなければそこでおしまい
        if ($complete == FALSE) {
            if (json_pack(DATAROOT . 'exam/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');
            continue;
        }

        //以下、全員の回答が終わった時の処理

        //意見が一致したのか？（resultが0のままだったら対立してる）
        $result = 0;

        //計測用変数
        $op1 = 0;
        $op2 = 0;
        $op3 = 0;
        $count = 0;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) continue;
            $data = $answerdata[$key];
            switch ($data["opinion"]){
                case 1:
                    $op1++;
                break;
                case 2:
                    $op2++;
                break;
                case 3:
                    $op3++;
                break;
                default:
                    continue;
            }
            $count++;
        }
        if ($op1 == $count or $count == 0) $result = 1;
        else if ($op2 == $count) $result = 2;
        else if ($op3 == $count) $result = 3;

        //計測結果を保存
        $frame = FALSE;
        if ($result == 0) $answerdata["_state"] = 1;
        else {
            //理由取りまとめの必要不要
            if (id_leader("submit") != NULL and $count >= 2 and $result != 1 and $formsetting["reason"] == "notice") {
                $frame = TRUE;
                $answerdata["_state"] = 4;
            } else $answerdata["_state"] = 3;
            $answerdata["_result"] = ["opinion" => $result, "reason" => ""];
        }

        if (json_pack(DATAROOT . 'exam/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');

        //入力内容を読み込んで書き換え
        $formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
        if (!$frame) {
            $formdata["exam"] = $result;
            if (json_pack(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $formdata) === FALSE) die('作品データの書き込みに失敗しました。');
        }

        $authornick = nickname($author);

        if ($result == 0) {
            $pageurl = $siteurl . 'mypage/exam/discuss.php?examname=' . $subject;
            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                $nickname = nickname($key);
                if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この作品の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                else $content = "$nickname 様

作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
メンバー間で意見が分かれたため、この作品の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                sendmail(email($key), 'ファイル確認の結果（議論の必要あり・' . $formdata["title"] . '）', $content);
            }
        } else {
            switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この作品を承認しました。
作品の提出者に承認の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（承認・' . $formdata["title"] . '）';
                    $authorsubject = '作品を承認しました（' . $formdata["title"] . '）';
                break;
                case 2:
                    if ($frame) $contentpart = '軽微な修正が必要であるという意見で一致したため、この作品を修正待ち状態にしました。
作品の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、修正待ちとなった理由を添えて修正依頼の通知をします。';
                    else $contentpart = '軽微な修正が必要であるという意見で一致したため、この作品を修正待ち状態にしました。
作品の提出者に、修正依頼の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（修正待ち・' . $formdata["title"] . '）';
                    $authorsubject = '作品を修正して下さい（' . $formdata["title"] . '）';
                break;
                case 3:
                    if ($frame) $contentpart = '内容上の問題が多い、もしくは重大な問題があるという意見で一致したため、この作品を拒否しました。
作品の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、拒否となった理由を添えて拒否の通知をします。';
                    else $contentpart = '内容上の問題が多い、もしくは重大な問題があるという意見で一致したため、この作品を拒否しました。
作品の提出者に拒否の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（拒否・' . $formdata["title"] . '）';
                    $authorsubject = '作品の承認が見送られました（' . $formdata["title"] . '）';
                break;
            }

            //内部関数で送信
            if (!$dontnotice) foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                if ($author === (string)$key) continue;
                $nickname = nickname($key);
                if ($frame and (string)id_leader("submit") === (string)$key) $ps = "\n\n※ファイル確認のリーダーの方は、ファイル提出者への通知のため、以下のURLから、メンバーの意見を取りまとめる画面に進んで下さい。\n\n　理由入力ページ：" . $siteurl . 'mypage/exam/frame.php?examname=' . $subject;
                else $ps = "";
                if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                else $content = "$nickname 様

作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                sendmail(email($key), $mailsubject, $content);
            }

            //提出者向け
            $reasons = "";
            if ($formsetting["reason"] == "notice") {
                foreach ($answerdata as $key => $data) {
                    if (strpos($key, '_') !== FALSE) continue;
                    if ($data["reason"] != "") $reasons = $reasons . "◇" . $data["reason"] . "\n\n";
                }
            }
            else if ($formsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
            else if ($formsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
            switch ($result){
                case 1:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイル内容に問題が無いと判断されたため、この作品は承認されました。

$eventname にご参加頂き、ありがとうございます。


【提出内容の修正・削除をしたい場合や、作品を追加提出したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、提出内容の修正・削除や、追加提出を行えます。
提出内容を修正・削除する場合は、マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
追加提出をする場合は、「作品を提出する」をクリックし、改めて作品の提出を行って下さい。
";
                break;
                case 2:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイルの軽微な修正が必要と判断されました。
お手数ですが、以下をご確認頂き、ファイルの再提出をして頂けますと幸いです。


【修正が必要と判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには（ファイル提出の締め切り前まで）】
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。


【既にファイル提出の締め切りを迎えている場合】
大変お手数ですが、主催者にご相談願います。
主催者が認めた場合は、締め切り後であっても入力内容の編集を行えます。
";
                break;
                case 3:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、提出されたファイルは、内容などの観点上、本イベントに相応しくないと判断されました。
そのため、大変恐れ入りますが、この作品の承認を見送らせて頂きます。


【相応しくないと判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには】
本イベントに相応しくないとされる内容を修正の上、ファイルを再提出する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
                break;
            }
            if (!$frame) sendmail(email($author), $authorsubject, $content);
        }
    }
    if (isset($result)) return $result;
    else return FALSE;
}

//ファイル確認集計処理ショートカット（既存作品編集・共通情報）
//意見の書き込み後、もしくは確認者リストの更新後に呼び出す
//基本仕様は新規提出用ショートカットと同じ
function exam_totalization_edit($subject, $forceclose) {
    global $siteurl;
    global $eventname;
    if ($subject === "_all") $subjectarray = glob(DATAROOT . 'exam_edit/*.txt');
    else $subjectarray = array("exam_edit/$subject.txt");
    $formsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);

    $mode1file = DATAROOT . 'exammember_submit.txt';
    $mode1mem = file($mode1file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $mode1mem);
    if ($key !== FALSE) {
        $mode1mem[$key] = id_promoter();
    }
    $mode2file = DATAROOT . 'exammember_edit.txt';
    $mode2mem = file($mode2file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $mode2mem);
    if ($key !== FALSE) {
        $mode2mem[$key] = id_promoter();
    }
    foreach($subjectarray as $filename) {
        $subject = basename($filename, '.txt');
        $answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $subject . '.txt'), true);
        list($author, $id, $editid) = explode("/", $answerdata["_realid"]);
        if ($id !== "common" and !file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) continue;

        if ($id === "common") {
            $submitmem = $mode2mem;
            $membermode = "edit";
        }
        else {
            $membermode = $answerdata["_membermode"];
            if ($answerdata["_membermode"] === "submit") $submitmem = $mode1mem;
            else $submitmem = $mode2mem;
        }

        if ($answerdata["_state"] != 0 and $answerdata["_state"] != 4) continue;
        if ($answerdata["_state"] == 4) $dontnotice = TRUE;
        else $dontnotice = FALSE;

        //メンバーにいない人をファイルから外す
        foreach ($answerdata as $key => $data) {
            if (strpos($key, '_') !== FALSE) continue;
            if (array_search($key, $submitmem) === FALSE) unset($answerdata[$key]);
        }

        //全員の回答終わった？
        $complete = TRUE;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) {
                $complete = FALSE;
                continue;
            }
            $data = $answerdata[$key];
            if ($data["opinion"] == 0) $complete = FALSE;
        }
        if ($forceclose) $complete = TRUE;

        //回答終わってなければここでおしまい
        if ($complete == FALSE) {
            if (json_pack(DATAROOT . 'exam_edit/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');
            continue;
        }

        //以下、全員の回答が終わった時の処理

        //意見が一致したのか？（resultが0のままだったら対立してる）
        $result = 0;

        //計測用変数
        $op1 = 0;
        $op2 = 0;
        $count = 0;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) continue;
            $data = $answerdata[$key];
            switch ($data["opinion"]){
                case 1:
                    $op1++;
                break;
                case 2:
                    $op2++;
                break;
                default:
                    continue;
            }
            $count++;
        }
        if ($op1 == $count or $count == 0) $result = 1;
        else if ($op2 == $count) $result = 2;

        //計測結果を保存
        $frame = FALSE;
        if ($result == 0) $answerdata["_state"] = 1;
        else {
            //理由取りまとめの必要不要
            if (id_leader($membermode) != NULL and $count >= 2 and $result != 1 and $formsetting["reason"] == "notice") {
                $frame = TRUE;
                $answerdata["_state"] = 4;
            } else $answerdata["_state"] = 3;
            $answerdata["_result"] = ["opinion" => $result, "reason" => ""];
        }

        if (json_pack(DATAROOT . 'exam_edit/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');

        if ($id !== "common") $formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
        else $formdata = json_decode(file_get_contents_repeat(DATAROOT . "users/" . $author . ".txt"), true);

        //議論入りしないなら入力内容を読み込んで書き換え
        if ($result != 0 and $id !== "common" and !$frame) {
            $formdata["editing"] = 0;
            if ($result == 1) {
                $formdata["exam"] = 1;
                $formdata["editdate"] = $editid;
                $changeddata = json_decode(file_get_contents_repeat(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
                foreach($changeddata as $key => $data) {
                    if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
                        $fileto = DATAROOT . 'files/' . $author . '/' . $id . '/';
                        if (!file_exists($fileto)) {
                            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
                        }
                        $tmp = explode("_", $key);
                        $partid = $tmp[0];
                        if ($partid === "submit") $saveid = "main";
                        else $saveid = $partid;
                        if ($tmp[1] === "add") {
                            foreach ($data as $fileplace => $name) {
                                rename(DATAROOT . 'edit_files/' . $author . '/' . $id . '/' . $saveid . "_$fileplace", DATAROOT . 'files/' . $author . '/' . $id . '/' . $saveid . "_$fileplace");
                            }
                            if (!is_array($formdata[$partid])) $formdata[$partid] = array();
                            $formdata[$partid] = array_merge($formdata[$partid], $data);
                        }
                        if ($tmp[1] === "delete") {
                            foreach ($data as $name) {
                                unlink(DATAROOT . 'files/' . $author . '/' . $id . '/' . $saveid . "_$name");
                                unset($formdata[$partid][$name]);
                            }
                        }
                        continue;
                    }
                    $formdata[$key] = $data;
                }
            }
            if (json_pack(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $formdata) === FALSE) die('作品データの書き込みに失敗しました。');
        }

        if ($result != 0 and $id === "common" and !$frame) {
            $formdata["common_editing"] = 0;
            if ($answerdata["_commonmode"] === "new") $formdata["common_acceptance"] = $result;
            else if ($result == 1) {
                $formdata["common_acceptance"] = 1;
                $changeddata = json_decode(file_get_contents_repeat(DATAROOT . "edit/" . $author . "/common.txt"), true);
                foreach($changeddata as $key => $data) {
                    if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
                        $fileto = DATAROOT . 'files/' . $author . '/common/';
                        if (!file_exists($fileto)) {
                            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
                        }
                        $tmp = explode("_", $key);
                        $partid = $tmp[0];
                        if ($tmp[1] === "add") {
                            foreach ($data as $fileplace => $name) {
                                rename(DATAROOT . 'edit_files/' . $author . '/common/' . $partid . "_$fileplace", DATAROOT . 'files/' . $author . '/common/' . $partid . "_$fileplace");
                            }
                            if (!is_array($formdata[$partid])) $formdata[$partid] = array();
                            $formdata[$partid] = array_merge($formdata[$partid], $data);
                        }
                        if ($tmp[1] === "delete") {
                            foreach ($data as $name) {
                                unlink(DATAROOT . 'files/' . $author . '/common/' . $partid . "_$name");
                                unset($formdata[$partid][$name]);
                            }
                        }
                        continue;
                    }
                    $formdata[$key] = $data;
                }
            }
            if (json_pack(DATAROOT . "users/" . $author . ".txt", $formdata) === FALSE) die('提出データの書き込みに失敗しました。');
        }

        $authornick = nickname($author);

        if ($result == 0) {
            if ($id !== "common") $pageurl = $siteurl . 'mypage/exam/discuss_edit.php?examname=' . $subject;
            else $pageurl = $siteurl . 'mypage/exam/discuss_common.php?examname=' . $subject;
            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                $nickname = nickname($key);
                if ($id !== "common") {
                    $mailsubject = 'ファイル確認の結果（議論の必要あり・内容変更・' . $formdata["title"] . '）';
                    if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この変更の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                    else $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
メンバー間で意見が分かれたため、この変更の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                } else {
                    $mailsubject = '内容確認の結果（議論の必要あり・共通情報）';
                    if (!$forceclose) $content = "$nickname 様

提出された共通情報について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この内容の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                    else $content = "$nickname 様

提出された共通情報について、確認を締め切りました。
メンバー間で意見が分かれたため、この内容の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                }
                sendmail(email($key), $mailsubject, $content);
            }
        } else {
            if ($id !== "common") switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この変更を承認しました。
作品の提出者に承認の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（承認・内容変更・' . $formdata["title"] . '）';
                    $authorsubject = '内容変更を承認しました（' . $formdata["title"] . '）';
                break;
                case 2:
                    if ($frame) $contentpart = '問題があるという意見で一致したため、この変更を拒否しました。
作品の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、拒否となった理由を添えて拒否の通知をします。';
                    else $contentpart = '問題があるという意見で一致したため、この変更を拒否しました。
作品の提出者に拒否の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（拒否・内容変更・' . $formdata["title"] . '）';
                    $authorsubject = '内容変更の承認が見送られました（' . $formdata["title"] . '）';
                break;
            }
            else switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この内容を承認しました。
情報の提出者に承認の通知をしました。';
                    $mailsubject = '内容確認の結果（承認・共通情報）';
                    $authorsubject = '内容を承認しました（共通情報）';
                break;
                case 2:
                    if ($frame) $contentpart = '問題があるという意見で一致したため、この内容を拒否しました。
情報の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、拒否となった理由を添えて拒否の通知をします。';
                    else $contentpart = '問題があるという意見で一致したため、この内容を拒否しました。
情報の提出者に拒否の通知をしました。';
                    $mailsubject = '内容確認の結果（拒否・共通情報）';
                    $authorsubject = '内容の承認が見送られました（共通情報）';
                break;
            }

            //内部関数で送信
            if (!$dontnotice) foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                if ($author == $key) continue;
                $nickname = nickname($key);
                if ($id !== "common") {
                    if ($frame and (string)id_leader($membermode) === (string)$key) $ps = "\n\n※ファイル確認のリーダーの方は、ファイル提出者への通知のため、以下のURLから、メンバーの意見を取りまとめる画面に進んで下さい。\n\n　理由入力ページ：" . $siteurl . 'mypage/exam/frame_edit.php?examname=' . $subject;
                    else $ps = "";
                    if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                    else $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                } else {
                    if ($frame and (string)id_leader($membermode) === (string)$key) $ps = "\n\n※ファイル確認のリーダーの方は、情報の提出者への通知のため、以下のURLから、メンバーの意見を取りまとめる画面に進んで下さい。\n\n　理由入力ページ：" . $siteurl . 'mypage/exam/frame_common.php?examname=' . $subject;
                    else $ps = "";
                    if (!$forceclose) $content = "$nickname 様

提出された共通情報について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                    else $content = "$nickname 様

提出された共通情報について、確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                }
                sendmail(email($key), $mailsubject, $content);
            }

            //提出者向け
            $reasons = "";
            if ($formsetting["reason"] == "notice") {
                foreach ($answerdata as $key => $data) {
                    if (strpos($key, '_') !== FALSE) continue;
                    if ($data["reason"] != "") $reasons = $reasons . "◇" . $data["reason"] . "\n\n";
                }
            }
            else if ($formsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
            else if ($formsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
            if ($id !== "common") switch ($result){
                case 1:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」の内容変更について、イベントの運営メンバーが確認しました。
確認の結果、変更内容に問題が無いと判断されたため、この変更は承認されました。

$eventname にご参加頂き、ありがとうございます。


【提出内容の修正・削除をしたい場合や、作品を追加提出したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、提出内容の修正・削除や、追加提出を行えます。
提出内容を修正・削除する場合は、マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
追加提出をする場合は、「作品を提出する」をクリックし、改めて作品の提出を行って下さい。
";
            break;
            case 2:
                $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」の内容変更について、イベントの運営メンバーが確認しました。
確認の結果、変更後の内容に問題があると判断されました。
そのため、大変恐れ入りますが、この変更の承認を見送らせて頂きます。
現在は、変更前の内容を維持したままの状態となっています。


【問題があると判断された理由（ファイル確認者によるコメント）】
$reasons

【再編集をするには】
問題があるとされる内容を修正の上、ファイルを再編集する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
                break;
            }
            else switch ($result){
                case 1:
                    $content = "$authornick 様

あなたの共通情報について、イベントの運営メンバーが確認しました。
確認の結果、内容に問題が無いと判断されたため、この内容は承認されました。

$eventname にご参加頂き、ありがとうございます。


【共通情報を修正したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、共通情報の修正を行えます。
共通情報を修正する場合は、マイページにログイン後、「共通情報の入力・編集」をクリックして下さい。
";
                break;
                case 2:
                    if ($answerdata["_commonmode"] === "new") $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。";
                    else $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。\n現在は、変更前の内容を維持したままの状態となっています。";
                    $content = "$authornick 様

あなたの共通情報について、イベントの運営メンバーが確認しました。
確認の結果、その内容に問題があると判断されました。
$changeinfo


【問題があると判断された理由（ファイル確認者によるコメント）】
$reasons

【再編集をするには】
問題があるとされる内容を修正の上、共通情報を再編集する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「共通情報の入力・編集」をクリックして下さい。
";
                break;
            }
            if (!$frame) {
                sendmail(email($author), $authorsubject, $content);
                unlink(DATAROOT . "edit/" . $author . "/" . $id . ".txt");
            }
        }
    }
    if (isset($result)) return $result;
    else return FALSE;
}

//普通のmb_strlenだと改行が2文字扱いになるのでそれを避ける
function length_with_lb($string) {
    $string = str_replace(array("\r\n", "\r", "\n"), " ", $string);
    return mb_strlen($string);
}

//文字列をHTML出力しても大丈夫なようにし、更に改行タグを付与
function give_br_tag($string) {
    $string = hsc($string);
    $string = str_replace(array("\r\n", "\r", "\n"), "\n", $string);
    return str_replace("\n", "<br>", $string);
}

//リダイレクトして終了
function redirect($to) {
    header("Location: $to");
    exit;
}

//htmlspecialcharsのショートカット
function hsc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

//FAQで、結果をヒット数順に並べ替える用の関数
function faq_callback_fnc($a, $b) {
    if ((int)$a["hits"] > (int)$b["hits"]) return -1;
    else if ((int)$a["hits"] == (int)$b["hits"]) return 0;
    else return 1;
}

//CSRF（クロスサイトリクエストフォージェリ）対策用
//セッションIDからトークンを作成し、処理スクリプト（handle.php等）で照合
//参考　https://qiita.com/mpyw/items/8f8989f8575159ce95fc
function csrf_prevention_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) return false;
    return hash("sha256", session_id());
}

//CSRF対策用のhiddenパーツ
function csrf_prevention_in_form() {
    echo '<input type="hidden" name="csrf_prevention_token" value="' . csrf_prevention_token() . '">';
}

//検証（上のcsrf_prevention_in_formとセットで）
function csrf_prevention_validate($dont_die = FALSE) {
    if (!isset($_POST["csrf_prevention_token"]) or $_POST["csrf_prevention_token"] !== csrf_prevention_token()) {
        if (!$dont_die) die("フォームが入力されていないか、CSRF（クロスサイトリクエストフォージェリ）の可能性があると判定されたため、操作を停止しました。");
        return FALSE;
    } else return TRUE;
}

//作品数カウント（引数無しで自分）
function count_works($userid = "") {
    if ($userid == "") $userid = $_SESSION["userid"];
    return count(glob(DATAROOT . "submit/$userid/*.txt"));
}

//セッションが有効期限切れたりしてないかとか
//$booleanがTRUEだったらbooleanで返す
function session_validation($goback = FALSE, $boolean = FALSE) {
    global $siteurl;

    //ログインしてない場合はログインページへ
    $currenturl = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $redirtopass = str_replace($siteurl, '', $currenturl);

    if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
        if ($boolean) return FALSE;
        if ($goback) $_SESSION['guest_redirto'] = $redirtopass;
        redirect($siteurl . "index.php");
    }

    //ブロックされてたら強制ログアウト
    if (blackuser($_SESSION['userid'])) {
        if ($boolean) return FALSE;
        redirect($siteurl . "mypage/logout.php");
    }

    //セッション切れ起こしてない？
    if ($_SESSION['expire'] <= time()) {
        //ログアウト処理
        //情報をリセット
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        if ($boolean) return FALSE;
        die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="10; URL=\'' . $siteurl . 'index.php\'" />
<title>セッション・エラー（タイムアウト）</title>
</head>
<body>
<p>しばらくの間アクセスが無かったため、セキュリティの観点から接続を中断しました。<br>
再度ログインして下さい。</p>
<p>10秒後にログインページに自動的に移動します。<br>
<a href="' . $siteurl . 'index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>
</body>
</html>');
    } else $_SESSION['expire'] = time() + (30 * 60);

    //ブラウザがなぜか変わってたりしない？（セッションハイジャック？）
    if ($_SESSION['useragent'] != $_SERVER['HTTP_USER_AGENT']) {
        if ($boolean) return FALSE;
        die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ユーザーエージェント認証失敗</title>
</head>
<body>
<p>ログイン時のユーザーエージェントと異なるため、接続出来ません。<br>
不正ログインしようとした可能性があります（セッション・ハイジャック　など）。</p>
</body>
</html>');
    }

    //ログイン情報更新
    $refresh_userdata = id_array($_SESSION["userid"]);
    $_SESSION['nickname'] = $refresh_userdata["nickname"];
    $_SESSION['email'] = $refresh_userdata["email"];
    $_SESSION['state'] = $refresh_userdata["state"];
    if ($boolean) return TRUE;
}

//自分のアクセス権チェック（TRUEでアクセス権無し）
//メッセージをエコーする場合はそこでdie
function no_access_right($allowed, $echo_message = FALSE) {
    global $siteurl;
    if (array_search($_SESSION["state"], $allowed) === FALSE) {
        if ($echo_message) {
            $state_text = implode("、", $allowed);
            $state_text = str_replace(array("p", "c", "g", "o"), array("<b>主催者</b>", "<b>共同運営者</b>", "<b>一般参加者</b>", "<b>非参加者</b>"), $state_text);
            http_response_code(403);
            die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、' . $state_text . 'のみです。</p>
<p><a href="' . $siteurl . 'mypage/index.php">マイページトップに戻る</a></p>');
        }
        return TRUE;
    } else return FALSE;
}

//確認modalのエコー
function echo_modal_confirm($body = null, $title = null, $dismiss = null, $dismiss_class = null, $send = null, $send_class = null, $meta_modal_id = null, $meta_send_id = null, $meta_send_onclick = null) {
    if (is_null($body)) $body = "入力内容に問題は見つかりませんでした。<br><br>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。";
    if (is_null($title)) $title = "送信確認";
    if (is_null($dismiss)) $dismiss = "戻る";
    if (is_null($dismiss_class)) $dismiss_class = "secondary";
    if (is_null($send)) $send = "送信する";
    if (is_null($send_class)) $send_class = "primary";
    if (is_null($meta_modal_id)) $meta_modal_id = "confirmmodal";
    if (is_null($meta_send_id)) $meta_send_id = "submitbtn";
    if (is_null($meta_send_onclick)) $meta_send_onclick = 'document.getElementById("submitbtn").disabled = "disabled"; document.form.submit();';
    echo <<<EOT
<div class="modal fade" id="$meta_modal_id" tabindex="-1" role="dialog" aria-labelledby="{$meta_modal_id}title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="{$meta_modal_id}title">$title</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
$body
</div>
<div class="modal-footer">
<button type="button" class="btn btn-$dismiss_class" data-dismiss="modal">$dismiss</button>
<button type="button" class="btn btn-$send_class" id="$meta_send_id" onClick='$meta_send_onclick'>$send</button>
</div>
</div>
</div>
</div>
EOT;
}

//アラートmodalのエコー（大体フォームの内容エラーだと思われ）
function echo_modal_alert($body = null, $title = null, $dismiss = null, $dismiss_class = null, $meta_modal_id = null, $meta_dismiss_id = null) {
    if (is_null($body)) $body = "入力内容に問題が見つかりました。<br>お手数ですが、表示されているエラー内容を参考に、入力内容の確認・修正をお願いします。<br><br>修正後、再度「送信する」を押して下さい。";
    if (is_null($title)) $title = "入力内容の修正が必要です";
    if (is_null($dismiss)) $dismiss = "OK";
    if (is_null($dismiss_class)) $dismiss_class = "primary";
    if (is_null($meta_modal_id)) $meta_modal_id = "errormodal";
    if (is_null($meta_dismiss_id)) $meta_dismiss_id = "dismissbtn";
    echo <<<EOT
<div class="modal fade" id="$meta_modal_id" tabindex="-1" role="dialog" aria-labelledby="{$meta_modal_id}title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="{$meta_modal_id}title">$title</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
$body
</div>
<div class="modal-footer">
<button type="button" class="btn btn-$dismiss_class" data-dismiss="modal" id="$meta_dismiss_id">$dismiss</button>
</div>
</div>
</div>
</div>
EOT;
}

//待機modal（処理に時間が掛かる時）
//他と違い、ユーザーの操作では消失不可
function echo_modal_wait($body = null, $title = null, $meta_modal_id = null) {
    if (is_null($body)) $body = "入力内容・ファイルを送信中です。<br>画面が自動的に推移するまでしばらくお待ち下さい。";
    if (is_null($title)) $title = "送信中…";
    if (is_null($meta_modal_id)) $meta_modal_id = "sendingmodal";
    echo <<<EOT
<div class="modal fade" id="$meta_modal_id" tabindex="-1" role="dialog" aria-labelledby="{$meta_modal_id}title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="{$meta_modal_id}title">$title</h5>
</div>
<div class="modal-body">
$body
</div>
</div>
</div>
</div>
EOT;
}

//上部表示用のアラートを登録（handle系のページで使う用）
function register_alert($body, $class = "primary") {
    if (!isset($_SESSION["alerts_holder"])) $_SESSION["alerts_holder"] = array();
    $_SESSION["alerts_holder"][] = array("body" => $body, "class" => $class);
}

//register_alertで登録したアラートを表示
function output_alert() {
    if (!is_array($_SESSION["alerts_holder"])) return;
    foreach ($_SESSION["alerts_holder"] as $contents) {
        echo <<<EOT
<div class="alert alert-{$contents["class"]} alert-dismissible fade show system-alert-spacer" role="alert">
{$contents["body"]}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
EOT;
    }
    unset($_SESSION["alerts_holder"]);
}

//普通にアラートを表示
function echo_alert($body, $class = "primary", $not_dismissable = FALSE) {
    if (!$not_dismissable) echo <<<EOT
<div class="alert alert-$class alert-dismissible fade show system-alert-spacer" role="alert">
$body
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
EOT;
    else echo <<<EOT
<div class="alert alert-$class system-alert-spacer" role="alert">
$body
</div>
EOT;
}

//セッションのセットアップ・スタート（Cookie名、セキュアなど）
function setup_session() {
    session_name("filesystemsessid");
    session_set_cookie_params(0, "/", null, (!empty($_SERVER['HTTPS'])), TRUE);
    session_start();
}

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
  $item = str_replace(array("\r\n", "\r", "\n"), " ", $item);
  if ($max != 0) {
    if (mb_strlen($item) > $max) return 1;
  }
  if ($min != 0) {
    if (mb_strlen($item) < $min && mb_strlen($item) > 0) return 1;
  }
  return 0;
}

//--------
//入力内容検証ショートカット
function check_textbox($array) {
    $item = $_POST["custom-" . $array["id"]];
    $result = check_required($array["required"], $item);
    if ($result != 0) return TRUE;
    else {
        if ($array["max"] != "") $vmax = (int) $array["max"];
        else $vmax = 9999;
        if ($array["min"] != "") $vmin = (int) $array["min"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item);
        if ($result != 0) return TRUE;
    }
    return FALSE;
}

function check_textbox2($array) {
    $item = $_POST["custom-" . $array["id"] . "-1"];
    $item2 = $_POST["custom-" . $array["id"] . "-2"];
    $result = check_required2($array["required"], $item, $item2);
    if ($result != 0) return TRUE;
    if ($item != "") {
        if ($array["max"] != "") $vmax = (int) $array["max"];
        else $vmax = 9999;
        if ($array["min"] != "") $vmin = (int) $array["min"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item);
        if ($result != 0) return TRUE;
    }
    if ($item2 != "") {
        if ($array["max2"] != "") $vmax = (int) $array["max2"];
        else $vmax = 9999;
        if ($array["min2"] != "") $vmin = (int) $array["min2"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item2);
        if ($result != 0) return TRUE;
    }
    return FALSE;
}

function check_checkbox($array) {
    $f = $_POST["custom-" . $array["id"]];
    if ($f == "") $f = array();
    if((array)$f == array() && $array["required"] == "1") return TRUE;
    $choices = choices_array($array["list"]);
    $compare = count($choices);
    foreach ($f as $value) {
        if ((int)$value >= $compare) return TRUE;
    }
    return FALSE;
}

function check_radio($array) {
    $item = $_POST["custom-" . $array["id"]];
    $result = check_required($array["required"], $item);
    if ($result != 0) return TRUE;
    $choices = choices_array($array["list"]);
    $compare = count($choices);
    if ((int)$item >= $compare) return TRUE;
    return FALSE;
}

function check_attach($array, $uploadedfs, $currentsize) {
    $sizesum = 0;
    if ($array["filenumber"] == "") $filemax = 100;
    else $filemax = (int) $array["filenumber"];
    $ext = $array["ext"];
    $ext = str_replace(",", "|", $ext);
    $ext = strtoupper($ext);
    $reg = '/\.(' . $ext . ')$/i';
    $upped = 0;
    if ($array["size"] != "") $oksize = (int) $array["size"];
    else $oksize = FILE_MAX_SIZE;
    $oksize = $oksize * 1024 * 1024;

    for ($j=0; $j<count($_FILES["custom-" . $array["id"]]['name']); $j++) {
        if ($_FILES["custom-" . $array["id"]]['error'][$j] == 4) break;
        else if ($_FILES["custom-" . $array["id"]]['error'][$j] == 1) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ファイル　アップロードエラー</title>
</head>
<body>
<p>ファイルのアップロードに失敗しました。アップロードしようとしたファイルのサイズが、サーバーで扱えるファイルサイズを超えていました。<br>
お手数ですが、サーバーの管理者にお問い合わせ下さい。</p>
<p>問い合わせの際、サーバーの管理者に以下の事項をお伝え下さい。<br>
<b>ユーザーがアップロードしようとしたファイルのサイズが、php.ini の upload_max_filesize ディレクティブの値を超えていたため、アップロードが遮断されました。<br>
php.ini の設定を見直して下さい。</b></p>
</body>
</html>');
        else if ($_FILES["custom-" . $array["id"]]['error'][$j] == 3) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ファイル　アップロードエラー</title>
</head>
<body>
<p>ファイルのアップロードに失敗しました。通信環境が悪かったために、アップロードが中止された可能性があります。<br>
通信環境を見直したのち、再度送信願います。</p>
</body>
</html>');
        else if ($_FILES["custom-" . $array["id"]]['error'][$j] == 0) {
            if (!preg_match($reg, $_FILES["custom-" . $array["id"]]['name'][$j])) return TRUE;
            $sizesum += $_FILES["custom-" . $array["id"]]['size'][$j];
            $upped++;
        }
    }
    $sizesum += $currentsize;
    $deletenum = 0;
    foreach((array)$_POST["custom-" . $array["id"] . "-delete"] as $key){
        if ($key === "none") break;
        if (!isset($uploadedfs[basename($key)])) return TRUE;
        $sizesum -= $uploadedfs[basename($key)];
        $deletenum++;
    }
    $filenumber = $upped + count($uploadedfs) - $deletenum;
    if($filenumber <= 0 && $array["required"] == "1") return TRUE;
    if($filenumber > $filemax) return TRUE;
    if ($sizesum > $oksize) return TRUE;
    return FALSE;
}

function check_submitfile($array, $uploadedfs, $currentsize) {
    $sizesum = 0;
    if ($array["filenumber"] == "") $filemax = 100;
    else $filemax = (int) $array["filenumber"];
    $ext = $array["ext"];
    $ext = str_replace(",", "|", $ext);
    $ext = strtoupper($ext);
    $reg = '/\.(' . $ext . ')$/i';
    $upped = 0;
    if ($array["size"] != "") $oksize = (int) $array["size"];
    else $oksize = FILE_MAX_SIZE;
    $oksize = $oksize * 1024 * 1024;

    for ($j=0; $j<count($_FILES["submitfile"]['name']); $j++) {
        if ($_FILES["submitfile"]['error'][$j] == 4) break;
        else if ($_FILES["submitfile"]['error'][$j] == 1) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ファイル　アップロードエラー</title>
</head>
<body>
<p>ファイルのアップロードに失敗しました。アップロードしようとしたファイルのサイズが、サーバーで扱えるファイルサイズを超えていました。<br>
お手数ですが、サーバーの管理者にお問い合わせ下さい。</p>
<p>問い合わせの際、サーバーの管理者に以下の事項をお伝え下さい。<br>
<b>ユーザーがアップロードしようとしたファイルのサイズが、php.ini の upload_max_filesize ディレクティブの値を超えていたため、アップロードが遮断されました。<br>
php.ini の設定を見直して下さい。</b></p>
</body>
</html>');
        else if ($_FILES["submitfile"]['error'][$j] == 3) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ファイル　アップロードエラー</title>
</head>
<body>
<p>ファイルのアップロードに失敗しました。通信環境が悪かったために、アップロードが中止された可能性があります。<br>
通信環境を見直したのち、再度送信願います。</p>
</body>
</html>');
        else if ($_FILES["submitfile"]['error'][$j] == 0) {
            if (!preg_match($reg, $_FILES["submitfile"]['name'][$j])) return TRUE;
            $sizesum += $_FILES["submitfile"]['size'][$j];
            $upped++;
        }
    }
    $sizesum += $currentsize;
    $deletenum = 0;
    foreach((array)$_POST["submitfile-delete"] as $key){
        if ($key === "none") break;
        if (!isset($uploadedfs[basename($key)])) return TRUE;
        $sizesum -= $uploadedfs[basename($key)];
        $deletenum++;
    }
    $filenumber = $upped + count($uploadedfs) - $deletenum;
    if($filenumber <= 0) return TRUE;
    if($filenumber > $filemax) return TRUE;
    if ($sizesum > $oksize) return TRUE;
    return FALSE;
}

//タイトルと接頭・接尾辞、詳細はHTMLタグ使える（呼び出す側でエスケープ）
function echo_textbox($title, $name, $id, $prefill = "", $showcounter = FALSE, $detail = "", $jspart = "", $prefix = "", $suffix = "", $width = "", $disabled = FALSE) {
    echo '<div class="form-group"><label for="' . hsc($id) . '">' . $title . '</label>';
    if ($width != "") echo '<div class="input-group" style="width:' . hsc($width) . 'em;">';
    else echo '<div class="input-group">';
    if ($prefix != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $prefix . '</span></div>';
    echo '<input type="text" name="' . hsc($name) . '" class="form-control" id="' . hsc($id) . '" value="' . hsc($prefill) . '"';
    if ($showcounter) echo ' onkeyup="ShowLength(value, &quot;' . hsc($id) . '-counter&quot;);"';
    if ($disabled) echo ' disabled="disabled"';
    if ($jspart != "") echo ' ' . $jspart;
    echo ">";
    if ($suffix != "") echo '<div class="input-group-append"><span class="input-group-text">' . $suffix . '</span></div>';
    echo '</div>';
    if ($showcounter) echo '<div id="' . hsc($id) . '-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
    echo '<div id="' . hsc($id) . '-errortext" class="system-form-error"></div>';
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

function echo_textbox2($title, $name, $id, $prefill = "", $prefill2 = "", $showcounter = FALSE, $horizontally = FALSE, $detail = "", $jspart = "", $prefix = "", $suffix = "", $width = "", $prefix2 = "", $suffix2 = "", $width2 = "", $disabled = FALSE) {
    echo '<div class="form-group">' . $title;
    if ($horizontally) echo '<div class="form-row"><div class="col">';
    if ($width != "") echo '<div class="input-group" style="width:' . hsc($width) . 'em;">';
    else echo '<div class="input-group">';
    if ($prefix != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $prefix . '</span></div>';
    echo '<input type="text" name="' . hsc($name) . '-1" class="form-control" id="' . hsc($id) . '-1" value="' . hsc($prefill) . '"';
    if ($showcounter) echo ' onkeyup="ShowLength(value, &quot;' . hsc($id) . '-1-counter&quot;);"';
    if ($disabled) echo ' disabled="disabled"';
    if ($jspart != "") echo ' ' . $jspart;
    echo ">";
    if ($suffix != "") echo '<div class="input-group-append"><span class="input-group-text">' . $suffix . '</span></div>';
    echo '</div>';
    if ($showcounter) echo '<div id="' . hsc($id) . '-1-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
    if ($horizontally) echo '</div><div class="col">';
    if ($width2 != "") echo '<div class="input-group" style="width:' . hsc($width2) . 'em;">';
    else echo '<div class="input-group">';
    if ($prefix2 != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $prefix2 . '</span></div>';
    echo '<input type="text" name="' . hsc($name) . '-2" class="form-control" id="' . hsc($id) . '-2" value="' . hsc($prefill2) . '"';
    if ($showcounter) echo ' onkeyup="ShowLength(value, &quot;' . hsc($id) . '-2-counter&quot;);"';
    if ($disabled) echo ' disabled="disabled"';
    if ($jspart != "") echo ' ' . $jspart;
    echo ">";
    if ($suffix2 != "") echo '<div class="input-group-append"><span class="input-group-text">' . $suffix2 . '</span></div>';
    echo '</div>';
    if ($showcounter) echo '<div id="' . hsc($id) . '-2-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
    if ($horizontally) echo '</div></div>';
    echo '<div id="' . hsc($id) . '-errortext" class="system-form-error"></div>';
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

function echo_textarea($title, $name, $id, $prefill, $showcounter = FALSE, $detail = "", $jspart = "", $width = "", $height = "", $disabled = FALSE) {
    echo '<div class="form-group"><label for="' . hsc($id) . '">' . $title . '</label>';
    if ($width != "") echo '<div class="input-group" style="width:' . hsc($width) . 'em;">';
    else echo '<div class="input-group">';
    if ($height == "") $height = "4";
    echo '<textarea id="' . hsc($id) . '" name="' . hsc($name) . '" rows="' . hsc($height) . '" class="form-control"';
    if ($showcounter) echo ' onkeyup="ShowLength(value, &quot;' . hsc($id) . '-counter&quot;);"';
    if ($disabled) echo ' disabled="disabled"';
    if ($jspart != "") echo ' ' . $jspart;
    echo ">";
    echo hsc($prefill) . '</textarea>';
    echo '</div>';
    if ($showcounter) echo '<div id="' . hsc($id) . '-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
    echo '<div id="' . hsc($id) . '-errortext" class="system-form-error"></div>';
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

//$choicesは配列、HTMLタグ可　valueは指定無しの場合選択肢番号0から　prefillはvaluesと照らし合わせ
function echo_radio($title, $name, $id, $choices, $values = [], $prefill = "", $horizontally = FALSE, $detail = "", $jspart = "", $disabled = FALSE) {
    echo '<div class="form-group">' . $title;
    if ($horizontally) echo '<div>';
    foreach ($choices as $num => $choice) {
        if ($horizontally) echo '<div class="form-check form-check-inline">';
        else echo '<div class="form-check">';
        if ($values !== []) $value = $values[$num];
        else $value = $num;
        echo '<input id="' . hsc($id) . '-' . hsc($num) . '" class="form-check-input" type="radio" name="' . hsc($name) . '" value="' . hsc($value) . '"';
        if ($prefill !== "" and $value == $prefill) echo ' checked="checked"';
        if ($disabled) echo ' disabled="disabled"';
        if ($jspart != "") echo ' ' . $jspart;
        echo ">";
        echo '<label class="form-check-label" for="' . hsc($id) . '-' . hsc($num) . '">' . $choice . '</label>';
        echo '</div>';
    }
    if ($horizontally) echo '</div>';
    echo '<div id="' . hsc($id) . '-errortext" class="system-form-error"></div>';
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

function echo_check($title, $name, $id, $choices, $values = [], $prefill = [], $horizontally = FALSE, $detail = "", $jspart = "", $disabled = FALSE) {
    echo '<div class="form-group">' . $title;
    if ($horizontally) echo '<div>';
    foreach ($choices as $num => $choice) {
        if ($horizontally) echo '<div class="form-check form-check-inline">';
        else echo '<div class="form-check">';
        if ($values !== []) $value = $values[$num];
        else $value = $num;
        echo '<input id="' . hsc($id) . '-' . hsc($num) . '" class="form-check-input" type="checkbox" name="' . hsc($name) . '[]" value="' . hsc($value) . '"';
        if (array_search($value, $prefill) !== FALSE) echo ' checked="checked"';
        if ($disabled) echo ' disabled="disabled"';
        if ($jspart != "") echo ' ' . $jspart;
        echo ">";
        echo '<label class="form-check-label" for="' . hsc($id) . '-' . hsc($num) . '">' . $choice . '</label>';
        echo '</div>';
    }
    if ($horizontally) echo '</div>';
    echo '<div id="' . hsc($id) . '-errortext" class="system-form-error"></div>';
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

//この$choiceはHTML不可
function echo_dropdown($title, $name, $id, $choices, $values = [], $prefill = "", $detail = "", $jspart = "", $prefix = "", $suffix = "", $disabled = FALSE) {
    echo '<div class="form-group"><label for="' . hsc($id) . '">' . $title . '</label>';
    echo '<div class="input-group">';
    if ($prefix != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $prefix . '</span></div>';
    echo '<select id="' . hsc($id) . '" class="form-control" name="' . hsc($name) . '"';
    if ($disabled) echo ' disabled="disabled"';
    if ($jspart != "") echo ' ' . $jspart;
    echo ">";
    echo '<option value="">【選択して下さい】</option>';
    foreach ($choices as $num => $choice) {
        if ($values !== []) $value = $values[$num];
        else $value = $num;
        echo '<option value="' . hsc($value) . '"';
        if ($prefill !== "" and $prefill == $value) echo ' selected';
        echo '>' . hsc($choice) . '</option>';
    }
    echo '</select>';
    if ($suffix != "") echo '<div class="input-group-append"><span class="input-group-text">' . $suffix . '</span></div>';
    echo '</div>';
    echo '<div id="' . hsc($id) . '-errortext" class="system-form-error"></div>';
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

function file_get_contents_repeat($filename) {
    for ($i=0; $i<10; $i++) {
        $result = file_get_contents($filename);
        if ($result !== FALSE) return $result;
        sleep(1);
    }
    return FALSE;
}

function file_put_contents_repeat($filename, $data, $flags = 0) {
    for ($i=0; $i<10; $i++) {
        $result = file_put_contents($filename, $data, $flags);
        if ($result !== FALSE) return $result;
        sleep(1);
    }
    return FALSE;
}

function choices_array($choices_string, $escape = FALSE) {
    $choices = str_replace(array("\r\n", "\r", "\n"), "\n", $choices_string);
    $choices = explode("\n", $choices);
    $choices = array_map('trim', $choices);
    if ($escape) $choices = array_map('hsc', $choices);
    //参考　https://www.hachi-log.com/php-arrayfilter-arrayvalue/
    $choices = array_filter($choices);
    $choices = array_values($choices);
    return $choices;
}
