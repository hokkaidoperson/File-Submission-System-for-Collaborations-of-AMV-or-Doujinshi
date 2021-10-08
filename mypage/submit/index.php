<?php
require_once('../../set.php');
setup_session();
$titlepart = 'ファイル提出';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル提出を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//フォーム設定ファイル読み込み
$submitformdata = array();

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$submitformdata["general"] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

if (outofterm('submit') != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if (!in_term() and $_SESSION["state"] == 'p') $outofterm = TRUE;

//共通情報の項目名取得
$commonitems = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $tmp = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
    $commonitems[$i] = $tmp["title"];
}

//Javascriptに持って行く用　不要な要素をunset
$tojsp = $submitformdata;
unset($tojsp["general"]);
$validation_params = generate_validation_params($tojsp);
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
?>

<h1>ファイル提出</h1>
<p>本イベントに対し、ファイルを<strong>新規提出</strong>します（提出済みファイルの編集は、作品一覧の画面から行って下さい）。</p>
<p>提出されたファイルは、運営チーム（主催者・共同運営者）によって確認されます。確認結果（承認するかどうか）は、登録メールアドレス宛に送信されます。</p>
<p><strong>ファイルは、1作品ごとに送信して下さい</strong>（複数作品のファイルをまとめて送信しないで下さい）。<br>
ファイル提出後はこの画面に戻って来ますので、複数作品を送信したい場合はこの画面から改めて送信願います。</p>
<p>ファイルの提出期間は、<strong><?php echo date('Y年n月j日G時i分s秒', $submitformdata["general"]["from"]) . '～' . date('Y年n月j日G時i分s秒', $submitformdata["general"]["until"]); ?></strong>です。</p>
<div class="border border-warning system-border-spacer">
<strong>【ファイルの提出・情報の編集は時間に余裕を持って行って下さい】</strong><br>
システムの仕様上、入力途中またはファイル送信中に提出締め切りを迎えた場合、締め切り後に送信しようとしたと見なされ送信が遮断されます。<br>
提出・編集したいファイルや情報がある場合、なるべく早めに提出・編集を行って下さい。これは共通情報（ニックネームを含む）についても同様です。
</div>
<?php
if ($submitformdata["general"]["from"] > time() and !$outofterm) die_mypage('<div class="border border-danger system-border-spacer">
提出期間前です。
</div>');
else if ($submitformdata["general"]["until"] <= time() and !$outofterm) die_mypage('<div class="border border-danger system-border-spacer">
提出は締め切られました。
</div>');
if ($outofterm and $_SESSION["state"] == 'p') echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、主催者は常時ファイルの提出が可能です。
</div>';
else if ($outofterm) echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、あなたは主催者からファイル提出を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('submit')) . 'まで）。
</div>';
if (isset($submitformdata["general"]["worknumber"]) and $submitformdata["general"]["worknumber"] != "") {
    $myworks = count_works();
    $mylength = get_length_sum();
    $submitleft = (int)$submitformdata["general"]["worknumber"] - $myworks;
    $lengthleft = (int)$submitformdata["general"]["worklength"] - $mylength;
    if ($submitleft <= 0) die_mypage('<div class="border border-danger system-border-spacer">
提出可能な作品数の上限に達しています（' . $submitformdata["general"]["worknumber"] . '作品まで提出可能）。提出済みの作品を削除しないと、新規提出を行えません。
</div>');
    echo '<div class="border border-primary system-border-spacer">
残り <strong>' . $submitleft . '作品</strong> 提出出来ます（' . $submitformdata["general"]["worknumber"] . '作品まで提出可能）。
</div>';
    echo '<div class="border border-primary system-border-spacer">
残り合計 <strong>' . (int)($lengthleft / 60) . '分' . $lengthleft % 60 . '秒</strong> の動画ファイルを「提出ファイル」に追加出来ます（合計' . (int)((int)$submitformdata["general"]["worklength"] / 60) . '分' . (int)$submitformdata["general"]["worklength"] % 60 . '秒まで提出可能／動画・音声ファイルのみ集計対象）。
</div>';
}
?>
<form name="form" action="handle.php" method="post" enctype="multipart/form-data" onSubmit="return validation_call_custom();">
<div class="border border-primary system-border-spacer">
<?php
csrf_prevention_in_form();

echo_radio([
    "title" => "ファイルの提出方法【必須】",
    "name" => "method",
    "id" => "method",
    "choices" => ["ファイルをサーバーに直接アップロードする（通常はこちらを選んで下さい）", "外部のファイルアップロードサービスを利用して送信する（ファイルサイズの都合など、サーバーへの直接アップロードが出来ない場合に選択して下さい）"],
    "values" => ["direct", "url"],
    "prefill" => "direct",
    "jspart" => 'onChange="validation_call_custom(&quot;method&quot;); swap_submission_field(this.value);"'
]);

echo '<div class="form-group">';
echo '<div id="swap_field_direct" class="collapse show">';
echo_submitfile_section($submitformdata["general"]);
echo '</div>';

echo '<div id="swap_field_url" class="collapse">';
$submitformdata["general"]["detail"] = hsc($submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);
echo_textbox([
    "title" => "提出ファイルのダウンロードURL【必須】",
    "name" => "url",
    "id" => "url",
    "detail" => $submitformdata["general"]["detail"],
    "jspart" => 'onChange="validation_call_custom(&quot;url&quot;);"'
]);
echo_textbox([
    "title" => "ファイルのダウンロードに必要なパスワード（あれば）",
    "name" => "dldpw",
    "id" => "dldpw",
    "detail" => "※サービスによってパスワードの名称が異なります（「復号キー」など）。",
    "jspart" => 'onChange="validation_call_custom(&quot;dldpw&quot;);"'
]);
echo_datetime([
    "title" => "ファイルのダウンロード期限（あれば）",
    "name" => "due",
    "id" => "due",
    "detail" => "※ダウンロードURLに有効期限がある場合は必ず入力して下さい。入力が無い場合は、URLに有効期限が無いものとして扱います。<br>※日付の欄をクリックするとカレンダーから日付を選べます。<br>※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。",
    "jspart" => [
        'onChange="validation_call_custom(&quot;due_date&quot;);"',
        'onChange="validation_call_custom(&quot;due_time&quot;);"'
    ]
]);
echo '</div></div>';

echo_textbox([
    "title" => "タイトル（50文字以内）【必須】",
    "name" => "title",
    "id" => "title",
    "showcounter" => TRUE,
    "jspart" => 'onChange="validation_call_custom(&quot;title&quot;);"'
]);

foreach ($submitformdata as $number => $data) {
    if ($data["type"] === "general") continue;
    echo_custom_item($data);
}

echo_buttons(["primary"], ["submit"], ['<i class="bi bi-upload"></i> 送信する'], '※送信前に、入力内容の確認をお願い致します。');
?>
</div>
<?php
$modaltext = "<p>入力内容に問題は見つかりませんでした。</p><p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p>";
if ($commonitems != array()) {
    $commonitems = implode("、", $commonitems);
    $modaltext .= '<div class="form-check small">
<input id="jumptocommonpage" class="form-check-input" type="checkbox" name="jumptocommonpage" value="1">
<label class="form-check-label" for="jumptocommonpage">提出完了後に共通情報（' . $commonitems . '）の入力・編集画面にジャンプする場合は、左のチェックボックスにチェックして下さい。</label>
</div>';
}

echo_modal_confirm($modaltext, null, null, null, null, null, null, null, "closesubmit();");
echo_modal_wait();
?>
</form>
<script type="text/javascript">
let types = {
    method: 'radio',
    submitfile: 'attach',
    url: 'textbox',
    dldpw: 'textbox',
    due_date: 'textbox',
    due_time: 'textbox',
    title: 'textbox',
    <?php echo $validation_params[0]; ?>
};
let rules = {
    method: 'required',
    submitfile: 'submission_validation',
    url: 'required_if:method,url|url',
    dldpw: 'present',
    due_date: 'date',
    due_time: 'present',
    title: 'required',
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
    if (items.method !== "direct") items.submitfile = '';
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

function swap_submission_field(value) {
    var disable_subject = ["method[0]", "method[1]", "submitfile", "url", "dldpw", "due_date", "due_time"];
    disable_items(disable_subject);
    if (value === "direct") {
        $('#swap_field_url').collapse('hide');
        $('#swap_field_url').on('hidden.bs.collapse', function () {
            $('#swap_field_direct').collapse('show');
            $('#swap_field_direct').on('shown.bs.collapse', function () {
                disable_items(disable_subject, false);
            });
        });
    } else {
        $('#swap_field_direct').collapse('hide');
        $('#swap_field_direct').on('hidden.bs.collapse', function () {
            $('#swap_field_url').collapse('show');
            $('#swap_field_url').on('shown.bs.collapse', function () {
                disable_items(disable_subject, false);
            });
        });
    }
}

function disable_items(array, status = true) {
    array.forEach(function(id) {
        document.getElementById(id).disabled = status;
    });
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
