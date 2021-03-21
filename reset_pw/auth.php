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

    if (json_decode($response)->success == FALSE) die_error_html("認証エラー", '<p>reCAPTCHA認証に失敗しました。</p><p>5秒後にログインページに自動的に移動します。<br><a href="index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>', '<meta http-equiv="refresh" content="5; URL=\'index.php\'" />');
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
    die_error_html('認証エラー', '<p>ユーザーID、ニックネーム、メールアドレスのいずれかもしくは全てに誤りがあるため、メールを送信出来ませんでした。</p><p>5秒後に再発行ページに自動的に移動します。<br><a href="index.php">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>', '<meta http-equiv="refresh" content="5; URL=\'index.php\'" />');
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
    if ($filedata["expire"] >= time()) die_error_html('既にURLを送信しています', '<p>ご指定頂いたユーザーIDのパスワード再発行URLは、1時間以内に送信されています。メールをご確認下さい。<br>
メールを誤って削除してしまった場合は、しばらく待ってから、再度パスワード再発行URLを作成して下さい。</p>');
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

$titlepart = "パスワード再発行";
require_once(PAGEROOT . 'guest_header.php');

?>
<h1>パスワード再発行 - メール送信完了</h1>
<div class="border system-border-spacer">
<p>お使いのアカウントの連絡メールアドレス宛に、パスワード再発行用URLが記載されたメールを送信しました。<br>
メールを確認し、指示に従って下さい。</p>
<p>※この画面は閉じても構いません。</p>
</div>
<?php
require_once(PAGEROOT . 'guest_footer.php');
