<?php
function sendmail($email, $subject, $content) {
    $sendmaildata = json_decode(file_get_contents(DATAROOT . 'mail.txt'), true);
    $sendmailurl = file_get_contents(DATAROOT . 'siteurl.txt');
    $sendmailevn = file_get_contents(DATAROOT . 'eventname.txt');
    if ($sendmaildata["pre"] == '') $mailpre = mb_substr($sendmailevn, 0, 15);
    else $mailpre = $sendmaildata["pre"];
    if ($sendmaildata["fromname"] != '') $from = "From: " . $sendmaildata["fromname"] . " <" . $sendmaildata["from"] . ">";
    else $from = "From: " . $sendmaildata["from"];
    $subject = '【' . $mailpre . '】' . $subject;
    if ($sendmaildata["sendonly"] == 1 ) $content = "※このメールは、$sendmailevn に関する自動送信メールです。
　あなたが $sendmailevn に関わっている覚えが無い場合は、このまま本メールを破棄して下さい。
※このメールアドレスは送信専用です。
　こちらに返信頂いても受信出来ませんのでご了承下さい。
------------------------------
$content
------------------------------
$sendmailevn
$sendmailurl";
    else $content = "※このメールは、$sendmailevn に関する自動送信メールです。
　あなたが $sendmailevn に関わっている覚えが無い場合は、このまま本メールを破棄して下さい。
------------------------------
$content
------------------------------
$sendmailevn
$sendmailurl";

    if ($sendmaildata["from"] != '') {
      if (!mb_send_mail($email, $subject, $content, $from)) die("メール送信に失敗しました。");
    } else {
      if (!mb_send_mail($email, $subject, $content)) die("メール送信に失敗しました。");
    }

}

