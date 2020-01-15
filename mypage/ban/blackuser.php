<?php
require_once('../../set.php');
session_start();
$titlepart = 'アカウントの凍結・凍結解除';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

$canshow = users_array();
unset($canshow[$_SESSION["userid"]]);
unset($canshow[id_admin()]);

?>
<h1>アカウントの凍結・凍結解除</h1>
<p>凍結・凍結解除の操作を行いたいユーザーを1人選び、そのユーザーの左側にあるラジオボタン（丸印）にチェックを入れて下さい。</p>
<p>そのユーザーが凍結されていない場合は、凍結します。凍結済みの場合は、凍結解除します。</p>
<p>凍結されたユーザーは、作品提出などの操作を一切行えなくなります。また、提出情報の一括ダウンロード時は、凍結されたユーザーは除外されます。<br>
ただし、凍結されたユーザーのファイルは抹消されず、作品一覧のページから閲覧出来ます（凍結されたユーザーである旨が表示されます）。また、凍結解除の操作を行えば、これまで通りファイル提出などを行えるようになります。</p>

<form name="form" action="blackuser_handle.php" method="post" onSubmit="return check()" style="margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>選択</th><th>ユーザー</th><th>立場</th><th>凍結状態</th>
</tr>
<?php
foreach ($canshow as $author => $array) {
    $nickname = nickname($author);
    echo '<tr>';
    echo '<td>';
    echo '<div class="form-check">';
    echo '<input id="user_' . $author . '" class="form-check-input" type="radio" name="subject" value="' . $author . '">';
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
    if (blackuser($author)) echo '<td class="text-danger">凍結済み</td>';
    else echo '<td>非凍結</td>';
    echo "</tr>\n";
}
if ($canshow == array()) die_mypage('<tr><td colspan="4">現在、表示出来るユーザーはありません。</td></tr></table></div>');
?>
</table>
</div>
<div class="form-group">
<label for="add">メッセージ（500文字以内・省略可能）</label>
<textarea id="message_mail" name="message_mail" rows="4" cols="80" class="form-control"></textarea>
<font size="2">※本人宛に通知するメールに記載するメッセージです。<br>
※例1：「あなたはイベント運営を著しく妨害しているため、アカウントを凍結する運びとなりました。」<br>
※例2：「諸問題の解決が確認出来たため、アカウントの凍結を解除致しました。」
</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">実行</button>
</form>
<script language="JavaScript" type="text/javascript">
<!--
function check(){

  problem = 0;

  probsel = 0;
  probmsg = 0;


  //ラジオボタンの処理　参考：http://allcreator.net/joomz20ps-294/
  if(typeof document.form.subject.innerHTML === 'string') {
    if(!document.form.subject.checked){
      problem = 1;
      probsel = 1;
    }
  } else {
    if(document.form.subject.value === ""){
      problem = 1;
      probsel = 1;
    }
  }

//文字数
  if(document.form.message_mail.value === ""){
  } else if(document.form.message_mail.value.length > 500){
    problem = 1;
    probmsg = 1;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probsel == 1) {
    alert( "【操作対象】\nいずれかを選択して下さい。" );
  }
  if ( probmsg == 1) {
    alert( "【メッセージ】\n文字数が多すぎます（現在" + document.form.message_mail.value.length + "文字）。500文字以内に抑えて下さい。" );
  }

  return false;
}

  if(window.confirm('現在の入力内容を送信します。よろしいですか？')){
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
