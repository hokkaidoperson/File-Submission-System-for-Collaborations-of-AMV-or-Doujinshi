<?php
require_once('../set.php');

$deny = FALSE;

$userid = basename($_GET["userid"]);

$fileplace = DATAROOT . 'mail/reset_pw/' . $userid . '.txt';

if (file_exists($fileplace)) {
    $filedata = json_decode(file_get_contents_repeat($fileplace), true);
    if ($filedata["expire"] <= time()) {
        unlink($fileplace);
        $deny = TRUE;
    }
    if ($filedata["sectok"] !== $_GET["sectok"]) $deny = TRUE;
} else $deny = TRUE;

if ($deny) die_error_html('認証エラー', '<p>認証に失敗しました。以下が原因として考えられます。<br>
1. 設定リンクの有効期限が切れている。<br>
2. 設定リンクのURLのうち一部分だけが切り取られ、サーバー側で正常に認識されなかった。</p>');

$titlepart = "パスワード再発行";
require_once(PAGEROOT . 'guest_header.php');

?>

<h1>パスワード再発行</h1>
<div class="border system-border-spacer">
新しいパスワードを入力して下さい。
</div>
<form name="form" action="special_handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<input type="hidden" name="sectok" value="<?php echo $_GET["sectok"]; ?>">
<input type="hidden" name="userid" value="<?php echo $userid; ?>">
<div class="form-group">
<label for="password">パスワード（8文字以上72文字以内）【必須】</label>
<input type="password" name="password" class="form-control" id="password" onkeyup="show_length(value, &quot;password-counter&quot;);" onChange="check_individual(&quot;password&quot;);">
<div id="password-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="password-errortext" class="system-form-error"></div>
<small class="form-text">※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</small>
</div>
<div class="form-group">
<label for="passwordagn">パスワード（確認の為再入力）【必須】</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn" onChange="check_individual(&quot;passwordagn&quot;);">
<div id="passwordagn-errortext" class="system-form-error"></div>
</div>
<button type="submit" class="btn btn-primary">入力したパスワードで設定する</button>
</div>
<?php
echo_modal_confirm("入力したパスワードで再発行を行います。<br>よろしければ「再発行する」を押して下さい。<br>別のパスワードにする場合は「戻る」を押して下さい。", "変更確認", null, null, "再発行する");
?>
</form>
<script type="text/javascript">

function check_individual(id) {
    var valid = 1;
    if (id === "password") {
        document.getElementById("password-errortext").innerHTML = "";
        if(document.form.password.value === ""){
            valid = 0;
            document.getElementById("password-errortext").innerHTML = "入力されていません。";
        } else if(document.form.password.value.length > 72){
            valid = 0;
            document.getElementById("password-errortext").innerHTML = "文字数が多すぎます。72文字以内に抑えて下さい。";
        } else if(document.form.password.value.length < 8){
            valid = 0;
            document.getElementById("password-errortext").innerHTML = "文字数が少なすぎます。8文字以上のパスワードにして下さい。";
        }
        if (valid) {
            document.form.password.classList.add("is-valid");
            document.form.password.classList.remove("is-invalid");
        } else {
            document.form.password.classList.add("is-invalid");
            document.form.password.classList.remove("is-valid");
        }
        return;
    }

    if (id === "passwordagn") {
        document.getElementById("passwordagn-errortext").innerHTML = "";
        if(document.form.password.value !== document.form.passwordagn.value){
            valid = 0;
            document.getElementById("passwordagn-errortext").innerHTML = "再入力のパスワードが違います。もう一度入力して下さい。パスワードは間違っていませんか？";
        }
        if (valid) {
            document.form.passwordagn.classList.add("is-valid");
            document.form.passwordagn.classList.remove("is-invalid");
        } else {
            document.form.passwordagn.classList.add("is-invalid");
            document.form.passwordagn.classList.remove("is-valid");
        }
        return;
    }
}

function check(){

    var problem = 0;
    var valid = 1;

    document.getElementById("password-errortext").innerHTML = "";
    document.getElementById("passwordagn-errortext").innerHTML = "";
    if(document.form.password.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
    } else if(document.form.password.value.length > 72){
        problem = 1;
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "文字数が多すぎます。72文字以内に抑えて下さい。";
    } else if(document.form.password.value.length < 8){
        problem = 1;
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "文字数が少なすぎます。8文字以上のパスワードにして下さい。";
    } else if(document.form.password.value !== document.form.passwordagn.value){
        problem = 1;
        valid = 0;
        document.getElementById("passwordagn-errortext").innerHTML = "再入力のパスワードが違います。もう一度入力して下さい。パスワードは間違っていませんか？";
    }
    if (valid) {
        document.form.password.classList.add("is-valid");
        document.form.password.classList.remove("is-invalid");
        document.form.passwordagn.classList.add("is-valid");
        document.form.passwordagn.classList.remove("is-invalid");
    } else {
        document.form.password.classList.add("is-invalid");
        document.form.password.classList.remove("is-valid");
        document.form.passwordagn.classList.add("is-invalid");
        document.form.passwordagn.classList.remove("is-valid");
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
require_once(PAGEROOT . 'guest_footer.php');
