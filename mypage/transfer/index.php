<?php
require_once('../../set.php');
setup_session();
$titlepart = '引継ぎ用データ作成';
require_once(PAGEROOT . 'mypage_header.php');

if (!$_SESSION["admin"]) die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<strong>システム管理者</strong>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

$entereddata = array();

$entereddata["eventname"] = $eventname;
$entereddata["filesize"] = FILE_MAX_SIZE;
$entereddata["mail"] = json_decode(file_get_contents_repeat(DATAROOT . 'mail.txt'), true);
$entereddata["recaptcha"] = json_decode(file_get_contents_repeat(DATAROOT . 'rec.txt'), true);

?>

<h1>引継ぎ用データ作成</h1>
<p>現在の設定・アカウントを他の提出システムに引き継ぐためのZIPファイルを生成します。<br>
引継ぎ先のシステムでは、生成されたZIPファイルを引継ぎ先のデータディレクトリに設定した上で、初期設定URLにアクセスして下さい。自動的に設定・引継ぎを行います。</p>
<p>以下の「引継ぎ用ZIPファイルを生成」ボタンを押した段階でサーバーに保存されている下記のデータを、ZIPファイル内に格納します。</p>
<ul>
    <li>登録されているアカウント情報（共通情報の入力データ・添付ファイル、メールアドレスなど）</li>
    <li>共通情報の記入事項</li>
    <li>ファイル提出時の記入事項・提出期間などの設定（提出期間などは引継ぎ後に再設定して下さい。）</li>
    <li>ファイル確認に関する設定</li>
    <li>以下の入力画面で設定した初期設定内容</li>
