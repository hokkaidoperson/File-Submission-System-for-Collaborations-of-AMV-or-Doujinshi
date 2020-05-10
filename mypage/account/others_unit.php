<?php
require_once('../../set.php');
setup_session();
$titlepart = 'アカウント情報変更（パスワード以外）';
require_once(PAGEROOT . 'mypage_header.php');

$userid = $_SESSION["userid"];

//入力済み情報を読み込む
$entereddata = json_decode(file_get_contents(DATAROOT . "users/" . $userid . ".txt"), true);

//提出期間外だとメールアドレス以外変更不可にする
if (outofterm('userform') != FALSE) $disable = FALSE;
else $disable = TRUE;
if ($_SESSION["state"] == 'p') $disable = FALSE;
if (before_deadline()) $disable = FALSE;

?>

<h1>アカウント情報変更（パスワード以外）</h1>
<p>現在登録されている情報が入力欄に入力されています。変更したい項目のみ、入力欄の中身を変更して下さい（ユーザーIDは変更出来ません）。</p>
<?php
if (!before_deadline() and $disable) echo '<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在、ファイル提出期間外のため、ニックネームのみ変更不可になっています。
</div>';
else {
    if (!before_deadline() and $_SESSION["state"] == 'p') echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外のため、本来はニックネームが変更不可になっていますが、主催者は常時編集が可能です。
</div>';
    else if (!before_deadline()) echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外のため、本来はニックネームが変更不可になっていますが、あなたは主催者から編集を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('userform')) . 'まで）。
