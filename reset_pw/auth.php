<?php
require_once('../set.php');
setup_session();
//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../mypage/index.php");
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
<title>認証エラー</title>
</head>
<body>
<p>reCAPTCHA認証に失敗しました。しばらくしてからもう一度お試し下さい。</p>
</body>
</html>');
}


$invalid = FALSE;

$userid = basename($_POST["userid"]);

if (!file_exists(DATAROOT . 'users/' . $userid . '.txt')) $invalid = TRUE;
else {
    $userdata = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . $userid . '.txt'), true);
    $email = $userdata["email"];
    $nickname = $userdata["nickname"];
    if ($email != $_POST["email"]) $invalid = TRUE;
    if ($nickname != $_POST["nickname"]) $invalid = TRUE;
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
<p>ユーザーID、ニックネーム、メールアドレスのいずれかもしくは全てに誤りがあるため、メールを送信出来ませんでした。</p>

<p>5秒後に再発行ページに自動的に移動します。<br>
<a href="index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>
</body>
</html>');
}

//認証文字列（参考：https://qiita.com/suin/items/c958bcca90262467f2c0）
$randomchar128 = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 128)), 0, 128);

if (!file_exists(DATAROOT . 'mail/reset_pw/')) {
    if (!mkdir(DATAROOT . 'mail/reset_pw/', 0777, true)) die('ディレクトリの作成に失敗しました。');
}

$fileplace = DATAROOT . 'mail/reset_pw/' . $userid . '.txt';

//もうURLを発行してるんならはじく
if (file_exists($fileplace)) {
    $filedata = json_decode(file_get_contents_repeat($fileplace), true);
    if ($filedata["expire"] >= time()) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>既にURLを送信しています</title>
</head>
<body>
<p>ご指定頂いたユーザーIDのパスワード再発行URLは、1時間以内に送信されています。メールをご確認下さい。<br>
メールを誤って削除してしまった場合は、しばらく待ってから、再度パスワード再発行URLを作成して下さい。</p>
</body>
</html>');
}


//1時間後に有効期限切れ
$expire = time() + (1 * 60 * 60);

//ファイル内容
$filedata = array(
    "sectok" => $randomchar128,
    "expire" => $expire
);

$filedatajson =  json_encode($filedata);

if (file_put_contents_repeat($fileplace, $filedatajson) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//メール本文形成
$expireformat = date('Y年n月j日G時i分s秒', $expire);
$pageurl = $siteurl . 'reset_pw/special_unit.php?userid=' . $userid . '&sectok=' . $randomchar128;
$content = "$nickname 様

$eventname のポータルサイトで、パスワード再発行のリクエストがありました。
もし、あなた自身のリクエストに相違なければ、以下のURLからパスワードの再発行を行って下さい。

　再発行用URL　　　：$pageurl
　上記URLの有効期限：$expireformat

※URLは一部だけ切り取られると認識されません。
　改行されているなどの理由により正常にURLをクリック出来ない場合は、URLを直接コピーしてブラウザに貼り付けて下さい。
※有効期限は24時間表記です。
※再発行前に有効期限が切れてしまった場合は、お手数ですがユーザーIDなどを入力する手順からやり直して下さい。
";
//内部関数で送信
sendmail($email, 'パスワード再発行用URL', $content);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php
if (META_NOFOLLOW) echo '<meta name="robots" content="noindex, nofollow, noarchive">';
?>
<link rel="stylesheet" href="../css/bootstrap.css?<?php echo urlencode(VERSION); ?>">
<link rel="stylesheet" href="../css/style.css?<?php echo urlencode(VERSION); ?>">
<title>パスワード再発行 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
<script type="text/javascript">
<!--
//Cookie判定（参考：https://qiita.com/tatsuyankmura/items/8e09cbd5ee418d35f169）
var setCookie = function(cookieName, value){
  var cookie = cookieName + "=" + value + ";";
  document.cookie = cookie;
}

var getCookie = function(cookieName){
  var l = cookieName.length + 1 ;
  var cookieAry = document.cookie.split("; ") ;
  var str = "" ;
  for(i=0; i < cookieAry.length; i++){
    if(cookieAry[i].substr(0, l) === cookieName + "="){
      str = cookieAry[i].substr(l, cookieAry[i].length) ;
      break ;
    }
  }
  return str;
}

setCookie('check_cookie', true);
var val = getCookie('check_cookie');

// -->
</script>
</head>
<body>
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
<p>上記を有効にしてもこの画面が表示される場合、ご利用のブラウザは当サイトが使用するJavascriptの機能を提供していない、もしくは充分にサポートしていない可能性がありますので、ブラウザを変えて再度お試し下さい（推奨環境のブラウザでこの画面が表示される場合、システム管理者までご連絡下さい）。</p>
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1>パスワード再発行 - メール送信完了</h1>
<div class="border system-border-spacer">
<p>お使いのアカウントの連絡メールアドレス宛に、パスワード再発行用URLが記載されたメールを送信しました。<br>
メールを確認し、指示に従って下さい。</p>
<p>※この画面は閉じても構いません。</p>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js?<?php echo urlencode(VERSION); ?>"></script>
</body>
</html>
