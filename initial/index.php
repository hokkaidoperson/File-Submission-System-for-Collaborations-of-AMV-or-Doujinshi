<?php
if (file_exists('../dataplace.php')) require_once('../dataplace.php'); else define('DATAROOT', dirname(__FILE__).'/../data/');
if (file_exists(DATAROOT . 'init.txt')) die('初期設定が既に終わっています。');
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../css/bootstrap.css">
<title>初期設定</title>
</head>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probid = 0;
  probnick = 0;
  probmail = 0;
  probpw = 0;
  probst = 0;
  probevname = 0;
  probsiz = 0;
  probsys = 0;
  probfrm = 0;
  probpre = 0;
  probrec = 0;

//必須の場合のパターン・文字種・文字数
  if(document.form.userid.value === ""){
    problem = 1;
    probid = 1;
  } else if(!document.form.userid.value.match(/^[0-9a-zA-Z]*$/)){
    problem = 1;
    probid = 2;
  } else if(document.form.userid.value.length > 20){
    problem = 1;
    probid = 3;
  }

//必須の場合のパターン・文字種・文字数
  if(document.form.nickname.value === ""){
    problem = 1;
    probnick = 1;
  } else if(document.form.nickname.value.length > 30){
    problem = 1;
    probnick = 2;
  }


//メールアドレス形式確認　必須・一致確認
  if(document.form.email.value === ""){
    problem = 1;
    probmail = 1;
  } else if(!document.form.email.value.match(/.+@.+\..+/)){
    problem = 1;
    probmail = 2;
  } else if(document.form.email.value !== document.form.emailagn.value){
    problem = 1;
    probmail = 3;
  }

//必須の場合のパターン・文字数・一致確認
  if(document.form.password.value === ""){
    problem = 1;
    probpw = 1;
  } else if(document.form.password.value.length > 30){
    problem = 1;
    probpw = 2;
  } else if(document.form.password.value.length < 8){
    problem = 1;
    probpw = 3;
  } else if(document.form.password.value !== document.form.passwordagn.value){
    problem = 1;
    probpw = 4;
  }

