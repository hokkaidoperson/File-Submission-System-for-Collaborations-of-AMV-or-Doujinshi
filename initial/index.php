<?php
if (file_exists('../dataplace.php')) require_once('../dataplace.php'); else define('DATAROOT', dirname(__FILE__).'/../data/');
if (file_exists(DATAROOT . 'init.txt')) die('初期設定が既に終わっています。');
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="stylesheet" href="../css/bootstrap.css">
<link rel="stylesheet" href="../css/style.css">
<title>初期設定</title>
</head>
<script type="text/javascript">
<!--
function check_individual(id){

    var valid = 1;

    if (id === "userid") {
        document.getElementById("userid-errortext").innerHTML = "";
        if(document.form.userid.value === ""){
            valid = 0;
            document.getElementById("userid-errortext").innerHTML = "入力されていません。";
        } else if(!document.form.userid.value.match(/^[0-9a-zA-Z]*$/)){
            valid = 0;
            document.getElementById("userid-errortext").innerHTML = "半角英数字以外の文字が含まれています（記号も使用出来ません）。別のIDを指定して下さい。";
        } else if(document.form.userid.value.length > 20){
            valid = 0;
            document.getElementById("userid-errortext").innerHTML = "文字数が多すぎます。20文字以内に抑えて下さい。";
        }
        if (valid) {
            document.form.userid.classList.add("is-valid");
            document.form.userid.classList.remove("is-invalid");
        } else {
            document.form.userid.classList.add("is-invalid");
            document.form.userid.classList.remove("is-valid");
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

    if (id === "state") {
        document.getElementById("state-errortext").innerHTML = "";
        var f = document.getElementsByName("state");
        if(document.form.state.value === ""){
            problem = 1;
            document.getElementById("state-errortext").innerHTML = "いずれかを選択して下さい。";
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-invalid");
                f[j].classList.remove("is-valid");
            }
        } else {
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-valid");
                f[j].classList.remove("is-invalid");
            }
        }
        return;
    }

    if (id === "eventname") {
        document.getElementById("eventname-errortext").innerHTML = "";
        if(document.form.eventname.value === ""){
            problem = 1;
            valid = 0;
            document.getElementById("eventname-errortext").innerHTML = "入力されていません。";
        } else if(document.form.eventname.value.length > 50){
            problem = 1;
            valid = 0;
            document.getElementById("eventname-errortext").innerHTML = "文字数が多すぎます。50文字以内に抑えて下さい。";
        }
        if (valid) {
            document.form.eventname.classList.add("is-valid");
            document.form.eventname.classList.remove("is-invalid");
        } else {
            document.form.eventname.classList.add("is-invalid");
            document.form.eventname.classList.remove("is-valid");
        }
        return;
    }

    if (id === "filesize") {
        document.getElementById("filesize-errortext").innerHTML = "";
        if(document.form.filesize.value === ""){
            problem = 1;
            valid = 0;
            document.getElementById("filesize-errortext").innerHTML = "入力されていません。";
        } else if(!document.form.filesize.value.match(/^[0-9]*$/)){
            problem = 1;
            valid = 0;
            document.getElementById("filesize-errortext").innerHTML = "半角数字以外の文字が含まれています。";
        } else if(parseInt(document.form.filesize.value) < 1){
            problem = 1;
            valid = 0;
            document.getElementById("filesize-errortext").innerHTML = "数字が小さすぎます。1以上で指定して下さい。";
        }
        if (valid) {
            document.form.filesize.classList.add("is-valid");
            document.form.filesize.classList.remove("is-invalid");
        } else {
            document.form.filesize.classList.add("is-invalid");
            document.form.filesize.classList.remove("is-valid");
        }
        return;
    }

    if (id === "accounts") {
        document.getElementById("accounts-errortext").innerHTML = "";
        if(document.form.accounts.value === ""){
            valid = 0;
            document.getElementById("accounts-errortext").innerHTML = "入力されていません。";
        } else if(!document.form.accounts.value.match(/^[0-9]*$/)){
            valid = 0;
            document.getElementById("accounts-errortext").innerHTML = "半角数字以外の文字が含まれています。";
        } else if(parseInt(document.form.accounts.value) < 1 || parseInt(document.form.accounts.value) > 10){
            valid = 0;
            document.getElementById("accounts-errortext").innerHTML = "数字が小さすぎるか、大きすぎます。1～10で指定して下さい。";
        }
        if (valid) {
            document.form.accounts.classList.add("is-valid");
            document.form.accounts.classList.remove("is-invalid");
        } else {
            document.form.accounts.classList.add("is-invalid");
            document.form.accounts.classList.remove("is-valid");
        }
        return;
    }

    if (id === "system") {
        document.getElementById("system-errortext").innerHTML = "";
        if(document.form.system.value !== "" && !document.form.system.value.match(/.+@.+\..+/)){
            problem = 1;
            document.getElementById("system-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
            document.form.system.classList.add("is-invalid");
            document.form.system.classList.remove("is-valid");
        } else {
            document.form.system.classList.add("is-valid");
            document.form.system.classList.remove("is-invalid");
        }
        return;
    }

    if (id === "systemfrom") {
        document.getElementById("systemfrom-errortext").innerHTML = "";
        if(document.form.systemfrom.value !== "" && document.form.systemfrom.value.length > 30){
            problem = 1;
            document.getElementById("systemfrom-errortext").innerHTML = "文字数が多すぎます。30文字以内に抑えて下さい。";
            document.form.systemfrom.classList.add("is-invalid");
            document.form.systemfrom.classList.remove("is-valid");
        } else {
            document.form.systemfrom.classList.add("is-valid");
            document.form.systemfrom.classList.remove("is-invalid");
        }
        return;
    }

    if (id === "systempre") {
        document.getElementById("systempre-errortext").innerHTML = "";
        if(document.form.systempre.value !== "" && document.form.systempre.value.length > 15){
            problem = 1;
            document.getElementById("systempre-errortext").innerHTML = "文字数が多すぎます。15文字以内に抑えて下さい。";
            document.form.systempre.classList.add("is-invalid");
            document.form.systempre.classList.remove("is-valid");
        } else {
            document.form.systempre.classList.add("is-valid");
            document.form.systempre.classList.remove("is-invalid");
        }
        return;
    }

    if (id === "recaptcha") {
        document.getElementById("recaptcha-errortext").innerHTML = "";
        if(document.form.recaptcha_sec.value === "" && document.form.recaptcha_site.value === ""){
        } else if(document.form.recaptcha_sec.value === "" || document.form.recaptcha_site.value === ""){
            problem = 1;
            valid = 0;
            document.getElementById("recaptcha-errortext").innerHTML = "入力する場合は、いずれの入力欄も入力して下さい。";
        } else if(!document.form.recaptcha_sec.value.match(/^[0-9a-zA-Z-_]*$/) || !document.form.recaptcha_site.value.match(/^[0-9a-zA-Z-_]*$/)){
            problem = 1;
            valid = 0;
            document.getElementById("recaptcha-errortext").innerHTML = "正しく入力されていません。入力されたキーをご確認下さい。キーは間違っていませんか？";
        }
        if (valid) {
            document.form.recaptcha_sec.classList.add("is-valid");
            document.form.recaptcha_sec.classList.remove("is-invalid");
            document.form.recaptcha_site.classList.add("is-valid");
            document.form.recaptcha_site.classList.remove("is-invalid");
        } else {
            document.form.recaptcha_sec.classList.add("is-invalid");
            document.form.recaptcha_sec.classList.remove("is-valid");
            document.form.recaptcha_site.classList.add("is-invalid");
            document.form.recaptcha_site.classList.remove("is-valid");
        }
        return;
    }
}

function check(){

    var problem = 0;
    var valid = 1;

    document.getElementById("userid-errortext").innerHTML = "";
    if(document.form.userid.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("userid-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.userid.value.match(/^[0-9a-zA-Z]*$/)){
        problem = 1;
        valid = 0;
        document.getElementById("userid-errortext").innerHTML = "半角英数字以外の文字が含まれています（記号も使用出来ません）。別のIDを指定して下さい。";
    } else if(document.form.userid.value.length > 20){
        problem = 1;
        valid = 0;
        document.getElementById("userid-errortext").innerHTML = "文字数が多すぎます。20文字以内に抑えて下さい。";
    }
    if (valid) {
        document.form.userid.classList.add("is-valid");
        document.form.userid.classList.remove("is-invalid");
    } else {
        document.form.userid.classList.add("is-invalid");
        document.form.userid.classList.remove("is-valid");
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
    valid = 1;

    document.getElementById("state-errortext").innerHTML = "";
    var f = document.getElementsByName("state");
    if(document.form.state.value === ""){
        problem = 1;
        document.getElementById("state-errortext").innerHTML = "いずれかを選択して下さい。";
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-invalid");
            f[j].classList.remove("is-valid");
      	}
    } else {
        for(var j = 0; j < f.length; j++ ){
      	    f[j].classList.add("is-valid");
            f[j].classList.remove("is-invalid");
      	}
    }

    document.getElementById("eventname-errortext").innerHTML = "";
    if(document.form.eventname.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("eventname-errortext").innerHTML = "入力されていません。";
    } else if(document.form.eventname.value.length > 50){
        problem = 1;
        valid = 0;
        document.getElementById("eventname-errortext").innerHTML = "文字数が多すぎます。50文字以内に抑えて下さい。";
    }
    if (valid) {
        document.form.eventname.classList.add("is-valid");
        document.form.eventname.classList.remove("is-invalid");
    } else {
        document.form.eventname.classList.add("is-invalid");
        document.form.eventname.classList.remove("is-valid");
    }
    valid = 1;

    document.getElementById("filesize-errortext").innerHTML = "";
    if(document.form.filesize.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("filesize-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.filesize.value.match(/^[0-9]*$/)){
        problem = 1;
        valid = 0;
        document.getElementById("filesize-errortext").innerHTML = "半角数字以外の文字が含まれています。";
    } else if(parseInt(document.form.filesize.value) < 1){
        problem = 1;
        valid = 0;
        document.getElementById("filesize-errortext").innerHTML = "数字が小さすぎます。1以上で指定して下さい。";
    }
    if (valid) {
        document.form.filesize.classList.add("is-valid");
        document.form.filesize.classList.remove("is-invalid");
    } else {
        document.form.filesize.classList.add("is-invalid");
        document.form.filesize.classList.remove("is-valid");
    }
    valid = 1;

    document.getElementById("accounts-errortext").innerHTML = "";
    if(document.form.accounts.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("accounts-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.accounts.value.match(/^[0-9]*$/)){
        problem = 1;
        valid = 0;
        document.getElementById("accounts-errortext").innerHTML = "半角数字以外の文字が含まれています。";
    } else if(parseInt(document.form.accounts.value) < 1 || parseInt(document.form.accounts.value) > 10){
        problem = 1;
        valid = 0;
        document.getElementById("accounts-errortext").innerHTML = "数字が小さすぎるか、大きすぎます。1～10で指定して下さい。";
    }
    if (valid) {
        document.form.accounts.classList.add("is-valid");
        document.form.accounts.classList.remove("is-invalid");
    } else {
        document.form.accounts.classList.add("is-invalid");
        document.form.accounts.classList.remove("is-valid");
    }
    valid = 1;

    document.getElementById("system-errortext").innerHTML = "";
    if(document.form.system.value !== "" && !document.form.system.value.match(/.+@.+\..+/)){
        problem = 1;
        document.getElementById("system-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
        document.form.system.classList.add("is-invalid");
        document.form.system.classList.remove("is-valid");
    } else {
        document.form.system.classList.add("is-valid");
        document.form.system.classList.remove("is-invalid");
    }

    document.getElementById("systemfrom-errortext").innerHTML = "";
    if(document.form.systemfrom.value !== "" && document.form.systemfrom.value.length > 30){
        problem = 1;
        document.getElementById("systemfrom-errortext").innerHTML = "文字数が多すぎます。30文字以内に抑えて下さい。";
        document.form.systemfrom.classList.add("is-invalid");
        document.form.systemfrom.classList.remove("is-valid");
    } else {
        document.form.systemfrom.classList.add("is-valid");
        document.form.systemfrom.classList.remove("is-invalid");
    }

    document.getElementById("systempre-errortext").innerHTML = "";
    if(document.form.systempre.value !== "" && document.form.systempre.value.length > 15){
        problem = 1;
        document.getElementById("systempre-errortext").innerHTML = "文字数が多すぎます。15文字以内に抑えて下さい。";
        document.form.systempre.classList.add("is-invalid");
        document.form.systempre.classList.remove("is-valid");
    } else {
        document.form.systempre.classList.add("is-valid");
        document.form.systempre.classList.remove("is-invalid");
    }

    document.getElementById("recaptcha-errortext").innerHTML = "";
    if(document.form.recaptcha_sec.value === "" && document.form.recaptcha_site.value === ""){
    } else if(document.form.recaptcha_sec.value === "" || document.form.recaptcha_site.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("recaptcha-errortext").innerHTML = "入力する場合は、いずれの入力欄も入力して下さい。";
    } else if(!document.form.recaptcha_sec.value.match(/^[0-9a-zA-Z-_]*$/) || !document.form.recaptcha_site.value.match(/^[0-9a-zA-Z-_]*$/)){
        problem = 1;
        valid = 0;
        document.getElementById("recaptcha-errortext").innerHTML = "正しく入力されていません。入力されたキーをご確認下さい。キーは間違っていませんか？";
    }
    if (valid) {
        document.form.recaptcha_sec.classList.add("is-valid");
        document.form.recaptcha_sec.classList.remove("is-invalid");
        document.form.recaptcha_site.classList.add("is-valid");
        document.form.recaptcha_site.classList.remove("is-invalid");
    } else {
        document.form.recaptcha_sec.classList.add("is-invalid");
        document.form.recaptcha_sec.classList.remove("is-valid");
        document.form.recaptcha_site.classList.add("is-invalid");
        document.form.recaptcha_site.classList.remove("is-valid");
    }


  if ( problem == 1 ) {
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
  return false;

}

//文字数カウント　参考　https://www.nishishi.com/javascript-tips/input-counter.html
function ShowLength(str, resultid) {
   document.getElementById(resultid).innerHTML = "現在 " + str.length + " 文字";
}


//Cookie判定（参考：https://qiita.com/tatsuyankmura/items/8e09cbd5ee418d35f169）
var setCookie = function(cookieName, value){
  var cookie = cookieName + "=" + value + ";";
  document.cookie = cookie;
}

var getCookie = function(cookieName){
  var l = cookieName.length + 1 ;
  var cookieAry = document.cookie.split("; ") ;
  var str = "" ;
  for(i=0; i < cookieAry.length; i++){
    if(cookieAry[i].substr(0, l) === cookieName + "="){
      str = cookieAry[i].substr(l, cookieAry[i].length) ;
      break ;
    }
  }
  return str;
}

setCookie('check_cookie', true);
var val = getCookie('check_cookie');


// -->
</script>
<body>
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
<p>上記を有効にしてもこの画面が表示される場合、ご利用のブラウザは当サイトが使用するJavascriptの機能を提供していない、もしくは充分にサポートしていない可能性がありますので、ブラウザを変えて再度お試し下さい（推奨環境のブラウザでこの画面が表示される場合、システム管理者までご連絡下さい）。</p>
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1>初期設定</h1>
<div class="border system-border-spacer">
<p>管理者のアカウント、およびシステムの最低限必要な事項を設定します。「【必須】」と記載されている項目は必ず入力して下さい。</p>
<p><b>パスワードは絶対に外部に漏れないようにして下さい。</b>第三者によって不正にアクセスされると、提出されたファイルの内容が見られたり、改ざんされたりする可能性があります。<b>イベントの主催者や共同運営者が、あなたのパスワードを直接お聞きする事はありません。</b></p>
<p><u>ユーザーID以外の項目は、後から変更する事が出来ます</u>（ただし、あなたの立場の変更に際しては、他人による承認が必要になる場合があります）。<br>
ニックネームなどについては、マイページトップ画面の「アカウント情報編集」、システムの設定事項については「システム設定」から編集出来ます。</p>
<p>当サイトではJavascript（Ajax含む）及びCookieを使用します。現在はJavascriptとCookieが有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。</p>
<p><a href="https://www.hkdyukkuri.space/filesystem/doc/security" target="_blank" rel="noopener">ポータルサイト設置にあたって、セキュリティ上注意すべき点をこちらからご確認願います。</a></p>
</div>
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<h2>管理者アカウントの情報</h2>
<div class="form-group">
<label for="userid">ユーザーID（半角英数字のみ　20文字以内　<b>後から変更出来ません</b>）【必須】</label>
<input type="text" name="userid" class="form-control" id="userid" onkeyup="ShowLength(value, &quot;userid-counter&quot;);" onBlur="check_individual(&quot;userid&quot;);">
<div id="userid-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="userid-errortext" class="system-form-error"></div>
<small class="form-text">※ログインの際にこのユーザーIDを使用します。</small>
</div>
<div class="form-group">
<label for="nickname">ニックネーム（30文字以内）【必須】</label>
<input type="text" name="nickname" class="form-control" id="nickname" onkeyup="ShowLength(value, &quot;nickname-counter&quot;);" onBlur="check_individual(&quot;nickname&quot;);">
<div id="nickname-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="nickname-errortext" class="system-form-error"></div>
<small class="form-text">※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。</small>
</div>
<div class="form-group">
<label for="email">メールアドレス【必須】</label>
<input type="email" name="email" class="form-control" id="email" value="" onBlur="check_individual(&quot;email&quot;);">
<div id="email-errortext" class="system-form-error"></div>
<small class="form-text">※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。</small>
</div>
<div class="form-group">
<label for="emailagn">メールアドレス（確認の為再入力）【必須】</label>
<input type="email" name="emailagn" class="form-control" id="emailagn" onBlur="check_individual(&quot;emailagn&quot;);">
<div id="emailagn-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
<label for="password">パスワード（8文字以上72文字以内）【必須】</label>
<input type="password" name="password" class="form-control" id="password" onkeyup="ShowLength(value, &quot;password-counter&quot;);" onBlur="check_individual(&quot;password&quot;);">
<div id="password-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="password-errortext" class="system-form-error"></div>
<small class="form-text">※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</small>
</div>
<div class="form-group">
<label for="passwordagn">パスワード（確認の為再入力）【必須】</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn" onBlur="check_individual(&quot;passwordagn&quot;);">
<div id="passwordagn-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
あなたの立場【必須】
<div class="form-check">
<input id="state-p" class="form-check-input" type="radio" name="state" value="p" onChange="check_individual(&quot;state&quot;);">
<label class="form-check-label" for="state-p">主催者<br><span class="small">イベントに提出されるあらゆるファイルへのアクセス権や、意思決定権を有します。</span></label>
</div>
<div class="form-check">
<input id="state-c" class="form-check-input" type="radio" name="state" value="c" onChange="check_individual(&quot;state&quot;);">
<label class="form-check-label" for="state-c">共同運営者<br><span class="small">一部の意思決定権を有します。自分が提出したファイルへのみアクセス権を有しますが、主催者が認めた場合は他人のファイルへアクセス出来る可能性があります。</span></label>
</div>
<div class="form-check">
<input id="state-g" class="form-check-input" type="radio" name="state" value="g" onChange="check_individual(&quot;state&quot;);">
<label class="form-check-label" for="state-g">一般参加者<br><span class="small">自分が提出したファイルへのみアクセス権を有します。</span></label>
</div>
<div class="form-check">
<input id="state-o" class="form-check-input" type="radio" name="state" value="o" onChange="check_individual(&quot;state&quot;);">
<label class="form-check-label" for="state-o">非参加者<br><span class="small">このイベントには参加せず、システム管理に関してのみ従事する場合はこちらを選んで下さい。</span></label>
</div>
<div id="state-errortext" class="system-form-error"></div>
<small class="form-text">※「非参加者」と「一般参加者」の間の変更は後から自由に出来ます。共同運営者から一般参加者もしくは非参加者に変更するには主催者の承認が必要です。また、主催者から他の立場へ変更する場合、代わりの主催者を任命する必要があります。</small>
</div>
<h2>システムの設定事項</h2>
<div class="form-group">
<label for="eventname">イベント名（50文字以内）【必須】</label><br/>
<input type="text" name="eventname" id="eventname" class="form-control" onkeyup="ShowLength(value, &quot;eventname-counter&quot;);" onBlur="check_individual(&quot;eventname&quot;);">
<div id="eventname-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="eventname-errortext" class="system-form-error"></div>
<small class="form-text">※イベント名は、サイトのトップページなど、随所に表示されます。</small>
</div>
<div class="form-group">
<label for="filesize">1つの入力欄でアップロード可能な最大ファイルサイズ（1以上の半角数字）【必須】</label>
<div class="input-group" style="width:8em;">
<input type="text" name="filesize" class="form-control" id="filesize" onBlur="check_individual(&quot;filesize&quot;);">
<div class="input-group-append">
<span class="input-group-text">MB</span>
</div>
</div>
<div id="filesize-errortext" class="system-form-error"></div>
<small class="form-text">※ファイルの最大サイズについては、主催者が入力内容の設定をする時にも設定項目がありますが、こちらは、その際にあまりに大きなファイルサイズを設定してしまわないように制限するためのものとなります。<br>
※複数個のファイルを1つの入力欄に添付出来る設定になっている場合、その入力欄に添付するファイルの合計サイズが、指定するサイズ以下になっている必要があります。<br>
※この最大サイズを大きくし過ぎると、サーバーの容量をひっ迫させてしまう可能性があります。<br>
　用途を考え、最大サイズをあまり大きくし過ぎないようにして下さい。<br>
※以下に、ファイルアップロードの制限に関する、サーバーの設定値を表示します。<br>
　これらを上回る最大サイズを指定すると、送信中にエラーが返される事があります（単位は「バイト」、「M」は「メガ」の事です）。<br>
　<u>ファイル1つ辺りの最大サイズ（upload_max_filesize）：<b><?php echo ini_get('upload_max_filesize'); ?></b></u><br>
　<u>他の添付ファイルも含めた最大サイズ（post_max_size）：<b><?php echo ini_get('post_max_size'); ?></b></u></small>
</div>
<div class="form-group">
<label for="accounts">メールアドレス1つ当たりの最大アカウント数（1～10の半角数字）【必須】</label>
<div class="input-group" style="width:8em;">
<input type="text" name="accounts" class="form-control" id="accounts" value="1" onBlur="check_individual(&quot;accounts&quot;);">
<div class="input-group-append">
<span class="input-group-text">個</span>
</div>
</div>
<div id="accounts-errortext" class="system-form-error"></div>
<small class="form-text">※通常は「1個」に設定する事をお勧めします。<br>
※複数名義を使った提出を認める場合は、最大アカウント数の設定を引き上げ、アカウントを複数作るように案内して下さい。</small>
</div>
<div class="form-group">
<label for="system">システムが送信するメールの送信元アドレス</label>
<input type="email" name="system" class="form-control" id="system" onBlur="check_individual(&quot;system&quot;);">
<div class="form-check">
<input id="systemsend" class="form-check-input" type="checkbox" name="systemsend" value="1">
<label class="form-check-label" for="systemsend">「このアドレスは送信専用です」という旨のメッセージを追記したい場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<div id="system-errortext" class="system-form-error"></div>
<small class="form-text">※作品が提出された時などの通知の際に、システムが自動でメールを送信します。そのメールの送信元（From）を指定する場合はここで指定して下さい。<br>
　よく分からない場合は空欄にしておいて下さい。空欄の場合は、サーバー側のデフォルトの設定を使用します。</small>
</div>
<div class="form-group">
<label for="systemfrom">システムが送信するメールの差出人名（30文字以内）</label>
<input type="text" name="systemfrom" class="form-control" id="systemfrom" onkeyup="ShowLength(value, &quot;systemfrom-counter&quot;);" onBlur="check_individual(&quot;systemfrom&quot;);">
<div id="systemfrom-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="systemfrom-errortext" class="system-form-error"></div>
<small class="form-text">※システムが自動で送信するメールの送信元（From）の差出人名を指定する場合はここで指定して下さい。<br>
　指定すると、メールの閲覧ソフトの差出人名の欄に、メールアドレスの代わりに表示されます。<br>
　空欄の場合は、差出人名の欄にメールアドレスが表示されます。</small>
</div>
<div class="form-group">
<label for="systempre">システムが送信するメールの接頭辞（15文字以内）</label>
<input type="text" name="systempre" class="form-control" id="systempre" onkeyup="ShowLength(value, &quot;systempre-counter&quot;);" onBlur="check_individual(&quot;systempre&quot;);">
<div id="systempre-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="systempre-errortext" class="system-form-error"></div>
<small class="form-text">※システムが自動で送信するメールの件名の頭に、ここで指定した接頭辞が付きます。接頭辞は括弧【】で囲われます。<br>
　例えば、接頭辞として「●●合作」と指定した場合は、メールの件名の頭に「【●●合作】」が付きます。<br>
　空欄の場合はイベント名を接頭辞としてそのまま使用しますが、15文字を超えた分は省略されます。</small>
</div>
<div class="form-group">
reCAPTCHA v2（非表示reCAPTCHAバッジ）の設定
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">サイトキー：</span>
</div>
<input type="text" name="recaptcha_site" class="form-control" id="recaptcha_site" onBlur="check_individual(&quot;recaptcha&quot;);">
</div>
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">シークレットキー：</span>
</div>
<input type="text" name="recaptcha_sec" class="form-control" id="recaptcha_sec" onBlur="check_individual(&quot;recaptcha&quot;);">
</div>
<div id="recaptcha-errortext" class="system-form-error"></div>
<small class="form-text">※reCAPTCHA v2（非表示reCAPTCHAバッジ／Invisible reCAPTCHA）を利用出来ます。ログイン画面やユーザー登録画面など、ログインしていない状態で利用可能な入力画面を、ロボットなどによる攻撃から保護出来ます。<br>
※reCAPTCHA v2（非表示reCAPTCHAバッジ）の詳細については、各自で調べて下さい。<br>
※reCAPTCHAの管理画面から設定する際は、「reCAPTCHA v2」→「非表示reCAPTCHAバッジ」の順に選択して下さい。<br>
※特に、シークレットキーは外部に漏れてはいけません。データの保管先が外部から見られないように十分注意して下さい（<a href="https://www.hkdyukkuri.space/filesystem/doc/security#%E3%83%87%E3%83%BC%E3%82%BF%E3%82%92%E4%BF%9D%E7%AE%A1%E3%81%99%E3%82%8B%E3%83%87%E3%82%A3%E3%83%AC%E3%82%AF%E3%83%88%E3%83%AA%E3%81%AE%E5%AE%89%E5%85%A8%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6" target="_blank" rel="noopener">詳細</a>）。<br>
※reCAPTCHA v2（非表示reCAPTCHAバッジ）を利用するには、PHPの拡張モジュール Client URL Library（cURL）が有効になっている必要があります。<?php
if (extension_loaded('curl')) echo '<br>　現在、Client URL Libraryが有効になっているため、reCAPTCHA v2をご利用になれます。';
else echo '<br>　<b>現在、Client URL Libraryが無効になっているため、このままではreCAPTCHA v2をご利用になれません。reCAPTCHA v2を利用するには、Client URL Libraryをインストール・有効化して下さい。</b>';
?></small>
</div>
<div class="form-group">
検索除けの有効・無効
<div class="form-check">
<input id="robot" class="form-check-input" type="checkbox" name="robot" value="1">
<label class="form-check-label" for="robot">このサイトがGoogleやYahoo!などの検索結果に載らないようにしたい場合は、左のチェックボックスにチェックして下さい。</label>
</div>
</div>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
</div>
<!-- エラーModal -->
<div class="modal fade" id="errormodal" tabindex="-1" role="dialog" aria-labelledby="errormodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="errormodaltitle">入力内容の修正が必要です</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
<p>入力内容に問題が見つかりました。<br>
お手数ですが、表示されているエラー内容を参考に、入力内容の確認・修正をお願いします。</p>
<p>修正後、再度「送信する」を押して下さい。</p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-primary" data-dismiss="modal" id="dismissbtn">OK</button>
</div>
</div>
</div>
</div>
<!-- 送信確認Modal -->
<div class="modal fade" id="confirmmodal" tabindex="-1" role="dialog" aria-labelledby="confirmmodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="confirmmodaltitle">送信確認</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
<p>入力内容に問題は見つかりませんでした。</p>
<p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>
入力内容の修正を行う場合は「戻る」を押して下さい。</p>
<p>※「送信する」を押下すると、<b>ユーザーIDはこれ以降変更出来なくなります</b>のでご注意下さい。</p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
<button type="button" class="btn btn-primary" id="submitbtn" onClick='document.getElementById("submitbtn").disabled = "disabled"; document.form.submit();'>送信する</button>
</div>
</div>
</div>
</div>
</form>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
