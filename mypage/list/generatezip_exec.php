<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p", "c", "g"))) redirect("./index.php");

//zipモジュールチェック
if (!extension_loaded('zip')) die_error_html('拡張機能エラー', '<p>大変申し訳ございませんが、現在の状況ではZIPファイルを生成出来ません。<br>
このZIPファイル生成機能では、PHPの拡張機能 <strong>zip</strong> を利用しますが、現在の環境では無効になっています。<br>
システム管理者にお問い合わせ下さい。<br>
もしあなた自身がシステム管理者であれば、PHPの設定を確認し、拡張機能を有効化もしくはインストールして下さい。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、前の画面にお戻り下さい。</a></p>');


csrf_prevention_validate();


//設定チェック
if (isset($_POST["include_non_accepted"]) and $_POST["include_non_accepted"] == "1") $incna = TRUE;
else $incna = FALSE;
if (isset($_POST["include_without_submission"]) and $_POST["include_without_submission"] == "1") $incws = TRUE;
else $incws = FALSE;


//ユーザーフォーム設定ファイル読み込み
$userformdata = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}

//提出フォーム設定ファイル読み込み
$submitformdata = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}


//zip用のディレクトリ
$fileto = DATAROOT . 'zip/';
if (!file_exists($fileto)) {
    if (!mkdir($fileto)) die('ディレクトリの作成に失敗しました。');
}

//zipに書き込むデータ（[ユーザーID][提出ID][データもろもろ]）
$zipdata = array();
//ユーザーデータ（[ユーザーID][データもろもろ]）
$zipuser = array();

//データの整理
foreach (users_array() as $userid => $data) {
    if (blackuser($userid)) continue;
    //ユーザーフォームの閲覧権があるか確認
    $allowed = FALSE;
    switch($_SESSION["state"]) {
        case 'p':
            //主催者は基本的にアクセスおｋ
            $allowed = TRUE;
        break;
        case 'c':
            //主催がアクセス権を与えていたらおｋ
            $aclplace = DATAROOT . 'fileacl/' . $_SESSION["userid"] . '.txt';
            if (file_exists($aclplace)) {
                $acldata = json_decode(file_get_contents_repeat($aclplace), true);
                if (array_search($userid . '_userform', $acldata) !== FALSE) $allowed = TRUE;
            }
            //breakしない、下へ行く
        case 'g':
            //自分のファイルだけ（共同運営者も同じく）
            if ($userid == $_SESSION['userid']) $allowed = TRUE;
        break;
    }
    if ($allowed) {
        $zipuser[$userid] = $data;
    }

    $zipdata[$userid] = array();
    foreach (glob(DATAROOT . "submit/" . $userid . "/*.txt") as $filename) {
        $id = basename($filename, '.txt');
        //ファイルの閲覧権があるか確認
        $allowed = FALSE;
        switch($_SESSION["state"]) {
            case 'p':
                //主催者は基本的にアクセスおｋ
                $allowed = TRUE;
            break;
            case 'c':
                //主催がアクセス権を与えていたらおｋ
                $aclplace = DATAROOT . 'fileacl/' . $_SESSION["userid"] . '.txt';
                if (file_exists($aclplace)) {
                    $acldata = json_decode(file_get_contents_repeat($aclplace), true);
                    if (array_search($userid . '_' . $id, $acldata) !== FALSE) $allowed = TRUE;
                }
                //breakしない、下へ行く
            case 'g':
                //自分のファイルだけ（共同運営者も同じく）
                if ($userid == $_SESSION['userid']) $allowed = TRUE;
            break;
        }
        if (!$allowed) continue;
        $zipdata[$userid][$id] = json_decode(file_get_contents_repeat($filename), true);
        if ($zipdata[$userid][$id]["exam"] != 1 and $incna == FALSE) unset($zipdata[$userid][$id]);
    }
    if ($zipdata[$userid] == array()) {
        unset($zipdata[$userid]);
        if (isset($zipuser[$userid]) and $incws == FALSE) unset($zipuser[$userid]);
    }
}


//CSV生成用の配列　まずはユーザー情報
$usercsv = array();
$usercsv[0] = array("ニックネーム");

foreach($userformdata as $array) {
    $usercsv[0][] = $array["title"];
}

$i = 1;