</div>';
}
?>
<form name="form" action="others_handle.php" method="post" onSubmit="return check()">
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<label for="password">現在のパスワード（本人確認の為ご入力願います）【必須】</label>
<input type="password" name="password" class="form-control" id="password" onBlur="check_individual(&quot;password&quot;);">
<div id="password-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
<div class="form-group">
<label for="userid_dummy">ユーザーID（変更出来ません）</label>
<input type="text" name="userid_dummy" class="form-control" id="userid_dummy" value="<?php echo $userid; ?>" disabled>
</div>
<div class="form-group">
<label for="nickname">ニックネーム（30文字以内）【必須】</label>
<input type="text" name="nickname" class="form-control" id="nickname" value="<?php
if (isset($entereddata["nickname"])) echo hsc($entereddata["nickname"]);
?>"<?php if ($disable) echo ' disabled="disabled"'; ?> onkeyup="ShowLength(value, &quot;nickname-counter&quot;);" onBlur="check_individual(&quot;nickname&quot;);">
<font size="2"><div id="nickname-counter" class="text-right text-md-left text-muted">現在 - 文字</div></font>
<div id="nickname-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。</font>
</div>
<div class="form-group">
<label for="email">メールアドレス【必須】</label>
<input type="email" name="email" class="form-control" id="email" value="<?php
if (isset($entereddata["email"])) echo hsc($entereddata["email"]);
?>" onBlur="check_individual(&quot;email&quot;);">
<div id="email-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。</font>
</div>
<div class="form-group">
<label for="emailagn">メールアドレス（確認の為再入力）【必須】</label>
<input type="email" name="emailagn" class="form-control" id="emailagn" value="<?php
if (isset($entereddata["email"])) echo hsc($entereddata["email"]);
?>" onBlur="check_individual(&quot;emailagn&quot;);">
<div id="emailagn-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
</div>
<?php
echo_modal_alert();
echo_modal_alert("入力内容の検証中にエラーが発生しました。<br>お手数ですが、インターネット接続環境をご確認頂き、再度「送信する」を押して下さい。", "ネットワーク・エラー", null, null, "neterrormodal", "dismissbtn2");
echo_modal_confirm();
?>
</form>
<script type="text/javascript">
<!--
function check_individual(id){
    var valid = 1;
    if (id === "password") {
        document.getElementById("password-errortext").innerHTML = "";
        if(document.form.password.value === ""){
            valid = 0;
            document.getElementById("password-errortext").innerHTML = "入力されていません。";
        } else {
            //参考　https://qiita.com/legokichi/items/801e88462eb5c84af97d
            const obj = {password: document.form.password.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
            const method = "POST";
            const body = Object.keys(obj).map((key)=>key+"="+encodeURIComponent(obj[key])).join("&");
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            };
            fetch('../fnc/api_verifypw.php', {method, headers, body})
            .then((response) => {
                if(response.ok) {
                    return response.json();
                } else {
                    throw new Error("Stopped because of a network error");
                }
            })
            .then((result) => {
                if (result.auth_status == "NG") {
                    throw new Error("Stopped because of an API error - response: " + result.error_detail);
                } else if (result.result == 0) {
                    document.getElementById("password-errortext").innerHTML = "現在のパスワードに誤りがあります。";
                    document.form.password.classList.add("is-invalid");
                    document.form.password.classList.remove("is-valid");
                } else {
                    document.form.password.classList.add("is-valid");
                    document.form.password.classList.remove("is-invalid");
                }
            })
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

    if (id === "nickname") {
        document.getElementById("nickname-errortext").innerHTML = "";
        if(document.form.nickname.value === ""){
            valid = 0;
            document.getElementById("nickname-errortext").innerHTML = "入力されていません。";
        } else if(document.form.nickname.value.length > 30){
            valid = 0;
            document.getElementById("nickname-errortext").innerHTML = "文字数が多すぎます。30文字以内に抑えて下さい。";
        }
        if (valid) {
            document.form.nickname.classList.add("is-valid");
            document.form.nickname.classList.remove("is-invalid");
        } else {
            document.form.nickname.classList.add("is-invalid");
            document.form.nickname.classList.remove("is-valid");
        }
        return;
    }
    if (id === "email") {
        document.getElementById("email-errortext").innerHTML = "";
        if(document.form.email.value === ""){
            valid = 0;
            document.getElementById("email-errortext").innerHTML = "入力されていません。";
        } else if(!document.form.email.value.match(/.+@.+\..+/)){
            valid = 0;
            document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
        } else {
            const obj = {email: document.form.email.value, skipmyself: "1", csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
            const method = "POST";
            const body = Object.keys(obj).map((key)=>key+"="+encodeURIComponent(obj[key])).join("&");
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            };
            fetch('../fnc/api_emailduplication.php', {method, headers, body})
            .then((response) => {
                if(response.ok) {
                    return response.json();
                } else {
                    throw new Error("Stopped because of a network error");
                }
            })
            .then((result) => {
                if (result.auth_status == "NG") {
                    throw new Error("Stopped because of an API error - response: " + result.error_detail);
                } else if (result.emailresult == 0) {
                    document.getElementById("email-errortext").innerHTML = "このメールアドレスは既に使用されています。1つのメールアドレスにつき、紐づけられるアカウントは1個のみです。";
                    document.form.email.classList.add("is-invalid");
                    document.form.email.classList.remove("is-valid");
                } else {
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

    if (id === "emailagn") {
        document.getElementById("emailagn-errortext").innerHTML = "";
        if(document.form.email.value !== document.form.emailagn.value){
            valid = 0;
            document.getElementById("emailagn-errortext").innerHTML = "再入力のメールアドレスが違います。もう一度入力して下さい。メールアドレスは間違っていませんか？";
        }
        if (valid) {
            document.form.emailagn.classList.add("is-valid");
            document.form.emailagn.classList.remove("is-invalid");
        } else {
            document.form.emailagn.classList.add("is-invalid");
            document.form.emailagn.classList.remove("is-valid");
        }
        return;
    }
}

function check(){
    var problem = 0;
    var valid = 1;
    var validpw = 0;
    var validemail = 0;

    document.getElementById("password-errortext").innerHTML = "";
    if(document.form.password.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
    } else {
        validpw = 1;
    }
    if (valid) {
        document.form.password.classList.add("is-valid");
        document.form.password.classList.remove("is-invalid");
    } else {
        document.form.password.classList.add("is-invalid");
        document.form.password.classList.remove("is-valid");
    }
    valid = 1;

    document.getElementById("nickname-errortext").innerHTML = "";
    if(document.form.nickname.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("nickname-errortext").innerHTML = "入力されていません。";
    } else if(document.form.nickname.value.length > 30){
        problem = 1;
        valid = 0;
        document.getElementById("nickname-errortext").innerHTML = "文字数が多すぎます。30文字以内に抑えて下さい。";
    }
    if (valid) {
        document.form.nickname.classList.add("is-valid");
        document.form.nickname.classList.remove("is-invalid");
    } else {
        document.form.nickname.classList.add("is-invalid");
        document.form.nickname.classList.remove("is-valid");
    }
    valid = 1;

    document.getElementById("email-errortext").innerHTML = "";
    document.getElementById("emailagn-errortext").innerHTML = "";
    if(document.form.email.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.email.value.match(/.+@.+\..+/)){
        problem = 1;
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
    } else if(document.form.email.value !== document.form.emailagn.value){
        problem = 1;
        valid = 0;
        document.getElementById("emailagn-errortext").innerHTML = "再入力のメールアドレスが違います。もう一度入力して下さい。メールアドレスは間違っていませんか？";
    } else {
        validemail = 1;
    }
    if (valid) {
        document.form.email.classList.add("is-valid");
        document.form.email.classList.remove("is-invalid");
        document.form.emailagn.classList.add("is-valid");
        document.form.emailagn.classList.remove("is-invalid");
    } else {
        document.form.email.classList.add("is-invalid");
        document.form.email.classList.remove("is-valid");
        document.form.emailagn.classList.add("is-invalid");
        document.form.emailagn.classList.remove("is-valid");
    }
    valid = 1;

    //参考　https://qiita.com/legokichi/items/801e88462eb5c84af97d
    const obj = {password: document.form.password.value, email:document.form.email.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
    const method = "POST";
    const body = Object.keys(obj).map((key)=>key+"="+encodeURIComponent(obj[key])).join("&");
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
    };
    fetch('../fnc/api_verifypw.php', {method, headers, body})
    .then((response) => {
        if(response.ok) {
            return response.json();
        } else {
            $('#neterrormodal').modal();
            $('#neterrormodal').on('shown.bs.modal', function () {
                document.getElementById("dismissbtn2").focus();
            });
            throw new Error("Stopped because of a network error");
        }
    })
    .catch((error) => {
        $('#neterrormodal').modal();
        $('#neterrormodal').on('shown.bs.modal', function () {
            document.getElementById("dismissbtn2").focus();
        });
        throw new Error("Stopped because of a network error");
    })
    .then((result) => {
        if (result.auth_status == "NG") {
            throw new Error("Stopped because of an API error - response: " + result.error_detail);
        } else {
            if (result.result == 0 && validpw == 1) {
                document.getElementById("password-errortext").innerHTML = "現在のパスワードに誤りがあります。";
                document.form.password.classList.add("is-invalid");
                document.form.password.classList.remove("is-valid");
            } else if (validpw == 1) {
                document.form.password.classList.add("is-valid");
                document.form.password.classList.remove("is-invalid");
            }
            if (result.emailresult == 0 && validemail == 1) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスは既に使用されています。1つのメールアドレスにつき、紐づけられるアカウントは1個のみです。";
                document.form.email.classList.add("is-invalid");
                document.form.email.classList.remove("is-valid");
            } else if (validemail == 1) {
                document.form.email.classList.add("is-valid");
                document.form.email.classList.remove("is-invalid");
            }
            if (problem == 1 || result.emailresult == 0 || result.result == 0) {
                $('#errormodal').modal();
                $('#errormodal').on('shown.bs.modal', function () {
                    document.getElementById("dismissbtn").focus();
                });
            } else {
                $('#confirmmodal').modal();
                $('#confirmmodal').on('shown.bs.modal', function () {
                    document.getElementById("submitbtn").focus();
                });
            }
        }
    })
    return false;

}

//文字数カウント　参考　https://www.nishishi.com/javascript-tips/input-counter.html
function ShowLength(str, resultid) {
   document.getElementById(resultid).innerHTML = "現在 " + str.length + " 文字";
}

// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
