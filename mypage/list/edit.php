<?php
require_once('../../set.php');
setup_session();
$titlepart = 'ファイル編集';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

//ファイル提出者のユーザーID
$author = basename($_GET["author"]);

//提出ID
$id = basename($_GET["id"]);

if ($author == "" or $id == "") die_mypage('パラメーターエラー');


//自分のファイルのみ編集可
if ($author != $_SESSION['userid']) die_mypage('ご自身のファイルのみ、編集が可能です。');

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die_mypage('現在、ファイル提出期間外のため、ファイル操作は行えません。');


//入力済み情報を読み込む
$entereddata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
if ($entereddata["exam"] == 0 or $entereddata["editing"] == 1) die_mypage('現在、ファイルの確認待ちです。確認が完了するまでは、ファイルの編集が出来ません。');


//フォーム設定ファイル読み込み
$submitformdata = array();

//添付ファイルを含むかどうかの変数（添付ファイルがある場合はenctypeの設定が必要なため）
$includeattach = FALSE;

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
    if ($submitformdata[$i]["type"] == "attach") $includeattach = TRUE;
}
$submitformdata["general"] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

if (isset($entereddata["submit"]) and $entereddata["submit"] != "") {
    $includeattach = TRUE;
    $method = 'direct';
} else $method = 'url';


//Javascriptに持って行く用　不要な要素をunset
$tojsp = $submitformdata;
unset($tojsp["general"]);
$validation_params = generate_validation_params($tojsp);
if ($method == "direct") {
    $validation_params[0] = "submitfile: 'attach',\n    title: 'textbox'," . $validation_params[0];
    $validation_params[1] = "submitfile: 'submission_validation',\n    title: 'required'," . $validation_params[1];
} else {
    $validation_params[0] = "url: 'textbox',\n    dldpw: 'textbox',\n    due_date: 'textbox',\n    due_time: 'textbox',\n    title: 'textbox',\n" . $validation_params[0];
    $validation_params[1] = "url: 'required|url',\n    dldpw: 'present',\n    due_date: 'date',\n    due_time: 'present',\n    title: 'required',\n" . $validation_params[1];
}
$tojsp2 = $submitformdata["general"];
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
    unset($tojsp[$i]["recheck"][0]);
}

$userid = $_SESSION["userid"];

//アップ済みのファイルのサイズ（jspでの引き算処理用）
$uploadedfs = array();

?>

<h1>ファイル編集</h1>
<p>変更したい項目のみ、入力欄の内容を変更して下さい（変更する内容によっては、運営チームによる承認が必要な可能性があります）。</p>
<?php
$mylength = get_length_sum();
$lengthleft = (int)$submitformdata["general"]["worklength"] - $mylength;
echo '<div class="border border-primary system-border-spacer">
残り合計 <strong>' . (int)($lengthleft / 60) . '分' . $lengthleft % 60 . '秒</strong> の動画ファイルを「提出ファイル」に追加出来ます（合計' . (int)((int)$submitformdata["general"]["worklength"] / 60) . '分' . (int)$submitformdata["general"]["worklength"] % 60 . '秒まで提出可能／動画・音声ファイルのみ集計対象）。
</div>';
?>
<form name="form" action="edit_handle.php" method="post" <?php
if ($includeattach) echo 'enctype="multipart/form-data" ';
?> onSubmit="return validation_call_custom();">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="method" value="<?php echo $method; ?>">
<input type="hidden" name="author" value="<?php echo $author; ?>">
<input type="hidden" name="workid" value="<?php echo $id; ?>">
<?php
$submitformdata["general"]["detail"] = hsc($submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);

if ($method == 'direct') {
    echo_submitfile_section($submitformdata["general"], TRUE, FALSE, $entereddata["submit"], $id);
} else {
    $submitformdata["general"]["detail"] = hsc($submitformdata["general"]["detail"]);
    $submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $submitformdata["general"]["detail"]);
    $submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
    $submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);
    echo_textbox([
        "title" => "提出ファイルのダウンロードURL【必須】",
        "name" => "url",
        "id" => "url",
        "prefill" => $entereddata["url"],
        "detail" => $submitformdata["general"]["detail"],
        "jspart" => 'onChange="validation_call_custom(&quot;url&quot;);"'
    ]);
    echo_textbox([
        "title" => "ファイルのダウンロードに必要なパスワード（あれば）",
        "name" => "dldpw",
        "id" => "dldpw",
        "prefill" => $entereddata["dldpw"],
        "detail" => "※サービスによってパスワードの名称が異なります（「復号キー」など）。",
        "jspart" => 'onChange="validation_call_custom(&quot;dldpw&quot;);"'
    ]);
    echo_datetime([
        "title" => "ファイルのダウンロード期限（あれば）",
        "name" => "due",
        "id" => "due",
        "prefill" => [
            isset($entereddata["due"]) ? date('Y-m-d', $entereddata["due"]) : '',
            isset($entereddata["due"]) ? date('H:i', $entereddata["due"]) : ''
        ],
        "detail" => "※ダウンロードURLに有効期限がある場合は必ず入力して下さい。入力が無い場合は、URLに有効期限が無いものとして扱います。<br>※日付の欄をクリックするとカレンダーから日付を選べます。<br>※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。",
        "jspart" => [
            'onChange="validation_call_custom(&quot;due_date&quot;);"',
            'onChange="validation_call_custom(&quot;due_time&quot;);"'
        ]
    ]);
}

echo_textbox([
    "title" => "タイトル（50文字以内）【必須】",
    "name" => "title",
    "id" => "title",
    "prefill" => $entereddata["title"],
    "showcounter" => TRUE,
    "detail" => "<strong>※この項目の変更には、運営メンバーによる承認が必要です。</strong>",
    "jspart" => 'onChange="check_individual(&quot;title&quot;);"'
]);

foreach ($submitformdata as $data) {
    if ($data["type"] === "general") continue;
    echo_custom_item($data, TRUE, FALSE, $entereddata[$data["id"]], $id);
}

echo_buttons(["primary"], ["submit"], ['<i class="bi bi-upload"></i> 送信する'], '※送信前に、入力内容の確認をお願い致します。');
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
var submission_setting = <?php echo json_encode($tojsp2); ?>;

function validation_call_custom(indv = null){
    Validator.registerAsync('submission_validation', function(userid, req, attribute, passes) {
        submission_setting["required"] = "1";
        check_attachments(submission_setting, "submitfile")
        .then((result) => {
            if (result == 0) passes();
            else passes(false, result);
        });
    });
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
