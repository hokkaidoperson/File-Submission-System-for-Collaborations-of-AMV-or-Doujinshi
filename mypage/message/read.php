<?php
require_once('../../set.php');
setup_session();
$titlepart = 'メッセージ詳細';
require_once(PAGEROOT . 'mypage_header.php');


//メッセージID
$id = basename($_GET["name"]);

if ($id == "") die_mypage('パラメーターエラー');

//メッセージの閲覧権があるか確認
$allowed = FALSE;
list($from, $time) = explode('_', $id);
$filename = DATAROOT . 'messages/' . $id . '.txt';
if (!file_exists($filename)) die_mypage('このメッセージは存在しません。URLが誤っているか、送信者がメッセージを削除した可能性があります。');

//自分が送ったやつ？
if ($from == $_SESSION["userid"]) $allowed = TRUE;
//自分へのメッセージなら見せる
$data = json_decode(file_get_contents_repeat($filename), true);
if (isset($data[$_SESSION["userid"]])) $allowed = TRUE;

if (!$allowed) die_mypage('このメッセージの閲覧権限がありません。');

//既読の処理
if (isset($data[$_SESSION["userid"]]) and $data[$_SESSION["userid"]] == 0) {
    $data[$_SESSION["userid"]] = 1;
    $filedatajson = json_encode($data);
    if (file_put_contents_repeat(DATAROOT . 'messages/' . $id . '.txt', $filedatajson) === FALSE) die('メッセージデータの書き込みに失敗しました。');
}

if (blackuser($from)) echo '<div class="border border-danger system-border-spacer">
このメッセージの送信者は凍結されています。
</div>';
?>

