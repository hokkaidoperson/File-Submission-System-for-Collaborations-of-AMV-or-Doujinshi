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
}
$titlepart = "パスワード再発行";
require_once(PAGEROOT . 'guest_header.php');

?>

<h1>パスワード再発行</h1>
<div class="border system-border-spacer">
<p>パスワードの再発行を行うためのURLを、お使いのアカウントのメールアドレスに送信します。</p>
<p>確認の為、お使いのアカウントの情報を以下に入力して下さい。</p>
<p class="d-flex"><span class="pr-1">※</span><span><a href="../search_id/index.php">ユーザーID・ニックネームが分からない場合は、このままではパスワードを再発行出来ないのでこちらをご覧下さい。</a></span></p>
</div>
<div class="border border-primary system-border-spacer">
<form name="form" action="auth.php" method="post" onSubmit="return check()">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<label for="userid">ユーザーID</label>
<input type="text" name="userid" class="form-control" id="userid" onChange="check_individual(&quot;userid&quot;);">
<div id="userid-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
<label for="nickname">ニックネーム</label>
<input type="text" name="nickname" class="form-control" id="nickname" onChange="check_individual(&quot;nickname&quot;);">
<div id="nickname-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
<label for="email">メールアドレス</label>
<input type="email" name="email" class="form-control" id="email" onChange="check_individual(&quot;email&quot;);">
<div id="email-errortext" class="system-form-error"></div>
</div>
<?php
if ($userec) echo '<div id=\'recaptcha\' class="g-recaptcha" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit" data-error-callback="recError" data-size="invisible" data-badge="bottomleft"></div>
<button class="btn btn-primary" type="submit">URLを送信</button><div class="small text-muted d-flex"><span class="pr-1">※</span>「URLを送信」を押下した直後、あなたがスパムやボットでない事を確かめるために画像認証画面が表示される場合があります。</div>';
else echo '<button type="submit" class="btn btn-primary" id="submitbtn">URLを送信</button>';
?>
<div id="neterrortext" style="display: none;"><span class="small text-danger">ユーザーの認証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「URLを送信」を押して下さい。</span></div>
</form>
</div>
<script type="text/javascript">

function check_individual(id){

  if (id === "userid") {
      document.getElementById("userid-errortext").innerHTML = "";
      if(document.form.userid.value === ""){
        document.getElementById("userid-errortext").innerHTML = "入力されていません。";
        document.form.userid.classList.add("is-invalid");
        document.form.userid.classList.remove("is-valid");
      } else {
        document.form.userid.classList.add("is-valid");
        document.form.userid.classList.remove("is-invalid");
      }
      return;
  }

  if (id === "nickname") {
      document.getElementById("nickname-errortext").innerHTML = "";
      if(document.form.nickname.value === ""){
        document.getElementById("nickname-errortext").innerHTML = "入力されていません。";
        document.form.nickname.classList.add("is-invalid");
        document.form.nickname.classList.remove("is-valid");
      } else {
        document.form.nickname.classList.add("is-valid");
        document.form.nickname.classList.remove("is-invalid");
      }
      return;
  }

  if (id === "email") {
      document.getElementById("email-errortext").innerHTML = "";
      if(document.form.email.value === ""){
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
        document.form.email.classList.add("is-invalid");
        document.form.email.classList.remove("is-valid");
      } else {
        document.form.email.classList.add("is-valid");
        document.form.email.classList.remove("is-invalid");
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

  document.getElementById("nickname-errortext").innerHTML = "";
  if(document.form.nickname.value === ""){
    problem = 1;
    document.getElementById("nickname-errortext").innerHTML = "入力されていません。";
    document.form.nickname.classList.add("is-invalid");
    document.form.nickname.classList.remove("is-valid");
  } else {
    document.form.nickname.classList.add("is-valid");
    document.form.nickname.classList.remove("is-invalid");
  }

  document.getElementById("email-errortext").innerHTML = "";
  if(document.form.email.value === ""){
    problem = 1;
    document.getElementById("email-errortext").innerHTML = "入力されていません。";
    document.form.email.classList.add("is-invalid");
    document.form.email.classList.remove("is-valid");
  } else {
    document.form.email.classList.add("is-valid");
    document.form.email.classList.remove("is-invalid");
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
