<?php
//ユーザーデータ・セッション関連関数

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

//作品数カウント（引数無しで自分）
function count_works($userid = "") {
    if ($userid == "") $userid = $_SESSION["userid"];
    return count(glob(DATAROOT . "submit/$userid/*.txt"));
}

//合計時間
function get_length_sum($userid = "") {
    if ($userid == "") $userid = $_SESSION["userid"];
    $array = id_array($userid);
    if (isset($array["length_sum"])) return $array["length_sum"];
    else return 0;
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
        die_error_html('セッション・エラー（タイムアウト）', '<p>しばらくの間アクセスが無かったため、セキュリティの観点から接続を中断しました。<br>
再度ログインして下さい。</p>
<p>10秒後にログインページに自動的に移動します。<br>
<a href="' . $siteurl . 'index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>', '<meta http-equiv="refresh" content="10; URL=\'' . $siteurl . 'index.php\'" />');
    } else $_SESSION['expire'] = time() + (60 * 60);

    //ブラウザがなぜか変わってたりしない？（セッションハイジャック？）
    if ($_SESSION['useragent'] != $_SERVER['HTTP_USER_AGENT']) {
        if ($boolean) return FALSE;
        die_error_html('ユーザーエージェント認証失敗', '<p>ログイン時のユーザーエージェントと異なるため、接続出来ません。<br>
不正ログインしようとした可能性があります（セッション・ハイジャック　など）。</p>');
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
            $state_text = str_replace("b", "strong", $state_text);
            http_response_code(403);
            die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、' . $state_text . 'のみです。</p>
<p><a href="' . $siteurl . 'mypage/index.php">マイページトップに戻る</a></p>');
        }
        return TRUE;
    } else return FALSE;
}

//セッションのセットアップ・スタート（Cookie名、セキュアなど）
function setup_session() {
    session_name("filesystemsessid");
    session_set_cookie_params(0, "/", null, (!empty($_SERVER['HTTPS'])), TRUE);
    session_start();
}

//ユーザー情報パス
function user_file_path($userid = null) {
    if ($userid == null) $userid = $_SESSION["userid"];
    return DATAROOT . 'users/' . $userid . '.txt';
}