//未入力の場合強制終了
if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン・文字種・文字数
if($_POST["userid"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9a-zA-Z]*$/', $_POST["userid"])) $invalid = TRUE;
else if(mb_strlen($_POST["userid"]) > 20) $invalid = TRUE;

//必須の場合のパターン 文字数
if($_POST["nickname"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["nickname"]) > 30) $invalid = TRUE;

//メールアドレス形式確認　必須・一致確認
if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;
else if($_POST["email"] != $_POST["emailagn"]) $invalid = TRUE;

//必須の場合のパターン・文字数・一致確認
if($_POST["password"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["password"]) > 30) $invalid = TRUE;
else if(mb_strlen($_POST["password"]) < 8) $invalid = TRUE;
else if($_POST["password"] != $_POST["passwordagn"]) $invalid = TRUE;

//必須の場合
if($_POST["state"] == "") $invalid = TRUE;

//必須の場合のパターン 文字数
if($_POST["eventname"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["eventname"]) > 50) $invalid = TRUE;

if($_POST["filesize"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9]*$/', $_POST["filesize"])) $invalid = TRUE;
else if((int)$_POST["filesize"] < 1) $invalid = TRUE;

//メールアドレス形式確認　必須でない
if($_POST["system"] == ""){
} else if(!preg_match('/.+@.+\..+/', $_POST["system"])) $invalid = TRUE;

//文字数 必須でない
if($_POST["systemfrom"] == ""){
} else if(mb_strlen($_POST["systemfrom"]) > 30) $invalid = TRUE;

//文字数 必須でない
if($_POST["systempre"] == ""){
} else if(mb_strlen($_POST["systempre"]) > 15) $invalid = TRUE;

//文字種　どっちかかたっぽだけはNG
  if($_POST["recaptcha_sec"] == "" && $_POST["recaptcha_site"] == ""){
  } else if($_POST["recaptcha_sec"] == "" || $_POST["recaptcha_site"] == "") $invalid = TRUE;
  else if(!preg_match('/^[0-9a-zA-Z-]*$/', $_POST["recaptcha_sec"]) || !preg_match('/^[0-9a-zA-Z-]*$/', $_POST["recaptcha_site"])) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


if (file_exists('../dataplace.php')) require_once('../dataplace.php'); else define('DATAROOT', dirname(__FILE__).'/../data/');
if (file_exists(DATAROOT . 'init.txt')) die('初期設定が既に終わっています。');


$IP = getenv("REMOTE_ADDR");
$browser = $_SERVER['HTTP_USER_AGENT'];

if (!file_exists(DATAROOT)) {
    if (!mkdir(DATAROOT, 0777, true)) die('ディレクトリの作成に失敗しました。');
}

if (!file_exists(DATAROOT . 'users/')) {
    if (!mkdir(DATAROOT . 'users/')) die('ディレクトリの作成に失敗しました。');
}


//サイトのURLを取得
$url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$url = preg_replace('/initial\/handle\.php$/', '', $url);

//サイト名を保管しとく
if (file_put_contents(DATAROOT . 'siteurl.txt', $url) === FALSE) die('サイトURLの書き込みに失敗しました。');

//イベント名書き込み
$eventname = $_POST["eventname"];
if (file_put_contents(DATAROOT . 'eventname.txt', $eventname) === FALSE) die('イベント名の書き込みに失敗しました。');

//maxサイズ
if (file_put_contents(DATAROOT . 'maxsize.txt', $_POST["filesize"]) === FALSE) die('ファイルサイズの書き込みに失敗しました。');

$maildata = array(
    "from" => $_POST["system"],
    "sendonly" => $_POST["systemsend"],
    "fromname" => $_POST["systemfrom"],
    "pre" => $_POST["systempre"]
);

$maildatajson =  json_encode($maildata);

if (file_put_contents(DATAROOT . 'mail.txt', $maildatajson) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//reCAPTCHA
$recdata = array(
    "site" => $_POST["recaptcha_site"],
    "sec" => $_POST["recaptcha_sec"],
);

$recdatajson =  json_encode($recdata);

if (file_put_contents(DATAROOT . 'rec.txt', $recdatajson) === FALSE) die('reCAPTCHA関連のデータの書き込みに失敗しました。');


//パスワードハッシュ化
$hash = password_hash($_POST["password"], PASSWORD_BCRYPT);

//ユーザー情報格納
//lastipとlastbrは、最終ログイン時のIPとブラウザ情報
//ログインしたときにこのipかbrが違う場合にセキュリティ通知する（不正ログインされた時に発見しやすい）
$userid = $_POST["userid"];
$nickname = $_POST["nickname"];
$userfile = $userid . '.txt';
$email = $_POST["email"];
$state = $_POST["state"];
$userdata = array(
    "nickname" => $nickname,
    "email" => $email,
    "pwhash" => $hash,
    "state" => $state,
    "admin" => 1,
    "lastip" => $IP,
    "lastbr" => $browser
);

if ($state == "p") $statej = "主催者";
else if ($state == "c") $statej = "共同運営者";
else if ($state == "g") $statej = "一般参加者";
else $statej = "非参加者";

$userdatajson =  json_encode($userdata);

if (file_put_contents(DATAROOT . 'users/' . $userfile, $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//立場別の一覧
$statedata = "$userid\n";
if ($state == "p") $statedtp = DATAROOT . 'users/_promoter.txt';
else if ($state == "c") $statedtp = DATAROOT . 'users/_co.txt';
else if ($state == "g") $statedtp = DATAROOT . 'users/_general.txt';
else $statedtp = DATAROOT . 'users/_outsider.txt';

if (file_put_contents($statedtp, $statedata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//メール本文形成
$date = date('Y/m/d H:i:s');
$content = "$nickname 様

$eventname のポータルサイトの管理者アカウントの設定が完了しました。
登録内容は以下の通りです。

　ユーザーID　　　　　：$userid
　ニックネーム　　　　：$nickname
　メールアドレス　　　：$email
　立場　　　　　　　　：$statej

　登録時のIPアドレス　：$IP
　登録時のブラウザ情報：$browser
　登録日時　　　　　　：$date
";

//内部関数で送信
sendmail($email, '管理者アカウントの設定完了通知', $content);

//処理完了後、初期設定完了ファイル作成
if (file_put_contents(DATAROOT . 'init.txt', '1') === FALSE) die('初期設定完了データの書き込みに失敗しました。');

//ログイン状態に
session_start();
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    $_SESSION['userid'] = $userid;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['state'] = $state;
    $_SESSION['admin'] = 1;
    $_SESSION['situation'] = 'registered';
    $_SESSION['expire'] = time() + (30 * 60);
    $_SESSION['useragent'] = $browser;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='../mypage/index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
