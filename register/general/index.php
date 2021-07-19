<?php
require_once('../../set.php');
setup_session();
//ログイン済みの場合はマイページに飛ばす
if ($_SESSION['authinfo'] === 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    redirect("../../mypage/index.php");
}

if (blackip(0, "g")) {
    die_error_html('アクセスが制限されています', '<p>現在ご利用のアクセス元（IPアドレス）からのユーザー登録が制限されているため、ユーザー登録出来ません。<br>
あなた、もしくは同じアクセス元を利用する他の誰かが、イベントの運営を妨害するなどしたために主催者により制限されています。<br>
もしそのような事をした覚えが無い場合は、以下のブロック情報を添えて主催者にご相談下さい。</p>
<p>【ブロック情報】<br>
IPアドレス：' . getenv("REMOTE_ADDR") . '<br>
リモートホスト名：' . gethostbyaddr(getenv("REMOTE_ADDR")) . '</p>');
}

//recaptcha周りの参考URL https://webbibouroku.com/Blog/Article/invisible-recaptcha
$recdata = json_decode(file_get_contents_repeat(DATAROOT . 'rec.txt'), true);

$userec = FALSE;
if ($recdata["site"] != "" and $recdata["sec"] != "" and extension_loaded('curl')) $userec = TRUE;

if ($userec) {
    $includepart = "<script src='https://www.google.com/recaptcha/api.js' async defer></script>";
    $bodyincludepart = ' style="margin-bottom: 90px;"';
}
$titlepart = "アカウント登録";
require_once(PAGEROOT . 'guest_header.php');

?>

<h1>アカウント登録</h1>
<div class="border system-border-spacer">
<p>本イベントのポータルサイトで使用するアカウントを登録します。登録したアカウントを使用して、ファイルを提出して下さい。</p>
<p><strong>パスワードは絶対に外部に漏れないようにして下さい。</strong>第三者によって不正にアクセスされると、提出されたファイルの内容が見られたり、改ざんされたりする可能性があります。<strong>イベントの主催者や共同運営者が、あなたのパスワードを直接お聞きする事はありません。</strong></p>
<p><span class="text-decoration-underline">ユーザーID以外の項目は、後から変更する事が出来ます</span>（マイページトップ画面の「アカウント情報編集」から編集出来ます）。</p>
</div>
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php
csrf_prevention_in_form();

echo_textbox([
    "title" => 'ユーザーID（半角英数字のみ　20文字以内　<strong>後から変更出来ません</strong>）【必須】',
    "name" => 'userid',
    "id" => 'userid',
    "detail" => "※ログインの際にこのユーザーIDを使用します。",
    "jspart" => 'onChange="check_individual(&quot;userid&quot;);"',
    "showcounter" => TRUE,
    "additional_feedback" => '<div id="userid-dupinfo" class="system-form-success" style="display: none;">このユーザーIDはご利用になれます。</div>'
]);
echo_textbox([
    "title" => 'ニックネーム（30文字以内）【必須】',
    "name" => 'nickname',
    "id" => 'nickname',
    "detail" => "※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。",
    "jspart" => 'onChange="check_individual(&quot;nickname&quot;);"',
    "showcounter" => TRUE
]);
echo_textbox([
    "title" => 'メールアドレス【必須】',
    "name" => 'email',
    "id" => 'email',
    "type" => 'email',
    "detail" => "※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。",
    "jspart" => 'onChange="check_individual(&quot;email&quot;);"',
    "confirmation" => TRUE,
    "additional_feedback" => '<div id="email-dupinfo" class="system-form-error" style="display: none;">このメールアドレスを使用したアカウントが既に存在します。ユーザー登録がお済みではありませんか？（1つのメールアドレスに最大' . ACCOUNTS_PER_ADDRESS . '個のアカウントを紐づけられます。複数の名義を使うために別のアカウントを作成している場合は、このまま続行して下さい。）<br><a href="../../search_id/index.php">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。</a></div>'
]);
echo_textbox([
    "title" => 'パスワード（8文字以上72文字以内）【必須】',
    "name" => 'password',
    "id" => 'password',
    "type" => 'password',
    "detail" => "※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。",
    "jspart" => 'onChange="check_individual(&quot;password&quot;);"',
    "showcounter" => TRUE,
    "confirmation" => TRUE
]);
echo_radio([
    "title" => 'あなたの立場',
    "name" => 'state',
    "id" => 'state',
    "choices" => [
        '一般参加者<br><span class="small">自分が提出したファイルへのみアクセス権を有します。</span>'
    ],
    "values" => ["g"],
    "prefill" => "g",
    "detail" => "※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。"
]);

if ($userec) echo '<div id=\'recaptcha\' class="g-recaptcha" data-sitekey="' . $recdata["site"] . '" data-callback="recSubmit" data-error-callback="recError" data-size="invisible"></div>';

echo_buttons(["primary"], ["submit"], ['<i class="bi bi-person-plus-fill"></i> 登録する'], '※送信前に、入力内容の確認をお願い致します。');
?>
</div>
<?php
$modaltext = "<p>入力内容に問題は見つかりませんでした。</p>
<p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>
入力内容の修正を行う場合は「戻る」を押して下さい。</p>
<p>※「送信する」を押下すると、<strong>ユーザーIDはこれ以降変更出来なくなります</strong>のでご注意下さい。</p>";
if ($userec) $modaltext .= '<div><span class="small text-muted">※「送信する」を押下した直後、あなたがスパムやボットでない事を確かめるために画像認証画面が表示される場合があります。</span></div>
<div id="neterrortext" style="display: none;"><span class="small text-danger">ユーザーの認証中にエラーが発生しました。お手数ですが、インターネット接続環境をご確認頂き、再度「送信する」を押して下さい。</span></div>';

echo_modal_alert("入力内容の検証中にエラーが発生しました。<br>お手数ですが、インターネット接続環境をご確認頂き、再度「送信する」を押して下さい。", "ネットワーク・エラー", null, null, "neterrormodal", "dismissbtn2");
echo_modal_confirm($modaltext, null, null, null, null, null, null, null, "submittohandle();");
?>
</form>
<script type="text/javascript">
let types = {
    userid: 'textbox',
    nickname: 'textbox',
    email: 'textbox',
    password: 'textbox',
    state: 'radio'
};
let rules = {
    userid: 'required|alpha_num|max:20|duplication_check_id',
    nickname: 'required|max:30',
    email: 'required|email|confirmed|duplication_check_email',
    password: 'required|max:72|min:8|confirmed',
    state: 'required|in:g'
};

let old_userid = null;
let old_email = null;

let promise_callback_reg = function(result) {
    if (result !== null) {
        scroll_and_focus(result);
        return false;
    }
    $('#confirmmodal').modal();
    $('#confirmmodal').on('shown.bs.modal', function () {
        document.getElementById("submitbtn").focus();
    });
};

function check_individual(id){
    Validator.registerAsync('duplication_check_id', function(userid, attribute, req, passes) {
        if (old_userid === userid) {
            passes();
            return;
        }
        document.getElementById("userid-dupinfo").style.display = "none";
        fetch('useridcheck.php?userid=' + userid)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                throw new Error();
            }
        })
        .then((result) => {
            if (result.idresult === 0) {
                passes(false, '申し訳ございませんが、このユーザーIDは既に使われています。別のユーザーIDをご利用願います。');
            } else {
                document.getElementById("userid-dupinfo").style.display = "block";
                old_userid = userid;
                passes();
            }
        });
    });
    Validator.registerAsync('duplication_check_email', function(email, attribute, req, passes) {
        if (old_email === email) {
            passes();
            return;
        }
        document.getElementById("email-dupinfo").style.display = "none";
        fetch('useridcheck.php?email=' + email)
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                throw new Error();
            }
        })
        .then((result) => {
            if (result.emailresult === 0) {
                passes(false, 'このメールアドレスではこれ以上アカウントを作成出来ません（1つのメールアドレスに紐づけられるアカウントの数は最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個です）。<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。<\/a>');
            } else {
                if (result.emailresult === 2) document.getElementById("email-dupinfo").style.display = "block";
                old_email = email;
                passes();
            }
        });
    });
    var inputs = {
        [id]: document.form[id].value
    };
    if (id === "email" || id === "password") inputs[id + "_confirmation"] = document.form[id + "_confirmation"].value;
    form_validation(inputs, types, rules, id);
}

