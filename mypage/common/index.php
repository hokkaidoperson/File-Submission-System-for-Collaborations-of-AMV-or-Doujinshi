<?php
require_once('../../set.php');
setup_session();
$titlepart = '共通情報の入力・編集';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、共通事項の設定を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


//フォーム設定ファイル読み込み
$userformdata = array();

//添付ファイルを含むかどうかの変数（添付ファイルがある場合はenctypeの設定が必要なため）
$includeattach = FALSE;

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
    if ($userformdata[$i]["type"] == "attach") $includeattach = TRUE;
}

//Javascriptに持って行く用　不要な要素をunset
$tojsp = $userformdata;
$validation_params = generate_validation_params($tojsp);
for ($i = 0; $i <= 9; $i++) {
  unset($tojsp[$i]["detail"]);
  unset($tojsp[$i]["width"]);
  unset($tojsp[$i]["width2"]);
  unset($tojsp[$i]["height"]);
  unset($tojsp[$i]["prefix_a"]);
  unset($tojsp[$i]["suffix_a"]);
  unset($tojsp[$i]["prefix_b"]);
  unset($tojsp[$i]["suffix_b"]);
  unset($tojsp[$i]["arrangement"][0]);
  unset($tojsp[$i]["list"]);
}

$userid = $_SESSION["userid"];

//入力済み情報を読み込む
$entereddata = json_decode(file_get_contents_repeat(DATAROOT . "users/" . $userid . ".txt"), true);

//締め切り後は変更不可・例外処理
if (outofterm('userform') != FALSE) $disable = FALSE;
else $disable = TRUE;
if ($_SESSION["state"] == 'p') $disable = FALSE;
if (before_deadline()) $disable = FALSE;
if ((isset($entereddata["common_acceptance"]) and $entereddata["common_acceptance"] == 0) or (isset($entereddata["common_editing"]) and $entereddata["common_editing"] == 1)) {
    $waiting = TRUE;
    $disable = TRUE;
} else $waiting = FALSE;

?>

<h1>共通情報の入力・編集</h1>
<p>以下の内容について入力して下さい。</p>
<p>情報を入力済みの場合、現在登録されている情報が入力欄に入力されています。変更したい項目のみ、入力欄の中身を変更して下さい。</p>
<p>※ニックネームの編集はこの画面では行えません。ニックネームを変更する場合は「<a href="../account/">アカウント情報編集</a>」画面で変更出来ます。</p>
<?php
if ($userformdata == array()) die_mypage('<div class="border border-danger system-border-spacer">
設定可能な項目はありません。
</div>');
if (!before_deadline() and $_SESSION["state"] == 'p') echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、主催者は常時共通情報の編集が可能です。
</div>';
else if (!before_deadline() and !$disable) echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、あなたは主催者から共通情報の編集を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('userform')) . 'まで）。
</div>';
else if (!before_deadline()) echo '<div class="border border-danger system-border-spacer">
現在、ファイル提出期間外です。入力内容の確認は出来ますが、変更は出来ません。
</div>';
if ($waiting) {
    echo '<div class="border border-danger system-border-spacer">
現在、共通情報の確認待ちです。確認が完了するまでは、共通情報の編集が出来ません。
</div>';
}
?>
<form name="form" action="handle.php" method="post" <?php
if ($includeattach) echo 'enctype="multipart/form-data" ';
?> onSubmit="return validation_call_custom();">
<div class="border border-primary system-border-spacer">
<?php 
echo '<div class="border-bottom border-primary table-primary system-border-spacecancel">
共通情報の承認状態：';
if (isset($entereddata["common_acceptance"])) {
    if (isset($entereddata["common_editing"]) and $entereddata["common_editing"] == 1) echo '項目編集の承認待ち<br>※変更後の内容は下記に反映されていません。';
    else switch ($entereddata["common_acceptance"]) {
        case 0:
            echo '承認待ち';
        break;
        case 1:
            echo '<span class="text-success"><strong>承認</strong></span>';
        break;
        case 2:
            echo '<span class="text-danger"><strong>承認見送り</strong></span>';
        break;
    }
} else echo '未入力';
echo '</div>';

csrf_prevention_in_form();

foreach ($userformdata as $data) {
    echo_custom_item($data, TRUE, $disable, $entereddata[$data["id"]]);
}

echo_buttons(["primary"], ["submit"], ['<i class="bi bi-upload"></i> 送信する'], '※送信前に、入力内容の確認をお願い致します。', [], [$disable]);
?>
</div>
<?php
echo_modal_confirm(null, null, null, null, null, null, null, null, "closesubmit();");
echo_modal_wait();
?>
</form>
<script type="text/javascript">
let types = {
    <?php echo $validation_params[0]; ?>
};
let rules = {
    <?php echo $validation_params[1]; ?>
};

var changed = false;
var setting = <?php echo json_encode($tojsp); ?>;

function validation_call_custom(indv = null){
    Validator.registerAsync('attachment_validation', function(userid, req, attribute, passes) {
        check_attachments(setting[req], "custom-" + setting[req]["id"])
        .then((result) => {
            if (result == 0) passes();
            else passes(false, result);
        });
    });
    changed = true;
    var items = {};
    Object.keys(types).forEach(function(id) {
        items[id] = get_value(id);
    });
    if (indv !== null) form_validation(items, types, rules, indv);
    else form_validation(items, types, rules, null, function(result) {
        if (result !== null) {
            scroll_and_focus(result);
            return false;
        }
        $('#confirmmodal').modal();
        $('#confirmmodal').on('shown.bs.modal', function () {
            document.getElementById("submitbtn").focus();
        });
    });
    return false;
}

window.addEventListener('beforeunload', function (e) {
    if (changed) {
        e.preventDefault();
        e.returnValue = '';
    }
});


</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
