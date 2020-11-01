<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p", "c", "g"))) redirect("./index.php");


csrf_prevention_validate();

$IP = getenv("REMOTE_ADDR");

//ファイル提出者のユーザーID
$author = basename($_POST["author"]);

//提出ID
$id = basename($_POST["workid"]);

if ($author == "" or $id == "") die('パラメーターエラー');


//自分のファイルのみ編集可
if ($author != $_SESSION['userid']) die('ご自身のファイルのみ、編集が可能です。');

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die('現在、ファイル提出期間外のため、ファイル操作は行えません。');


//入力済み情報を読み込む
$entereddata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
if ($entereddata["exam"] == 0 or $entereddata["editing"] == 1) die('現在、ファイルの確認待ちです。確認が完了するまでは、ファイルの編集が出来ません。');

//フォーム設定ファイル読み込み
$submitformdata = array();

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$submitformdata["general"] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die('現在、ファイル提出期間外のため、ファイル操作は行えません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

switch ($_POST["method"]) {
    case 'direct':
        $uploadedfs = array();
        $currentsize = 0;
        if (isset($entereddata["submit"]) and $entereddata["submit"] != array()) {
            foreach ($entereddata["submit"] as $key => $element){
                $currentsize += filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/main_' . $key);
                $uploadedfs[$key] = filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/main_' . $key);
            }
        }
        if (check_submitfile($submitformdata["general"], $uploadedfs, $currentsize)) $invalid = TRUE;
    break;
    case 'url':
        if($_POST["url"] == "") $invalid = TRUE;
        else if(!preg_match('{^https?://[\w/:%#\$&\?\(\)~\.=\+\-]+$}', $_POST["url"])) $invalid = TRUE;
        if ($_POST["due_date"] != "" or $_POST["due_time"] != "") {
            list($Y, $m, $d) = explode('-', $_POST["due_date"]);
            if (checkdate($m, $d, $Y) !== true) $invalid = TRUE;
            list($hr, $mn) = explode(':', $_POST["due_time"]);
            if ($hr < 0 and $hr > 23) $invalid = TRUE;
            if ($mn < 0 and $mn > 59) $invalid = TRUE;
        }
    break;
    default: $invalid = TRUE;
}

//必須の場合のパターン 文字数
if($_POST["title"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["title"]) > 50) $invalid = TRUE;

//カスタム内容
foreach ($submitformdata as $array) {
    if ($array["type"] == "textbox2") {
        if (check_textbox2($array)) $invalid = TRUE;
    } else if ($array["type"] == "textbox" || $array["type"] == "textarea") {
        if (check_textbox($array)) $invalid = TRUE;
    } else if ($array["type"] == "check") {
        if (check_checkbox($array)) $invalid = TRUE;
    } else if ($array["type"] == "radio" || $array["type"] == "dropdown") {
        if (check_radio($array)) $invalid = TRUE;
    } else if ($array["type"] == "attach") {
        $uploadedfs = array();
        $currentsize = 0;
        if (isset($entereddata[$array["id"]]) and $entereddata[$array["id"]] != array()) {
            foreach ($entereddata[$array["id"]] as $key => $element){
                $currentsize += filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/' . $array["id"] . '_' . $key);
                $uploadedfs[$key] = filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/' . $array["id"] . '_' . $key);
            }
        }
        if (check_attach($array, $uploadedfs, $currentsize)) $invalid = TRUE;
    }
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


$userid = $_SESSION["userid"];

//変更内容だけ入れる
$changeditem = array();

//ファイルアップロードの識別ID
$uploadid = time();

//承認について　0:自動　1:編集の承認メンツ　2:新規提出の承認メンツ
$recheck = 0;

if ($_POST["method"] == 'direct') {
    $fileto = DATAROOT . 'edit_files/' . $userid . '/' . $id . '/';
    if (!file_exists($fileto)) {
        if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
    }
    for ($j=0; $j<count($_FILES["submitfile"]['name']); $j++) {
        if ($_FILES["submitfile"]['error'][$j] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["submitfile"]["tmp_name"][$j];
            $ext = $_FILES["submitfile"]['name'][$j];
            $savename = "main_" . $uploadid . "_$j";
            if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
            chmod($fileto . $savename, 0644);
            if (!isset($changeditem["submit_add"])) $changeditem["submit_add"] = array();
            $changeditem["submit_add"][$uploadid . "_$j"] = $ext;
            $recheck = max($recheck, 2);
        }
    }
    foreach((array)$_POST["submitfile-delete"] as $key){
        if ($key === "none") break;
        if (!isset($changeditem["submit_delete"])) $changeditem["submit_delete"] = array();
        $changeditem["submit_delete"][] = basename($key);
        $recheck = max($recheck, 2);
    }
} else {
    if ($entereddata["url"] != $_POST["url"]) {
        $changeditem["url"] = $_POST["url"];
        $recheck = max($recheck, 2);
    }
    if ($entereddata["dldpw"] != $_POST["dldpw"]) {
        $changeditem["dldpw"] = $_POST["dldpw"];
    }
    list($Yf, $mf, $df) = explode('-', $_POST["due_date"]);
    list($hrf, $mnf) = explode(':', $_POST["due_time"]);
    $dueunix = mktime($hrf, $mnf, 0, $mf, $df, $Yf);
    if ($entereddata["due"] != $dueunix) {
        $changeditem["due"] = $dueunix;
    }
}

if ($entereddata["title"] != $_POST["title"]) {
    $changeditem["title"] = $_POST["title"];
    $recheck = max($recheck, 1);
}

//カスタムデータ格納
foreach ($submitformdata as $array) {
    if ($array["type"] == "attach") {
        $fileto = DATAROOT . 'edit_files/' . $userid . '/' . $id . '/';
        if (!file_exists($fileto)) {
            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
        }
        for ($j=0; $j<count($_FILES["custom-" . $array["id"]]['name']); $j++) {
            if ($_FILES["custom-" . $array["id"]]['error'][$j] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["custom-" . $array["id"]]["tmp_name"][$j];
                $ext = $_FILES["custom-" . $array["id"]]['name'][$j];
                $savename = $array["id"] . "_" . $uploadid . "_$j";
                if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
                chmod($fileto . $savename, 0644);
                if (!isset($changeditem[$array["id"] . "_add"])) $changeditem[$array["id"] . "_add"] = array();
                $changeditem[$array["id"] . "_add"][$uploadid . "_$j"] = $ext;
                if ($array["recheck"] != 'auto') $recheck = max($recheck, 1);
            }
        }
        foreach((array)$_POST["custom-" . $array["id"] . "-delete"] as $key){
            if ($key === "none") break;
            if (!isset($changeditem[$array["id"] . "_delete"])) $changeditem[$array["id"] . "_delete"] = array();
            $changeditem[$array["id"] . "_delete"][] = basename($key);
            if ($array["recheck"] != 'auto') $recheck = max($recheck, 1);
        }
        continue;
    }
    if ($array["type"] == "radio" or $array["type"] == "dropdown") {
        $choices = choices_array($array["list"]);
        $selected = $choices[$_POST["custom-" . $array["id"]]];
        if ($entereddata[$array["id"]] != $selected) {
            $changeditem[$array["id"]] = $selected;
            if ($array["recheck"] != 'auto') $recheck = max($recheck, 1);
        }
        continue;
    }
    if ($array["type"] == "check") {
        $selected = [];
        if ($_POST["custom-" . $array["id"]] !== "") {
            $choices = choices_array($array["list"]);
            foreach ((array)$_POST["custom-" . $array["id"]] as $key => $value) {
                $selected[$key] = $choices[$value];
            }
        }
        $oldcompare = implode("\n", (array)$entereddata[$array["id"]]);
        $newcompare = implode("\n", $selected);
        if ($oldcompare != $newcompare) {
            $changeditem[$array["id"]] = $selected;
            if ($array["recheck"] != 'auto') $recheck = max($recheck, 1);
        }
        continue;
    }
    if ($array["type"] == "textbox2") {
        if ($entereddata[$array["id"] . "-1"] != $_POST["custom-" . $array["id"] . "-1"]) {
            $changeditem[$array["id"] . "-1"] = $_POST["custom-" . $array["id"] . "-1"];
            if ($array["recheck"] != 'auto') $recheck = max($recheck, 1);
        }
        if ($entereddata[$array["id"] . "-2"] != $_POST["custom-" . $array["id"] . "-2"]) {
            $changeditem[$array["id"] . "-2"] = $_POST["custom-" . $array["id"] . "-2"];
            if ($array["recheck"] != 'auto') $recheck = max($recheck, 1);
        }
        continue;
    }
    if ($entereddata[$array["id"]] != $_POST["custom-" . $array["id"]]) {
        $changeditem[$array["id"]] = $_POST["custom-" . $array["id"]];
        if ($array["recheck"] != 'auto') $recheck = max($recheck, 1);
    }
}

if ($changeditem == array()) {
    register_alert("登録情報の変更はありませんでした。", "success");
    redirect("./index.php");
}

//IPアドレスデータ
$entereddata["author_ip"] = $IP;

//自動承認していいなら上書き
if ($recheck == 0) {
    foreach($changeditem as $key => $data) {
        if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
            $fileto = DATAROOT . 'files/' . $userid . '/' . $id . '/';
            if (!file_exists($fileto)) {
                if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
            }
            $tmp = explode("_", $key);
            $partid = $tmp[0];
            if ($partid === "submit") $saveid = "main";
            else $saveid = $partid;
            if ($tmp[1] === "add") {
                foreach ($data as $fileplace => $name) {
                    rename(DATAROOT . 'edit_files/' . $userid . '/' . $id . '/' . $saveid . "_$fileplace", DATAROOT . 'files/' . $userid . '/' . $id . '/' . $saveid . "_$fileplace");
                }
                if (!is_array($entereddata[$partid])) $entereddata[$partid] = array();
                $entereddata[$partid] = array_merge($entereddata[$partid], $data);
            }
            if ($tmp[1] === "delete") {
                foreach ($data as $name) {
                    unlink(DATAROOT . 'files/' . $userid . '/' . $id . '/' . $saveid . "_$name");
                    unset($entereddata[$partid][$name]);
                }
            }
            continue;
        }
        $entereddata[$key] = $data;
    }
    $entereddata["exam"] = 1;
    $entereddata["editdate"] = $uploadid;
    $entereddatajson =  json_encode($entereddata);
    if (file_put_contents_repeat(DATAROOT . 'submit/' . $userid . '/' . $id . '.txt', $entereddatajson) === FALSE) die('提出データの書き込みに失敗しました。');

    register_alert("ファイルの編集が完了しました。<br>自動承認される項目のみ変更されていたため、変更は自動的に承認されました。", "success");
    redirect("./index.php");
}


//以下、承認が必要なケース

//ディレクトリ作成
if (!file_exists(DATAROOT . 'exam_edit/')) {
    if (!mkdir(DATAROOT . 'exam_edit/')) die('ディレクトリの作成に失敗しました。');
}
if (!file_exists(DATAROOT . 'edit/')) {
    if (!mkdir(DATAROOT . 'edit/')) die('ディレクトリの作成に失敗しました。');
}


$userfile = $id . '.txt';
$entereddata["editing"] = 1;

//編集ID
$editid = time();

//ファイル確認のメンバー（送信者自身の場合は承認に自動投票）
//※_state：0…全員の確認が終わってない、1…議論中、2…議論終了、3…即決された
$exammember = array("_state" => 0, "_ip" => $IP, "_realid" => $userid . '/' . $id . '/' . $editid);
$fileid = time() . "_" . md5(microtime() . $userid);
$autoaccept = TRUE;

if ($recheck == 2) {
    $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $exammember["_membermode"] = "submit";
    foreach ($submitmem as $key) {
        if ((string)$key === "_promoter") $key = id_promoter();
        if (!user_exists($key)) continue;
        $data = id_array($key);
        if ($data["state"] == 'g') continue;
        if ($data["state"] == 'o') continue;
        if ($_SESSION["userid"] == $key) {
            $exammember[$key] = array(
                "opinion" => 1,
                "reason" => ""
            );
            continue;
        }
        $autoaccept = FALSE;
        //通知メール
        $nickname = $data["nickname"];
        $pageurl = $siteurl . 'mypage/exam/do_edit.php?examname=' . $fileid;
        $content = "$nickname 様

$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」の情報が編集されました。
その際、メインとなる提出ファイルに変更がありました。
下記のURLからファイルをダウンロードし、作品内容を確認して下さい。

　ファイル内容確認ページ：$pageurl

※編集元のIPアドレス・リモートホスト名は、上記ページもしくは作品詳細画面から、主催者のみ
　閲覧可能です。万が一、不適切な作品投稿を繰り返す、イベント運営を妨害するなどの行為が
　同じIPアドレスや似たリモートホスト名から行われる場合、主催者の判断で該当IPアドレス・
　リモートホスト名からのアカウント作成制限を行う事が可能です。
";

        //内部関数で送信
        sendmail($data["email"], 'ファイル確認のお願い（内容変更・' . $_POST["title"] . '）', $content);
    }
} else {
    $submitmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $exammember["_membermode"] = "edit";
    foreach ($submitmem as $key) {
        if ((string)$key === "_promoter") $key = id_promoter();
        if (!user_exists($key)) continue;
        $data = id_array($key);
        if ($data["state"] == 'g') continue;
        if ($data["state"] == 'o') continue;
        if ($_SESSION["userid"] == $key) {
            $exammember[$key] = array(
                "opinion" => 1,
                "reason" => ""
            );
            continue;
        }
        $autoaccept = FALSE;
        //通知メール
        $nickname = $data["nickname"];
        $pageurl = $siteurl . 'mypage/exam/do_edit.php?examname=' . $fileid;
        $content = "$nickname 様

$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」の情報が編集されました。
下記のURLから、変更内容を確認し、承認するか決めて下さい。

　ファイル内容確認ページ：$pageurl

※編集元のIPアドレス・リモートホスト名は、上記ページもしくは作品詳細画面から、主催者のみ
　閲覧可能です。万が一、不適切な作品投稿を繰り返す、イベント運営を妨害するなどの行為が
　同じIPアドレスや似たリモートホスト名から行われる場合、主催者の判断で該当IPアドレス・
　リモートホスト名からのアカウント作成制限を行う事が可能です。
";

        //内部関数で送信
        sendmail($data["email"], 'ファイル確認のお願い（内容変更・' . $_POST["title"] . '）', $content);
    }
}

//メンバー無しなら自動承認
if ($autoaccept) {
    $entereddata["editing"] = 0;
    foreach($changeditem as $key => $data) {
        if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
            $fileto = DATAROOT . 'files/' . $userid . '/' . $id . '/';
            if (!file_exists($fileto)) {
                if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
            }
            $tmp = explode("_", $key);
            $partid = $tmp[0];
            if ($partid === "submit") $saveid = "main";
            else $saveid = $partid;
            if ($tmp[1] === "add") {
                foreach ($data as $fileplace => $name) {
                    rename(DATAROOT . 'edit_files/' . $userid . '/' . $id . '/' . $saveid . "_$fileplace", DATAROOT . 'files/' . $userid . '/' . $id . '/' . $saveid . "_$fileplace");
                }
                if (!is_array($entereddata[$partid])) $entereddata[$partid] = array();
                $entereddata[$partid] = array_merge($entereddata[$partid], $data);
            }
            if ($tmp[1] === "delete") {
                foreach ($data as $name) {
                    unlink(DATAROOT . 'files/' . $userid . '/' . $id . '/' . $saveid . "_$name");
                    unset($entereddata[$partid][$name]);
                }
            }
            continue;
        }
        $entereddata[$key] = $data;
    }
    $entereddata["exam"] = 1;
    $entereddata["editdate"] = $uploadid;
}
else {
    if (!isset($userdata["related_exams"])) $userdata["related_exams"] = [];
    $userdata["related_exams"][] = 'exam_edit/' . $fileid;
    $exammemberjson =  json_encode($exammember);
    if (file_put_contents_repeat(DATAROOT . 'exam_edit/' . $fileid . '.txt', $exammemberjson) === FALSE) die('ファイル確認データの書き込みに失敗しました。');
}


