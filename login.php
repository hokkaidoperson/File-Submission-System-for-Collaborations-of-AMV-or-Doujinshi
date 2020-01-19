<?php
require_once('set.php');
session_start();
//↓セッションハイジャック対策
session_regenerate_id(true);
//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'mypage/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

//ロボット認証チェック 参考　https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents(DATAROOT . 'rec.txt'), true);

if ($recdata["site"] != "" and $recdata["sec"] != "") {
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

$userid = $_POST["userid"];

if (!file_exists(DATAROOT . 'users/' . $userid . '.txt')) $invalid = TRUE;
else {
    $userdata = json_decode(file_get_contents(DATAROOT . 'users/' . $userid . '.txt'), true);
    $email = $userdata["email"];
    $state = $userdata["state"];
    $nickname = $userdata["nickname"];
    if (!password_verify($_POST["password"], $userdata["pwhash"])) $invalid = TRUE;
    if ($userdata["deleted"]) $invalid = TRUE;
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

//if (blackip($userdata["admin"], $state)) {
//    die('<!DOCTYPE html>
//<html>
//<head>
//<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
//<meta name="viewport" content="width=device-width,initial-scale=1">
//<title>アクセスが制限されています</title>
//</head>
//<body>
//<p>現在ご利用のアクセス元（IPアドレス）からのログイン等が制限されているため、ログイン出来ませんでした。<br>
//あなた、もしくは同じアクセス元を利用する他の誰かが、イベントの運営を妨害するなどしたために主催者により制限されています。<br>
//もしそのような事をした覚えが無い場合は、以下のブロック情報を添えて主催者にご相談下さい。</p>
//<p>【ブロック情報】<br>
//IPアドレス：' . getenv("REMOTE_ADDR") . '<br>
//リモートホスト：' . gethostbyaddr(getenv("REMOTE_ADDR")) . '</p>
//<p><a href="index.php">こちらをクリックするとログインページに戻ります。</a></p>
//</body>
//</html>');
//}


//認証成功　ログイン情報の格納
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    $_SESSION['userid'] = $userid;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['state'] = $state;
    $_SESSION['admin'] = $userdata["admin"];
    $_SESSION['situation'] = 'loggedin';
    $_SESSION['expire'] = time() + (30 * 60);
    $_SESSION['useragent'] = $browser;
    $_SESSION['authinfo'] = 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $userid;
}

//セキュリティ通知するかどうか
if (($userdata["lastip"] != $IP) or ($userdata["lastbr"] != $browser)) {
  //メール本文形成
  $date = date('Y/m/d H:i:s');
  $eventname = file_get_contents(DATAROOT . 'eventname.txt');

  $content = "$nickname 様

$eventname のポータルサイトに、あなたのアカウントでログインがありました。

この通知は、$eventname のポータルサイトで、あなたが最近ログインした際のIPアドレスとブラウザの組み合わせと、
今回ログインした際のIPアドレスとブラウザの組み合わせが異なっている場合に、
不正ログインにいち早く気付くためにお送りしているものです。


【あなた自身の操作でログインした場合】
ご確認ありがとうございます。引き続きポータルサイトをご利用下さい。
このメールは削除しても構いません。


【ログインした覚えが無い場合】
第三者があなたのアカウントに不正ログインした可能性があります。直ちに、パスワードの変更を行って下さい。

あなたの操作でログインした後、「アカウント情報編集」→「パスワード変更」の順に選択して下さい。
また、その後、あなたが提出した内容が改ざんされていないか、もしくはファイルが勝手に新規提出されていないかなどをご確認下さい。
提出内容が編集されていたり、新規提出があったりする場合は、メールで通知が届いているはずです。


【今回のログイン情報】
　ユーザーID　　　　　　　：$userid

　ログイン時のIPアドレス　：$IP
　ログイン時のブラウザ情報：$browser
　ログイン日時　　　　　　：$date
";

  //内部関数で送信
  sendmail($email, 'セキュリティ通知', $content);

  //新しいログイン情報
  $userdata["lastip"] = $IP;
  $userdata["lastbr"] = $browser;
  $userdatajson =  json_encode($userdata);

  if (file_put_contents(DATAROOT . 'users/' . $userid . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

}

if (isset($_POST['redirto'])) $redirto = $_POST['redirto'];
else $redirto = 'mypage/index.php';


?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='<?php echo htmlspecialchars($redirto) ?>'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
