<?php
require_once('../../set.php');
session_start();
$titlepart = '招待リンク送信';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'invite_forceexpire') {
    echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
招待リンクをリセットしました。正しい送信先にリンクを送り直して下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'invite_sent') {
    echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
招待リンクを送信しました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'invite_addco') {
    echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
手続用リンクを送信しました。
</div>';
    $_SESSION["situation"] = '';
}

$accessok = 'none';

//主催者が登録されていないかつシステム管理者
if (!file_exists(DATAROOT . 'users/_promoter.txt') and $_SESSION["admin"]) $accessok = 'p';

//主催者かつフォーム設定完了済み
if ($_SESSION["state"] == 'p' and file_exists(DATAROOT . 'form/userinfo/done.txt')) $accessok = 'c';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者が登録されていないシステムの管理者</b>、もしくは<b>ユーザー登録フォームの設定を完了させた主催者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

?>

<h1><?php
switch ($accessok) {
    case 'p':
        echo '主催者を招待する';
        break;
    case 'c':
        echo '共同運営者の追加・招待';
        break;
}
?></h1>
<p><?php
//有効期限切れのリンクを整理
foreach (glob(DATAROOT . 'mail/invitation/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents($filename), true);
    if ($filedata["expire"] <= time()) unlink($filename);
}
switch ($accessok) {
    case 'p':
        if (file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) {
            die_mypage('招待リンクを既に送っています。受信者が登録を完了させるまでお待ち下さい。<br>
メール送信より48時間が経過すると招待リンクは無効になります。登録完了前に無効になった場合は、再度この画面で招待リンクを送って下さい。<br><br>
<a href="forceexpire.php">メールの送信先を誤った事が判明した場合は、こちらのリンクをクリックすると、現在の招待リンクを無効化し、正しい送信先に招待リンクを送れるようになります。</a></p>');
        } else {
            echo 'このイベントの主催者に対し、メールで招待リンクを送付します。<br>
主催者は、送信されるリンクからアカウントを作成し、各種の詳細設定を行います。<br><br>
以下に、主催者の連絡先メールアドレスを入力し、「送信」を押して下さい。';
        }
        break;
    case 'c':
        echo '<a href="selector.php"><b>登録済みのユーザーを共同運営者として追加する場合はこちらをクリックして下さい。</b></a><br><br>
もしくは、未登録の共同運営者に対し、メールで招待リンクを送付出来ます。<br>
招待リンクを受信する共同運営者は、招待リンクからアカウントを作成し、イベントの運営に合流出来ます。<br><br>
以下に、共同運営者の連絡先メールアドレスを入力し、「送信」を押して下さい。';
        break;
}
?></p>
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<input type="email" name="email" class="form-control" id="email" onBlur="check_individual()">
<div id="email-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
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
入力したメールアドレス宛てに招待リンクを送信します。<br>
よろしければ「送信する」を押して下さい。<br>
入力内容の修正を行う場合は「戻る」を押して下さい。
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
<button type="button" class="btn btn-primary" id="submitbtn" onClick="submittohandle();">送信する</button>
</div>
</div>
</div>
</div>
</form>
<script type="text/javascript">
<!--
function check_individual() {
    var valid = 1;
    document.getElementById("email-errortext").innerHTML = "";
    if(document.form.email.value === ""){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.email.value.match(/.+@.+\..+/)){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
    } else {
        fetch('../fnc/api_emailduplication.php?skipmyself=0&email=' + document.form.email.value)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                throw new Error();
            }
        })
        .then((result) => {
            if (result.emailresult == 0) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスは既に使用されています。";
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

function check(){
    var valid = 1;

    document.getElementById("email-errortext").innerHTML = "";
    if(document.form.email.value === ""){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.email.value.match(/.+@.+\..+/)){
        valid = 0;
        document.getElementById("email-errortext").innerHTML = "正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？";
    } else {
        fetch('../fnc/api_emailduplication.php?skipmyself=0&email=' + document.form.email.value)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                $('#neterrormodal').modal();
                $('#neterrormodal').on('shown.bs.modal', function () {
                    document.getElementById("dismissbtn").focus();
                });
                throw new Error();
            }
        })
        .catch((error) => {
            $('#neterrormodal').modal();
            $('#neterrormodal').on('shown.bs.modal', function () {
                document.getElementById("dismissbtn").focus();
            });
            throw new Error();
        })
        .then((result) => {
            if (result.emailresult == 0) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスは既に使用されています。";
                document.form.email.classList.add("is-invalid");
                document.form.email.classList.remove("is-valid");
            } else {
                document.form.email.classList.add("is-valid");
                document.form.email.classList.remove("is-invalid");
                $('#confirmmodal').modal();
                $('#confirmmodal').on('shown.bs.modal', function () {
                    document.getElementById("submitbtn").focus();
                });
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
    return false;

}

function submittohandle() {
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    document.form.submit();
}


// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
