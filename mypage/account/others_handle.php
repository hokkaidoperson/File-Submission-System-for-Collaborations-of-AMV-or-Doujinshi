<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

//パスワード認証
$userdata = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt'), true);
if (!password_verify($_POST["password"], $userdata["pwhash"])) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>認証エラー</title>
</head>
<body>
<p>現在のパスワードが誤っています。お手数ですが、入力をやり直して下さい。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、設定画面にお戻り下さい。</a></p>
</body>
</html>');

//提出期間外だとメールアドレス以外変更不可
//disable属性を使用していて、値が送られてこない可能性があるのでそれを調べつつ
if (outofterm('userform') != FALSE) $disable = FALSE;
else $disable = TRUE;
if ($_SESSION["state"] == 'p') $disable = FALSE;

if (before_deadline()) $disable = FALSE;

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン 文字数
if($_POST["nickname"] == "") {
    if (!$disable) $invalid = TRUE;
}
else if(mb_strlen($_POST["nickname"]) > 30) $invalid = TRUE;

//メールアドレス形式確認　必須・一致確認
if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;
else if($_POST["email"] != $_POST["emailagn"]) $invalid = TRUE;
//重複確認
$email = $_POST["email"];
$conflict = 0;
//登録済みの中から探す
foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents_repeat($filename), true);
    if ($filedata["email"] == $email) {
        if (basename($filename, ".txt") == $_SESSION["userid"]) continue;
        $conflict++;
    }
}
if ($conflict >= ACCOUNTS_PER_ADDRESS) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

//変更点
$changed = array();

//ニックネームの変更が遮断された？
$rejected = FALSE;

if (!$disable and $userdata["nickname"] != $_POST["nickname"]) {
    $changed[] = "【ニックネーム】" . hsc($userdata["nickname"]) . " → " . hsc($_POST["nickname"]);
    $userdata["nickname"] = $_POST["nickname"];
} else if ($userdata["nickname"] != $_POST["nickname"]) $rejected = TRUE;
if ($userdata["email"] != $_POST["email"]) {
    $changed[] = "【メールアドレス】" . hsc($userdata["email"]) . " → " . hsc($_POST["email"]);
    $userdata["email"] = $_POST["email"];
}
if ($rejected) $changed[] = "※ニックネームについては、入力中もしくは送信中に提出締め切りを迎えたため、変更出来ませんでした。もし変更が必要な場合は主催者にご相談下さい。";


if ($changed == array()) {
    register_alert("登録情報の変更はありませんでした。", "success");
    redirect("./index.php");
}

$userdatajson =  json_encode($userdata);

if (file_put_contents_repeat(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

$_SESSION['nickname'] = $userdata["nickname"];
$_SESSION['email'] = $userdata["email"];

$changed = implode("\n", $changed);

//メール本文形成
$nickname = $_SESSION['nickname'];
$date = date('Y年n月j日G時i分s秒');
$content = "$nickname 様

$eventname のポータルサイトのマイページで、アカウント情報が変更されました。

この通知は、$eventname のポータルサイトであなたの登録情報に変更があった際（ファイルの新規提出など）に、
それが不正ログインによる変更でないかどうか確認するためにお送りしているものです。


【あなた自身の操作で変更した場合】
ご確認ありがとうございます。引き続きポータルサイトをご利用下さい。
このメールは削除しても構いません。


【変更した覚えが無い場合】
第三者があなたのアカウントに不正ログインした可能性があります。直ちに、パスワードの変更を行って下さい。

あなたの操作でログインした後、「アカウント情報編集」→「パスワード変更」の順に選択して下さい。


【変更情報】
$changed
";
//内部関数で送信
sendmail($_SESSION['email'], 'アカウント情報変更通知', $content);


$changed = hsc($changed);
$changed = str_replace(array("\r\n", "\r", "\n"), "\n", $changed);
$changed = str_replace("\n", "<br>", $changed);
register_alert("次の通り、登録情報を変更しました。<br>$changed", "success");

redirect("./index.php");