foreach ($zipuser as $userid => $data) {
    $usercsv[$i] = array();
    $usercsv[$i][] = $data["nickname"];
    foreach($userformdata as $key => $array) {
        $export = "";
        if ($array["type"] == "attach") {
            //ユーザー添付ファイルのファイル名は「項目番号 (内部ID) - 項目名 - ファイル名」
            if (isset($data["common_acceptance"]) and $data[$array["id"]] != array()) {
                $exportarray = array();
                foreach ($data[$array["id"]] as $internal => $external) {
                    $exportarray[] = $data["nickname"] . " ($userid) / $key ($internal) - " . $array["title"] . " - $external";
                }
                $export .= implode("\n", $exportarray);
            }
        } else if (isset($data["common_acceptance"])) {
            $exportarray = array();
            for ($answer = 0; $answer < count($data[$array["id"]]); $answer++) {
                $exportpart = '';
                if (isset($array["prefix"][$answer]) and $array["prefix"][$answer] != "") $exportpart .= '（' . hsc($array["prefix"][$answer]) . '）';
                $exportpart .= $data[$array["id"]][$answer];
                if (isset($array["suffix"][$answer]) and $array["suffix"][$answer] != "") $exportpart .= '（' . hsc($array["suffix"][$answer]) . '）';
                $exportarray[] = $exportpart;
            }
            $export .= implode("\n", $exportarray);
        }
        $usercsv[$i][] = $export;
    }
    $i++;
}


//次に提出情報
$submitcsv = array();
$submitcsv[0] = array("提出ファイル", "提出者", "タイトル");

foreach($submitformdata as $array) {
    $submitcsv[0][] = $array["title"];
}

$i = 1;

foreach ($zipdata as $userid => $works) {
    $nickname = nickname($userid);
    foreach ($works as $id => $data) {
        $submitcsv[$i] = array();
        //メインファイル名は「提出ファイル (内部ID) - ファイル名」
        if (isset($data["submit"]) and $data["submit"] != array()) {
            $exportarray = array();
            foreach ($data["submit"] as $internal => $external) {
                $exportarray[] = $nickname . " ($userid) / " . $data["title"] . " ($id) / 提出ファイル ($internal) - $external";
            }
            $submitcsv[$i][] = implode("\n", $exportarray);
        }
        else {
            $export = $data["url"];
            if (isset($data["dldpw"]) and $data["dldpw"] != "") $export .= "\n※パスワード： " . $data["dldpw"];
            if (isset($data["due"]) and $data["due"] != "") $export .= "\n※URLの有効期限： " . date('Y年n月j日G時i分', $data["due"]);
            $submitcsv[$i][] = $export;
        }
        $submitcsv[$i][] = $nickname;
        $submitcsv[$i][] = $data["title"];
        foreach($submitformdata as $key => $array) {
            $export = "";
            if ($array["type"] == "attach") {
                //提出添付ファイルのファイル名も「項目番号 (内部ID) - 項目名 - ファイル名」
                if ($data[$array["id"]] != array()) {
                    $exportarray = array();
                    foreach ($data[$array["id"]] as $internal => $external) {
                        $exportarray[] = $nickname . " ($userid) / " . $data["title"] . " ($id) / $key ($internal) - " . $array["title"] . " - $external";
                    }
                    $export .= implode("\n", $exportarray);
                }
            } else {
                $exportarray = array();
                for ($answer = 0; $answer < count($data[$array["id"]]); $answer++) {
                    $exportpart = '';
                    if (isset($array["prefix"][$answer]) and $array["prefix"][$answer] != "") $exportpart .= '（' . hsc($array["prefix"][$answer]) . '）';
                    $exportpart .= $data[$array["id"]][$answer];
                    if (isset($array["suffix"][$answer]) and $array["suffix"][$answer] != "") $exportpart .= '（' . hsc($array["suffix"][$answer]) . '）';
                    $exportarray[] = $exportpart;
                }
                $export .= implode("\n", $exportarray);
            }
            $submitcsv[$i][] = $export;
        }
        $i++;
    }
}


//CSVを作る　文字化け対策参考：http://dev.blog.fairway.ne.jp/php%E3%82%A8%E3%82%AF%E3%82%BB%E3%83%AB%E3%81%A7%E6%96%87%E5%AD%97%E5%8C%96%E3%81%91%E3%81%95%E3%81%9B%E3%81%AA%E3%81%84csv%E3%81%AE%E4%BD%9C%E3%82%8A%E6%96%B9/
$fp = fopen(DATAROOT . 'zip/tmp_user_' . $_SESSION["userid"] . '.csv', 'w');
fwrite($fp, "\xEF\xBB\xBF");
foreach ($usercsv as $fields) {
    fputcsv($fp, $fields);
}
fclose($fp);

