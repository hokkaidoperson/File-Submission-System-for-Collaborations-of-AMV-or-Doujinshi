<?php
require_once('../set.php');

$deny = FALSE;

$name = basename($_GET["name"]);
$userid = basename($_GET["userid"]);
$sectok = basename($_GET["sectok"]);

if ($name == "" or $userid == "" or $sectok == "") die("パラメーターエラー");
if (!user_exists($userid)) die();

if (file_exists(DATAROOT . 'messages/' . $name . '.txt')) {
    $filedata = json_decode(file_get_contents_repeat(DATAROOT . 'messages/' . $name . '.txt'), true);
    if (!isset($filedata["sectok_$userid"]) or $filedata["sectok_$userid"] !== $sectok) $deny = TRUE;
} else $deny = TRUE;

if ($deny) die_error_html('認証エラー', '<p>認証に失敗しました。以下が原因として考えられます。<br>
1. 送信者がメッセージを削除した。<br>
2. メッセージの閲覧権が無い。<br>
3. 開封確認URLのうち一部分だけが切り取られ、サーバー側で正常に認識されなかった。</p>');

//既読の処理
if (isset($filedata[$userid]) and $filedata[$userid] == 0) {
    $filedata[$userid] = 1;
    $filedatajson = json_encode($filedata);
    if (file_put_contents_repeat(DATAROOT . 'messages/' . $name . '.txt', $filedatajson) === FALSE) die('メッセージデータの書き込みに失敗しました。');
    $print = "メッセージ「" . hsc($filedata["_subject"]) . "」に既読を付けました。<br>ブラウザを閉じても構いません。";
} else $print = "メッセージ「" . hsc($filedata["_subject"]) . "」には既に既読が付いています。<br>ブラウザを閉じても構いません。";

$titlepart = 'メッセージ開封確認';
require_once(PAGEROOT . 'guest_header.php');
?>

<h1>メッセージ開封確認</h1>
<div class="border system-border-spacer">
<?php echo $print; ?>
</div>

<?php
require_once(PAGEROOT . 'guest_footer.php');
