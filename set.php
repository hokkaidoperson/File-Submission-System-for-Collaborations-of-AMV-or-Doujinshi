<?php
//デバッグ用　リリース時にはコメントアウト---------------------
//ini_set("display_errors", 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//ini_set("log_errors", "On");
//ini_set("error_log", "******error.log.txt");
//----------------------------------------------------------

if (file_exists('dataplace.php')) require_once('dataplace.php'); else define('DATAROOT', dirname(__FILE__).'/data/');
if (!file_exists(DATAROOT . 'init.txt')) die('初期設定が済んでいません。');

define('PAGEROOT', dirname(__FILE__).'/');

//バージョン情報
define('VERSION', 'Gamma-2E-0');

$initdata = json_decode(file_get_contents(DATAROOT . 'init.txt'), true);

define('FILE_MAX_SIZE', $initdata["maxsize"]);

$eventname = $initdata["eventname"];
$siteurl = file_get_contents(DATAROOT . 'siteurl.txt');

//メール配信制御
require_once('mail_scheduler.php');

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
    $array = json_decode(file_get_contents(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array;
}

//ID→ニックネーム（IDが無ければFALSE）
function nickname($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array["nickname"];
}

//ID→メルアド（IDが無ければFALSE）
function email($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
    if (!file_exists(DATAROOT . 'users/' . $id . '.txt')) return FALSE;
    $array = json_decode(file_get_contents(DATAROOT . 'users/' . $id . '.txt'), true);
    return $array["email"];
}

//ID→立場記号（IDが無ければFALSE）
function state($id) {
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
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
    if ($id !== basename($id)) return FALSE;
    $id = basename($id);
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
    return file_put_contents($filename, $arrayjson);
}

//jsonのファイルをほどいた配列を返す
//ファイルが無い場合はFALSE
function json_unpack($filename) {
    if (!file_exists($filename)) return FALSE;
    return json_decode(file_get_contents($filename), true);
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
    $formsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

    foreach($subjectarray as $filename) {
        $subject = basename($filename, '.txt');
        list($author, $id) = explode('_', $subject);
        if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) continue;
        //回答データ
        $answerdata = json_decode(file_get_contents(DATAROOT . 'exam/' . $subject . '.txt'), true);
        $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $key = array_search("_promoter", $submitmem);
        if ($key !== FALSE) {
            $submitmem[$key] = id_promoter();
        }

        if ($answerdata["_state"] != 0) continue;

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
        if ($result == 0) $answerdata["_state"] = 1;
        else {
            $answerdata["_state"] = 3;
            $answerdata["_result"] = $result;
        }

        if (json_pack(DATAROOT . 'exam/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');

        //入力内容を読み込んで書き換え
        $formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
        $formdata["exam"] = $result;
        if (json_pack(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $formdata) === FALSE) die('作品データの書き込みに失敗しました。');

        $authornick = nickname($author);

        if ($result == 0) {
            $pageurl = $siteurl . 'mypage/exam/discuss.php?author=' . $author . '&id=' . $id;
            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                $nickname = nickname($key);
                if (!$forceclose) $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この作品の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                else $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
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
                    $subject = 'ファイル確認の結果（承認・' . $formdata["title"] . '）';
                    $authorsubject = '作品を承認しました（' . $formdata["title"] . '）';
                break;
                case 2:
                    $contentpart = '軽微な修正が必要であるという意見で一致したため、この作品を修正待ち状態にしました。
作品の提出者に、修正依頼の通知をしました。';
                    $subject = 'ファイル確認の結果（修正待ち・' . $formdata["title"] . '）';
                    $authorsubject = '作品を修正して下さい（' . $formdata["title"] . '）';
                break;
                case 3:
                    $contentpart = '内容上問題があるという意見で一致したため、この作品を拒否しました。
作品の提出者に拒否の通知をしました。';
                    $subject = 'ファイル確認の結果（拒否・' . $formdata["title"] . '）';
                    $authorsubject = '作品の承認が見送られました（' . $formdata["title"] . '）';
                break;
            }

            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                if ($author == $key) continue;
                $nickname = nickname($key);
                if (!$forceclose) $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
                else $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
                sendmail(email($key), $subject, $content);
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
            sendmail(email($author), $authorsubject, $content);
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
    $formsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

    foreach($subjectarray as $filename) {
        $subject = basename($filename, '.txt');
        list($author, $id, $editid) = explode('_', $subject);
        if ($id !== "common" and !file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) continue;

        //回答データ
        $answerdata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $subject . '.txt'), true);
        if ($id === "common") $membermode = "edit";
        else $membermode = $answerdata["_membermode"];
        $memberfile = DATAROOT . 'exammember_' . $membermode . '.txt';
        $submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $key = array_search("_promoter", $submitmem);
        if ($key !== FALSE) {
            $submitmem[$key] = id_promoter();
        }

        if ($answerdata["_state"] != 0) continue;

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
        if ($result == 0) $answerdata["_state"] = 1;
        else {
            $answerdata["_state"] = 3;
            $answerdata["_result"] = $result;
        }

        if (json_pack(DATAROOT . 'exam_edit/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');

        if ($id !== "common") $formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
        else $formdata = json_decode(file_get_contents(DATAROOT . "users/" . $author . ".txt"), true);

        //議論入りしないなら入力内容を読み込んで書き換え
        if ($result != 0 and $id !== "common") {
            $formdata["editing"] = 0;
            if ($result == 1) {
                $formdata["exam"] = 1;
                $formdata["editdate"] = $editid;
                $changeddata = json_decode(file_get_contents(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
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

        if ($result != 0 and $id === "common") {
            $formdata["common_editing"] = 0;
            if ($answerdata["_commonmode"] === "new") $formdata["common_acceptance"] = $result;
            else if ($result == 1) {
                $formdata["common_acceptance"] = 1;
                $changeddata = json_decode(file_get_contents(DATAROOT . "edit/" . $author . "/common.txt"), true);
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
            if ($id !== "common") $pageurl = $siteurl . 'mypage/exam/discuss_edit.php?author=' . $author . '&id=' . $id . '&edit=' . $editid;
            else $pageurl = $siteurl . 'mypage/exam/discuss_common.php?author=' . $author . '&edit=' . $editid;
            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                $nickname = nickname($key);
                if ($id !== "common") {
                    $subject = 'ファイル確認の結果（議論の必要あり・内容変更・' . $formdata["title"] . '）';
                    if (!$forceclose) $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この変更の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                    else $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
メンバー間で意見が分かれたため、この変更の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                } else {
                    $subject = '内容確認の結果（議論の必要あり・共通情報）';
                    if (!$forceclose) $content = "$nickname 様

$authornick 様の共通情報について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この内容の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                    else $content = "$nickname 様

$authornick 様の共通情報について、確認を締め切りました。
メンバー間で意見が分かれたため、この内容の承認・拒否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                }
                sendmail(email($key), $subject, $content);
            }
        } else {
            if ($id !== "common") switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この変更を承認しました。
作品の提出者に承認の通知をしました。';
                    $subject = 'ファイル確認の結果（承認・内容変更・' . $formdata["title"] . '）';
                    $authorsubject = '内容変更を承認しました（' . $formdata["title"] . '）';
                break;
                case 2:
                    $contentpart = '問題があるという意見で一致したため、この変更を拒否しました。
作品の提出者に拒否の通知をしました。';
                    $subject = 'ファイル確認の結果（拒否・内容変更・' . $formdata["title"] . '）';
                    $authorsubject = '内容変更の承認が見送られました（' . $formdata["title"] . '）';
                break;
            }
            else switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この内容を承認しました。
情報の提出者に承認の通知をしました。';
                    $subject = '内容確認の結果（承認・共通情報）';
                    $authorsubject = '内容を承認しました（共通情報）';
                break;
                case 2:
                    $contentpart = '問題があるという意見で一致したため、この内容を拒否しました。
情報の提出者に拒否の通知をしました。';
                    $subject = '内容確認の結果（拒否・共通情報）';
                    $authorsubject = '内容の承認が見送られました（共通情報）';
                break;
            }

            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                if ($author == $key) continue;
                $nickname = nickname($key);
                if ($id !== "common") {
                    if (!$forceclose) $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
                    else $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
                } else {
                    if (!$forceclose) $content = "$nickname 様

$authornick 様の共通情報について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
                    else $content = "$nickname 様

$authornick 様の共通情報について、確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。
";
                }
                sendmail(email($key), $subject, $content);
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
            sendmail(email($author), $authorsubject, $content);
            unlink(DATAROOT . "edit/" . $author . "/" . $id . ".txt");
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
    $string = htmlspecialchars($string);
    $string = str_replace(array("\r\n", "\r", "\n"), "\n", $string);
    return str_replace("\n", "<br>", $string);
}

//リダイレクトして終了
function redirect($to) {
    header("Location: $to");
    exit;
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
    return FALSE;
}

function check_radio($array) {
    $item = $_POST["custom-" . $array["id"]];
    $result = check_required($array["required"], $item);
    if ($result != 0) return TRUE;
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
