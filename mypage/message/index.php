<?php
require_once('../../set.php');
session_start();
$titlepart = 'メッセージ機能';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'message_sent') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
メッセージを送信しました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'message_deleted') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
メッセージを削除しました。
</div>';
    $_SESSION["situation"] = '';
}

//受信BOX
$inbox = array();
//送信BOX
$outbox = array();

foreach(glob(DATAROOT . 'messages/*.txt') as $filename) {
    $id = basename($filename, '.txt');
    list($from, $time) = explode('_', $id);

    //自分が送ったやつ？
    if ($from == $_SESSION["userid"]) {
        $outbox[$id] = json_decode(file_get_contents($filename), true);
        continue;
    }

    //自分へのメッセージなら見せる
    $inbox[$id] = json_decode(file_get_contents($filename), true);
    if (!isset($inbox[$id][$_SESSION["userid"]])) unset($inbox[$id]);
}

//日付順に並べ替える
uksort($inbox, "msg_callback_fnc");
uksort($outbox, "msg_callback_fnc");

?>
<h1>メッセージ機能</h1>
<p>ここでは、他ユーザーからあなたに送信されたメッセージを受信BOXに、あなたが他ユーザーに送信したメッセージを送信BOXに表示しています。</p>
<h2>メッセージ新規作成</h2>
<?php
if ($_SESSION["state"] == 'p') echo '<p><a href="write.php">宛先を選んでメッセージを送信する</a></p>
<p><a href="write_simultaneously.php">全員にメッセージを一斉送信する</a></p>';
if ($_SESSION["state"] == 'c') echo '<p><a href="write.php">宛先を選んでメッセージを送信する</a></p>';
if ($_SESSION["state"] == 'g') echo '<p><a href="write.php">主催者にメッセージを送信する</a></p>';
if ($_SESSION["state"] == 'o') echo '<p><a href="write.php">主催者にメッセージを送信する</a></p>';
if ($_SESSION["admin"] and $_SESSION["state"] != 'p') echo '<p><a href="write_simultaneously.php">全員にメッセージを一斉送信する（サーバーメンテナンスのお知らせ時など）</a></p>';
?>

<h2>受信BOX</h2>
<p>※未読メッセージは<span class="text-danger table-primary">青背景＆赤文字の件名</span>で強調されています。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th width="25%">送信者</th><th width="25%">送信日時</th><th width="50%">件名（クリックすると内容を表示します）</th>
</tr>
<?php
foreach ($inbox as $id => $array) {
    list($from, $time) = explode('_', $id);
    $namepart = htmlspecialchars(nickname($from));
    if (blackuser($from)) $namepart .= '<span class="text-danger">（凍結ユーザー）</span>';
    if (state($from) == "p") $namepart .= ' <span class="badge badge-success text-wrap">
主催者
</span>';
    else if (state($from) == "c") $namepart .= ' <span class="badge badge-warning text-wrap">
共同運営者
</span>';
    if (id_admin() == $from) $namepart .= ' <span class="badge badge-danger text-wrap">
システム管理者
</span>';
    if ($array[$_SESSION["userid"]] == "0") echo '<tr class="table-primary">';
    else echo "<tr>\n";
    echo "<td>" . $namepart . "</td>";
    echo "<td>" . date('Y年n月j日G時i分s秒', $time) . "</td>";
    if ($array[$_SESSION["userid"]] == "0") echo '<td><a href="read.php?name=' . $id .'" class="text-danger">' . htmlspecialchars($array["_subject"]) . '</a></td>';
    else echo '<td><a href="read.php?name=' . $id .'">' . htmlspecialchars($array["_subject"]) . '</a></td>';
    echo "</tr>\n";
}
if ($inbox == array()) echo '<tr><td colspan="3">現在、表示出来るメッセージはありません。</td></tr>';
?>
</table>
</div>
<h2>送信BOX</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th width="25%">送信相手</th><th width="25%">送信日時</th><th width="50%">件名（クリックすると内容を表示します）</th>
</tr>
<?php
foreach ($outbox as $id => $array) {
    list($from, $time) = explode('_', $id);
    $to = array();
    foreach ($array as $key => $dummy) {
        if ($key == "_subject") continue;
        if ($key == "_replyof") continue;
        if ($key == "_content") continue;
        if (strpos($key, "sectok_") !== FALSE) continue;
        $to[] = $key;
    }
    $tonickname = array();
    $i = 0;
    foreach ($to as $userid) {
        if ($i > 2) {
            $tonickname[$i] = '他' . count($to) - 3 . '名';
        }
        $tonickname[$i] = htmlspecialchars(nickname($userid));
        if (blackuser($userid)) $tonickname[$i] .= '<span class="text-danger">（凍結ユーザー）</span>';
        if (state($userid) == "p") $tonickname[$i] .= ' <span class="badge badge-success text-wrap">
主催者
</span>';
        else if (state($userid) == "c") $tonickname[$i] .= ' <span class="badge badge-warning text-wrap">
共同運営者
</span>';
        if (id_admin() == $userid) $tonickname[$i] .= ' <span class="badge badge-danger text-wrap">
システム管理者
</span>';
        $i++;
    }
    echo "<tr>\n";
    echo "<td>" . implode("<br>", $tonickname) . "</td>";
    echo "<td>" . date('Y年n月j日G時i分s秒', $time) . "</td>";
    echo '<td><a href="read.php?name=' . $id .'">' . htmlspecialchars($array["_subject"]) . '</a></td>';
    echo "</tr>\n";
}
if ($outbox == array()) echo '<tr><td colspan="3">現在、表示出来るメッセージはありません。</td></tr>';
?>
</table>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
