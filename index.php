<?php
require_once('set.php');
setup_session();
//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("./mypage/index.php");
}

//recaptcha周りの参考URL https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents_repeat(DATAROOT . 'rec.txt'), true);

$userec = FALSE;
if ($recdata["site"] != "" and $recdata["sec"] != "" and extension_loaded('curl')) $userec = TRUE;

if ($userec) {
    $includepart = "<script src='https://www.google.com/recaptcha/api.js' async defer></script>";
    $bodyincludepart = ' style="margin-bottom: 90px;"';
}
require_once(PAGEROOT . 'guest_header.php');

?>

<h1><?php echo $eventname; ?>　ファイル提出用ポータルサイト</h1>
<p class="text-right"><a href='open/index.php' target="_blank"><i class="bi bi-question-circle-fill text-dark"></i> ヘルプ</a></p>
<?php if (isset($_SESSION['guest_redirto']) and $_SESSION['guest_redirto'] != "") echo '<div class="border border-primary system-border-spacer">
このページの利用にはログインが必要です。
</div>'; ?>
<div class="border system-border-spacer">
当サイトではJavascript及びCookieを使用します。現在は有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。
</div>
<div class="border border-primary system-border-spacer">
<h2 class="border-bottom border-primary table-primary h3 p-1"><i class="bi bi-lock-fill"></i> ログイン</h2>
<form name="form" action="login.php" method="post" onSubmit="return check()">
<?php
csrf_prevention_in_form();
?>
<div>
<label for="userid">ユーザーID</label>
<input type="text" name="userid" class="form-control" id="userid" autofocus onChange="check_individual(&quot;userid&quot;);">
<div id="userid-errortext" class="system-form-error"></div>
</div>
<div class="mb-2">
<label for="password">パスワード</label>
<input type="password" name="password" class="form-control" id="password" onChange="check_individual(&quot;password&quot;);">
<div id="password-errortext" class="system-form-error"></div>
</div>
<?php
if ($userec) echo '<div id=\'recaptcha\' class="g-recaptcha" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit" data-error-callback="recError" data-size="invisible"></div>
<button class="btn btn-primary" type="submit"><i class="bi bi-box-arrow-in-right"></i> ログイン</button><div class="small text-muted mb-2 system-asterisk-indent">※ログインボタン押下直後、あなたがスパムやボットでない事を確かめるために画像認証画面が表示される場合があります。</div>';
else echo '<button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> ログイン</button>';
?>
<div id="neterrortext" class="small text-danger" style="display: none;">ユーザーの認証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「ログイン」を押して下さい。</div>
</form>
<div class="small"><a href='reset_pw/index.php' class="d-flex"><i class="bi bi-question-circle-fill text-dark pr-1"></i>パスワードを忘れてしまった方はこちらから再発行して下さい。</a><a href='search_id/index.php' class="d-flex"><i class="bi bi-question-circle-fill text-dark pr-1"></i>ユーザーID・ニックネームを忘れてしまいパスワード再発行が行えない方はこちらから再送信して下さい。</a></div>
</div>
<div class="border border-primary system-border-spacer">
<a href='register/general/index.php' class="d-flex"><i class="bi bi-person-plus-fill text-dark pr-1"></i><?php echo $eventname; ?>のポータルサイトに未登録の参加者はこちらから登録して下さい。</a>
</div>
<div class="border border-success system-border-spacer">
<?php echo $eventname; ?>では、<a href='https://www.hkdyukkuri.space/filesystem/' target="_blank" rel="noopener">MAD合作・合同誌向けファイル提出システム</a>を利用しています。<br>
また、本システムでは、ウェブデザインの調整に<a href="https://getbootstrap.jp/" target="_blank" rel="noopener">Bootstrap4</a> / <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a>を利用しています。
</div>
<div class="border border-success system-border-spacer">
バージョン情報：<?php echo VERSION; ?>
</div>
<script type="text/javascript">

function check_individual(id){
  if (id === "userid") {
      document.getElementById("userid-errortext").innerHTML = "";
      if(document.form.userid.value === ""){
        problem = 1;
        document.getElementById("userid-errortext").innerHTML = "入力されていません。";
        document.form.userid.classList.add("is-invalid");
        document.form.userid.classList.remove("is-valid");
      } else {
        document.form.userid.classList.add("is-valid");
        document.form.userid.classList.remove("is-invalid");
      }
      return;
  }

  if (id === "password") {
      document.getElementById("password-errortext").innerHTML = "";
      if(document.form.password.value === ""){
        problem = 1;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
        document.form.password.classList.add("is-invalid");
        document.form.password.classList.remove("is-valid");
      } else {
        document.form.password.classList.add("is-valid");
        document.form.password.classList.remove("is-invalid");
      }
      return;
  }
}

function check(){

  var problem = 0;
  document.getElementById("neterrortext").style.display = "none";
  document.getElementById("userid-errortext").innerHTML = "";
  if(document.form.userid.value === ""){
    problem = 1;
    document.getElementById("userid-errortext").innerHTML = "入力されていません。";
    document.form.userid.classList.add("is-invalid");
    document.form.userid.classList.remove("is-valid");
  } else {
    document.form.userid.classList.add("is-valid");
    document.form.userid.classList.remove("is-invalid");
  }

  document.getElementById("password-errortext").innerHTML = "";
  if(document.form.password.value === ""){
    problem = 1;
    document.getElementById("password-errortext").innerHTML = "入力されていません。";
    document.form.password.classList.add("is-invalid");
    document.form.password.classList.remove("is-valid");
  } else {
    document.form.password.classList.add("is-valid");
    document.form.password.classList.remove("is-invalid");
  }

  if ( problem == 1 ) {
    return false;
  }

  <?php if ($userec) echo "grecaptcha.execute(); return false;"; else echo "return true;"; ?>

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