$fp = fopen(DATAROOT . 'zip/tmp_submit_' . $_SESSION["userid"] . '.csv', 'w');
fwrite($fp, "\xEF\xBB\xBF");
foreach ($submitcsv as $fields) {
    fputcsv($fp, $fields);
}
fclose($fp);


//ZIPにしやすいように配列を統合
$zipmerge = array();
foreach(users_array() as $key => $data) {
    $zipmerge[$key] = array();
    if (isset($zipdata[$key])) $zipmerge[$key] = $zipdata[$key];
    if (isset($zipuser[$key])) $zipmerge[$key]["userform"] = $zipuser[$key];
    if ($zipmerge[$key] == array()) unset($zipmerge[$key]);
}


//ファイル名の置換用
$repbefore = array('\\', '/', ':', '*', '?', '"', '<', '>', '|');
$repafter = array('￥', '／', '：', '＊', '？', '”', '＜', '＞', '｜');


//ZIP作るよ！！！！！！
$zip = new ZipArchive;
$zip->open(DATAROOT . 'zip/' . $_SESSION["userid"] . '.zip', ZipArchive::CREATE|ZipArchive::OVERWRITE);

$zip->addFile(DATAROOT . 'zip/tmp_user_' . $_SESSION["userid"] . '.csv', mb_convert_encoding('参加者データ.csv', 'CP932', 'UTF-8'));
$zip->addFile(DATAROOT . 'zip/tmp_submit_' . $_SESSION["userid"] . '.csv', mb_convert_encoding('提出作品データ.csv', 'CP932', 'UTF-8'));

foreach($zipmerge as $userid => $data) {
    $nickname = nickname($userid);
    $nickname = str_replace($repbefore, $repafter, $nickname);
    $zip->addEmptyDir("$nickname ($userid)");
    if (isset($data["userform"])) foreach($userformdata as $key => $array) {
        if ($array["type"] == "attach") {
            //ユーザー添付ファイルのファイル名は「項目番号 (内部ID) - 項目名 - ファイル名」
            if (isset($data["userform"]["common_acceptance"]) and $data["userform"][$array["id"]] != array()) {
                foreach ($data["userform"][$array["id"]] as $internal => $external) {
                    $title = str_replace($repbefore, $repafter, $array["title"]);
                    $external = str_replace($repbefore, $repafter, $external);
                    $zip->addFile(DATAROOT . "files/" . $userid . "/common/" . $array["id"] . "_$internal", mb_convert_encoding("$nickname ($userid)/$key ($internal) - $title - $external", 'CP932', 'UTF-8'));
                }
            }
        }
    }
    foreach ($data as $id => $work) {
        if ($id === "userform") continue;
        $worktitle = str_replace($repbefore, $repafter, $work["title"]);
        //メインファイル名は「提出ファイル (内部ID) - ファイル名」
        $zip->addEmptyDir("$nickname ($userid)/$worktitle ($id)");
        if (isset($work["submit"]) and $work["submit"] != array()) {
            foreach ($work["submit"] as $internal => $external) {
                $external = str_replace($repbefore, $repafter, $external);
                $zip->addFile(DATAROOT . "files/$userid/$id/main_$internal", mb_convert_encoding("$nickname ($userid)/$worktitle ($id)/提出ファイル ($internal) - $external", 'CP932', 'UTF-8'));
            }
        }
        foreach($submitformdata as $key => $array) {
            if ($array["type"] == "attach") {
                //提出添付ファイルのファイル名も「項目番号 (内部ID) - 項目名 - ファイル名」
                $parttitle = str_replace($repbefore, $repafter, $array["title"]);
                if ($work[$array["id"]] != array()) {
                    foreach ($work[$array["id"]] as $internal => $external) {
                        $zip->addFile(DATAROOT . "files/$userid/$id/" . $array["id"] . "_$internal", mb_convert_encoding("$nickname ($userid)/$worktitle ($id)/$key ($internal) - $parttitle - $external", 'CP932', 'UTF-8'));
                    }
                }
            }
        }
    }
}

$zip->close();
unlink(DATAROOT . 'zip/tmp_user_' . $_SESSION["userid"] . '.csv');
unlink(DATAROOT . 'zip/tmp_submit_' . $_SESSION["userid"] . '.csv');

register_alert('ZIPファイルの生成が完了しました。<br>
<a href="generatezip_dld.php" target="_blank">こちらをクリックして、生成したZIPファイルをダウンロードして下さい。</a>', "success");

redirect("./generatezip.php");
