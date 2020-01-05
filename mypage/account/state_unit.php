<?php
require_once('../../set.php');
session_start();
$titlepart = '立場の変更';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催・共同運営・システム管理者
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c' or $_SESSION["admin"]) $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>、<b>共同運営者</b>、<b>システム管理者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//有効期限切れのリンクを整理
foreach (glob(DATAROOT . 'mail/state/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents($filename), true);
    if ($filedata["expire"] <= time()) unlink($filename);
}

?>

<h1>立場の変更</h1>
<?php
if ($_SESSION["state"] == 'p')  {
    if (file_exists(DATAROOT . 'mail/state/promoter.txt')) die_mypage('手続用のURLを既に新しい主催者に送っています。手続完了までお待ち下さい。<br>
メール送信より48時間が経過するとURLは無効になります。手続完了前に無効になった場合は、再度この画面でURLを送って下さい。');
    ?>
<p>あなたの代わりになる主催者を任命し、主催者を辞退します。</p>
<p>予め、新しい主催者になる方の了承を得て下さい。<br>
了承を得ましたら、以下のユーザーリストから、新たな主催者になる方を選んで下さい。</p>
<p>新たな主催者となる方には、手続用のURLが記載されたメールを送信します。<br>
手続きが完了次第、主催者が交代となります。</p>
<p>手続きが完了するまでは、あなたは引き続き主催者のままです。<br>
手続きが完了次第、あなたは一般参加者に変更となります。<br>
提出済みの作品など、立場以外の情報は変更されません。</p>
<form name="form" action="state_leave_promoter.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>選択</th><th>ユーザー</th><th>立場</th>
</tr>
<?php
$canshow = users_array();
unset($canshow[$_SESSION["userid"]]);
foreach ($canshow as $author => $array) {
    if (blackuser($author)) continue;
    $nickname = nickname($author);
    echo '<tr>';
    echo '<td>';
    echo '<div class="form-check">';
    echo '<input id="user_' . $author . '" class="form-check-input" type="radio" name="userid" value="' . $author . '">';
    echo '</div>';
    echo '</td>';
    echo '<td>';
    echo htmlspecialchars($nickname);
    echo '</td>';

    switch ($array["state"]) {
        case 'p':
            echo '<td>主催者</td>';
        break;
        case 'c':
            echo '<td>共同運営者</td>';
        break;
        case 'g':
            echo '<td>一般参加者</td>';
        break;
        case 'o':
            echo '<td>非参加者</td>';
        break;
    }
    echo "</tr>\n";
}
if ($canshow == array()) die_mypage('<tr><td colspan="3">現在、表示出来るユーザーはありません。</td></tr></table></div>');
?>
</table>
</div>
<br>
<button type="submit" class="btn btn-warning" id="submitbtn">選択したユーザーを主催者に任命し、主催者を辞退する</button>
</form>
<script language="JavaScript" type="text/javascript">
<!--
function check(){

  problem = 0;

  probsel = 0;

  //ラジオボタンの処理　参考：http://allcreator.net/joomz20ps-294/
  if(typeof document.form.userid.innerHTML === 'string') {
    if(!document.form.userid.checked){
      problem = 1;
      probsel = 1;
    }
  } else {
    if(document.form.userid.value === ""){
      problem = 1;
      probsel = 1;
    }
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probsel == 1) {
    alert( "【新しい主催者】\nいずれかを選択して下さい。" );
  }

  return false;
}

  if(window.confirm('選択したユーザーに手続用のURLを送信します。手続が完了次第、あなたは主催者から一般参加者へ変更となります。\nこの操作は取り消せませんが、よろしいですか？')){
  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";

    return true;
  } else{
    return false;
  }
}
// -->
</script>
<?php }
else if ($_SESSION["state"] == 'c')  {
    if (file_exists(DATAROOT . 'mail/state/co_' . $_SESSION["userid"] . '.txt')) die_mypage('手続用のURLを既に主催者に送っています。手続完了までお待ち下さい。<br>
メール送信より48時間が経過するとURLは無効になります。手続完了前に無効になった場合は、再度この画面でURLを送って下さい。');
    ?>
<p>共同運営者を辞退します。</p>
<p>辞退にあたって、予め、主催者の了承を得て下さい。<br>
了承を得ましたら、以下の「共同運営者を辞退する」ボタンを押して下さい。</p>
<p>主催者には、承認用のURLが記載されたメールを送信します。<br>
承認され次第、あなたは一般参加者に変更となります。</p>
<p>承認されるまでは、あなたは引き続き共同運営者のままです。<br>
提出済みの作品など、立場以外の情報は変更されません。</p>
<p><a href="state_leave_co.php" class="btn btn-warning" role="button" onclick="return window.confirm('主催者に手続用のURLを送信します。手続が完了次第、あなたは共同運営者から一般参加者へ変更となります。\nこの操作は取り消せませんが、よろしいですか？')">共同運営者を辞退する</a></p>
<?php }
else if ($_SESSION["admin"]) {
    if ($_SESSION["state"] == 'o') { ?>
<p>あなたの立場を「一般参加者」に変更します。<br>
一般参加者になる事により、本イベントに対してファイルの提出が行えるようになります。</p>
<p>よろしければ、以下の「一般参加者になる」ボタンを押して下さい。</p>
<p><a href="state_admin_switcher.php" class="btn btn-primary" role="button" onclick="return window.confirm('一般参加者になります。よろしいですか？')">一般参加者になる</a></p>
<?php }
    if ($_SESSION["state"] == 'g') { ?>
<p>あなたの立場を「非参加者」に変更します。</p>
<p>非参加者は作品の新規提出・編集を行えませんが、提出済みの作品がある場合、削除はされません。<br>
立場を再び「一般参加者」に変更すると、再び、作品の提出・編集を行えるようになります。<br>
作品も削除したい場合は、予め、「提出済み作品一覧・編集」から、作品を削除して下さい。</p>
<p>よろしければ、以下の「非参加者になる」ボタンを押して下さい。</p>
<p><a href="state_admin_switcher.php" class="btn btn-warning" role="button" onclick="return window.confirm('非参加者になります。よろしいですか？')">非参加者になる</a></p>
<?php }
}

require_once(PAGEROOT . 'mypage_footer.php');
