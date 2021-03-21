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
$userid = array();
$nickname = array();

if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;
else {
    $email = $_POST["email"];

    $conflict = FALSE;

    //登録済みの中から探す
    foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents_repeat($filename), true);
        if ($filedata["email"] == $email) {
            $conflict = TRUE;
            $userid[] = basename($filename, ".txt");
            $nickname[] = $filedata["nickname"];
        }
    }

    if (!$conflict) $invalid = TRUE;
}

//認証失敗の時
if ($invalid) {
    die('認証エラー');
}

if (!file_exists(DATAROOT . 'mail/search_id/')) {
    if (!mkdir(DATAROOT . 'mail/search_id/', 0777, true)) die('ディレクトリの作成に失敗しました。');
}

$fileplace = DATAROOT . 'mail/search_id/' . md5($_POST["email"]) . '.txt';

//24時間以内に送信してるんならはじく
if (file_exists($fileplace)) {
    $filedata = json_decode(file_get_contents_repeat($fileplace), true);
    if ($filedata["expire"] >= time()) die_error_html('メールを送信出来ません', '<p>ご指定頂いたメールアドレスに紐づくユーザー情報は、24時間以内に送信されています。メールをご確認下さい。<br>
無暗に大量のメールが送信されるのを防ぐため、この機能でアカウント情報を再送出来るのは、1アカウントにつき、24時間に1回とさせて頂いております。<br>
メールを誤って削除してしまった場合は、しばらく待ってから、再度アカウント情報の再送を行って下さい。</p>');
}


//24時間後に有効期限切れ
$expire = time() + (24 * 60 * 60);

//ファイル内容
$filedata = array(
    "expire" => $expire
);

if (json_pack($fileplace, $filedata) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//メール本文形成
$pageurl = $siteurl . "reset_pw/index.php";
$text = "【ユーザーID】\n{$userid[0]}\n\n【ニックネーム】\n{$nickname[0]}";
$i = 1;
while (1) {
    if (!isset($userid[$i])) break;
    $dsp = $i + 1;
    $text .= "\n\n【ユーザーID（{$dsp}つ目のアカウント）】\n{$userid[$i]}\n\n【ニックネーム（{$dsp}つ目のアカウント）】\n{$nickname[$i]}";
    $i++;
}
$content = "{$nickname[0]} 様

$eventname のポータルサイトで、アカウント情報再送のリクエストがありました。
パスワード再発行に必要なアカウント情報は以下の通りです。

$text

※パスワードの再発行はこちらから行えます。
　$pageurl
";
//内部関数で送信
sendmail($email, 'アカウント情報再送', $content);

$titlepart = "ユーザーID・ニックネーム再送信";
require_once(PAGEROOT . 'guest_header.php');

?>

<h1>ユーザーID・ニックネーム再送信 - メール送信完了</h1>
<div class="border system-border-spacer">
<p>お使いのアカウントの連絡メールアドレス宛に、アカウント情報が記載されたメールを送信しました。<br>
メールをご確認下さい。</p>
<p><a href="<?php echo $pageurl; ?>">パスワードの再発行はこちらから行えます。</a></p>
</div>
<?php
require_once(PAGEROOT . 'guest_footer.php');
