<?php
require_once('../../../set.php');
session_start();
$titlepart = 'ファイル確認に関する設定';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../../index.php">マイページトップに戻る</a></p>');

if (file_exists(DATAROOT . 'examsetting.txt')) $examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);
if (file_exists(DATAROOT . 'exammember_submit.txt')) $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
else $submitmem = array();
if (file_exists(DATAROOT . 'exammember_edit.txt')) $editmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
else $editmem = array();

?>

<h1>ファイル確認に関する設定</h1>
<p>ファイル確認（提出された作品や情報を確認し、承認するかどうか決める作業）に関する設定をします。</p>
<form name="form" action="handle.php" method="post" onSubmit="return check()" style="margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<h2>ファイル確認の担当者</h2>
<p>主催者・共同運営者のうち、誰がファイル確認を担当するか設定出来ます。</p>
<p>最低でも1人は、ファイル確認のメンバーが必要となります。共同運営者からの辞退などでファイル確認者が誰もいなくなった場合、主催者がファイル確認担当者として自動的に追加されます。</p>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<div class="form-group">
新規提出時、及び提出ファイル（メイン）の変更時の確認担当者（複数選択可）【必須】
<?php
$choices = id_state('c');

echo '<div class="form-check">';
echo '<input id="submit_promoter" class="form-check-input" type="checkbox" name="submit[]" value="_promoter"';
if (array_search("_promoter", $submitmem) !== FALSE) echo ' checked="checked"';
echo '>';
echo '<label class="form-check-label" for="submit_promoter">主催者（' . htmlspecialchars(nickname($_SESSION["userid"])) . '）</label>';
echo '</div>';
foreach ($choices as $choice) {
    $disp = htmlspecialchars(nickname($choice));
    echo '<div class="form-check">';
    echo '<input id="submit_choice_' . $choice . '" class="form-check-input" type="checkbox" name="submit[]" value="' . $choice . '"';
    if (array_search($choice, $submitmem) !== FALSE) echo ' checked="checked"';
    echo '>';
    echo '<label class="form-check-label" for="submit_choice_' . $choice . '">' . $disp . '</label>';
    echo '</div>';
}
?>
<br>
<div class="form-check">
<input id="submit_add" class="form-check-input" type="checkbox" name="submit_add" value="1"<?php
if (isset($examsetting["submit_add"]) and $examsetting["submit_add"] == "1") echo ' checked="checked"';
?>>
<label class="form-check-label" for="submit_add">共同運営者が増えた際、そのユーザーを新規提出時・提出ファイルの変更時の確認メンバーとして自動的に追加する</label>
</div>
</div>
</div>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<div class="form-group">
作品編集時（提出ファイル自体は変更しない場合）、及び共通情報設定時の確認担当者（複数選択可）【必須】
<?php
echo '<div class="form-check">';
echo '<input id="edit_promoter" class="form-check-input" type="checkbox" name="edit[]" value="_promoter"';
if (array_search("_promoter", $editmem) !== FALSE) echo ' checked="checked"';
echo '>';
echo '<label class="form-check-label" for="edit_promoter">主催者（' . htmlspecialchars(nickname($_SESSION["userid"])) . '）</label>';
echo '</div>';
foreach ($choices as $choice) {
    $disp = htmlspecialchars(nickname($choice));
    echo '<div class="form-check">';
    echo '<input id="edit_choice_' . $choice . '" class="form-check-input" type="checkbox" name="edit[]" value="' . $choice . '"';
    if (array_search($choice, $editmem) !== FALSE) echo ' checked="checked"';
    echo '>';
    echo '<label class="form-check-label" for="edit_choice_' . $choice . '">' . $disp . '</label>';
    echo '</div>';
}
?>
<br>
<div class="form-check">
<input id="edit_add" class="form-check-input" type="checkbox" name="edit_add" value="1"<?php
if (isset($examsetting["edit_add"]) and $examsetting["edit_add"] == "1") echo ' checked="checked"';
?>>
<label class="form-check-label" for="edit_add">共同運営者が増えた際、そのユーザーを作品編集・共通情報設定時の確認メンバーとして自動的に追加する</label>
</div>
</div>
</div>
<h2>その他</h2>
<div class="form-group">
作品が修正待ち・拒否になった際の送信者への通知について【必須】
<div class="form-check">
<input id="reason-notice" class="form-check-input" type="radio" name="reason" value="notice" <?php
if (isset($examsetting["reason"]) and $examsetting["reason"] == "notice") echo 'checked="checked"';
?>>
<label class="form-check-label" for="reason-notice">修正待ち・拒否になった理由を記載する（複数人でファイル確認を行っている場合は、修正待ち・拒否に票を入れたメンバー全員分の理由文（下記参照）が記載されます。）</label>
</div>
<div class="form-check">
<input id="reason-dont-a" class="form-check-input" type="radio" name="reason" value="dont-a" <?php
if (isset($examsetting["reason"]) and $examsetting["reason"] == "dont-a") echo 'checked="checked"';
?>>
<label class="form-check-label" for="reason-dont-a">修正待ち・拒否になった理由は記載しないが、「承認されなかった理由についてはお問い合わせ下さい」という旨の文を付け加える</label>
</div>
<div class="form-check">
<input id="reason-dont-b" class="form-check-input" type="radio" name="reason" value="dont-b" <?php
if (isset($examsetting["reason"]) and $examsetting["reason"] == "dont-b") echo 'checked="checked"';
?>>
<label class="form-check-label" for="reason-dont-b">修正待ち・拒否になった理由は記載せず、「承認されなかった理由についてはお答えしかねます」という旨の文を付け加える</label>
</div>
<font size="2">※ファイル確認の際、修正待ち・拒否に票を入れる場合にはその理由を入力する事になっています。ここで記入する理由を「理由文」と呼称します。</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">設定変更</button>
</form>

<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  var problem = 0;
  var probcus = [];


// 参考　http://javascript.pc-users.net/browser/form/checkbox.html
    f = document.getElementsByName("submit[]");
    result = '';
    for(var j = 0; j < f.length; j++ ){
            if(f[j].checked ){
                    result = result +' '+ f[j].value;
            }
    }
    if(result == ''){
      problem = 1;
      probcus.push("【新規提出時、及び提出ファイル（メイン）の変更時の確認担当者】\nいずれかを選択して下さい。");
    }

    f = document.getElementsByName("edit[]");
    result = '';
    for(var j = 0; j < f.length; j++ ){
            if(f[j].checked ){
                    result = result +' '+ f[j].value;
            }
    }
    if(result == ''){
      problem = 1;
      probcus.push("【作品編集時（提出ファイル自体は変更しない場合）の確認担当者】\nいずれかを選択して下さい。");
    }

//必須の場合
  if(document.form.reason.value === ""){
    problem = 1;
    probcus.push("【作品が修正待ち・拒否になった際の送信者への通知について】\nいずれかを選択して下さい。");
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  alert( "入力内容に問題があります。\nエラー内容を順に表示しますので、お手数ですが入力内容の確認をお願いします。" );
  probcus.forEach(function(val){
    alert(val);
  });
  return false;
}

  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の入力内容の通りに設定を変更します。よろしいですか？')){
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
