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
            echo '招待リンクを既に送っています。受信者が登録を完了させるまでお待ち下さい。<br>
メール送信より48時間が経過すると招待リンクは無効になります。登録完了前に無効になった場合は、再度この画面で招待リンクを送って下さい。<br><br>
<a href="forceexpire.php">メールの送信先を誤った事が判明した場合は、こちらのリンクをクリックすると、現在の招待リンクを無効化し、正しい送信先に招待リンクを送れるようになります。</a>';
            $who = 'none';
        } else {
            echo 'このイベントの主催者に対し、メールで招待リンクを送付します。<br>
主催者は、送信されるリンクからアカウントを作成し、各種の詳細設定を行います。<br><br>
以下に、主催者の連絡先メールアドレスを入力し、「送信」を押して下さい。';
            $who = 'prom';
        }
        break;
    case 'c':
        echo '<a href="selector.php"><b>登録済みのユーザーを共同運営者として追加する場合はこちらをクリックして下さい。</b></a><br><br>
もしくは、未登録の共同運営者に対し、メールで招待リンクを送付出来ます。<br>
招待リンクを受信する共同運営者は、招待リンクからアカウントを作成し、イベントの運営に合流出来ます。<br><br>
以下に、共同運営者の連絡先メールアドレスを入力し、「送信」を押して下さい。';
        $who = 'co';
        break;
}
?></p>
<?php
switch ($who) {
    case 'prom':
        echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="towhom" value="promoter">
<div class="form-group">
<input type="email" name="email" class="form-control" id="email">
</div>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary" id="submitbtn">送信する</button>
</form>
</div>
';
        break;
    case 'co':
        echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="towhom" value="co">
<div class="form-group">
<input type="email" name="email" class="form-control" id="email">
</div>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary" id="submitbtn">送信する</button>
</form>
</div>
';

}
?>
<script type="text/javascript">
<!--
function check(){

  problem = 0;
  probmail = 0;

//メールアドレス形式確認　必須・一致確認
  if(document.form.email.value === ""){
    problem = 1;
    probmail = 1;
  } else if(!document.form.email.value.match(/.+@.+\..+/)){
    problem = 1;
    probmail = 2;
  }

//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probmail == 1) {
    alert( "メールアドレスが入力されていません。" );
  }
  if ( probmail == 2) {
    alert( "メールアドレスが正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？" );
  }

  return false;
}

  if(window.confirm('入力したメールアドレス宛てに招待リンクを送信します。よろしいですか？')){
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
