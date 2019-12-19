<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/exam/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$accessok = 'none';

//主催・共同運営
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') $accessok = 'ok';

if ($accessok == 'none') die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
');

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");
if (!file_exists(DATAROOT . 'exam_edit/' . $_POST["subject"] . '.txt')) die('ファイルが存在しません。');
list($author, $id, $editid) = explode('_', $_POST["subject"]);
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

if($_POST["add"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["add"]) > 500) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

//投票の回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $_POST["subject"] . '.txt'), true);
if ($answerdata["_state"] != 1) die();

if (!isset($answerdata[$_SESSION["userid"]])) die();
else if ($answerdata[$_SESSION["userid"]]["opinion"] == -1) die();

//議論ログ
if (!file_exists(DATAROOT . 'exam_edit_discuss/' . $author . '_' . $id . '_' . $editid . '.txt')) die('ファイルが存在しません。');
$discussdata = json_decode(file_get_contents(DATAROOT . 'exam_edit_discuss/' . $author . '_' . $id . '_' . $editid . '.txt'), true);

//入力内容を読み込む（作品名はなんじゃろな）
$formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);


//ログにデータ追加
$discussdata["comments"][$_SESSION["userid"] . "_" . time()] = $_POST["add"];

//既読を未読にする＆通知飛ばす
$authornick = nickname($author);
$pageurl = $siteurl . 'mypage/exam/discuss_edit.php?author=' . $author . '&id=' . $id . '&edit=' . $editid;
foreach ($answerdata as $key => $data) {
    if (strpos($key, '_') !== FALSE) continue;
    if (!isset($discussdata["read"][$key])) die();
    if ($key == $_SESSION["userid"]) continue;
    if ($discussdata["read"][$key] == 1) {
        $discussdata["read"][$key] = 0;
        $nickname = nickname($key);
        $content = "$nickname 様

$authornick 様の作品「" . $formdata["title"] . "」の項目変更に関する議論について、コメントが追加されました。
簡易チャットページを再確認し、必要に応じてコメントして下さい。

※この通知は、あなたが簡易チャットページを最後に確認した後にコメントが追加された際に、それを通知するためのものです。
　あなたが簡易チャットページを再確認するまでは、コメントが追加されても通知されません。


　簡易チャットページ：$pageurl
";
        sendmail(email($key), 'コメント追加のお知らせ（「' . $formdata["title"] . '」の内容変更に関する議論）', $content);
    }
}

$filedatajson = json_encode($discussdata);
if (file_put_contents(DATAROOT . 'exam_edit_discuss/' . $_POST["subject"] . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');

$_SESSION['situation'] = 'exam_discuss_added';

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='discuss_edit.php?author=<?php echo $author; ?>&id=<?php echo $id; ?>&edit=<?php echo $editid; ?>'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
