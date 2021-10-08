<?php
require_once('../../../set.php');
setup_session();
$titlepart = 'ファイル提出画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

?>

<h1>項目設定 - 全体</h1>

<div class="border border-primary system-border-spacer">
<form name="form" action="save.php" method="post" onSubmit="return validation_call()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="type" value="general">
<div class="form-group">
<div>ファイル提出期間（開始）【必須】</div>
<label for="from_date">
日付：
</label>
<input type="date" cmanCLDat="USE:ON" name="from_date" class="form-control w-auto d-inline-block" id="from_date" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["from"])) echo date('Y-m-d', $_SESSION["submitformdata"]["general"]["from"]);
?>" onChange="validation_call(&quot;from_date&quot;);">
<div id="from_date-errortext" class="system-form-error"></div>
<label for="from_time">
時刻（24時間制）：
</label>
<input type="time" name="from_time" id="from_time" class="form-control w-auto d-inline-block" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["from"])) echo date('H:i', $_SESSION["submitformdata"]["general"]["from"]);
?>" onChange="validation_call(&quot;from_time&quot;);">
<div id="from_time-errortext" class="system-form-error"></div>
<small class="form-text">※日付の欄をクリックするとカレンダーから日付を選べます。<br>
※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>
　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。<br>
※指定時間になった瞬間に、ファイルの提出受付を開始します。</small>
</div>
<div class="form-group">
<div>ファイル提出期間（締切）【必須】</div>
<label for="until_date">
日付：
</label>
<input type="date" cmanCLDat="USE:ON" name="until_date" class="form-control w-auto d-inline-block" id="until_date" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["until"])) echo date('Y-m-d', $_SESSION["submitformdata"]["general"]["until"]);
?>" onChange="validation_call(&quot;until_date&quot;);">
<div id="until_date-errortext" class="system-form-error"></div>
<label for="until_time">
時刻（24時間制）：
</label>
<input type="time" name="until_time" id="until_time" class="form-control w-auto d-inline-block" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["until"])) echo date('H:i', $_SESSION["submitformdata"]["general"]["until"]);
?>" onChange="validation_call(&quot;until_time&quot;);">
<div id="until_time-errortext" class="system-form-error"></div>
<small class="form-text">※日付・時刻の入力方法については「ファイル提出期間（開始）」をご参照願います。<br>
※指定時間になった瞬間に、ファイルの提出受付を終了します（例えば、10月31日の、日付が変わるギリギリまで提出を受け付ける場合は、締切日時を「11月01日00時00分」に設定して下さい）。</small>
</div>
<?php
echo_textarea([
    "title" => '項目詳細（500文字以内）',
    "name" => 'detail',
    "id" => 'detail',
    "showcounter" => TRUE,
    "jspart" => 'onChange="validation_call(&quot;detail&quot;);"',
    "prefill" => isset($_SESSION["submitformdata"]["general"]["detail"]) ? hsc($_SESSION["submitformdata"]["general"]["detail"]) : '',
    "detail" => "※入力欄の下に、このようにして小さく表示される文字です。<br>　改行は反映されます（この入力欄で改行すると実際の登録画面でも改行されます）が、HTMLタグはお使いになれません。<br>　ただし、URLを記載すると、自動的にリンクが張られます。<br>※入力が無い場合は、入力欄の下に何も表示されません。"
]);
echo_textbox([
    "title" => 'ファイルの拡張子指定（半角英数字（小文字）とカンマ「,」）【必須】',
    "name" => 'ext',
    "id" => 'ext',
    "jspart" => 'onChange="validation_call(&quot;ext&quot;);"',
    "prefill" => isset($_SESSION["submitformdata"]["general"]["ext"]) ? hsc($_SESSION["submitformdata"]["general"]["ext"]) : '',
    "detail" => "※ <code>jpg,png,gif</code> のように、拡張子をカンマ <code>,</code> で区切って指定して下さい（ドット <code>.</code> は付けないで下さい）。<br>※無差別に全ての種類のファイルを受け入れられるようにすると、セキュリティ的に脆弱になる恐れがあります。<br>　項目の用途に応じて、アップロード出来るファイルの種類をある程度制限して下さい。<br>※アップロード出来るファイルの種類を制限しても、悪意あるファイルの全てを防げる訳ではありません。<br>　参加者から送られたファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティ面の準備を万全にしておく事をお勧め致します。"
]);
echo_textbox([
    "title" => 'サーバーに同時にアップロード可能なファイル数（1～100の間の半角数字）',
    "name" => 'filenumber',
    "id" => 'filenumber',
    "jspart" => 'onChange="validation_call(&quot;filenumber&quot;);"',
    "type" => "number",
    "width" => 5,
    "suffix" => "個",
    "prefill" => isset($_SESSION["submitformdata"]["general"]["filenumber"]) ? hsc($_SESSION["submitformdata"]["general"]["filenumber"]) : '',
    "detail" => "※サーバーに直接アップロードする際に提出欄に添付出来るファイル数を設定します。<br>※入力が無い場合は、100個として設定します。<br>※外部URL経由の提出に関しては、チェックの対象外となります。"
]);
echo_textbox([
    "title" => 'サーバーに直接アップロード可能な最大サイズ（1～' . FILE_MAX_SIZE . 'の間の半角数字）',
    "name" => 'size',
    "id" => 'size',
    "jspart" => 'onChange="validation_call(&quot;size&quot;);"',
    "type" => "number",
    "width" => 5,
    "suffix" => "MB",
    "prefill" => isset($_SESSION["submitformdata"]["general"]["size"]) ? hsc($_SESSION["submitformdata"]["general"]["size"]) : '',
    "detail" => "※システム管理者によって、ファイルのサイズは" . FILE_MAX_SIZE . "MBまでに制限されています。<br>※複数個のファイルをこの入力欄に添付出来る設定にしている場合、この入力欄に添付するファイルの合計サイズが、ここで指定するサイズ以下になっている必要があります。<br>※入力が無い場合は、" . FILE_MAX_SIZE . "MBとして設定します。<br>※外部URL経由の提出に関しては、チェックの対象外となります。"
]);
echo_textbox_mlt([
    [
        "prefill" => isset($_SESSION["submitformdata"]["general"]["reso"][0]) ? hsc($_SESSION["submitformdata"]["general"]["reso"][0]) : '',
        "jspart" => 'onChange="validation_call(&quot;reso[0]&quot;);"',
        "width" => 5,
        "prefix" => "横（幅）",
        "suffix" => "px"
    ],
    [
        "prefill" => isset($_SESSION["submitformdata"]["general"]["reso"][1]) ? hsc($_SESSION["submitformdata"]["general"]["reso"][1]) : '',
        "jspart" => 'onChange="validation_call(&quot;reso[1]&quot;);"',
        "width" => 5,
        "prefix" => "縦（高さ）",
        "suffix" => "px"
    ]
], "動画・画像ファイルの最大解像度（1以上の半角数字）", 'reso', 'reso', '※動画・画像ファイル以外の場合は解像度のチェックを行いません。<br>※入力が無い場合は、解像度の制限を設けません。<br>※外部URL経由の提出に関しては、チェックの対象外となります。', "number");
echo_textbox_mlt([
    [
        "prefill" => isset($_SESSION["submitformdata"]["general"]["length"]) ? hsc((int)((int)$_SESSION["submitformdata"]["general"]["length"] / 60)) : '',
        "jspart" => 'onChange="validation_call(&quot;length[0]&quot;);"',
        "width" => 5,
        "suffix" => "分"
    ],
    [
        "prefill" => isset($_SESSION["submitformdata"]["general"]["length"]) ? hsc((int)$_SESSION["submitformdata"]["general"]["length"] % 60) : '',
        "jspart" => 'onChange="validation_call(&quot;length[1]&quot;);"',
        "width" => 5,
        "suffix" => "秒"
    ]
], "動画・音声ファイルの最大再生時間（0分0秒以上の時間指定）", 'length', 'length', '※動画・音声ファイル以外の場合、再生時間は0分0秒として扱われます。<br>※複数個のファイルをこの入力欄に添付出来る設定にしている場合、この入力欄に添付するファイルの合計再生時間が、ここで指定する再生時間以下になっている必要があります。<br>※入力が無い場合は、再生時間の制限を設けません。<br>※外部URL経由の提出に関しては、チェックの対象外となります。', "number");
echo_textbox([
    "title" => '提出可能な作品の最大個数（1以上の半角数字）',
    "name" => 'worknumber',
    "id" => 'worknumber',
    "jspart" => 'onChange="validation_call(&quot;worknumber&quot;);"',
    "type" => "number",
    "width" => 5,
    "suffix" => "個",
    "prefill" => isset($_SESSION["submitformdata"]["general"]["worknumber"]) ? hsc($_SESSION["submitformdata"]["general"]["worknumber"]) : '',
    "detail" => "※作品数の上限を設定出来ます。ユーザーが作品数の上限を超えて作品を提出しようとすると、エラーをユーザーに表示します。<br>※入力が無い場合は、作品数の上限を設定しません。"
]);
echo_textbox_mlt([
    [
        "prefill" => isset($_SESSION["submitformdata"]["general"]["worklength"]) ? hsc((int)((int)$_SESSION["submitformdata"]["general"]["worklength"] / 60)) : '',
        "jspart" => 'onChange="validation_call(&quot;worklength[0]&quot;);"',
        "width" => 5,
        "suffix" => "分"
    ],
    [
        "prefill" => isset($_SESSION["submitformdata"]["general"]["worklength"]) ? hsc((int)$_SESSION["submitformdata"]["general"]["worklength"] % 60) : '',
        "jspart" => 'onChange="validation_call(&quot;worklength[1]&quot;);"',
        "width" => 5,
        "suffix" => "秒"
    ]
], "提出可能な作品の総再生時間（0分0秒以上の時間指定）", 'worklength', 'worklength', '※再生時間の上限を設定出来ます。ユーザーが合計再生時間の上限を超えて作品を提出しようとすると、エラーをユーザーに表示します。<br>※入力が無い場合は、総再生時間の上限を設定しません。<br>※動画・音声ファイルに対してのみ集計を行います。それ以外のファイルの再生時間は0分0秒として扱われます。<br>※外部URL経由の提出に関しては、チェックの対象外となります。', "number");
echo_buttons(["primary", "secondary"], ["submit", "button"], ['<i class="bi bi-check-circle-fill"></i> 設定変更', '<i class="bi bi-x-circle-fill"></i> 変更内容を保存しないで戻る'], NULL, [NULL, "set_link_confirmation_modal('index.php');"]);
?>
</form>
</div>

