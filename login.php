<?php
require_once('set.php');
setup_session();

//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("./mypage/index.php");
}

csrf_prevention_validate();

//ロボット認証チェック 参考　https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents_repeat(DATAROOT . 'rec.txt'), true);

if ($recdata["site"] != "" and $recdata["sec"] != "" and extension_loaded('curl')) {
    $secret_key = $recdata["sec"];
    $token = $_POST['g-recaptcha-response'];
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => $secret_key,
            'response' => $token
        ]),
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (json_decode($response)->success == FALSE) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="5; URL=\'index.php\'" />
<title>認証エラー</title>
</head>
<body>
<p>reCAPTCHA認証に失敗しました。</p>

<p>5秒後にログインページに自動的に移動します。<br>
<a href="index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>
</body>
</html>');
}


$IP = getenv("REMOTE_ADDR");
$browser = $_SERVER['HTTP_USER_AGENT'];

$invalid = FALSE;

$userid = basename($_POST["userid"]);

if (!file_exists(DATAROOT . 'users/' . $userid . '.txt')) $invalid = TRUE;
else {
    $userdata = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $userid . '.txt'), true);
    $email = $userdata["email"];
    $state = $userdata["state"];
    $nickname = $userdata["nickname"];
    if (!password_verify($_POST["password"], $userdata["pwhash"])) $invalid = TRUE;
    if (isset($userdata["deleted"]) and $userdata["deleted"]) $invalid = TRUE;
}

//認証失敗の時
if ($invalid) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="5; URL=\'index.php\'" />
<title>認証エラー</title>
</head>
<body>
<p>ユーザーIDもしくはパスワードに誤りがあるため、ログイン出来ませんでした。</p>

<p>5秒後にログインページに自動的に移動します。<br>
<a href="index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>
</body>
</html>');
}

//ブラックリスト？
if (blackuser($userid)) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="10; URL=\'index.php\'" />
<title>アカウントが凍結されています</title>
</head>
<body>
<p>現在、このアカウントは凍結されているため、ログイン出来ませんでした。<br>
凍結の理由や、不服申し立てなどについては、「アカウント凍結のお知らせ」という件名のメールをご参照願います。</p>

<p>10秒後にログインページに自動的に移動します。<br>
<a href="index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>
</body>
</html>');
}

//↓セッションハイジャック対策
session_regenerate_id(true);

//認証成功　ログイン情報の格納
$_SESSION['userid'] = $userid;
$_SESSION['nickname'] = $nickname;
$_SESSION['email'] = $email;
$_SESSION['state'] = $state;
$_SESSION['admin'] = $userdata["admin"];
$_SESSION['expire'] = time() + (30 * 60);
$_SESSION['useragent'] = $browser;
$_SESSION['authinfo'] = 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $userid;

//ハッシュ化パスワードを更新するかどうか
if (password_needs_rehash($userdata["pwhash"], PASSWORD_DEFAULT)) {
    $userdata["pwhash"] = password_hash($_POST["password"], PASSWORD_DEFAULT);
    if (json_pack(DATAROOT . 'users/' . $userid . '.txt', $userdata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
}

//セキュリティ通知するかどうか
if (($userdata["lastip"] != $IP) or ($userdata["lastbr"] != $browser)) {
    //メール本文形成
    $date = date('Y/m/d H:i:s');

    $content = "$nickname 様

$eventname のポータルサイトに、あなたのアカウントでログインがありました。

この通知は、$eventname のポータルサイトで、ログイン時のIPアドレスとブラウザの組み合わせが
前回と今回で異なっている場合に、不正ログインにいち早く気付くためにお送りしているものです。


【あなた自身の操作でログインした場合】
ご確認ありがとうございます。引き続きポータルサイトをご利用下さい。
このメールは削除しても構いません。


【ログインした覚えが無い場合】
第三者があなたのアカウントに不正ログインした可能性があります。直ちに、パスワードの変更を行って下さい。

あなたの操作でログインした後、「アカウント情報編集」→「パスワード変更」の順に選択して下さい。
また、その後、あなたが提出した内容が改ざんされていないか、もしくはファイルが勝手に新規提出されていないかなどをご確認下さい。
提出内容が編集されていたり、新規提出があったりする場合は、メールで通知が届いているはずです。


【今回のログイン情報】
　ユーザーID　　　　　　：$userid

　ログイン元IPアドレス　：$IP
　（前回ログイン時　　　：{$userdata["lastip"]}）
　ログイン元ブラウザ情報：$browser
　（前回ログイン時　　　：{$userdata["lastbr"]}）

　ログイン日時　　　　　：$date
";

    //内部関数で送信
    sendmail($email, 'セキュリティ通知', $content);

    //新しいログイン情報
    $userdata["lastip"] = $IP;
    $userdata["lastbr"] = $browser;

    if (json_pack(DATAROOT . 'users/' . $userid . '.txt', $userdata) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
}

if (isset($_SESSION['guest_redirto'])) $redirto = $_SESSION['guest_redirto'];
else $redirto = 'mypage/index.php';

register_alert("ログインしました。", "success");
register_alert("当サイトでは、30分以上サーバーへの接続が無い場合は、セキュリティの観点から自動的にログアウトします。<br>特に、情報入力画面など、同じページにしばらく留まり続ける場面ではご注意願います。", "warning");

redirect("./$redirto");
