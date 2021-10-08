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
if ($set) echo '<p>現在、一部あるいは全部の操作権限が有効になっており、その設定内容が以下に反映されています。<br>操作権限を解除したい場合は、操作期限に過去の日付を選択するか、許可する機能を全て選択解除して下さい。</p>';
?>
<form name="form" action="outofterm_handle.php" method="post" onSubmit="return validation_call()" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="userid" value="<?php echo $userid; ?>">
<h2>操作期限</h2>
<?php
echo_datetime([
    "title" => "",
    "name" => "time",
    "id" => "time",
    "prefill" => [
        isset($acldata["expire"]) ? date('Y-m-d', $acldata["expire"]) : '',
        isset($acldata["expire"]) ? date('H:i', $acldata["expire"]) : ''
    ],
    "detail" => "※日付の欄をクリックするとカレンダーから日付を選べます。<br>※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。",
    "jspart" => [
        'onChange="validation_call(&quot;time_date&quot;);"',
        'onChange="validation_call(&quot;time_time&quot;);"'
    ]
]);
?>
<h2>許可する機能を選択</h2>
<div class="form-group">
<div class="form-check">
<input id="userform" class="form-check-input" type="checkbox" name="fncs[]" value="userform"<?php
if (array_search('userform', $acldata) !== FALSE) echo ' checked="checked"';
?>>
<label class="form-check-label" for="userform">共通情報（ニックネーム含む）編集機能</label>
</div>
<div class="form-check">
<input id="submitform" class="form-check-input" type="checkbox" name="fncs[]" value="submit"<?php
if (array_search('submit', $acldata) !== FALSE) echo ' checked="checked"';
?>>
<label class="form-check-label" for="submitform">ファイル新規提出機能</label>
</div>
</div>
<p>編集を許可したい作品の左側にあるチェックボックスにチェックを入れて下さい。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th class="system-cell-checkbox">
<div class="form-check">
<input id="tickall_files[]" class="form-check-input" type="checkbox" onClick="tick_all_toggler(&quot;files[]&quot;);">
<label class="form-check-label" for="tickall_files[]"><strong>全て選択</strong></label>
</div>
</th>
<th>作品名</th><th>承認の状態</th>
</tr>
<?php
foreach ($canshow as $id => $data) {
    echo "<tr>\n";
    echo '<td>';
    echo '<div class="form-check">';
    echo '<input id="' . $id . '" class="form-check-input" type="checkbox" name="files[]" value="' . $id . '" onClick="tick_all_child_toggler(&quot;files[]&quot;);"';
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
<?php
echo_buttons(["primary"], ["submit"], ['<i class="bi bi-check-circle-fill"></i> 権限を変更する']);
?>
</form>
<script language="JavaScript" type="text/javascript">
let types = {
    time_date: 'textbox',
    time_time: 'textbox',
};
let rules = {
    time_date: 'required|date',
    time_time: 'required',
};
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
