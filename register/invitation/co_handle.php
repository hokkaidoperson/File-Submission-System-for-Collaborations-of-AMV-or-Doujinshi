<?php
require_once('../../set.php');

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン・文字種・文字数
if($_POST["userid"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9a-zA-Z]*$/', $_POST["userid"])) $invalid = TRUE;
else if(mb_strlen($_POST["userid"]) > 20) $invalid = TRUE;

$IP = getenv("REMOTE_ADDR");

$conflict = FALSE;
$userid = basename($_POST["userid"]);
$IP = getenv("REMOTE_ADDR");

//登録中のユーザーID横取り阻止（保証期間は30分）
if (file_exists(DATAROOT . 'users_reserve/')) {
    foreach (glob(DATAROOT . 'users_reserve/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents($filename), true);
        if ($filedata["expire"] <= time()) {
            unlink($filename);
            continue;
        }
        // 自分自身だったら通してあげる
        if (basename($filename, ".txt") == $IP) continue;
        if ($filedata["userid"] == $userid) {
            $conflict = TRUE;
            break;
        }
    }
}

//登録済みの中から探す
foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
    if (basename($filename, ".txt") == $userid) {
        $conflict = TRUE;
        break;
    }
}

if ($conflict) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ユーザーID重複</title>
</head>
<body>
<p>大変申し訳ございませんが、このユーザーIDは既に使用されているため、登録出来ません。<br>
お手数をお掛けしますが、別のユーザーIDをご利用願います。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、ユーザーID入力画面にお戻り下さい。</a></p>
</body>
</html>');

//必須の場合のパターン 文字数
if($_POST["nickname"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["nickname"]) > 30) $invalid = TRUE;

//メールアドレス形式確認　必須・一致確認
if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;
else if($_POST["email"] != $_POST["emailagn"]) $invalid = TRUE;

//重複確認
$email = $_POST["email"];
$conflict = FALSE;
//登録済みの中から探す
foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents($filename), true);
    if ($filedata["email"] == $email) {
        $conflict = TRUE;
        break;
    }
}
if ($conflict) $invalid = TRUE;


//必須の場合のパターン・文字数・一致確認
if($_POST["password"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["password"]) > 30) $invalid = TRUE;
else if(mb_strlen($_POST["password"]) < 8) $invalid = TRUE;
else if($_POST["password"] != $_POST["passwordagn"]) $invalid = TRUE;

//必須の場合
if($_POST["state"] != "c") $invalid = TRUE;

//sectokをもっかいチェック
if (file_exists(DATAROOT . 'mail/invitation/' . basename($_POST["towhom"]) . '.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/invitation/' . basename($_POST["towhom"]) . '.txt'), true);
    if ($filedata["sectok"] !== $_POST["sectok"]) $invalid = TRUE;
} else $invalid = TRUE;


if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');



$browser = $_SERVER['HTTP_USER_AGENT'];

//パスワードハッシュ化
$hash = password_hash($_POST["password"], PASSWORD_BCRYPT);

//ユーザー情報格納
//lastipとlastbrは、最終ログイン時のIPとブラウザ情報
//ログインしたときにこのipかbrが違う場合にセキュリティ通知する（不正ログインされた時に発見しやすい）
$userid = $_POST["userid"];
$nickname = $_POST["nickname"];
$userfile = $userid . '.txt';
$email = $_POST["email"];
$state = 'c';
$userdata = array(
    "nickname" => $nickname,
    "email" => $email,
    "pwhash" => $hash,
    "state" => $state,
    "admin" => 0,
    "lastip" => $IP,
    "lastbr" => $browser
);

$statej = "共同運営者";

$userdatajson =  json_encode($userdata);

if (file_put_contents(DATAROOT . 'users/' . $userfile, $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//立場別の一覧
$statedata = "$userid\n";
$statedtp = DATAROOT . 'users/_co.txt';

if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//この人をファイル確認メンバーに入れる？
if (file_exists(DATAROOT . 'examsetting.txt')) {
    $examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

    if ($examsetting["submit_add"] == "1") {
        $statedata = "$userid\n";
        $statedtp = DATAROOT . 'exammember_submit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
    if ($examsetting["edit_add"] == "1") {
        $statedata = "$userid\n";
        $statedtp = DATAROOT . 'exammember_edit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
}

//メール本文形成
$date = date('Y/m/d H:i:s');
$content = "$nickname 様

$eventname のポータルサイトのアカウントの設定が完了しました。
登録内容は以下の通りです。

《ユーザーID》$userid
《ニックネーム》$nickname
《メールアドレス》$email
《立場》$statej

　登録時のIPアドレス　：$IP
　登録時のブラウザ情報：$browser
　登録日時　　　　　　：$date
";

//内部関数で送信
sendmail($email, 'アカウントの設定完了通知', $content);

//招待リンクを消す
unlink(DATAROOT . 'mail/invitation/' . $_POST["towhom"] . '.txt');

//主催者に事後報告
$promoter = id_state('p');
$nicknamep = nickname($promoter[0]);

$content = "$nicknamep 様

$eventname のポータルサイトに、共同運営者 $nickname 様がアカウントを登録しましたのでお知らせ致します。

　登録時のIPアドレス：$IP
　登録日時　　　　　：$date
";

//内部関数で送信
sendmail(email($promoter[0]), '共同運営者が登録されました', $content);

//ログイン状態に
session_start();
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    $_SESSION['userid'] = $userid;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['state'] = $state;
    $_SESSION['admin'] = 0;
    $_SESSION['situation'] = 'registered';
    $_SESSION['expire'] = time() + (30 * 60);
    $_SESSION['useragent'] = $browser;
    $_SESSION['authinfo'] = 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $userid;
}

redirect("../../mypage/index.php");
