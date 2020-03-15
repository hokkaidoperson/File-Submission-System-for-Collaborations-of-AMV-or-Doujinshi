<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>
');

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル提出を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>
');


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

$IP = getenv("REMOTE_ADDR");

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
        if (check_submitfile($submitformdata["general"], array(), 0)) $invalid = TRUE;
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
        if (check_textbox2($array)) $invalid = TRUE;
    } else if ($array["type"] == "textbox" || $array["type"] == "textarea") {
        if (check_textbox($array)) $invalid = TRUE;
    } else if ($array["type"] == "check") {
        if (check_checkbox($array)) $invalid = TRUE;
    } else if ($array["type"] == "radio" || $array["type"] == "dropdown") {
        if (check_radio($array)) $invalid = TRUE;
    } else if ($array["type"] == "attach") {
        if (check_attach($array, array(), 0)) $invalid = TRUE;
    }
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//ID発行（unix）
$id = time();
$userid = $_SESSION["userid"];

//ディレクトリ作成
if (!file_exists(DATAROOT . 'submit/')) {
    if (!mkdir(DATAROOT . 'submit/')) die('ディレクトリの作成に失敗しました。');
}
if (!file_exists(DATAROOT . 'exam/')) {
    if (!mkdir(DATAROOT . 'exam/')) die('ディレクトリの作成に失敗しました。');
}

//ファイル情報格納
$userfile = $id . '.txt';
$userdata = array(
    "title" => $_POST["title"],
    "exam" => 0,
    "editing" => 0
);

//メインのファイルを保存
if ($_POST["method"] == 'direct') {
    $fileto = DATAROOT . 'files/' . $userid . '/' . $id . '/';
    if (!file_exists($fileto)) {
        if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
    }
    $userdata["submit"] = array();
    for ($j=0; $j<count($_FILES["submitfile"]['name']); $j++) {
        if ($_FILES["submitfile"]['error'][$j] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["submitfile"]["tmp_name"][$j];
            $ext = $_FILES["submitfile"]['name'][$j];
            $savename = "main_" . $id . "_$j";
            if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
            chmod($fileto . $savename, 0644);
            $userdata["submit"][$id . "_$j"] = $ext;
        }
    }
} else {
    $userdata["url"] = $_POST["url"];
    $userdata["dldpw"] = $_POST["dldpw"];
    list($Yf, $mf, $df) = explode('-', $_POST["due_date"]);
    list($hrf, $mnf) = explode(':', $_POST["due_time"]);
    $userdata["due"] = mktime($hrf, $mnf, 0, $mf, $df, $Yf);
}

//カスタムデータ格納
foreach ($submitformdata as $array) {
    if ($array["type"] == "general") continue;
    if ($array["type"] == "attach") {
        $fileto = DATAROOT . 'files/' . $userid . '/' . $id . '/';
        if (!file_exists($fileto)) {
            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
        }
        $userdata[$array["id"]] = array();
        for ($j=0; $j<count($_FILES["custom-" . $array["id"]]['name']); $j++) {
            if ($_FILES["custom-" . $array["id"]]['error'][$j] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["custom-" . $array["id"]]["tmp_name"][$j];
                $ext = $_FILES["custom-" . $array["id"]]['name'][$j];
                $savename = $array["id"] . "_" . $id . "_$j";
                if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
                chmod($fileto . $savename, 0644);
                $userdata[$array["id"]][$id . "_$j"] = $ext;
            }
        }
        continue;
    }
    if ($array["type"] == "radio" or $array["type"] == "dropdown") {
        $userdata[$array["id"]] = htmlspecialchars_decode($_POST["custom-" . $array["id"]]);
        continue;
    }
    if ($array["type"] == "check") {
        if ($_POST["custom-" . $array["id"]] == "") {
            $userdata[$array["id"]] = array();
            continue;
        }
        foreach ((array)$_POST["custom-" . $array["id"]] as $key => $value) {
            $userdata[$array["id"]][$key] = htmlspecialchars_decode($value);
        }
        continue;
    }
    if ($array["type"] == "textbox2") {
        $userdata[$array["id"] . "-1"] = $_POST["custom-" . $array["id"] . "-1"];
        $userdata[$array["id"] . "-2"] = $_POST["custom-" . $array["id"] . "-2"];
        continue;
    }
    $userdata[$array["id"]] = $_POST["custom-" . $array["id"]];
}

//ファイル確認のメンバー（送信者自身の場合は承認に自動投票）
//※_state：0…全員の確認が終わってない、1…議論中、2…議論終了、3…即決された
$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$exammember = array("_state" => 0);
$autoaccept = TRUE;
foreach ($submitmem as $key) {
    if ($key == "_promoter") $key = id_promoter();
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
    $pageurl = $siteurl . 'mypage/exam/do.php?author=' . $userid . '&id=' . $id;
    $content = "$nickname 様

$author 様が、$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」を提出しました。
下記のURLからファイルをダウンロードし、作品内容を確認して下さい。

　ファイル内容確認ページ：$pageurl
　提出元IPアドレス　　　：$IP

※万が一、不適切な作品投稿を繰り返す、イベント運営を妨害するなどの行為が同じIPアドレスから行われる場合、
　主催者の判断で該当IPアドレスからのアクセス制限を行う事が可能です。
";

    //内部関数で送信
    sendmail($data["email"], 'ファイル確認のお願い（' . $_POST["title"] . '）', $content);
}

//メンバー無しなら自動承認
if ($autoaccept) $userdata["exam"] = 1;
else {
    $exammemberjson =  json_encode($exammember);
    if (file_put_contents(DATAROOT . 'exam/' . $userid . '_' . $userfile, $exammemberjson) === FALSE) die('ファイル確認データの書き込みに失敗しました。');
}


$userdatajson =  json_encode($userdata);
if (!file_exists(DATAROOT . 'submit/' . $userid . '/')) {
    if (!mkdir(DATAROOT . 'submit/' . $userid . '/')) die('ディレクトリの作成に失敗しました。');
}
if (file_put_contents(DATAROOT . 'submit/' . $userid . '/' . $userfile, $userdatajson) === FALSE) die('提出データの書き込みに失敗しました。');

$email = $_SESSION["email"];

switch ($userdata["exam"]) {
    case 0:
        $nickname = $_SESSION["nickname"];
        $content = "$nickname 様

$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」を提出しました。
提出されたファイルは、運営チーム（主催者・共同運営者）によって確認されます。
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
        sendmail($email, '作品提出を受け付けました', $content);
        $_SESSION['situation'] = 'file_submitted';
        break;
    default:
        $nickname = $_SESSION["nickname"];
        $content = "$nickname 様

$eventname のポータルサイトにて、作品「" . $_POST["title"] . "」を提出しました。
ファイル確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、
この作品は自動的に承認されました。

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
        sendmail($email, '作品提出を受け付け・承認しました', $content);
        $_SESSION['situation'] = 'file_submitted_auto_accept';
}

if ($_POST["jumptocommonpage"]) redirect("../common/index.php");
else redirect("./index.php");
