<?php
require_once('../../set.php');
setup_session();
$titlepart = '提出期間外の操作権限';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

//ユーザーID
$userid = basename($_GET["userid"]);

if ($userid == "") die_mypage('パラメーターエラー');
if (!user_exists($userid)) die_mypage('ユーザーが存在しません。');

$canshow = array();

foreach(glob(DATAROOT . 'submit/' . $userid . '/*.txt') as $filename) {
    $id = basename($filename, '.txt');
    $canshow[$id] = json_decode(file_get_contents_repeat($filename), true);
}

$aclplace = DATAROOT . 'outofterm/' . $userid . '.txt';
if (file_exists($aclplace)) {
    $acldata = json_decode(file_get_contents_repeat($aclplace), true);
    if ($acldata["expire"] > time()) $set = TRUE;
    else $set = FALSE;
}
else {
    $acldata = array();
    $set = FALSE;
}

?>
<h1>提出期間外の操作権限 - <?php echo hsc(nickname($userid)); ?></h1>
<p>操作を許可したい機能もしくは提出作品を選択して下さい。</p>
<?php
if ($set) echo '<p>現在、一部あるいは全部の操作権限が有効になっています（操作期限：' . date('Y年n月j日G時i分s秒', $acldata["expire"]) . '）<br>
その設定内容が以下に反映されています。<br>
設定を変更すると、操作期限が更新されます。操作期限が切れるまでの時間は改めて設定して下さい。</p>';
?>
<form name="form" action="outofterm_handle.php" method="post" onSubmit="return check()" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="userid" value="<?php echo $userid; ?>">
<h2>操作期限が切れるまでの時間</h2>
<div class="form-group">
<label for="time">（1～100の間の半角数字）</label>
<div class="input-group" style="width:8em;">
<input type="text" name="time" class="form-control" id="time" value="">
<div class="input-group-append">
<span class="input-group-text">時間</span>
</div>
</div>
</div>
<h2>許可する機能を選択</h2>
<div class="form-group">
<div class="form-check">
<input id="userform" class="form-check-input" type="checkbox" name="fncs[]" value="userform"<?php
if (array_search('userform', $acldata) !== FALSE) echo ' checked="checked"';
?>>
<label class="form-check-label" for="userform">共通情報（ニックネーム含む）編集機能</label>
</div>
<div class="form-check">
<input id="submit" class="form-check-input" type="checkbox" name="fncs[]" value="submit"<?php
if (array_search('submit', $acldata) !== FALSE) echo ' checked="checked"';
?>>
<label class="form-check-label" for="submit">ファイル新規提出機能</label>
</div>
</div>
<p>編集を許可したい作品の左側にあるチェックボックスにチェックを入れて下さい。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>
<div class="form-check">
<input id="tickall" class="form-check-input" type="checkbox" name="all" onClick="AllChecked();">
<label class="form-check-label" for="tickall"><strong>全て選択</strong></label>
</div>
</th>
<th>作品名</th><th>承認の状態</th>
</tr>
<?php
foreach ($canshow as $id => $data) {
    echo "<tr>\n";
    echo '<td>';
    echo '<div class="form-check">';
    echo '<input id="' . $id . '" class="form-check-input" type="checkbox" name="files[]" value="' . $id . '" onClick="DisChecked();"';
    if (array_search($id, $acldata) !== FALSE) echo ' checked="checked"';
    echo '>';
    echo '</div>';
    echo '</td>';
    echo '<td>' . hsc($data["title"]) . '</td>';
    if (isset($data["editing"]) and $data["editing"] == 1) echo '<td>項目編集の承認待ち</td>';
    else switch ($data["exam"]) {
        case 1:
            echo '<td class="text-success"><strong>承認</strong></td>';
        break;
        case 2:
            echo '<td class="text-warning"><strong>修正待ち</strong></td>';
        break;
        case 3:
            echo '<td class="text-danger"><strong>承認見送り</strong></td>';
        break;
        default:
            echo '<td>承認待ち</td>';
    }
    echo "</tr>\n";
}
if ($canshow == array()) echo '<tr><td colspan="3">現在、表示出来る作品はありません。</td></tr>';
?>
</table>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">権限を変更する</button>
</form>
<script language="JavaScript" type="text/javascript">

  function AllChecked(){
    var all = document.form.all.checked;
    for (var i=0; i<document.form.elements['files[]'].length; i++){
      document.form.elements['files[]'][i].checked = all;
    }
  }
  function　DisChecked(){
    var checks = document.form.elements['files[]'];
    var checksCount = 0;
    for (var i=0; i<checks.length; i++){
      if(checks[i].checked == false){
        document.form.all.checked = false;
      }else{
        checksCount += 1;
        if(checksCount == checks.length){
          document.form.all.checked = true;
        }
      }
    }
  }

function check(){

  problem = 0;

  probtim = 0;


//文字種・数字の大きさ　必須
  if(document.form.time.value === ""){
    problem = 1;
    probtim = 1;
  } else if(!document.form.time.value.match(/^[0-9]*$/)){
    problem = 1;
    probtim = 2;
  } else if(parseInt(document.form.time.value) < 1 | parseInt(document.form.time.value) > 100){
    problem = 1;
    probtim = 3;
  }

//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probtim == 1) {
    alert( "【操作期限が切れるまでの時間】\n入力されていません。" );
  }
  if ( probtim == 2) {
    alert( "【操作期限が切れるまでの時間】\n半角数字以外の文字が含まれています。" );
  }
  if ( probtim == 3) {
    alert( "【操作期限が切れるまでの時間】\n数字が小さすぎるか、大きすぎます。1～100の間で指定して下さい。" );
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

</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
