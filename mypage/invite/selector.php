<?php
require_once('../../set.php');
session_start();
$titlepart = '共同運営者の追加';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../../index.php">マイページトップに戻る</a></p>');

//有効期限切れのリンクを整理
foreach (glob(DATAROOT . 'mail/co_add/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents($filename), true);
    if ($filedata["expire"] <= time()) unlink($filename);
}

?>

<h1>共同運営者の追加</h1>
<p>登録済みのユーザーを共同運営者にします。</p>
<p>予め、新しい共同運営者になる方の了承を得て下さい。<br>
了承を得ましたら、以下のユーザーリストから、新たな共同運営者になる方を選んで下さい。</p>
<p>新たな共同運営者となる方には、手続用のURLが記載されたメールを送信します。<br>
手続きが完了次第、その方が共同運営者に変更となります。</p>
<p>手続きが完了するまでは、その方は引き続き一般参加者もしくは非参加者のままです。<br>
提出済みの作品など、立場以外の情報は変更されません。</p>
<form name="form" action="selector_handle.php" method="post" onSubmit="return check()" style="margin-top:1em; margin-bottom:1em;">
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
    if ($array["state"] == "c") continue;
    if (file_exists(DATAROOT . 'mail/co_add/' . $author . '.txt')) continue;
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
<button type="submit" class="btn btn-primary" id="submitbtn">選択したユーザーを共同運営者に任命する</button>
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
    alert( "【新しい共同運営者】\nいずれかを選択して下さい。" );
  }

  return false;
}

  if(window.confirm('選択したユーザーに手続用のURLを送信します。手続が完了次第、共同運営者が追加されます。\nよろしいですか？')){
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
