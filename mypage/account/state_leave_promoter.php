<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


csrf_prevention_validate();

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if (!user_exists($_POST["userid"]) or blackuser($_POST["userid"])) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$userdata = id_array($_POST["userid"]);
$email = $userdata["email"];

if (!file_exists(DATAROOT . 'mail/state/')) {
    if (!mkdir(DATAROOT . 'mail/state/', 0777, true)) die('ディレクトリの作成に失敗しました。');
}

$fileplace = DATAROOT . 'mail/state/promoter.txt';

//2日後に有効期限切れ
$expire = time() + (2 * 24 * 60 * 60);

//ファイル内容
$filedata = array(
    "new" => $_POST["userid"],
    "expire" => $expire
);

$filedatajson = json_encode($filedata);

if (file_put_contents_repeat($fileplace, $filedatajson) === FALSE) die('メール関連のデータの書き込みに失敗しました。');


//メール本文形成
$expireformat = date('Y年n月j日G時i分s秒', $expire);
$pageurl = $siteurl . 'mypage/state_special/prom_unit.php';
$nickname = $userdata["nickname"];
$promnick = nickname($_SESSION["userid"]);
$content = "$nickname 様

$promnick 様が、あなたを $eventname の新たな主催者に任命しました。
これについて{$promnick}様から事情を聞いており、主催者になってもよい場合は、以下の手続用URLから手続して下さい。
もし事情を聞いていない場合は、{$promnick}様に直接お問い合わせ下さい。

　手続用URL　　　　：$pageurl
　上記URLの有効期限：$expireformat

※URLは一部だけ切り取られると認識されません。
　改行されているなどの理由により正常にURLをクリック出来ない場合は、URLを直接コピーしてブラウザに貼り付けて下さい。
※有効期限は24時間表記です。
※手続前に有効期限が切れてしまった場合は、{$promnick}様にURLの再送を依頼して下さい。
";
//内部関数で送信
sendmail($email, '主催者交代のご案内', $content);

register_alert("メールを送信しました。手続き完了までしばらくお待ち下さい。", "success");

redirect("./index.php");
