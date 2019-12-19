<?php
require_once('../../set.php');
session_start();
$titlepart = 'メッセージ新規送信';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') {
    $canshow = users_array();
    unset($canshow[$_SESSION["userid"]]);
} else {
    $list = id_state("p");
    $canshow = array(
        $list[0] => id_array($list[0])
    );
}

?>
<h1>メッセージ新規送信</h1>
<p>下のユーザー一覧からメッセージの宛先を選び、そのユーザーの左側にあるチェックボックスにチェックを入れて下さい。</p>
<p>ユーザー一覧の下の入力欄にメッセージを入力し、「送信」ボタンを押して下さい。</p>

<?php
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') echo '<p>※宛先は複数指定出来ます。</p>';
else echo '<p>※あなたが選択出来る宛先は主催者のみです。</p>';
?>
<form name="form" action="write_handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>選択</th><th>ユーザー</th><th>立場</th>
</tr>
<?php
foreach ($canshow as $author => $array) {
    if (blackuser($author)) continue;
    $nickname = nickname($author);
    echo '<tr>';
    echo '<td>';
    echo '<div class="form-check">';
    echo '<input id="user_' . $author . '" class="form-check-input" type="checkbox" name="to[]" value="' . $author . '">';
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
<div class="border border-primary" style="padding:10px;">
<div class="form-group">
<label for="msg_subject">件名（50文字以内）</label>
<input type="text" name="msg_subject" class="form-control" id="msg_subject" value="">
<font size="2">※空欄の場合、メッセージ本文の最初の30文字が件名に利用されます（30文字を超えた分は省略されます）。</font>
</div>
<div class="form-group">
<label for="msg_content">メッセージ本文（1000文字以内）</label>
<textarea id="msg_content" name="msg_content" rows="4" cols="80" class="form-control"></textarea>
<font size="2">※改行は反映されます（この入力欄で改行すると実際のメッセージでも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">送信</button>
</div>
</form>
<script language="JavaScript" type="text/javascript">
<!--
function check(){

  problem = 0;

  probsel = 0;
  probsub = 0;
  probmsg = 0;

  // 参考　http://javascript.pc-users.net/browser/form/checkbox.html
    f = document.getElementsByName("to[]");
    result = '';
    for(var j = 0; j < f.length; j++ ){
            if(f[j].checked ){
                    result = result +' '+ f[j].value;
            }
    }
    if(result == ''){
        problem = 1;
        probsel = 1;
    }

//文字数
  if(document.form.msg_subject.value === ""){
  } else if(document.form.msg_subject.value.length > 50){
    problem = 1;
    probsub = 1;
  }

//文字数
  if(document.form.msg_content.value === ""){
    problem = 1;
    probmsg = 1;
  } else if(document.form.msg_content.value.length > 1000){
    problem = 1;
    probmsg = 2;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probsel == 1) {
    alert( "【操作対象】\nいずれかを選択して下さい。" );
  }
  if ( probsub == 1) {
    alert( "【件名】\n文字数が多すぎます（現在" + document.form.msg_subject.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  if ( probmsg == 1) {
    alert( "【メッセージ本文】\n入力されていません。" );
  }
  if ( probmsg == 2) {
    alert( "【メッセージ本文】\n文字数が多すぎます（現在" + document.form.msg_content.value.length + "文字）。1000文字以内に抑えて下さい。" );
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
