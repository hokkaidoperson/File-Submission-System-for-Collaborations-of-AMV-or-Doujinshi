<?php
require_once('../../set.php');
session_start();
$titlepart = 'システム設定';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'system_setting') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
システム設定を変更しました。
</div>';
    $_SESSION["situation"] = '';
}

if (!$_SESSION["admin"]) die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>システム管理者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//入力済み情報を読み込む
$entereddata = array();

$entereddata["eventname"] = file_get_contents(DATAROOT . 'eventname.txt');
$entereddata["filesize"] = file_get_contents(DATAROOT . 'maxsize.txt');
$entereddata["mail"] = json_decode(file_get_contents(DATAROOT . 'mail.txt'), true);
$entereddata["recaptcha"] = json_decode(file_get_contents(DATAROOT . 'rec.txt'), true);

?>

<h1>システム設定</h1>
<p>現在登録されている情報が入力欄に入力されています。変更したい項目のみ、入力欄の中身を変更して下さい。</p>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<label for="eventname">イベント名（50文字以内）【必須】</label><br/>
<input type="text" name="eventname" id="eventname" class="form-control" value="<?php
if (isset($entereddata["eventname"])) echo htmlspecialchars($entereddata["eventname"]);
?>">
<font size="2">※イベント名は、サイトのトップページなど、随所に表示されます。</font>
</div>
<div class="form-group">
<label for="filesize">ファイル1つ辺りのアップロード可能な最大サイズ（1以上の半角数字）【必須】</label>
<div class="input-group" style="width:8em;">
<input type="text" name="filesize" class="form-control" id="filesize" value="<?php
if (isset($entereddata["filesize"])) echo htmlspecialchars($entereddata["filesize"]);
?>">
<div class="input-group-append">
<span class="input-group-text">MB</span>
</div>
</div>
<font size="2">※ファイルの最大サイズについては、主催者が入力内容の設定をする時にも設定項目がありますが、こちらは、その際にあまりに大きなファイルサイズを設定してしまわないように制限するためのものとなります。<br>
※この最大サイズを大きくし過ぎると、サーバーの容量をひっ迫させてしまう可能性があります。<br>
　用途を考え、最大サイズをあまり大きくし過ぎないようにして下さい。<br>
※以下に、ファイルアップロードの制限に関する、サーバーの設定値を表示します。<br>
　これらを上回る最大サイズを指定すると、送信中にエラーが返される事がありますので、最大サイズは下記のサイズ以下にして下さい（単位は「バイト」、「M」は「メガ」の事です）。<br>
　<u>ファイル1つ辺りの最大サイズ（upload_max_filesize）：<b><?php echo ini_get('upload_max_filesize'); ?></b></u><br>
　<u>他の添付ファイルも含めた最大サイズ（post_max_size）：<b><?php echo ini_get('post_max_size'); ?></b></u></font>
</div>
<div class="form-group">
<label for="system">システムが送信するメールの送信元アドレス</label>
<input type="email" name="system" class="form-control" id="system" value="<?php
if (isset($entereddata["mail"]["from"])) echo htmlspecialchars($entereddata["mail"]["from"]);
?>">
<div class="form-check">
<input id="systemsend" class="form-check-input" type="checkbox" name="systemsend" value="1" <?php
if (isset($entereddata["mail"]["sendonly"]) and $entereddata["mail"]["sendonly"] == 1) echo 'checked="checked"';
?>>
<label class="form-check-label" for="systemsend">「このアドレスは送信専用です」という旨のメッセージを追記したい場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<font size="2">※作品が提出された時などの通知の際に、システムが自動でメールを送信します。そのメールの送信元（From）を指定する場合はここで指定して下さい。<br>
　よく分からない場合は空欄にしておいて下さい。空欄の場合は、サーバー側のデフォルトの設定を使用します。</font>
</div>
<div class="form-group">
<label for="systemfrom">システムが送信するメールの差出人名（30文字以内）</label>
<input type="text" name="systemfrom" class="form-control" id="systemfrom" value="<?php
if (isset($entereddata["mail"]["fromname"])) echo htmlspecialchars($entereddata["mail"]["fromname"]);
?>">
<font size="2">※システムが自動で送信するメールの送信元（From）の差出人名を指定する場合はここで指定して下さい。<br>
　指定すると、メールの閲覧ソフトの差出人名の欄に、メールアドレスの代わりに表示されます。<br>
　空欄の場合は、差出人名の欄にメールアドレスが表示されます。</font>
</div>
<div class="form-group">
<label for="systempre">システムが送信するメールの接頭辞（15文字以内）</label>
<input type="text" name="systempre" class="form-control" id="systempre" value="<?php
if (isset($entereddata["mail"]["pre"])) echo htmlspecialchars($entereddata["mail"]["pre"]);
?>">
<font size="2">※システムが自動で送信するメールの件名の頭に、ここで指定した接頭辞が付きます。接頭辞は括弧【】で囲われます。<br>
　例えば、接頭辞として「●●合作」と指定した場合は、メールの件名の頭に「【●●合作】」が付きます。<br>
　空欄の場合はイベント名を接頭辞としてそのまま使用しますが、15文字を超えた分は省略されます。</font>
</div>
<div class="form-group">
Invisible reCAPTCHAの設定
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">サイトキー：</span>
</div>
<input type="text" name="recaptcha_site" class="form-control" id="recaptcha_site" value="<?php
if (isset($entereddata["recaptcha"]["site"])) echo htmlspecialchars($entereddata["recaptcha"]["site"]);
?>">
</div>
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">シークレットキー：</span>
</div>
<input type="text" name="recaptcha_sec" class="form-control" id="recaptcha_sec" value="<?php
if (isset($entereddata["recaptcha"]["sec"])) echo htmlspecialchars($entereddata["recaptcha"]["sec"]);
?>">
</div>
<font size="2">※Invisible reCAPTCHAを利用出来ます。ログインフォームやユーザー登録フォームを、ロボットなどによる攻撃から保護出来ます。<br>
※Invisible reCAPTCHAの詳細については、各自で調べて下さい。<br>
※特に、シークレットキーは外部に漏れてはいけません。データの保管先が外部から見られないように十分注意して下さい（<a href="https://www.hkdyukkuri.space/filesystem/doc/security#%E3%83%87%E3%83%BC%E3%82%BF%E3%82%92%E4%BF%9D%E7%AE%A1%E3%81%99%E3%82%8B%E3%83%87%E3%82%A3%E3%83%AC%E3%82%AF%E3%83%88%E3%83%AA%E3%81%AE%E5%AE%89%E5%85%A8%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6" target="_blank">詳細</a>）。</font>
</div>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary" id="submitbtn">送信する</button>
</form>
</div>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probevname = 0;
  probsiz = 0;
  probsys = 0;
  probfrm = 0;
  probpre = 0;
  probrec = 0;