<script src="../../../js/calendar_script.js" charset="utf-8"></script>
<script type="text/javascript">
let types = {
    from_date: 'textbox',
    from_time: 'textbox',
    until_date: 'textbox',
    until_time: 'textbox',
    detail: 'textarea',
    ext: 'textbox',
    filenumber: 'textbox',
    size: 'textbox',
    "reso[0]": 'textbox',
    "reso[1]": 'textbox',
    "length[0]": 'textbox',
    "length[1]": 'textbox',
    worknumber: 'textbox',
    "worklength[0]": 'textbox',
    "worklength[1]": 'textbox',
};
let rules = {
    from_date: 'required|date',
    from_time: 'required',
    until_date: 'required|date',
    until_time: 'required',
    detail: 'max:500',
    ext: 'required|regex:/^[0-9a-z,]*[0-9a-z]$/',
    filenumber: 'numeric|min:1|max:100',
    size: 'numeric|min:1|max:' + file_size_max,
    "reso[0]": 'required_with:reso[1]|numeric|min:1',
    "reso[1]": 'required_with:reso[0]|numeric|min:1',
    "length[0]": 'required_with:length[1]|numeric|min:0',
    "length[1]": 'required_with:length[0]|numeric|min:0|max:59',
    worknumber: 'numeric|min:1',
    "worklength[0]": 'required_with:worklength[1]|numeric|min:0',
    "worklength[1]": 'required_with:worklength[0]|numeric|min:0|max:59',
};
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
