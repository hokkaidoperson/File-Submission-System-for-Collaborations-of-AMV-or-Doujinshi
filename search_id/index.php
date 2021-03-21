<?php
require_once('../set.php');
setup_session();
//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../mypage/index.php");
}

//recaptcha周りの参考URL https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents_repeat(DATAROOT . 'rec.txt'), true);

$userec = FALSE;
if ($recdata["site"] != "" and $recdata["sec"] != "" and extension_loaded('curl')) $userec = TRUE;

if ($userec) {
    $includepart = "<script src='https://www.google.com/recaptcha/api.js' async defer></script>";
    $bodyincludepart = ' style="margin-bottom: 90px;"';
}
$titlepart = "ユーザーID・ニックネーム再送信";
require_once(PAGEROOT . 'guest_header.php');

?>

<h1>ユーザーID・ニックネーム再送信</h1>
<div class="border system-border-spacer">
<p><strong>パスワードの再発行に必要な情報（ユーザーID・ニックネーム）は、ユーザー登録完了時にお送りしているメールに記載しています</strong>ので、まずはそちらをご確認願います。<br>
もし見当たらない場合や、ニックネームが変更されていて分からない場合は、パスワードの再発行に必要な情報をお使いのアカウントのメールアドレスに再送します。</p>
<p>お使いのアカウントのメールアドレスを以下に入力して下さい。</p>
<p>※無暗に大量のメールが送信されるのを防ぐため、この機能でアカウント情報を再送出来るのは、1アカウントにつき、24時間に1回のみとさせて頂きます。</p>
</div>
<div class="border border-primary system-border-spacer">
<form name="form" action="auth.php" method="post" onSubmit="return check()">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<label for="email">メールアドレス</label>
<input type="email" name="email" class="form-control" id="email" onChange="check_individual()">
<div id="email-errortext" class="system-form-error"></div>
<div id="email-searchinfo" class="system-form-success"></div>
</div>
<?php
if ($userec) echo '<div id=\'recaptcha\' class="g-recaptcha" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit" data-error-callback="recError" data-size="invisible"></div>
<button class="btn btn-primary" type="submit">再発行情報を送信</button><div class="small text-muted system-asterisk-indent">※「再発行情報を送信」を押下した直後、あなたがスパムやボットでない事を確かめるために画像認証画面が表示される場合があります。</div>';
else echo '<button type="submit" class="btn btn-primary" id="submitbtn">再発行情報を送信</button>';
?>
<div id="neterrortext" style="display: none;"><span class="small text-danger">ユーザーの認証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「再発行情報を送信」を押して下さい。</span></div>
</form>
</div>
<script type="text/javascript">

function check_individual() {
    var valid = 1;
    document.getElementById("email-errortext").innerHTML = "";
    document.getElementById("email-searchinfo").innerHTML = "";
    if(document.form.email.value === ""){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.email.value.match(/.+@.+\..+/)){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
    } else {
        fetch('../register/general/useridcheck.php?email=' + document.form.email.value)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                throw new Error();
            }
        })
        .then((result) => {
            if (result.emailresult != 0) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスと紐づいているアカウントが見つかりませんでした。";
                document.form.email.classList.add("is-invalid");
                document.form.email.classList.remove("is-valid");
            } else {
                document.getElementById("email-searchinfo").innerHTML = "このメールアドレスと紐づいているアカウントが見つかりました。「再発行情報を送信」を押すと、アカウント情報を再送します。";
                document.form.email.classList.add("is-valid");
                document.form.email.classList.remove("is-invalid");
            }
        })
    }
    if (valid) {
        document.form.email.classList.add("is-valid");
        document.form.email.classList.remove("is-invalid");
    } else {
        document.form.email.classList.add("is-invalid");
        document.form.email.classList.remove("is-valid");
    }
    return;
}

function check(){
    document.getElementById("neterrortext").style.display = "none";
    var valid = 1;

    document.getElementById("email-searchinfo").innerHTML = "";
    document.getElementById("email-errortext").innerHTML = "";
    if(document.form.email.value === ""){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.email.value.match(/.+@.+\..+/)){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
    } else {
        fetch('../register/general/useridcheck.php?email=' + document.form.email.value)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                document.getElementById("email-errortext").innerHTML = "入力内容の検証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「再発行情報を送信」を押して下さい。";
                throw new Error();
            }
        })
        .catch((error) => {
            document.getElementById("email-errortext").innerHTML = "入力内容の検証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「再発行情報を送信」を押して下さい。";
            throw new Error();
        })
        .then((result) => {
            if (result.emailresult == 1) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスと紐づいているアカウントが見つかりませんでした。";
                document.form.email.classList.add("is-invalid");
                document.form.email.classList.remove("is-valid");
            } else {
                <?php if ($userec) echo "grecaptcha.execute();"; else echo "document.form.submit();"; ?>
            }
        })
    }
    if (valid) {
        document.form.email.classList.add("is-valid");
        document.form.email.classList.remove("is-invalid");
    } else {
        document.form.email.classList.add("is-invalid");
        document.form.email.classList.remove("is-valid");
    }
    return false;

}

function recSubmit(token) {
  document.form.submit();
}

function recError(token) {
  document.getElementById("neterrortext").style.display = "block";
}

</script>
<?php
require_once(PAGEROOT . 'guest_footer.php');
