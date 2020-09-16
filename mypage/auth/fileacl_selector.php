<?php
require_once('../../set.php');
setup_session();
$titlepart = '共同運営者の他者ファイル閲覧権限';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

//ユーザーID
$userid = basename($_GET["userid"]);

if ($userid == "") die_mypage('パラメーターエラー');
if (!user_exists($userid)) die_mypage('ユーザーが存在しません。');
if (state($userid) != "c") die_mypage('このユーザーは共同運営者ではありません。');

$canshow = array();

foreach(glob(DATAROOT . 'submit/*', GLOB_MARK | GLOB_ONLYDIR) as $dirname) {
    $author = basename($dirname);
    $canshow[$author] = array();
    foreach(glob($dirname . '*.txt') as $filename) {
        $id = basename($filename, '.txt');
        if ($author == $userid) continue;
        $canshow[$author][$id] = json_decode(file_get_contents_repeat($filename), true);
    }
    if ($canshow[$author] == array()) unset($canshow[$author]);
}

$aclplace = DATAROOT . 'fileacl/' . $userid . '.txt';
if (file_exists($aclplace)) $acldata = json_decode(file_get_contents_repeat($aclplace), true);
else $acldata = array();

?>
<h1>共同運営者の他者ファイル閲覧権限 - <?php echo hsc(nickname($userid)); ?></h1>
<p>閲覧を許可したいユーザーもしくは提出作品を選択して下さい（設定済みの場合は、その内容が反映されています）。</p>
<p>共通情報の閲覧を許可すると、該当ユーザーが共通情報として入力した内容や添付ファイルを閲覧・ダウンロード出来ます。<br>
提出作品の閲覧を許可すると、提出時に入力された内容や提出ファイル・添付ファイルを閲覧・ダウンロード出来ます。</p>
<form name="form" action="fileacl_handle.php" method="post" onSubmit="return check()" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="userid" value="<?php echo $userid; ?>">
<h2>共通情報の閲覧許可</h2>
<p>作品を1つ以上提出しているユーザーが一覧化されています。</p>
<div class="form-group">
<div class="form-check">
<input id="tickall1" class="form-check-input" type="checkbox" name="all1" onClick="AllChecked1();">
<label class="form-check-label" for="tickall1"><b>【全て選択】</b></label>
</div>
<?php
foreach ($canshow as $author => $array) {
    $nickname = nickname($author);
    $namepart = hsc($nickname);

    echo '<div class="form-check">';
    echo '<input id="users-' . $author . '" class="form-check-input" type="checkbox" name="users[]" value="' . $author . '_userform" onClick="DisChecked1();"';
    if (array_search($author . '_userform', $acldata) !== FALSE) echo ' checked="checked"';
    echo '>';
    echo '<label class="form-check-label" for="users-' . $author . '">' . $namepart . '</label>';
    echo '</div>';
}
if ($canshow == array()) echo '現在、表示出来るユーザーはありません。';
?>
</div>
<h2>作品情報の閲覧許可</h2>
<p>閲覧を許可したい作品の左側にあるチェックボックスにチェックを入れて下さい。<br>
なお、<?php echo hsc(nickname($userid)); ?>様自身の作品については、もともと閲覧権限があるため、この一覧に載っていません。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>
<div class="form-check">
<input id="tickall2" class="form-check-input" type="checkbox" name="all2" onClick="AllChecked2();">
<label class="form-check-label" for="tickall2"><b>全て選択</b></label>
</div>
</th>
<th>提出者</th><th>作品名</th><th>承認の状態</th>
</tr>
<?php
foreach ($canshow as $author => $array) {
    $nickname = nickname($author);
    $namepart = hsc($nickname);

    foreach ($array as $id => $data) {
        echo "<tr>\n";
        echo '<td>';
        echo '<div class="form-check">';
        echo '<input id="files-' . $author . '-' . $id . '" class="form-check-input" type="checkbox" name="files[]" value="' . $author . '_' . $id . '" onClick="DisChecked2();"';
        if (array_search($author . '_' . $id, $acldata) !== FALSE) echo ' checked="checked"';
        echo '>';
        echo '</div>';
        echo '</td>';
        echo "<td>" . $namepart . "</td>";
        echo '<td>' . hsc($data["title"]) . '</td>';
        if (isset($data["editing"]) and $data["editing"] == 1) echo '<td>項目編集の承認待ち</td>';
        else switch ($data["exam"]) {
            case 1:
                echo '<td class="text-success"><b>承認</b></td>';
            break;
            case 2:
                echo '<td class="text-warning"><b>修正待ち</b></td>';
            break;
            case 3:
                echo '<td class="text-danger"><b>承認見送り</b></td>';
            break;
            default:
                echo '<td>承認待ち</td>';
        }
        echo "</tr>\n";

    }
}
if ($canshow == array()) die_mypage('<tr><td colspan="4">現在、表示出来る作品はありません。</td></tr></table></div></form>');
?>
</table>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">権限を変更する</button>
</form>
<script language="JavaScript" type="text/javascript">
<!--
  function AllChecked1(){
    var all = document.form.all1.checked;
    for (var i=0; i<document.form.elements['users[]'].length; i++){
      document.form.elements['users[]'][i].checked = all;
    }
  }
  function　DisChecked1(){
    var checks = document.form.elements['users[]'];
    var checksCount = 0;
    for (var i=0; i<checks.length; i++){
      if(checks[i].checked == false){
        document.form.all1.checked = false;
      }else{
        checksCount += 1;
        if(checksCount == checks.length){
          document.form.all1.checked = true;
        }
      }
    }
  }
  function AllChecked2(){
    var all = document.form.all2.checked;
    for (var i=0; i<document.form.elements['files[]'].length; i++){
      document.form.elements['files[]'][i].checked = all;
    }
  }
  function　DisChecked2(){
    var checks = document.form.elements['files[]'];
    var checksCount = 0;
    for (var i=0; i<checks.length; i++){
      if(checks[i].checked == false){
        document.form.all2.checked = false;
      }else{
        checksCount += 1;
        if(checksCount == checks.length){
          document.form.all2.checked = true;
        }
      }
    }
  }

function check(){

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
