<?php
require_once('../../set.php');

$deny = FALSE;

$towhom = basename($_GET["towhom"]);

if (file_exists(DATAROOT . 'mail/invitation/' . $towhom . '.txt')) {
    $filedata = json_decode(file_get_contents_repeat(DATAROOT . 'mail/invitation/' . $towhom . '.txt'), true);
    $email = $filedata["to"];
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/invitation/' . $towhom . '.txt');
        $deny = TRUE;
    }
    if ($filedata["sectok"] !== $_GET["sectok"]) $deny = TRUE;
} else $deny = TRUE;

if ($deny) die_error_html('認証エラー', '<p>認証に失敗しました。以下が原因として考えられます。<br>
1. 設定リンクの有効期限が切れている。<br>
2. 設定リンクのURLのうち一部分だけが切り取られ、サーバー側で正常に認識されなかった。</p>');

$titlepart = "共同運営者アカウント登録";
require_once(PAGEROOT . 'guest_header.php');

?>

<h1>共同運営者アカウント登録</h1>
<div class="border system-border-spacer">
<p>共同運営者アカウントの登録をします。アカウントの登録後、イベントの運営に合流します。</p>
<p><strong>パスワードは絶対に外部に漏れないようにして下さい。</strong>第三者によって不正にアクセスされると、提出されたファイルの内容が見られたり、改ざんされたりする可能性があります。</p>
<p><span class="text-decoration-underline">ユーザーID以外の項目は、後から変更する事が出来ます</span>（マイページトップ画面の「アカウント情報編集」から編集出来ます）。</p>
<p>当サイトではJavascript（Ajax含む）及びCookieを使用します。現在はJavascriptとCookieが有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。</p>
</div>
<form name="form" action="co_handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<input type="hidden" name="towhom" value="<?php echo $towhom; ?>">
<input type="hidden" name="sectok" value="<?php echo $_GET["sectok"]; ?>">
<div class="form-group">
<label for="userid">ユーザーID（半角英数字のみ　20文字以内　<strong>後から変更出来ません</strong>）【必須】</label>
<input type="text" name="userid" class="form-control" id="userid" onkeyup="show_length(value, &quot;userid-counter&quot;);" onChange="check_individual(&quot;userid&quot;);">
<div id="userid-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="userid-duptext" class="system-form-success"></div>
<div id="userid-errortext" class="system-form-error"></div>
<small class="form-text">※ログインの際にこのユーザーIDを使用します。</small>
</div>
<div class="form-group">
<label for="nickname">ニックネーム（30文字以内）【必須】</label>
<input type="text" name="nickname" class="form-control" id="nickname" onkeyup="show_length(value, &quot;nickname-counter&quot;);" onChange="check_individual(&quot;nickname&quot;);">
<div id="nickname-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="nickname-errortext" class="system-form-error"></div>
<small class="form-text">※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。</small>
</div>
<div class="form-group">
<label for="email">メールアドレス【必須】</label>
<input type="email" name="email" class="form-control" id="email" value="<?php echo $email; ?>" onChange="check_individual(&quot;email&quot;);">
<div id="email-errortext" class="system-form-error"></div>
<small class="form-text">※登録用リンクを受け取ったメールアドレスが入力されています。必要に応じて変更して下さい。<br>
※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。</small>
</div>
<div class="form-group">
<label for="emailagn">メールアドレス（確認の為再入力）【必須】</label>
<input type="email" name="emailagn" class="form-control" id="emailagn" onChange="check_individual(&quot;emailagn&quot;);">
<div id="emailagn-errortext" class="system-form-error"></div>
</div>
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
<div class="form-group">
あなたの立場
<div class="form-check">
<input id="state-c" class="form-check-input" type="radio" name="state" value="c" checked="checked">
<label class="form-check-label" for="state-c">共同運営者<br><span class="small">一部の意思決定権を有します。自分が提出したファイルへのみアクセス権を有しますが、主催者が認めた場合は他人のファイルへアクセス出来る可能性があります。</span></label>
</div>
<small class="form-text">※共同運営者から一般参加者に変更するには主催者の承認が必要です。</small>
</div>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
</div>
<?php
echo_modal_alert();
echo_modal_alert("入力内容の検証中にエラーが発生しました。<br>お手数ですが、インターネット接続環境をご確認頂き、再度「送信する」を押して下さい。", "ネットワーク・エラー", null, null, "neterrormodal", "dismissbtn2");
echo_modal_confirm("<p>入力内容に問題は見つかりませんでした。</p><p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p><p>※「送信する」を押下すると、<strong>ユーザーIDはこれ以降変更出来なくなります</strong>のでご注意下さい。</p>");
?>
</form>
<script type="text/javascript">

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
            fetch('../general/useridcheck.php?userid=' + document.form.userid.value)
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
            fetch('../general/useridcheck.php?email=' + document.form.email.value)
            .then((response) => {
                if(response.ok) {
                    return response.json();
                } else {
                    throw new Error();
                }
            })
            .then((result) => {
                if (result.emailresult == 0) {
                    document.getElementById("email-errortext").innerHTML = "このメールアドレスではこれ以上アカウントを作成出来ません（1つのメールアドレスに紐づけられるアカウントの数は最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個です）。<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。<\/a>";
                    document.form.email.classList.add("is-invalid");
                    document.form.email.classList.remove("is-valid");
                } else {
                    if (result.emailresult == 2) document.getElementById("email-errortext").innerHTML = "このメールアドレスを使用したアカウントが既に存在します。ユーザー登録がお済みではありませんか？（1つのメールアドレスに最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個のアカウントを紐づけられます。複数の名義を使うために別のアカウントを作成している場合は、このまま続行して下さい。）<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。<\/a>";
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
    var validuserid = 0;
    var validemail = 0;

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

    fetch('../general/useridcheck.php?userid=' + document.form.userid.value + "&email=" + document.form.email.value)
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
            document.getElementById("email-errortext").innerHTML = "このメールアドレスではこれ以上アカウントを作成出来ません（1つのメールアドレスに紐づけられるアカウントの数は最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個です）。<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。<\/a>";
            document.form.email.classList.add("is-invalid");
            document.form.email.classList.remove("is-valid");
        } else if (validemail == 1) {
            if (result.emailresult == 2) document.getElementById("email-errortext").innerHTML = "このメールアドレスを使用したアカウントが既に存在します。ユーザー登録がお済みではありませんか？（1つのメールアドレスに最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個のアカウントを紐づけられます。複数の名義を使うために別のアカウントを作成している場合は、このまま続行して下さい。）<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。<\/a>";
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
</script>

<?php
require_once(PAGEROOT . 'guest_footer.php');
