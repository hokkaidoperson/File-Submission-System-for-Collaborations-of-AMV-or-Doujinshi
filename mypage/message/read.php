<?php
require_once('../../set.php');
session_start();
$titlepart = 'メッセージ詳細';
require_once(PAGEROOT . 'mypage_header.php');


//メッセージID
$id = $_GET["name"];

if ($id == "") die_mypage('パラメーターエラー');

//メッセージの閲覧権があるか確認
$allowed = FALSE;
list($from, $time) = explode('_', $id);
$filename = DATAROOT . 'messages/' . $id . '.txt';
if (!file_exists($filename)) die_mypage('このメッセージは存在しません。URLが誤っているか、送信者がメッセージを削除した可能性があります。');

//自分が送ったやつ？
if ($from == $_SESSION["userid"]) $allowed = TRUE;
//自分へのメッセージなら見せる
$data = json_decode(file_get_contents($filename), true);
if (isset($data[$_SESSION["userid"]])) $allowed = TRUE;

if (!$allowed) die_mypage('このメッセージの閲覧権限がありません。');

//既読の処理
if (isset($data[$_SESSION["userid"]]) and $data[$_SESSION["userid"]] == 0) {
    $data[$_SESSION["userid"]] = 1;
    $filedatajson = json_encode($data);
    if (file_put_contents(DATAROOT . 'messages/' . $id . '.txt', $filedatajson) === FALSE) die('メッセージデータの書き込みに失敗しました。');
}

if (blackuser($from)) echo '<p><div class="border border-danger" style="padding:10px;">
このメッセージの送信者は凍結されています。
</div></p>';
?>

<h1>メッセージ詳細</h1>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>送信者</th><td><?php echo htmlspecialchars(nickname($from)); 
if (state($from) == "p") echo ' <span class="badge badge-success text-wrap" style="width: 3rem;">
主催者
</span>';
else if (state($from) == "c") echo ' <span class="badge badge-warning text-wrap" style="width: 5rem;">
共同運営者
</span>';
if (id_admin() == $from) echo ' <span class="badge badge-danger text-wrap" style="width: 7rem;">
システム管理者
</span>';?></td>
</tr>
<?php if ($from == $_SESSION["userid"]) { ?>
<tr>
<th>送信先・既読状態</th><td><?php
foreach ($data as $userid => $read) {
    if ($userid == "_subject") continue;
    if ($userid == "_replyof") continue;
    if ($userid == "_content") continue;
    echo htmlspecialchars(nickname($userid));
    if (blackuser($userid)) echo '<span class="text-danger">（凍結ユーザー）</span>';
    if (state($userid) == "p") echo ' <span class="badge badge-success text-wrap" style="width: 3rem;">
主催者
</span>';
    else if (state($userid) == "c") echo ' <span class="badge badge-warning text-wrap" style="width: 5rem;">
共同運営者
</span>';
    if (id_admin() == $userid) echo ' <span class="badge badge-danger text-wrap" style="width: 7rem;">
システム管理者
</span>';
    if ($read) echo '：<span class="text-success">既読</span>';
    else echo '：未読';
    echo '<br>';
}
?></td>
</tr>
<?php } ?>
<tr>
<th>送信日時</th><td><?php echo date('Y年n月j日G時i分s秒', $time); ?></td>
</tr>
<tr>
<th>件名</th><td><?php echo htmlspecialchars($data["_subject"]); ?></td>
</tr>
<?php
if (isset($data["_replyof"])) {
    if (file_exists(DATAROOT . 'messages/' . $data["_replyof"] . '.txt')) {
        $replyofdata = json_decode(file_get_contents(DATAROOT . 'messages/' . $data["_replyof"] . '.txt'), true);
        echo '<tr><td colspan="2">※このメッセージは、「<a href="read.php?name=' . $data["_replyof"] .'">' . htmlspecialchars($replyofdata["_subject"]) . '</a>」への返信です。</td></tr>';
    } else echo '<tr><td colspan="2">※このメッセージは、削除されたメッセージへの返信です。</td></tr>';
}
?>
<tr><td colspan="2"><?php
$log = str_replace('&amp;', '&', htmlspecialchars($data["_content"]));
$log = preg_replace('{https?://[\w/:%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank">$0</a>', $log);
$log = str_replace(array("\r\n", "\r", "\n"), "\n", $log);
echo str_replace("\n", "<br>", $log);
?></td></tr>
</table>
</div>
<?php if ($from != $_SESSION["userid"]) {
?>

<h2>このメッセージへ返信する</h2>
<form name="form" action="reply.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="replyof" value="<?php echo $id; ?>">
<div class="border border-primary" style="padding:10px;">
<div class="form-group">
<label for="msg_subject">件名（50文字以内）</label>
<input type="text" name="msg_subject" class="form-control" id="msg_subject" value="Re: <?php echo htmlspecialchars($data["_subject"]); ?>">
<font size="2">※必要に応じて変更して下さい。<br>
※空欄の場合、メッセージ本文の最初の30文字が件名に利用されます（30文字を超えた分は省略されます）。</font>
</div>
<div class="form-group">
<label for="msg_content">メッセージ本文（1000文字以内）</label>
<textarea id="msg_content" name="msg_content" rows="4" cols="80" class="form-control"></textarea>
<font size="2">※改行は反映されます（この入力欄で改行すると実際のメッセージでも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">送信</button>
</div>
</form>
<script language="JavaScript" type="text/javascript">
<!--
function check(){

  problem = 0;

  probsub = 0;
  probmsg = 0;

//文字数
  if(document.form.msg_subject.value === ""){
  } else if(document.form.msg_subject.value.length > 50){
    problem = 1;
    probsub = 1;
  }

//文字数
  if(document.form.msg_content.value === ""){
    problem = 1;
    probmsg = 1;
  } else if(document.form.msg_content.value.length > 1000){
    problem = 1;
    probmsg = 2;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probsub == 1) {
    alert( "【件名】\n文字数が多すぎます（現在" + document.form.msg_subject.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  if ( probmsg == 1) {
    alert( "【メッセージ本文】\n入力されていません。" );
  }
  if ( probmsg == 2) {
    alert( "【メッセージ本文】\n文字数が多すぎます（現在" + document.form.msg_content.value.length + "文字）。1000文字以内に抑えて下さい。" );
  }

  return false;
}

  if(window.confirm('現在の入力内容を送信します。よろしいですか？')){
  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";

    return true;
  } else{
    return false;
  }
}
// -->
</script>
<?php
    die_mypage();
} ?>
<h2>メッセージ削除</h2>
<p>メッセージを削除すると、自分だけでなく、宛先のユーザーからも閲覧出来なくなります。</p>
<p><a href="delete.php?name=<?php echo $id; ?>" class="btn btn-danger" role="button" onclick="return window.confirm('このメッセージを削除します。この操作は取り消せませんが、よろしいですか？')">このメッセージを削除する</a></p>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