</ul>
<h2>引継ぎ先のシステムの初期設定</h2>
<p>現在の（引継ぎ元の）システムに登録されている情報が入力欄に入力されています。変更したい項目のみ、入力欄の中身を変更して下さい。</p>
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<label for="eventname">イベント名（50文字以内）【必須】</label><br/>
<input type="text" name="eventname" id="eventname" class="form-control" value="<?php
if (isset($entereddata["eventname"])) echo hsc($entereddata["eventname"]);
?>" onkeyup="show_length(value, &quot;eventname-counter&quot;);" onChange="check_individual(&quot;eventname&quot;);">
<div id="eventname-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="eventname-errortext" class="system-form-error"></div>
<small class="form-text">※イベント名は、サイトのトップページなど、随所に表示されます。</small>
</div>
<div class="form-group">
<label for="filesize">1つの入力欄でアップロード可能な最大ファイルサイズ（1以上の半角数字）【必須】</label>
<div class="input-group" style="width:8em;">
<input type="text" name="filesize" class="form-control" id="filesize" value="<?php
if (isset($entereddata["filesize"])) echo hsc($entereddata["filesize"]);
?>" onChange="check_individual(&quot;filesize&quot;);">
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
　<span class="text-decoration-underline">ファイル1つ辺りの最大サイズ（upload_max_filesize）：<strong><?php echo ini_get('upload_max_filesize'); ?></strong></span><br>
　<span class="text-decoration-underline">他の添付ファイルも含めた最大サイズ（post_max_size）：<strong><?php echo ini_get('post_max_size'); ?></strong></span></small>
</div>
<div class="form-group">
<label for="accounts">メールアドレス1つ当たりの最大アカウント数（1～10の半角数字）【必須】</label>
<div class="input-group" style="width:8em;">
<input type="text" name="accounts" class="form-control" id="accounts" value="<?php
echo ACCOUNTS_PER_ADDRESS;
?>" onChange="check_individual(&quot;accounts&quot;);">
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
<input type="email" name="system" class="form-control" id="system" value="<?php
if (isset($entereddata["mail"]["from"])) echo hsc($entereddata["mail"]["from"]);
?>" onChange="check_individual(&quot;system&quot;);">
<div class="form-check">
<input id="systemsend" class="form-check-input" type="checkbox" name="systemsend" value="1" <?php
if (isset($entereddata["mail"]["sendonly"]) and $entereddata["mail"]["sendonly"] == 1) echo 'checked="checked"';
?>>
<label class="form-check-label" for="systemsend">「このアドレスは送信専用です」という旨のメッセージを追記したい場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<div id="system-errortext" class="system-form-error"></div>
<small class="form-text">※作品が提出された時などの通知の際に、システムが自動でメールを送信します。そのメールの送信元（From）を指定する場合はここで指定して下さい。<br>
　よく分からない場合は空欄にしておいて下さい。空欄の場合は、サーバー側のデフォルトの設定を使用します。</small>
</div>
<div class="form-group">
<label for="systemfrom">システムが送信するメールの差出人名（30文字以内）</label>
<input type="text" name="systemfrom" class="form-control" id="systemfrom" value="<?php
if (isset($entereddata["mail"]["fromname"])) echo hsc($entereddata["mail"]["fromname"]);
?>" onkeyup="show_length(value, &quot;systemfrom-counter&quot;);" onChange="check_individual(&quot;systemfrom&quot;);">
<div id="systemfrom-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="systemfrom-errortext" class="system-form-error"></div>
<small class="form-text">※システムが自動で送信するメールの送信元（From）の差出人名を指定する場合はここで指定して下さい。<br>
　指定すると、メールの閲覧ソフトの差出人名の欄に、メールアドレスの代わりに表示されます。<br>
　空欄の場合は、差出人名の欄にメールアドレスが表示されます。</small>
</div>
<div class="form-group">
<label for="systempre">システムが送信するメールの接頭辞（15文字以内）</label>
<input type="text" name="systempre" class="form-control" id="systempre" value="<?php
if (isset($entereddata["mail"]["pre"])) echo hsc($entereddata["mail"]["pre"]);
?>" onkeyup="show_length(value, &quot;systempre-counter&quot;);" onChange="check_individual(&quot;systempre&quot;);">
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
<input type="text" name="recaptcha_site" class="form-control" id="recaptcha_site" value="<?php
if (isset($entereddata["recaptcha"]["site"])) echo hsc($entereddata["recaptcha"]["site"]);
?>" onChange="check_individual(&quot;recaptcha&quot;);">
</div>
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">シークレットキー：</span>
</div>
<input type="text" name="recaptcha_sec" class="form-control" id="recaptcha_sec" value="<?php
if (isset($entereddata["recaptcha"]["sec"])) echo hsc($entereddata["recaptcha"]["sec"]);
?>" onChange="check_individual(&quot;recaptcha&quot;);">
</div>
<div id="recaptcha-errortext" class="system-form-error"></div>
<small class="form-text">※reCAPTCHA v2（非表示reCAPTCHAバッジ／Invisible reCAPTCHA）を利用出来ます。ログイン画面やユーザー登録画面など、ログインしていない状態で利用可能な入力画面を、ロボットなどによる攻撃から保護出来ます。<br>
※reCAPTCHA v2（非表示reCAPTCHAバッジ）の詳細については、各自で調べて下さい。<br>
※reCAPTCHAの管理画面から設定する際は、「reCAPTCHA v2」→「非表示reCAPTCHAバッジ」の順に選択して下さい。<br>
※特に、シークレットキーは外部に漏れてはいけません。データの保管先が外部から見られないように十分注意して下さい（<a href="https://www.hkdyukkuri.space/filesystem/doc/security#%E3%83%87%E3%83%BC%E3%82%BF%E3%82%92%E4%BF%9D%E7%AE%A1%E3%81%99%E3%82%8B%E3%83%87%E3%82%A3%E3%83%AC%E3%82%AF%E3%83%88%E3%83%AA%E3%81%AE%E5%AE%89%E5%85%A8%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6" target="_blank" rel="noopener">詳細</a>）。<br>
※reCAPTCHA v2（非表示reCAPTCHAバッジ）を利用するには、PHPの拡張モジュール Client URL Library（cURL）が有効になっている必要があります。<?php
if (extension_loaded('curl')) echo '<br>　現在、Client URL Libraryが有効になっているため、reCAPTCHA v2をご利用になれます。';
else echo '<br>　<strong>現在、Client URL Libraryが無効になっているため、このままではreCAPTCHA v2をご利用になれません。reCAPTCHA v2を利用するには、Client URL Libraryをインストール・有効化して下さい。</strong>';
?></small>
</div>
<div class="form-group">
検索除けの有効・無効
<div class="form-check">
<input id="robot" class="form-check-input" type="checkbox" name="robot" value="1" <?php
if (META_NOFOLLOW) echo 'checked="checked"';
?>>
<label class="form-check-label" for="robot">このサイトがGoogleやYahoo!などの検索結果に載らないようにしたい場合は、左のチェックボックスにチェックして下さい。</label>
</div>
</div>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">引継ぎ用ZIPファイルを生成</button>
</div>
<?php
echo_modal_alert();
echo_modal_confirm();
?>
</form>
<script type="text/javascript">

function check_individual(id){

    var valid = 1;

    if (id === "eventname") {
        document.getElementById("eventname-errortext").innerHTML = "";
        if(document.form.eventname.value === ""){
            valid = 0;
            document.getElementById("eventname-errortext").innerHTML = "入力されていません。";
        } else if(document.form.eventname.value.length > 50){
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
            valid = 0;
            document.getElementById("filesize-errortext").innerHTML = "入力されていません。";
        } else if(!document.form.filesize.value.match(/^[0-9]*$/)){
            valid = 0;
            document.getElementById("filesize-errortext").innerHTML = "半角数字以外の文字が含まれています。";
        } else if(parseInt(document.form.filesize.value) < 1){
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
            valid = 0;
            document.getElementById("recaptcha-errortext").innerHTML = "入力する場合は、いずれの入力欄も入力して下さい。";
        } else if(!document.form.recaptcha_sec.value.match(/^[0-9a-zA-Z-_]*$/) || !document.form.recaptcha_site.value.match(/^[0-9a-zA-Z-_]*$/)){
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

</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
