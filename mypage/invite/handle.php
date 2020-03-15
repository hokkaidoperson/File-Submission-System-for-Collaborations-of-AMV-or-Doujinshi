<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../index.php");
}

$accessok = 'none';

//主催者が登録されていないかつシステム管理者
if (!file_exists(DATAROOT . 'users/_promoter.txt') and $_SESSION["admin"]) $accessok = 'p';

//主催者かつフォーム設定完了済み
if ($_SESSION["state"] == 'p' and file_exists(DATAROOT . 'form/userinfo/done.txt')) $accessok = 'c';

if ($accessok == 'none') redirect("./index.php");


if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

//メールアドレス形式確認　必須・一致確認
if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;
else {
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
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$email = $_POST["email"];

if (!file_exists(DATAROOT . 'mail/invitation/')) {
    if (!mkdir(DATAROOT . 'mail/invitation/', 0777, true)) die('ディレクトリの作成に失敗しました。');
}

//送信先と送信時刻UNIXをもとにmd5で文字列生成（使用文字列がかぶっちゃった結婚するのは確実に起こらないはず）
$randomchar32 = md5($email . time());

//認証文字列（参考：https://qiita.com/suin/items/c958bcca90262467f2c0）
$randomchar128 = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 128)), 0, 128);

switch ($accessok) {
    case 'p':
        $fileplace = DATAROOT . 'mail/invitation/_promoter.txt';
        break;
    default:
        $fileplace = DATAROOT . 'mail/invitation/' . $randomchar32 . '.txt';
}

//2日後に有効期限切れ
$expire = time() + (2 * 24 * 60 * 60);

//ファイル内容
$filedata = array(
    "to" => $email,
    "sectok" => $randomchar128,
    "expire" => $expire
);

$filedatajson =  json_encode($filedata);

if (file_put_contents($fileplace, $filedatajson) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//メール本文形成
$expireformat = date('Y年n月j日G時i分s秒', $expire);
switch ($accessok) {
    case 'p':
        $pageurl = $siteurl . 'register/invitation/prom_unit.php?sectok=' . $randomchar128;
        $content = "$eventname 主催者様

$eventname のポータルサイトの設置が完了しました。
下記のURLから、主催者アカウントの登録をし、ファイル募集に必要な設定を行って下さい。

　登録用URL　　　　：$pageurl
　上記URLの有効期限：$expireformat

※URLは一部だけ切り取られると認識されません。
　改行されているなどの理由により正常にURLをクリック出来ない場合は、URLを直接コピーしてブラウザに貼り付けて下さい。
※有効期限は24時間表記です。
※登録前に有効期限が切れてしまった場合は、システム管理者にURLの再送を依頼して下さい。

また、ポータルサイトのトップページ（ログイン画面）のURLは
　$siteurl
です。募集の際は、参加者をこちらのURLに誘導して下さい。
";
        //内部関数で送信
        sendmail($email, '主催者アカウントの登録案内', $content);
        break;
    default:
        $pageurl = $siteurl . 'register/invitation/co_unit.php?towhom=' . $randomchar32 . '&sectok=' . $randomchar128;
        $content = "$eventname 共同運営者様

$eventname のポータルサイトの設置が完了しました。
下記のURLから、共同運営者アカウントの登録をし、イベントの運営に合流して下さい。

　登録用URL　　　　：$pageurl
　上記URLの有効期限：$expireformat

※URLは一部だけ切り取られると認識されません。
　改行されているなどの理由により正常にURLをクリック出来ない場合は、URLを直接コピーしてブラウザに貼り付けて下さい。
※有効期限は24時間表記です。
※登録前に有効期限が切れてしまった場合は、主催者にURLの再送を依頼して下さい。
";
        //内部関数で送信
        sendmail($email, '共同運営者アカウントの登録案内', $content);

}

$_SESSION['situation'] = 'invite_sent';

redirect("./index.php");