function check(){
    Validator.registerAsync('duplication_check_id', function(userid, attribute, req, passes) {
        if (old_userid === userid) {
            passes();
            return;
        }
        document.getElementById("userid-dupinfo").style.display = "none";
        fetch('useridcheck.php?userid=' + userid)
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
            if (result.idresult === 0) {
                passes(false, '申し訳ございませんが、このユーザーIDは既に使われています。別のユーザーIDをご利用願います。');
            } else {
                document.getElementById("userid-dupinfo").style.display = "block";
                old_userid = userid;
                passes();
            }
        });
    });
    Validator.registerAsync('duplication_check_email', function(email, attribute, req, passes) {
        if (old_email === email) {
            passes();
            return;
        }
        document.getElementById("email-dupinfo").style.display = "none";
        fetch('useridcheck.php?email=' + email)
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
            if (result.emailresult === 0) {
                passes(false, 'このメールアドレスではこれ以上アカウントを作成出来ません（1つのメールアドレスに紐づけられるアカウントの数は最大<?php echo ACCOUNTS_PER_ADDRESS; ?>個です）。<br><a href=\"../../search_id/index.php\">ユーザーIDをお忘れの場合、こちらから、メールアドレスと紐づいているユーザーのIDを調べられます。<\/a>');
            } else {
                if (result.emailresult === 2) document.getElementById("email-dupinfo").style.display = "block";
                old_email = email;
                passes();
            }
        });
    });
    <?php if ($userec) echo "document.getElementById(\"neterrortext\").style.display = \"none\";"; ?>
    form_validation({
        userid: document.form.userid.value,
        nickname: document.form.nickname.value,
        email: document.form.email.value,
        password: document.form.password.value,
        email_confirmation: document.form.email_confirmation.value,
        password_confirmation: document.form.password_confirmation.value,
        state: 'g'
    }, types, rules, null, promise_callback_reg);
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
    document.getElementById("submitbtn").disabled = "disabled";
    document.form.submit();
EOT;
    ?>
}


</script>

<?php
require_once(PAGEROOT . 'guest_footer.php');
