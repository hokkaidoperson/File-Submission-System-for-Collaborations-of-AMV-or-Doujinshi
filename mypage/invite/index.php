<?php
require_once('../../set.php');
setup_session();
$titlepart = '招待リンク送信';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者が登録されていないかつシステム管理者
if (!file_exists(DATAROOT . 'users/_promoter.txt') and $_SESSION["admin"]) $accessok = 'p';

//主催者
if ($_SESSION["state"] == 'p') $accessok = 'c';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<strong>主催者が登録されていないシステムの管理者</strong>、もしくは<strong>主催者</strong>のみです。</p>
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
    $filedata = json_decode(file_get_contents_repeat($filename), true);
    if ($filedata["expire"] <= time()) unlink($filename);
}
switch ($accessok) {
    case 'p':
        if (file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) {
            die_mypage('<p>招待リンクを既に送っています。受信者が登録を完了させるまでお待ち下さい。<br>
メール送信より48時間が経過すると招待リンクは無効になります。登録完了前に無効になった場合は、再度この画面で招待リンクを送って下さい。</p>
<p><a href="forceexpire.php">メールの送信先を誤った事が判明した場合は、こちらのリンクをクリックすると、現在の招待リンクを無効化し、正しい送信先に招待リンクを送れるようになります。</a></p>');
        } else {
            echo '<p>このイベントの主催者に対し、メールで招待リンクを送付します。<br>
主催者は、送信されるリンクからアカウントを作成し、各種の詳細設定を行います。</p>
<p>以下に、主催者の連絡先メールアドレスを入力し、「送信」を押して下さい。</p>';
        }
        break;
    case 'c':
        echo '<p><a href="selector.php"><strong>登録済みのユーザーを共同運営者として追加する場合はこちらをクリックして下さい。</strong></a></p>
<p>もしくは、未登録の共同運営者に対し、メールで招待リンクを送付出来ます。<br>
招待リンクを受信する共同運営者は、招待リンクからアカウントを作成し、イベントの運営に合流出来ます。</p>
<p>以下に、共同運営者の連絡先メールアドレスを入力し、「送信」を押して下さい。</p>';
        break;
}
?></p>
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<input type="email" name="email" class="form-control" id="email" onChange="check_individual()">
<div id="email-errortext" class="system-form-error"></div>
</div>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
</div>
<?php
echo_modal_alert("入力内容の検証中にエラーが発生しました。<br>お手数ですが、インターネット接続環境をご確認頂き、再度「送信する」を押して下さい。", "ネットワーク・エラー", null, null, "neterrormodal", "dismissbtn");
echo_modal_confirm("入力したメールアドレス宛てに招待リンクを送信します。<br>よろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。");
?>
</form>
<script type="text/javascript">

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
        const obj = {email: document.form.email.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
        const method = "POST";
        const body = Object.keys(obj).map((key)=>key+"="+encodeURIComponent(obj[key])).join("&");
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
        };
        fetch('../fnc/api_emailduplication.php', {method, headers, body})
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
            } else if (result.emailresult == 0) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスにこれ以上アカウントを紐づける事は出来ません。1つのメールアドレスにつき、紐づけられるアカウントの数は最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個です。";
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
        const obj = {email: document.form.email.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
        const method = "POST";
        const body = Object.keys(obj).map((key)=>key+"="+encodeURIComponent(obj[key])).join("&");
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
        };
        fetch('../fnc/api_emailduplication.php', {method, headers, body})
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
            } else if (result.emailresult == 0) {
                document.getElementById("email-errortext").innerHTML = "このメールアドレスにこれ以上アカウントを紐づける事は出来ません。1つのメールアドレスにつき、紐づけられるアカウントの数は最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個です。";
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



</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