$editdatajson =  json_encode($changeditem);
if (!file_exists(DATAROOT . 'edit/' . $userid . '/')) {
    if (!mkdir(DATAROOT . 'edit/' . $userid . '/')) die('ディレクトリの作成に失敗しました。');
}
if (file_put_contents_repeat(DATAROOT . 'edit/' . $userid . '/' . $userfile, $editdatajson) === FALSE) die('提出データの書き込みに失敗しました。');

$userdatajson =  json_encode($entereddata);
if (file_put_contents_repeat(DATAROOT . 'submit/' . $userid . '/' . $userfile, $userdatajson) === FALSE) die('提出データの書き込みに失敗しました。');


$email = $_SESSION["email"];

switch ($entereddata["editing"]) {
    case 1:
        $nickname = $_SESSION["nickname"];
        $content = "$nickname 様

$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」の編集を行いました。
変更後の内容の確認を行っています。
確認結果（承認・拒否）は、改めてメールで通知致します。
ファイル確認の結果、ファイルの再提出が必要になる可能性がありますので、
制作に使用した素材などは、しばらくの間消去せずに残しておいて下さい。

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
        sendmail($email, '作品編集を受け付けました', $content);
        register_alert("<p>ファイルの編集が完了しました。<br>変更内容を運営チームが確認するまでしばらくお待ち願います。</p><p>ファイル確認の結果、ファイルの再提出が必要になる可能性がありますので、<b>制作に使用した素材などは、しばらくの間消去せずに残しておいて下さい</b>。</p>", "success");
        break;
    default:
        $nickname = $_SESSION["nickname"];
        $content = "$nickname 様

$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」の編集を行いました。
ファイル確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、
変更は自動的に承認されました。

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
        sendmail($email, '作品編集を受け付け・承認しました', $content);
        register_alert("ファイルの編集が完了しました。<br>ファイル確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、変更内容は<b>自動的に承認されました</b>。", "success");
}

redirect("./index.php");
