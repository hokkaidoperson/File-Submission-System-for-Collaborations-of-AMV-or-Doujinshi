<?php
require_once('../../set.php');


if (blackip(0, "g")) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>アクセスが制限されています</title>
</head>
<body>
<p>現在ご利用のアクセス元（IPアドレス）からのユーザー登録が制限されているため、ユーザー登録出来ません。<br>
あなた、もしくは同じアクセス元を利用する他の誰かが、イベントの運営を妨害するなどしたために主催者により制限されています。<br>
もしそのような事をした覚えが無い場合は、以下のブロック情報を添えて主催者にご相談下さい。</p>
<p>【ブロック情報】<br>
IPアドレス：' . getenv("REMOTE_ADDR") . '<br>
リモートホスト：' . gethostbyaddr(getenv("REMOTE_ADDR")) . '</p>
</body>
</html>');
}

//recaptcha周りの参考URL https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents(DATAROOT . 'rec.txt'), true);

$userec = FALSE;
if ($recdata["site"] != "" and $recdata["sec"] != "" and extension_loaded('curl')) $userec = TRUE;

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../../css/bootstrap.css">
<title>アカウント登録 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<?php if ($userec) echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>"; ?>
<script type="text/javascript">
<!--
function check_individual(id) {
    var valid = 1;

    if (id === "userid") {
        document.getElementById("userid-errortext").innerHTML = "";
        document.getElementById("userid-duptext").innerHTML = "";
        if(document.form.userid.value === ""){
            valid = 0;
            document.getElementById("userid-errortext").innerHTML = "入力されていません。";
        } else if(!document.form.userid.value.match(/^[0-9a-zA-Z]*$/)){
            valid = 0;
            document.getElementById("userid-errortext").innerHTML = "半角英数字以外の文字が含まれています（記号も使用出来ません）。別のIDを指定して下さい。";
        } else if(document.form.userid.value.length > 20){
            valid = 0;
            document.getElementById("userid-errortext").innerHTML = "文字数が多すぎます。20文字以内に抑えて下さい。";
        } else {
            fetch('useridcheck.php?userid=' + document.form.userid.value)
            .then((response) => {
                if(response.ok) {
                    return response.json();
                } else {
                    throw new Error();
                }
            })
            .then((result) => {
                if (result.idresult == 0) {
                    document.getElementById("userid-errortext").innerHTML = "申し訳ございませんが、このユーザーIDは既に使われています。別のユーザーIDをご利用願います。";
                    document.form.userid.classList.add("is-invalid");
                    document.form.userid.classList.remove("is-valid");
                } else {
                    document.getElementById("userid-duptext").innerHTML = "このユーザーIDはご利用になれます。";
                    document.form.userid.classList.add("is-valid");
                    document.form.userid.classList.remove("is-invalid");
                }
            })
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
        } else {
            fetch('useridcheck.php?email=' + document.form.email.value)
            .then((response) => {
                if(response.ok) {
                    return response.json();
                } else {
                    throw new Error();
                }
            })
            .then((result) => {
                if (result.emailresult == 0) {
                    document.getElementById("email-errortext").innerHTML = "このメールアドレスは既に使用されています。ユーザー登録がお済みではありませんか？<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。</a>";
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

    if (id === "password") {
        document.getElementById("password-errortext").innerHTML = "";
        if(document.form.password.value === ""){
            valid = 0;
            document.getElementById("password-errortext").innerHTML = "入力されていません。";
        } else if(document.form.password.value.length > 30){
            valid = 0;
            document.getElementById("password-errortext").innerHTML = "文字数が多すぎます。30文字以内に抑えて下さい。";
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
    var validuserid = 0;
    var validemail = 0;
    <?php if ($userec) echo "document.getElementById(\"neterrortext\").style.display = \"none\";"; ?>
    document.getElementById("userid-errortext").innerHTML = "";
    document.getElementById("userid-duptext").innerHTML = "";
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
    } else {
        validuserid = 1;
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

    document.getElementById("password-errortext").innerHTML = "";
    document.getElementById("passwordagn-errortext").innerHTML = "";
    if(document.form.password.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
    } else if(document.form.password.value.length > 30){
        problem = 1;
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "文字数が多すぎます。30文字以内に抑えて下さい。";
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

    fetch('useridcheck.php?userid=' + document.form.userid.value + "&email=" + document.form.email.value)
    .then((response) => {
        if(response.ok) {
            return response.json();
        } else {
            $('#neterrormodal').modal();
            $('#neterrormodal').on('shown.bs.modal', function () {
                document.getElementById("dismissbtn2").focus();
            });
            throw new Error();
        }
    })
    .catch((error) => {
        $('#neterrormodal').modal();
        $('#neterrormodal').on('shown.bs.modal', function () {
            document.getElementById("dismissbtn2").focus();
        });
        throw new Error();
    })
    .then((result) => {
        if (result.idresult == 0 && validuserid == 1) {
            document.getElementById("userid-errortext").innerHTML = "申し訳ございませんが、このユーザーIDは既に使われています。別のユーザーIDをご利用願います。";
            document.form.userid.classList.add("is-invalid");
            document.form.userid.classList.remove("is-valid");
        } else if (validuserid == 1) {
            document.getElementById("userid-duptext").innerHTML = "このユーザーIDはご利用になれます。";
            document.form.userid.classList.add("is-valid");
            document.form.userid.classList.remove("is-invalid");
        }
        if (result.emailresult == 0 && validemail == 1) {
            document.getElementById("email-errortext").innerHTML = "このメールアドレスは既に使用されています。ユーザー登録がお済みではありませんか？<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。</a>";
            document.form.email.classList.add("is-invalid");
            document.form.email.classList.remove("is-valid");
        } else if (validemail == 1) {
            document.form.email.classList.add("is-valid");
            document.form.email.classList.remove("is-invalid");
        }
        if (problem == 1 || result.emailresult == 0 || result.idresult == 0) {
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
    })

    return false;
}

function recSubmit(token) {
    document.form.submit();
}

function recError(token) {
    document.getElementById("neterrortext").style.display = "block";
    $('#confirmmodal').modal('handleUpdate');
}

function submittohandle() {
    <?php if ($userec) echo "document.getElementById(\"neterrortext\").style.display = \"none\"; grecaptcha.execute();"; else echo <<<EOT
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    document.form.submit();
EOT;
    ?>
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
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1>アカウント登録</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
本イベントのポータルサイトで使用するアカウントを登録します。登録したアカウントを使用して、ファイルを提出して下さい。<br><br>
<b>パスワードは絶対に外部に漏れないようにして下さい。</b>第三者によって不正にアクセスされると、提出されたファイルの内容が見られたり、改ざんされたりする可能性があります。<b>イベントの主催者や共同運営者が、あなたのパスワードを直接お聞きする事はありません。</b><br><br>
<u>ユーザーID以外の項目は、後から変更する事が出来ます</u>（マイページトップ画面の「アカウント情報編集」から編集出来ます）。<br><br>
当サイトではJavascript（Ajax含む）及びCookieを使用します。現在はJavascriptとCookieが有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。
</div>
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<label for="userid">ユーザーID（半角英数字のみ　20文字以内　<b>後から変更出来ません</b>）【必須】</label>
<input type="text" name="userid" class="form-control" id="userid" onkeyup="ShowLength(value, &quot;userid-counter&quot;);" onBlur="check_individual(&quot;userid&quot;);">
<font size="2"><div id="userid-counter" class="text-right">現在 - 文字</div></font>
<div id="userid-duptext" class="valid-feedback" style="display: block;"></div>
<div id="userid-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※ログインの際にこのユーザーIDを使用します。</font>
</div>
<div class="form-group">
<label for="nickname">ニックネーム（30文字以内）【必須】</label>
<input type="text" name="nickname" class="form-control" id="nickname" onkeyup="ShowLength(value, &quot;nickname-counter&quot;);" onBlur="check_individual(&quot;nickname&quot;);">
<font size="2"><div id="nickname-counter" class="text-right">現在 - 文字</div></font>
<div id="nickname-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。</font>
</div>
<div class="form-group">
<label for="email">メールアドレス【必須】</label>
<input type="email" name="email" class="form-control" id="email" value="" onBlur="check_individual(&quot;email&quot;);">
<div id="email-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。</font>
</div>
<div class="form-group">
<label for="emailagn">メールアドレス（確認の為再入力）【必須】</label>
<input type="email" name="emailagn" class="form-control" id="emailagn" onBlur="check_individual(&quot;emailagn&quot;);">
<div id="emailagn-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
<div class="form-group">
<label for="password">パスワード（8文字以上30文字以内）【必須】</label>
<input type="password" name="password" class="form-control" id="password" onkeyup="ShowLength(value, &quot;password-counter&quot;);" onBlur="check_individual(&quot;password&quot;);">
<font size="2"><div id="password-counter" class="text-right">現在 - 文字</div></font>
<div id="password-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</font>
</div>
<div class="form-group">
<label for="passwordagn">パスワード（確認の為再入力）【必須】</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn" onBlur="check_individual(&quot;passwordagn&quot;);">
<div id="passwordagn-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
<div class="form-group">
あなたの立場
<div class="form-check">
<input id="state-g" class="form-check-input" type="radio" name="state" value="g" checked>
<label class="form-check-label" for="state-g">一般参加者<br><font size="2">自分が提出したファイルへのみアクセス権を有します。</font></label>
</div>
</div>
<?php
if ($userec) echo '<div id=\'recaptcha\' class="g-recaptcha" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit" data-error-callback="recError" data-size="invisible"></div>';
?>
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
入力内容に問題が見つかりました。<br>
お手数ですが、表示されているエラー内容を参考に、入力内容の確認・修正をお願いします。<br><br>
修正後、再度「送信する」を押して下さい。
</div>
<div class="modal-footer">
<button type="button" class="btn btn-primary" data-dismiss="modal" id="dismissbtn">OK</button>
</div>
</div>
</div>
</div>
<!-- 接続エラーModal -->
<div class="modal fade" id="neterrormodal" tabindex="-1" role="dialog" aria-labelledby="neterrormodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="neterrormodaltitle">ネットワーク・エラー</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
入力内容の検証中にエラーが発生しました。<br>
お手数ですが、インターネット接続環境をご確認頂き、再度「送信する」を押して下さい。
</div>
<div class="modal-footer">
<button type="button" class="btn btn-primary" data-dismiss="modal" id="dismissbtn2">OK</button>
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
入力内容に問題は見つかりませんでした。<br><br>
現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>
入力内容の修正を行う場合は「戻る」を押して下さい。<br><br>
※「送信する」を押下すると、<b>ユーザーIDはこれ以降変更出来なくなります</b>のでご注意下さい。
<?php
if ($userec) echo '<div><br><font size="2"><span class="text-muted">※「送信する」を押下した直後、あなたがスパムやボットでない事を確かめるために画像認証画面が表示される場合があります。</span></font></div>
<div id="neterrortext" style="display: none;"><br><font size="2"><span class="text-danger">ユーザーの認証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「送信する」を押して下さい。</span></font></div>';
?>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
<button type="button" class="btn btn-primary" id="submitbtn" onClick="submittohandle();">送信する</button>
</div>
</div>
</div>
</div>
</form>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../../js/bootstrap.bundle.js"></script>
</body>
</html>
