<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p", "c", "g"))) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、共通事項の設定を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


csrf_prevention_validate();

$IP = getenv("REMOTE_ADDR");


//入力済み情報を読み込む
$userid = $_SESSION["userid"];
$entereddata = json_decode(file_get_contents_repeat(DATAROOT . "users/" . $userid . ".txt"), true);

//締め切り後は変更不可・例外処理
if (outofterm('userform') != FALSE) $disable = FALSE;
else $disable = TRUE;
if ($_SESSION["state"] == 'p') $disable = FALSE;
if (before_deadline()) $disable = FALSE;
if ((isset($entereddata["common_acceptance"]) and $entereddata["common_acceptance"] == 0) or (isset($entereddata["common_editing"]) and $entereddata["common_editing"] == 1)) {
    $disable = TRUE;
}

if ($disable) die();

//フォーム設定ファイル読み込み
$submitformdata = array();

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

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
                $currentsize += filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $array["id"] . '_' . $key);
                $uploadedfs[$key] = filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $array["id"] . '_' . $key);
            }
        }
        if (check_attach($array, $uploadedfs, $currentsize)) $invalid = TRUE;
    }
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//変更内容だけ入れる
$changeditem = array();

$recheck = 0;

//ファイルアップロードの識別ID
$uploadid = time();

//カスタムデータ格納
foreach ($submitformdata as $array) {
    if ($array["type"] == "attach") {
        $fileto = DATAROOT . 'edit_files/' . $_SESSION["userid"] . '/common/';
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
                if ($array["recheck"] != 'auto') $recheck = 1;
            }
        }
        foreach((array)$_POST["custom-" . $array["id"] . "-delete"] as $key){
            if ($key === "none") break;
            if (!isset($changeditem[$array["id"] . "_delete"])) $changeditem[$array["id"] . "_delete"] = array();
            $changeditem[$array["id"] . "_delete"][] = basename($key);
            if ($array["recheck"] != 'auto') $recheck = 1;
        }
        continue;
    }
    if ($array["type"] == "radio" or $array["type"] == "dropdown") {
        $choices = choices_array($array["list"]);
        $selected = $choices[$_POST["custom-" . $array["id"]]];
        if ($entereddata[$array["id"]] != $selected) {
            $changeditem[$array["id"]] = $selected;
            if ($array["recheck"] != 'auto') $recheck = 1;
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
            if ($array["recheck"] != 'auto') $recheck = 1;
        }
        continue;
    }
    if ($array["type"] == "textbox2") {
        if ($entereddata[$array["id"] . "-1"] != $_POST["custom-" . $array["id"] . "-1"]) {
            $changeditem[$array["id"] . "-1"] = $_POST["custom-" . $array["id"] . "-1"];
            if ($array["recheck"] != 'auto') $recheck = 1;
        }
        if ($entereddata[$array["id"] . "-2"] != $_POST["custom-" . $array["id"] . "-2"]) {
            $changeditem[$array["id"] . "-2"] = $_POST["custom-" . $array["id"] . "-2"];
            if ($array["recheck"] != 'auto') $recheck = 1;
        }
        continue;
    }
    if ($entereddata[$array["id"]] != $_POST["custom-" . $array["id"]]) {
        $changeditem[$array["id"]] = $_POST["custom-" . $array["id"]];
        if ($array["recheck"] != 'auto') $recheck = 1;
    }
}

if ($changeditem == array()) {
    register_alert("入力内容の変更はありませんでした。", "success");
    redirect("./index.php");
}

//IPアドレスデータ
$entereddata["common_ip"] = $IP;

//自動承認していいなら上書き
if ($recheck == 0) {
    $entereddata["common_acceptance"] = 1;
    foreach($changeditem as $key => $data) {
        if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
            $fileto = DATAROOT . 'files/' . $_SESSION["userid"] . '/common/';
            if (!file_exists($fileto)) {
                if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
            }
            $tmp = explode("_", $key);
            $partid = $tmp[0];
            if ($tmp[1] === "add") {
                foreach ($data as $fileplace => $name) {
                    rename(DATAROOT . 'edit_files/' . $_SESSION["userid"] . '/common/' . $partid . "_$fileplace", DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $partid . "_$fileplace");
                }
                if (!is_array($entereddata[$partid])) $entereddata[$partid] = array();
                $entereddata[$partid] = array_merge($entereddata[$partid], $data);
            }
            if ($tmp[1] === "delete") {
                foreach ($data as $name) {
                    unlink(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $partid . "_$name");
                    unset($entereddata[$partid][$name]);
                }
            }
            continue;
        }
        $entereddata[$key] = $data;
    }

    $entereddatajson =  json_encode($entereddata);
    if (file_put_contents_repeat(DATAROOT . "users/" . $userid . ".txt", $entereddatajson) === FALSE) die('提出データの書き込みに失敗しました。');

    register_alert("共通情報の変更が完了しました。<br>自動承認される項目のみ変更されていたため、変更は自動的に承認されました。", "success");
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


$userfile = 'common.txt';
if (isset($entereddata["common_acceptance"])) $entereddata["common_editing"] = 1;
else {
    $entereddata["common_acceptance"] = 0;
    $entereddata["common_editing"] = 0;
}

//編集ID
$editid = $uploadid;

