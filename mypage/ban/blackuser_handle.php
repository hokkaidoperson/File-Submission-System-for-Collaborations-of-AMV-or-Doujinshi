<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


csrf_prevention_validate();

$invalid = FALSE;
if ($_POST["subject"] == "") $invalid = TRUE;
if ($_POST["message_mail"] == "") {
} else if (length_with_lb($_POST["message_mail"]) > 500) $invalid = TRUE;
if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$id = $_POST["subject"];

if (!user_exists($id)) die ("ユーザーが存在しません。");

$blplace = DATAROOT . 'blackuser.txt';
if (file_exists($blplace)) $bldata = json_decode(file_get_contents_repeat($blplace), true);
else $bldata = array();

$key = array_search($id, $bldata);
if ($key !== FALSE) {
    unset($bldata[$key]);
    $rem = TRUE;
    $title = "アカウント凍結解除のお知らせ";
}
else {
    $bldata[] = $id;
    $rem = FALSE;
    $title = "アカウント凍結のお知らせ";
}

$datajson =  json_encode($bldata);
if (file_put_contents_repeat($blplace, $datajson) === FALSE) die('リストデータの書き込みに失敗しました。');

//対象者にメール
$nickname = nickname($id);
if ($_POST["message_mail"] == "") $message = "（コメント無し）";
else $message = $_POST["message_mail"];
if (!$rem) $content = "$nickname 様

$eventname のポータルサイトにて、主催者があなたのアカウントを凍結しました。
現在、あなたのアカウントでログインしても何も操作を行う事が出来ません。

もし、誤って凍結された可能性がある場合、或いはこのアカウント凍結を不服とする場合は、
主催者に直接ご相談下さい。


【主催者からのメッセージ（凍結理由など）】
$message
";
else $content = "$nickname 様

$eventname のポータルサイトにて、主催者があなたのアカウントの凍結を解除しました。
これで、あなたのアカウントでログインして各種操作を行う事が可能になりました。

この度のアカウント凍結に関して不明点などあれば、主催者に直接お尋ね下さい。


【主催者からのメッセージ（凍結解除理由など）】
$message
";
//内部関数で送信
sendmail(email($id), $title, $content);
if ($rem) register_alert("選択したユーザーのアカウントの凍結を解除しました。", "success");
else register_alert("選択したユーザーのアカウントを凍結しました。", "success");

redirect("./index.php");