<h1>メッセージ詳細</h1>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th width="20%">詳細情報</th>
<td width="80%" class="system-lastp-nospacer">
<p><b>送信者：</b><br>
<?php echo hsc(nickname($from)); 
if (state($from) == "p") echo ' <span class="badge badge-success text-wrap">
主催者
</span>';
else if (state($from) == "c") echo ' <span class="badge badge-warning text-wrap">
共同運営者
</span>';
if (id_admin() == $from) echo ' <span class="badge badge-danger text-wrap">
システム管理者
</span>';?>
</p>
<?php if ($from == $_SESSION["userid"]) { ?>
<p><b>送信先・既読状態：</b><br>
<?php foreach ($data as $userid => $read) {
    if ($userid == "_subject") continue;
    if ($userid == "_replyof") continue;
    if ($userid == "_content") continue;
    if (strpos($userid, "sectok_") !== FALSE) continue;
    echo hsc(nickname($userid));
    if (blackuser($userid)) echo '<span class="text-danger">（凍結ユーザー）</span>';
    if (state($userid) == "p") echo ' <span class="badge badge-success text-wrap">
主催者
</span>';
    else if (state($userid) == "c") echo ' <span class="badge badge-warning text-wrap">
共同運営者
</span>';
    if (id_admin() == $userid) echo ' <span class="badge badge-danger text-wrap">
システム管理者
</span>';
    if ($read) echo '：<span class="text-success">既読</span>';
    else echo '：未読';
    echo '<br>';
}?>
</p>
<?php } ?>
<p><b>送信日時：</b><br>
<?php echo date('Y年n月j日G時i分s秒', $time); ?>
</p>
<p><b>件名：</b><br><?php echo hsc($data["_subject"]); ?></p>
<?php
if (isset($data["_replyof"])) {
    if (file_exists(DATAROOT . 'messages/' . $data["_replyof"] . '.txt')) {
        $replyofdata = json_decode(file_get_contents_repeat(DATAROOT . 'messages/' . $data["_replyof"] . '.txt'), true);
        echo '<p>※このメッセージは、「<a href="read.php?name=' . $data["_replyof"] .'">' . hsc($replyofdata["_subject"]) . '</a>」への返信です。</p>';
    } else echo '<p>※このメッセージは、削除されたメッセージへの返信です。</p>';
}
?>
</td>
</tr>
<tr><th>本文</th><td><?php
$log = hsc($data["_content"]);
$log = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $log);
$log = str_replace(array("\r\n", "\r", "\n"), "\n", $log);
echo str_replace("\n", "<br>", $log);
?></td></tr>
</table>
</div>
<?php if ($from != $_SESSION["userid"]) {
?>

<h2>このメッセージへ返信する</h2>
<form name="form" action="reply.php" method="post" onSubmit="return check()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="replyof" value="<?php echo $id; ?>">
<div class="border border-primary system-border-spacer">
<div class="form-group">
<label for="msg_subject">件名（50文字以内）</label>
<input type="text" name="msg_subject" class="form-control" id="msg_subject" value="Re: <?php echo hsc($data["_subject"]); ?>" onkeyup="ShowLength(value, &quot;subject-counter&quot;);" onBlur="check_individual(&quot;subject&quot;);">
<div id="subject-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="subject-errortext" class="system-form-error"></div>
<small class="form-text">※必要に応じて変更して下さい。<br>
※空欄の場合、メッセージ本文の最初の30文字が件名に利用されます（30文字を超えた分は省略されます）。</small>
</div>
<div class="form-group">
<label for="msg_content">メッセージ本文（1000文字以内）</label>
<textarea id="msg_content" name="msg_content" rows="4" class="form-control" onkeyup="ShowLength(value, &quot;msg_content-counter&quot;);" onBlur="check_individual(&quot;msg_content&quot;);"></textarea>
<div id="msg_content-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="msg_content-errortext" class="system-form-error"></div>
<small class="form-text">※改行は反映されます（この入力欄で改行すると実際のメッセージでも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。</small>
</div>
<br>
<button type="submit" class="btn btn-primary">送信</button>
</div>
<?php
echo_modal_confirm("このメッセージを送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。");
?>
</form>
<script language="JavaScript" type="text/javascript">
<!--
function check_individual(id){
    var valid = 1;

    if (id === "subject") {
        document.getElementById("subject-errortext").innerHTML = "";
        if(document.form.msg_subject.value.length > 50){
            document.getElementById("subject-errortext").innerHTML = "文字数が多すぎます。50文字以内に抑えて下さい。";
            document.form.msg_subject.classList.add("is-invalid");
            document.form.msg_subject.classList.remove("is-valid");
        } else {
            document.form.msg_subject.classList.add("is-valid");
            document.form.msg_subject.classList.remove("is-invalid");
        }
        return;
    }

    if (id === "msg_content") {
        document.getElementById("msg_content-errortext").innerHTML = "";
        if(document.form.msg_content.value === ""){
            valid = 0;
            document.getElementById("msg_content-errortext").innerHTML = "入力されていません。";
        } else if(document.form.msg_content.value.length > 1000){
            valid = 0;
            document.getElementById("msg_content-errortext").innerHTML = "文字数が多すぎます。1000文字以内に抑えて下さい。";
        }
        if (valid) {
            document.form.msg_content.classList.add("is-valid");
            document.form.msg_content.classList.remove("is-invalid");
        } else {
            document.form.msg_content.classList.add("is-invalid");
            document.form.msg_content.classList.remove("is-valid");
        }
        return;
    }
}

function check(){

    var problem = 0;
    var valid = 1;

    //文字数
    document.getElementById("subject-errortext").innerHTML = "";
    if(document.form.msg_subject.value.length > 50){
        problem = 1;
        document.getElementById("subject-errortext").innerHTML = "文字数が多すぎます。50文字以内に抑えて下さい。";
        document.form.msg_subject.classList.add("is-invalid");
        document.form.msg_subject.classList.remove("is-valid");
    } else {
        document.form.msg_subject.classList.add("is-valid");
        document.form.msg_subject.classList.remove("is-invalid");
    }

    //文字数
    document.getElementById("msg_content-errortext").innerHTML = "";
    if(document.form.msg_content.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("msg_content-errortext").innerHTML = "入力されていません。";
    } else if(document.form.msg_content.value.length > 1000){
        problem = 1;
        valid = 0;
        document.getElementById("msg_content-errortext").innerHTML = "文字数が多すぎます。1000文字以内に抑えて下さい。";
    }
    if (valid) {
        document.form.msg_content.classList.add("is-valid");
        document.form.msg_content.classList.remove("is-invalid");
    } else {
        document.form.msg_content.classList.add("is-invalid");
        document.form.msg_content.classList.remove("is-valid");
    }

    if ( problem == 0 ) {
        $('#confirmmodal').modal();
        $('#confirmmodal').on('shown.bs.modal', function () {
            document.getElementById("submitbtn").focus();
        });
    }
    return false;
}

//文字数カウント　参考　https://www.nishishi.com/javascript-tips/input-counter.html
function ShowLength(str, resultid) {
   document.getElementById(resultid).innerHTML = "現在 " + str.length + " 文字";
}
// -->
</script>
<?php
    die_mypage();
} ?>
<h2>メッセージ削除</h2>
<p>メッセージを削除すると、自分だけでなく、宛先のユーザーからも閲覧出来なくなります。</p>
<form name="form" action="delete.php" method="post" onSubmit="$('#confirmmodal').modal(); return false;" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="name" value="<?php echo $id; ?>">
<button type="submit" class="btn btn-danger">このメッセージを削除する</button>
<?php
echo_modal_confirm("このメッセージを削除します。この操作は取り消せませんのでご注意下さい。<br>よろしければ「削除する」を押して下さい。<br>削除を止める場合は「戻る」を押して下さい。", "削除確認", null, null, "削除する", "danger");
?>
</form>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