//必須の場合
  if(document.form.state.value === ""){
    problem = 1;
    probst = 1;
  }

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
  if ( probid == 1) {
    alert( "【ユーザーID】\n入力されていません。" );
  }
  if ( probid == 2) {
    alert( "【ユーザーID】\n半角英数字以外の文字が含まれています（記号も使用出来ません）。別のIDを指定して下さい。" );
  }
  if ( probid == 3) {
    alert( "【ユーザーID】\n文字数が多すぎます（現在" + document.form.userid.value.length + "文字）。20文字以内に抑えて下さい。" );
  }
  if ( probnick == 1) {
    alert( "【ニックネーム】\n入力されていません。" );
  }
  if ( probnick == 2) {
    alert( "【ニックネーム】\n文字数が多すぎます（現在" + document.form.nickname.value.length + "文字）。30文字以内に抑えて下さい。" );
  }
  if ( probmail == 1) {
    alert( "【メールアドレス】\n入力されていません。" );
  }
  if ( probmail == 2) {
    alert( "【メールアドレス】\n正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？" );
  }
  if ( probmail == 3) {
    alert( "【メールアドレス】\n再入力のメールアドレスが違います。もう一度入力して下さい。メールアドレスは間違っていませんか？" );
  }
  if ( probpw == 1) {
    alert( "【パスワード】\n入力されていません。" );
  }
  if ( probpw == 2) {
    alert( "【パスワード】\n文字数が多すぎます（現在" + document.form.password.value.length + "文字）。30文字以内に抑えて下さい。" );
  }
  if ( probpw == 3) {
    alert( "【パスワード】\n文字数が少なすぎます（現在" + document.form.password.value.length + "文字）。8文字以上のパスワードにして下さい。" );
  }
  if ( probpw == 4) {
    alert( "【パスワード】\n再入力のパスワードが違います。もう一度入力して下さい。パスワードは間違っていませんか？" );
  }
  if ( probst == 1) {
    alert( "【あなたの立場】\nいずれかを選択して下さい。" );
  }
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

  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の入力内容を送信します。よろしいですか？（特に、ユーザーIDはこれ以降変更出来なくなりますのでご注意下さい。）')){
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    return true;
  } else{
    return false;
  }

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
<h1>初期設定</h1>
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
管理者のアカウント、およびシステムの最低限必要な事項を設定します。「【必須】」と記載されている項目は必ず入力して下さい。<br><br>
<b>パスワードは絶対に外部に漏れないようにして下さい。</b>第三者によって不正にアクセスされると、提出されたファイルの内容が見られたり、改ざんされたりする可能性があります。<b>イベントの主催者や共同運営者が、あなたのパスワードを直接お聞きする事はありません。</b><br><br>
<b>ユーザーIDは後から変更出来ません</b>。<u>それ以外の項目は、後から変更する事が出来ます</u>（ただし、あなたの立場の変更に際しては、他人による承認が必要になる場合があります）。<br>
ニックネームなどについては、マイページトップ画面の「アカウント情報編集」、システムの設定事項については「システム設定」から編集出来ます。<br><br>
当サイトではJavascript及びCookieを使用します。現在は有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。<br><br>
<a href="https://www.hkdyukkuri.space/filesystem/doc/security" target="_blank">ポータルサイト設置にあたって、セキュリティ上注意すべき点をこちらからご確認願います。</a>
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<h2>管理者アカウントの情報</h2>
<div class="form-group">
<label for="userid">ユーザーID（半角英数字のみ　20文字以内　<b>後から変更出来ません</b>）【必須】</label>
<input type="text" name="userid" class="form-control" id="userid">
<font size="2">※ログインの際にこのユーザーIDを使用します。</font>
</div>
<div class="form-group">
<label for="nickname">ニックネーム（30文字以内）【必須】</label>
<input type="text" name="nickname" class="form-control" id="nickname">
<font size="2">※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。</font>
</div>
<div class="form-group">
<label for="email">メールアドレス【必須】</label>
<input type="email" name="email" class="form-control" id="email">
<font size="2">※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。</font>
</div>
<div class="form-group">
<label for="emailagn">メールアドレス（確認の為再入力）【必須】</label>
<input type="email" name="emailagn" class="form-control" id="emailagn">
</div>
<div class="form-group">
<label for="password">パスワード（8文字以上30文字以内）【必須】</label>
<input type="password" name="password" class="form-control" id="password">
<font size="2">※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</font>
</div>
<div class="form-group">
<label for="passwordagn">パスワード（確認の為再入力）【必須】</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn">
</div>
<div class="form-group">
あなたの立場【必須】
<div class="form-check">
<input id="state-p" class="form-check-input" type="radio" name="state" value="p">
<label class="form-check-label" for="state-p">主催者<br><font size="2">イベントに提出されるあらゆるファイルへのアクセス権や、意思決定権を有します。</font></label>
</div>
<div class="form-check">
<input id="state-c" class="form-check-input" type="radio" name="state" value="c">
<label class="form-check-label" for="state-c">共同運営者<br><font size="2">一部の意思決定権を有します。自分が提出したファイルへのみアクセス権を有しますが、主催者が認めた場合は他人のファイルへアクセス出来る可能性があります。</font></label>
</div>
<div class="form-check">
<input id="state-g" class="form-check-input" type="radio" name="state" value="g">
<label class="form-check-label" for="state-g">一般参加者<br><font size="2">自分が提出したファイルへのみアクセス権を有します。</font></label>
</div>
<div class="form-check">
<input id="state-o" class="form-check-input" type="radio" name="state" value="o">
<label class="form-check-label" for="state-o">非参加者<br><font size="2">このイベントには参加せず、システム管理に関してのみ従事する場合はこちらを選んで下さい。</font></label>
</div>
<font size="2">※「非参加者」と「一般参加者」の間の変更は後から自由に出来ます。共同運営者から一般参加者もしくは非参加者に変更するには主催者の承認が必要です。また、主催者から他の立場へ変更する場合、代わりの主催者を任命する必要があります。</font>
</div>
<h2>システムの設定事項</h2>
<div class="form-group">
<label for="eventname">イベント名（50文字以内）【必須】</label><br/>
<input type="text" name="eventname" id="eventname" class="form-control">
<font size="2">※イベント名は、サイトのトップページなど、随所に表示されます。</font>
</div>
<div class="form-group">
<label for="filesize">ファイル1つ辺りのアップロード可能な最大サイズ（1以上の半角数字）【必須】</label>
<div class="input-group" style="width:8em;">
<input type="text" name="filesize" class="form-control" id="filesize">
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
<input type="email" name="system" class="form-control" id="system">
<div class="form-check">
<input id="systemsend" class="form-check-input" type="checkbox" name="systemsend" value="1">
<label class="form-check-label" for="systemsend">「このアドレスは送信専用です」という旨のメッセージを追記したい場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<font size="2">※作品が提出された時などの通知の際に、システムが自動でメールを送信します。そのメールの送信元（From）を指定する場合はここで指定して下さい。<br>
　よく分からない場合は空欄にしておいて下さい。空欄の場合は、サーバー側のデフォルトの設定を使用します。</font>
</div>
<div class="form-group">
<label for="systemfrom">システムが送信するメールの差出人名（30文字以内）</label>
<input type="text" name="systemfrom" class="form-control" id="systemfrom">
<font size="2">※システムが自動で送信するメールの送信元（From）の差出人名を指定する場合はここで指定して下さい。<br>
　指定すると、メールの閲覧ソフトの差出人名の欄に、メールアドレスの代わりに表示されます。<br>
　空欄の場合は、差出人名の欄にメールアドレスが表示されます。</font>
</div>
<div class="form-group">
<label for="systempre">システムが送信するメールの接頭辞（15文字以内）</label>
<input type="text" name="systempre" class="form-control" id="systempre">
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
<input type="text" name="recaptcha_site" class="form-control" id="recaptcha_site">
</div>
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">シークレットキー：</span>
</div>
<input type="text" name="recaptcha_sec" class="form-control" id="recaptcha_sec">
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
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../js/bootstrap.bundle.js"></script>
</body>
</html>