//必須の場合のパターン・文字数
  if(document.form.eventname.value === ""){
    problem = 1;
    probevname = 1;
  } else if(document.form.eventname.value.length > 50){
    problem = 1;
    probevname = 2;
  }

//文字種・数字の大きさ
  if(document.form.filesize.value === ""){
    problem = 1;
    probsiz = 1;
  } else if(!document.form.filesize.value.match(/^[0-9]*$/)){
    problem = 1;
    probsiz = 2;
  } else if(parseInt(document.form.filesize.value) < 1){
    problem = 1;
    probsiz = 3;
  }

//メールアドレス形式確認　必須でない
  if(document.form.system.value === ""){
  } else if(!document.form.system.value.match(/.+@.+\..+/)){
    problem = 1;
    probsys = 1;
  }

//文字数 必須でない
  if(document.form.systemfrom.value === ""){
  } else if(document.form.systemfrom.value.length > 30){
    problem = 1;
    probfrm = 1;
  }

//文字数 必須でない
  if(document.form.systempre.value === ""){
  } else if(document.form.systempre.value.length > 15){
    problem = 1;
    probpre = 1;
  }

//文字種　どっちかかたっぽだけはNG
  if(document.form.recaptcha_sec.value === "" && document.form.recaptcha_site.value === ""){
  } else if(document.form.recaptcha_sec.value === "" || document.form.recaptcha_site.value === ""){
    problem = 1;
    probrec = 1;
  } else if(!document.form.recaptcha_sec.value.match(/^[0-9a-zA-Z-]*$/) || !document.form.recaptcha_site.value.match(/^[0-9a-zA-Z-]*$/)){
    problem = 1;
    probrec = 2;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  alert( "入力内容に問題があります。\nエラー内容を順に表示しますので、お手数ですが入力内容の確認をお願いします。" );
  if ( probevname == 1) {
    alert( "【イベント名】\n入力されていません。" );
  }
  if ( probevname == 2) {
    alert( "【イベント名】\n文字数が多すぎます（現在" + document.form.eventname.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  if ( probsiz == 1) {
    alert( "【ファイル1つ辺りのアップロード可能な最大サイズ】\n入力されていません。" );
  }
  if ( probsiz == 2) {
    alert( "【ファイル1つ辺りのアップロード可能な最大サイズ】\n半角数字以外の文字が含まれています。" );
  }
  if ( probsiz == 3) {
    alert( "【ファイル1つ辺りのアップロード可能な最大サイズ】\n数字が小さすぎます。1以上で指定して下さい。" );
  }
  if ( probsys == 1) {
    alert( "【システムが送信するメールの送信元アドレス】\n正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？" );
  }
  if ( probfrm == 1) {
    alert( "【システムが送信するメールの差出人名】\n文字数が多すぎます（現在" + document.form.systemfrom.value.length + "文字）。30文字以内に抑えて下さい。" );
  }
  if ( probpre == 1) {
    alert( "【システムが送信するメールの接頭辞】\n文字数が多すぎます（現在" + document.form.systempre.value.length + "文字）。15文字以内に抑えて下さい。" );
  }
  if ( probrec == 1) {
    alert( "【Invisible reCAPTCHAの設定】\n入力する場合は、いずれの入力欄も入力して下さい。" );
  }
  if ( probrec == 2) {
    alert( "【Invisible reCAPTCHAの設定】\n正しく入力されていません。入力されたキーをご確認下さい。キーは間違っていませんか？" );
  }

  return false;
}

  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の入力内容を送信します。よろしいですか？')){
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    return true;
  } else{
    return false;
  }

}

// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
