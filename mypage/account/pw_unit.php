<?php
require_once('../../set.php');
setup_session();
$titlepart = 'パスワード変更';
require_once(PAGEROOT . 'mypage_header.php');

?>

<h1>パスワード変更</h1>
<form name="form" action="pw_handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<label for="oldpassword">現在のパスワード（本人確認の為ご入力願います）【必須】</label>
<input type="password" name="oldpassword" class="form-control" id="oldpassword" onChange="check_individual(&quot;oldpassword&quot;);">
<div id="oldpassword-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
<label for="password">新しいパスワード（8文字以上72文字以内）【必須】</label>
<input type="password" name="password" class="form-control" id="password" onkeyup="ShowLength(value, &quot;password-counter&quot;);" onChange="check_individual(&quot;password&quot;);">
<div id="password-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="password-errortext" class="system-form-error"></div>
<small class="form-text">※パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</small>
</div>
<div class="form-group">
<label for="passwordagn">新しいパスワード（確認の為再入力）【必須】</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn" onChange="check_individual(&quot;passwordagn&quot;);">
<div id="passwordagn-errortext" class="system-form-error"></div>
</div>
<button type="submit" class="btn btn-primary">パスワードを変更する</button>
</div>
<?php
echo_modal_alert("入力内容の検証中にエラーが発生しました。<br>お手数ですが、インターネット接続環境をご確認頂き、再度「パスワードを変更する」を押して下さい。", "ネットワーク・エラー", null, null, "neterrormodal");
echo_modal_confirm("パスワードの変更を実行します。<br>よろしければ「変更する」を押して下さい。<br>変更を止める場合は「戻る」を押して下さい。", "変更確認", null, null, "変更する");
?>
</form>
<script type="text/javascript">

function check_individual(id) {
    var valid = 1;
    if (id === "oldpassword") {
        document.getElementById("oldpassword-errortext").innerHTML = "";
        if(document.form.oldpassword.value === ""){
            valid = 0;
            document.getElementById("oldpassword-errortext").innerHTML = "入力されていません。";
        } else {
            //参考　https://qiita.com/legokichi/items/801e88462eb5c84af97d
            const obj = {password: document.form.oldpassword.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
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
                    document.getElementById("oldpassword-errortext").innerHTML = "現在のパスワードに誤りがあります。";
                    document.form.oldpassword.classList.add("is-invalid");
                    document.form.oldpassword.classList.remove("is-valid");
                } else {
                    document.form.oldpassword.classList.add("is-valid");
                    document.form.oldpassword.classList.remove("is-invalid");
                }
            })
        }
        if (valid) {
            document.form.oldpassword.classList.add("is-valid");
            document.form.oldpassword.classList.remove("is-invalid");
        } else {
            document.form.oldpassword.classList.add("is-invalid");
            document.form.oldpassword.classList.remove("is-valid");
        }
        return;
    }

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
    var validpw = 0;

    document.getElementById("oldpassword-errortext").innerHTML = "";
    if(document.form.oldpassword.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("oldpassword-errortext").innerHTML = "入力されていません。";
    } else {
        validpw = 1;
    }
    if (valid) {
        document.form.oldpassword.classList.add("is-valid");
        document.form.oldpassword.classList.remove("is-invalid");
    } else {
        document.form.oldpassword.classList.add("is-invalid");
        document.form.oldpassword.classList.remove("is-valid");
    }
    valid = 1;

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

    //参考　https://qiita.com/legokichi/items/801e88462eb5c84af97d
    const obj = {password: document.form.oldpassword.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
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
                document.getElementById("dismissbtn").focus();
            });
            throw new Error("Stopped because of a network error");
        }
    })
    .catch((error) => {
        $('#neterrormodal').modal();
        $('#neterrormodal').on('shown.bs.modal', function () {
            document.getElementById("dismissbtn").focus();
        });
        throw new Error("Stopped because of a network error");
    })
    .then((result) => {
        if (result.auth_status == "NG") {
            throw new Error("Stopped because of an API error - response: " + result.error_detail);
        } else {
            if (result.result == 0 && validpw == 1) {
                document.getElementById("oldpassword-errortext").innerHTML = "現在のパスワードに誤りがあります。";
                document.form.oldpassword.classList.add("is-invalid");
                document.form.oldpassword.classList.remove("is-valid");
            } else if (validpw == 1) {
                document.form.oldpassword.classList.add("is-valid");
                document.form.oldpassword.classList.remove("is-invalid");
            }
            if ( problem == 0 && result.result == 1) {
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


</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');

