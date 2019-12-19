<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
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
</div>
</div>
<script>document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../../js/bootstrap.bundle.js"></script>
</body>
</html>
');


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

$IP = getenv("REMOTE_ADDR");

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


//入力済み情報を読み込む
$entereddata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
if ($entereddata["exam"] == 0 or $entereddata["editing"] == 1) die_mypage('現在、ファイルの確認待ちです。確認が完了するまでは、ファイルの編集が出来ません。');

//フォーム設定ファイル読み込み
$submitformdata = array();

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$submitformdata["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

if (outofterm('submit') != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;

if ($submitformdata["general"]["from"] > time() and !$outofterm) die_mypage('提出期間外です。');
else if ($submitformdata["general"]["until"] <= time() and !$outofterm) die_mypage('提出期間外です。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

switch ($_POST["method"]) {
    case 'direct':
        $name = $_FILES["submit"]['name'];
        if ($_FILES["submit"]['error'] == 4) {}
        else if ($_FILES["submit"]['error'] == 1) die('<!DOCTYPE html>
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
        else if ($_FILES["submit"]['error'] == 3) die('<!DOCTYPE html>
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
        else {
            $ext = $submitformdata["general"]["ext"];
            $ext = str_replace(",", "|", $ext);
            $ext = strtoupper($ext);
            $reg = '/\.(' . $ext . ')$/i';
            if (!preg_match($reg, $name)) $invalid = TRUE;
            else {
                $size = $_FILES["general"]['size'];
                if ($submitformdata["general"]["size"] != "") $oksize = (int) $submitformdata["general"]["size"];
                else $oksize = FILE_MAX_SIZE;
                $oksize = $oksize * 1024 * 1024;
                if ($size > $oksize) $invalid = TRUE;
            }
        }
    break;
    case 'url':
        if($_POST["url"] == "") $invalid = TRUE;
        else if(!preg_match('{^https?://[\w/:%#\$&\?\(\)~\.=\+\-]+$}', $_POST["url"])) $invalid = TRUE;
        list($Y, $m, $d) = explode('-', $_POST["due_date"]);
        if (checkdate($m, $d, $Y) !== true) $invalid = TRUE;
        list($hr, $mn) = explode(':', $_POST["due_time"]);
        if ($hr < 0 and $hr > 23) $invalid = TRUE;
        if ($mn < 0 and $mn > 59) $invalid = TRUE;
    break;
    default: $invalid = TRUE;
}

//必須の場合のパターン 文字数
if($_POST["title"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["title"]) > 50) $invalid = TRUE;

//カスタム内容
foreach ($submitformdata as $array) {
    if ($array["type"] == "textbox2") {
      $item = $_POST["custom-" . $array["id"] . "-1"];
      $item2 = $_POST["custom-" . $array["id"] . "-2"];
      $result = check_required2($array["required"], $item, $item2);
      if ($result != 0) $invalid = TRUE;
      if ($item != "") {
        if ($array["max"] != "") $vmax = (int) $array["max"];
        else $vmax = 9999;
        if ($array["min"] != "") $vmin = (int) $array["min"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item);
        if ($result != 0) $invalid = TRUE;
      }
      if ($item2 != "") {
        if ($array["max2"] != "") $vmax = (int) $array["max2"];
        else $vmax = 9999;
        if ($array["min2"] != "") $vmin = (int) $array["min2"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item2);
        if ($result != 0) $invalid = TRUE;
      }
    } else if ($array["type"] == "textbox" || $array["type"] == "textarea") {
      $item = $_POST["custom-" . $array["id"]];
      $result = check_required($array["required"], $item);
      if ($result != 0) $invalid = TRUE;
      else {
        if ($array["max"] != "") $vmax = (int) $array["max"];
        else $vmax = 9999;
        if ($array["min"] != "") $vmin = (int) $array["min"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item);
        if ($result != 0) $invalid = TRUE;
        }
    } else if ($array["type"] == "check") {
        $f = $_POST["custom-" . $array["id"]];
        if ($f == "") $f = array();
        if((array)$f == array() && $array["required"] == "1") $invalid = TRUE;
    } else if ($array["type"] == "radio" || $array["type"] == "dropdown") {
      $item = $_POST["custom-" . $array["id"]];
      $result = check_required($array["required"], $item);
      if ($result != 0) $invalid = TRUE;
    } else if ($array["type"] == "attach") {
        $name = $_FILES["custom-" . $array["id"]]['name'];
        if ($_POST["custom-" . $array["id"] . "-delete"] == "1" and $array["required"] == "1") $invalid = TRUE;
        if ($_FILES["custom-" . $array["id"]]['error'] == 4) {
            if ($array["required"] == "1") {
                if (isset($entereddata[$array["id"]]) and $entereddata[$array["id"]] == '') $invalid = TRUE;
            }
        }
        else if ($_FILES["custom-" . $array["id"]]['error'] == 1) die('<!DOCTYPE html>
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
        else if ($_FILES["custom-" . $array["id"]]['error'] == 3) die('<!DOCTYPE html>
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
        else if ($_FILES["custom-" . $array["id"]]['error'] == 0) {
          $ext = $array["ext"];
          $ext = str_replace(",", "|", $ext);
          $ext = strtoupper($ext);
          $reg = '/\.(' . $ext . ')$/i';
          if (!preg_match($reg, $name)) $invalid = TRUE;
          else {
            $size = $_FILES["custom-" . $array["id"]]['size'];
            if ($array["size"] != "") $oksize = (int) $array["size"];
            else $oksize = FILE_MAX_SIZE;
            $oksize = $oksize * 1024 * 1024;
            if ($size > $oksize) $invalid = TRUE;
          }
        }
    }
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


$userid = $_SESSION["userid"];

//変更内容だけ入れる
$changeditem = array();

//承認について　0:自動　1:主催だけ　2:主催・共催
$recheck = 0;

if ($_POST["method"] == 'direct') {
    $fileto = DATAROOT . 'edit_files/' . $userid . '/';
    if (!file_exists($fileto)) {
        if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
    }
    if ($_FILES["submit"]['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES["submit"]["tmp_name"];
        $ext = substr(basename($_FILES["submit"]["name"]), strrpos(basename($_FILES["submit"]["name"]), '.') + 1);
        $savename = $id;
        if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
        $changeditem["submit"] = $ext;
        $recheck = 2;
    }
} else {
    if ($entereddata["url"] != $_POST["url"]) {
        $changeditem["url"] = $_POST["url"];
        $recheck = 2;
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
    $recheck = 1;
}

//カスタムデータ格納　添付ファイルは専用フォルダに保存 別場所に拡張子
foreach ($submitformdata as $array) {
    if ($array["type"] == "attach") {
        $fileto = DATAROOT . 'edit_attach/' . $_SESSION['userid'] . '/';
        if (!file_exists($fileto)) {
            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
        }
        if ($_FILES["custom-" . $array["id"]]['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["custom-" . $array["id"]]["tmp_name"];
            $ext = substr(basename($_FILES["custom-" . $array["id"]]["name"]), strrpos(basename($_FILES["custom-" . $array["id"]]["name"]), '.') + 1);
            $savename = $id . '_' . $array["id"];
            if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
            $changeditem[$array["id"]] = $ext;
            if ($array["recheck"] != 'auto') $recheck = 1;
        }
        if ($_POST["custom-" . $array["id"] . "-delete"] == "1") {
            $changeditem[$array["id"]] = "";
            if ($array["recheck"] != 'auto') $recheck = 1;
        }
        continue;
    }
    if ($array["type"] == "radio" or $array["type"] == "dropdown") {
        $decode = htmlspecialchars_decode($_POST["custom-" . $array["id"]]);
        if ($entereddata[$array["id"]] != $decode) {
            $changeditem[$array["id"]] = $decode;
            if ($array["recheck"] != 'auto') $recheck = 1;
        }
        continue;
    }
    if ($array["type"] == "check") {
        $oldcompare = implode("、", (array)$entereddata[$array["id"]]);
        $newcompare = implode("、", (array)$_POST["custom-" . $array["id"]]);
        $newdecode = htmlspecialchars_decode($newcompare);
        if ($oldcompare != $newdecode) {
            $changeditem[$array["id"]] = array();
            foreach ((array)$_POST["custom-" . $array["id"]] as $key => $value) {
                $changeditem[$array["id"]][$key] = htmlspecialchars_decode($value);
            }
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
    $_SESSION['situation'] = 'edit_nochange';
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

//自動承認していいなら上書き
if ($recheck == 0) {
    foreach($changeditem as $key => $data) {
        $entereddata[$key] = $data;
    }
    $entereddatajson =  json_encode($entereddata);
    if (file_put_contents(DATAROOT . 'submit/' . $userid . '/' . $id . '.txt', $entereddatajson) === FALSE) die('提出データの書き込みに失敗しました。');

    if (file_exists(DATAROOT . 'edit_files/' . $userid . '/' . $id)) rename(DATAROOT . 'edit_files/' . $userid . '/' . $id, DATAROOT . 'files/' . $userid . '/' . $id);
    foreach(glob(DATAROOT . 'edit_attach/' . $id . '_*') as $filename) {
        $name = basename($filename);
        rename($filename, DATAROOT . 'submit_attach/' . $userid . '/' . $name);
    }
    $_SESSION['situation'] = 'edit_autoaccept';
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
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
$exammember = array();
$exammember["_state"] = 0;
$autoaccept = TRUE;

if ($recheck == 2) {
    foreach (users_array() as $key => $data) {
        if ($data["state"] == 'g') continue;
        if ($data["state"] == 'o') continue;
        $exammember[$key] = array(
            "opinion" => 0,
            "reason" => ""
        );
        if ($_SESSION["userid"] == $key) {
            $exammember[$key]["opinion"] = 1;
            continue;
        }
        $autoaccept = FALSE;
        //通知メール
        $nickname = $data["nickname"];
        $author = $_SESSION["nickname"];
        $pageurl = $siteurl . 'mypage/exam/do_edit.php?author=' . $userid . '&id=' . $id . '&edit=' . $editid;
        $content = "$nickname 様

$author 様が、$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」の情報を編集しました。
メインとなる提出ファイルに変更があったため、主催者・共同運営者による再確認が必要となります。
下記のURLからファイルをダウンロードし、作品内容を確認して下さい。

　ファイル内容確認ページ：$pageurl
　提出元IPアドレス　　　：$IP

※万が一、不適切な作品投稿を繰り返す、イベント運営を妨害するなどの行為が同じIPアドレスから行われる場合、
　主催者の判断で該当IPアドレスからのアクセス制限を行う事が可能です。
";

        //内部関数で送信
        sendmail($data["email"], 'ファイル確認のお願い（内容変更・' . $_POST["title"] . '）', $content);
    }
} else {
    foreach (users_array() as $key => $data) {
        if ($data["state"] == 'c') continue;
        if ($data["state"] == 'g') continue;
        if ($data["state"] == 'o') continue;
        $exammember[$key] = array(
            "opinion" => 0,
            "reason" => ""
        );
        if ($_SESSION["userid"] == $key) {
            $exammember[$key]["opinion"] = 1;
            continue;
        }
        $autoaccept = FALSE;
        //通知メール
        $nickname = $data["nickname"];
        $author = $_SESSION["nickname"];
        $pageurl = $siteurl . 'mypage/exam/do_edit.php?author=' . $userid . '&id=' . $id . '&edit=' . $editid;
        $content = "$nickname 様

$author 様が、$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」の情報を編集しました。
変更の完了には、主催者の承認が必要です。
下記のURLから、変更内容を確認して下さい。

　ファイル内容確認ページ：$pageurl
　提出元IPアドレス　　　：$IP

※万が一、不適切な作品投稿を繰り返す、イベント運営を妨害するなどの行為が同じIPアドレスから行われる場合、
　主催者の判断で該当IPアドレスからのアクセス制限を行う事が可能です。
";

        //内部関数で送信
        sendmail($data["email"], 'ファイル確認のお願い（内容変更・' . $_POST["title"] . '）', $content);
    }
}

//メンバー無しなら自動承認
if ($autoaccept) {
    $entereddata["editing"] = 0;
    foreach($changeditem as $key => $data) {
        $entereddata[$key] = $data;
    }

    if (file_exists(DATAROOT . 'edit_files/' . $userid . '/' . $id)) rename(DATAROOT . 'edit_files/' . $userid . '/' . $id, DATAROOT . 'files/' . $userid . '/' . $id);
    foreach(glob(DATAROOT . 'edit_attach/' . $id . '_*') as $filename) {
        $name = basename($filename);
        rename($filename, DATAROOT . 'submit_attach/' . $userid . '/' . $name);
    }
}
else {
    $exammemberjson =  json_encode($exammember);
    if (file_put_contents(DATAROOT . 'exam_edit/' . $userid . '_' . $id . '_' . $editid . '.txt', $exammemberjson) === FALSE) die('ファイル確認データの書き込みに失敗しました。');
}


$editdatajson =  json_encode($changeditem);
if (!file_exists(DATAROOT . 'edit/' . $userid . '/')) {
    if (!mkdir(DATAROOT . 'edit/' . $userid . '/')) die('ディレクトリの作成に失敗しました。');
}
if (file_put_contents(DATAROOT . 'edit/' . $userid . '/' . $userfile, $editdatajson) === FALSE) die('提出データの書き込みに失敗しました。');

$userdatajson =  json_encode($entereddata);
if (file_put_contents(DATAROOT . 'submit/' . $userid . '/' . $userfile, $userdatajson) === FALSE) die('提出データの書き込みに失敗しました。');


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
        $_SESSION['situation'] = 'edit_submitted';
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
        $_SESSION['situation'] = 'edit_submitted_auto_accept';
}


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