//ファイル確認のメンバー（送信者自身の場合は承認に自動投票）
//※_state：0…全員の確認が終わってない、1…議論中、2…議論終了、3…即決された
$exammember = array("_state" => 0, "_ip" => $IP, "_realid" => $userid . '/common/' . $editid);
$fileid = time() . "_" . md5(microtime() . $userid);
$autoaccept = TRUE;

$submitmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($entereddata["common_editing"] == 0) $exammember["_commonmode"] = "new";
else $exammember["_commonmode"] = "edit";
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
    $author = $_SESSION["nickname"];
    $pageurl = $siteurl . 'mypage/exam/do_common.php?examname=' . $fileid;
    $content = "$nickname 様

$eventname のポータルサイトにて、共通情報の登録もしくは編集がありました。
下記のURLから、登録内容を確認し、承認するか決めて下さい。

　登録内容確認ページ：$pageurl

※登録もしくは編集が行われた際のIPアドレス・リモートホスト名は、上記ページもしくは参加者一覧から、
　主催者のみ閲覧可能です。万が一、不適切な作品投稿を繰り返す、イベント運営を妨害するなどの行為が
　同じIPアドレスや似たリモートホスト名から行われる場合、主催者の判断で該当IPアドレス・
　リモートホスト名からのアカウント作成制限を行う事が可能です。
";

    //内部関数で送信
    sendmail($data["email"], '登録内容確認のお願い（共通情報）', $content);
}

//メンバー無しなら自動承認
if ($autoaccept) {
    $entereddata["common_editing"] = 0;
    $entereddata["common_acceptance"] = 1;
    foreach($changeditem as $key => $data) {
        if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
            $fileto = DATAROOT . 'files/' . $_SESSION["userid"] . '/common/';
            if (!file_exists($fileto)) {
                if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
            }
            $tmp = explode("_", $key);
            $partid = $tmp[0];
            if ($tmp[1] === "add") {
                foreach ($data as $fileplace => $name) {
                    rename(DATAROOT . 'edit_files/' . $_SESSION["userid"] . '/common/' . $partid . "_$fileplace", DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $partid . "_$fileplace");
                }
                if (!is_array($entereddata[$partid])) $entereddata[$partid] = array();
                $entereddata[$partid] = array_merge($entereddata[$partid], $data);
            }
            if ($tmp[1] === "delete") {
                foreach ($data as $name) {
                    unlink(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $partid . "_$name");
                    unset($entereddata[$partid][$name]);
                }
            }
            continue;
        }
        $entereddata[$key] = $data;
    }
}
else {
    $exammemberjson =  json_encode($exammember);
    if (file_put_contents_repeat(DATAROOT . 'exam_edit/' . $fileid . '.txt', $exammemberjson) === FALSE) die('ファイル確認データの書き込みに失敗しました。');
}


//初回だけもう書く
if ($exammember["_commonmode"] == "new" and !$autoaccept) {
    foreach($changeditem as $key => $data) {
        if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
            $fileto = DATAROOT . 'files/' . $_SESSION["userid"] . '/common/';
            if (!file_exists($fileto)) {
                if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
            }
            $tmp = explode("_", $key);
            $partid = $tmp[0];
            if ($tmp[1] === "add") {
                foreach ($data as $fileplace => $name) {
                    rename(DATAROOT . 'edit_files/' . $_SESSION["userid"] . '/common/' . $partid . "_$fileplace", DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $partid . "_$fileplace");
                }
                if (!is_array($entereddata[$partid])) $entereddata[$partid] = array();
                $entereddata[$partid] = array_merge($entereddata[$partid], $data);
            }
            if ($tmp[1] === "delete") {
                foreach ($data as $name) {
                    unlink(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $partid . "_$name");
                    unset($entereddata[$partid][$name]);
                }
            }
            continue;
        }
        $entereddata[$key] = $data;
    }
}

$editdatajson =  json_encode($changeditem);
if (!file_exists(DATAROOT . 'edit/' . $userid . '/')) {
    if (!mkdir(DATAROOT . 'edit/' . $userid . '/')) die('ディレクトリの作成に失敗しました。');
}
if (file_put_contents_repeat(DATAROOT . 'edit/' . $userid . '/' . $userfile, $editdatajson) === FALSE) die('提出データの書き込みに失敗しました。');

$entereddatajson =  json_encode($entereddata);
if (file_put_contents_repeat(DATAROOT . "users/" . $userid . ".txt", $entereddatajson) === FALSE) die('提出データの書き込みに失敗しました。');


$email = $_SESSION["email"];

switch ($autoaccept) {
    case FALSE:
        $nickname = $_SESSION["nickname"];
        $content = "$nickname 様

$eventname のポータルサイトにて、共通情報の登録・編集を行いました。
登録した内容の確認を行っています。
確認結果（承認・拒否）は、改めてメールで通知致します。

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
        sendmail($email, '共通情報の登録・変更を受け付けました', $content);
        register_alert("共通情報の変更が完了しました。<br>変更内容を運営チームが確認するまでしばらくお待ち願います。", "success");
        break;
    default:
        $nickname = $_SESSION["nickname"];
        $content = "$nickname 様

$eventname のポータルサイトにて、共通情報の登録・編集を行いました。
共通情報確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、
登録は自動的に承認されました。

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
        sendmail($email, '共通情報の登録・変更を受け付け・承認しました', $content);
        register_alert("共通情報の変更が完了しました。<br>共通情報確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、変更は自動的に承認されました。", "success");
}


redirect("./index.php");
