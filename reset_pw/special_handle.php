<?php
require_once('../set.php');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン・文字数・一致確認
if($_POST["password"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["password"]) > 72) $invalid = TRUE;
else if(mb_strlen($_POST["password"]) < 8) $invalid = TRUE;
else if($_POST["password"] != $_POST["passwordagn"]) $invalid = TRUE;

//sectokをもっかいチェック
$fileplace = DATAROOT . 'mail/reset_pw/' . basename($_POST["userid"]) . '.txt';

if (file_exists($fileplace)) {
    $filedata = json_decode(file_get_contents_repeat($fileplace), true);
    if ($filedata["sectok"] !== $_POST["sectok"]) $invalid = TRUE;
} else $invalid = TRUE;


if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//パスワードハッシュ化
$hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$userdata = json_decode(file_get_contents_repeat(DATAROOT . 'users/' . basename($_POST["userid"]) . '.txt'), true);
$userdata["pwhash"] = $hash;

$userdatajson =  json_encode($userdata);

if (file_put_contents_repeat(DATAROOT . 'users/' . basename($_POST["userid"]) . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');


//リンクを消す
unlink($fileplace);

$titlepart = "パスワード再発行完了";
require_once(PAGEROOT . 'guest_header.php');

?>
<h1>パスワード再発行完了</h1>
<div class="border system-border-spacer">
<p>パスワードの再発行が完了しました。新しいパスワードでログインして下さい。</p>
<p><a href="../index.php">ログインページへ</a></p>
</div>

<?php
require_once(PAGEROOT . 'guest_footer.php');
