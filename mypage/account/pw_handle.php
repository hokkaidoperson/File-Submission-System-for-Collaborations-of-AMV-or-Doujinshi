<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

//今のパスワードで認証
$userdata = json_decode(file_get_contents(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt'), true);
if (!password_verify($_POST["oldpassword"], $userdata["pwhash"])) die('<!DOCTYPE html>
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

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン・文字数・一致確認
if($_POST["password"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["password"]) > 72) $invalid = TRUE;
else if(mb_strlen($_POST["password"]) < 8) $invalid = TRUE;
else if($_POST["password"] != $_POST["passwordagn"]) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');


//パスワードハッシュ化
$hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$userdata["pwhash"] = $hash;

$userdatajson =  json_encode($userdata);

if (file_put_contents(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//メール本文形成
$nickname = $_SESSION['nickname'];
$date = date('Y年n月j日G時i分s秒');
$content = "$nickname 様

$eventname のポータルサイトのマイページで、パスワードが変更されました。

この通知は、$eventname のポータルサイトであなたの登録情報に変更があった際（ファイルの新規提出など）に、
それが不正ログインによる変更でないかどうか確認するためにお送りしているものです。


【あなた自身の操作で変更した場合】
ご確認ありがとうございます。引き続きポータルサイトをご利用下さい。
このメールは削除しても構いません。


【変更した覚えが無い場合】
第三者があなたのアカウントに不正ログインした可能性があります。直ちに、パスワードの変更を行って下さい。

パスワードが別のものに変えられているため、ログインページ下部の「パスワードを忘れてしまった方は
こちらから再発行して下さい。」をクリックし、そこで新しいパスワードに変更して下さい。


【変更情報】
変更日時：$date
※パスワードは、セキュリティの観点からメールに記載しておりません。
";
//内部関数で送信
sendmail($_SESSION['email'], 'パスワード変更通知', $content);


register_alert("パスワードの変更が完了しました。", "success");

redirect("./index.php");
