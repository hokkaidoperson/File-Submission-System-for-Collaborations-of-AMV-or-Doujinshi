<?php
require_once('../../set.php');
setup_session();
$titlepart = 'メッセージ新規送信（一斉送信）';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["state"] != 'p' and !$_SESSION["admin"]) die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<strong>主催者</strong>、<strong>システム管理者</strong>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

?>
<h1>メッセージ新規送信（一斉送信）</h1>
<p>現在当サイトに登録している全ユーザーにメッセージを送信します。</p>
<p>下の入力欄にメッセージを入力し、「送信」ボタンを押して下さい。</p>

<form name="form" action="write_simultaneously_handle.php" method="post" onSubmit="return check()">
<?php csrf_prevention_in_form(); ?>
<div class="border border-primary system-border-spacer">
<div class="form-group">
<label for="msg_subject">件名（50文字以内）</label>
<input type="text" name="msg_subject" class="form-control" id="msg_subject" value="" onkeyup="show_length(value, &quot;subject-counter&quot;);" onChange="check_individual(&quot;subject&quot;);">
<div id="subject-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="subject-errortext" class="system-form-error"></div>
<small class="form-text">※空欄の場合、メッセージ本文の最初の30文字が件名に利用されます（30文字を超えた分は省略されます）。</small>
</div>
<div class="form-group">
<label for="msg_content">メッセージ本文（1000文字以内）</label>
<textarea id="msg_content" name="msg_content" rows="5" class="form-control" onkeyup="show_length(value, &quot;msg_content-counter&quot;);" onChange="check_individual(&quot;msg_content&quot;);"></textarea>
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

</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
