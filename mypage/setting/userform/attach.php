<?php
require_once('../../../set.php');
setup_session();
$titlepart = '共通情報入力画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

$number = basename($_GET['number']);

if (!isset($_GET['number']) or !isset($_SESSION["userformdata"][$number]["id"]) or ('attach' != $_SESSION["userformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - 添付ファイル</h1>

<div class="border border-primary system-border-spacer">
<form name="form" action="save.php" method="post" onSubmit="return validation_call()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["userformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="attach">
<?php
echo_textbox([
    "title" => '項目名（50文字以内）【必須】',
    "name" => 'title',
    "id" => 'title',
    "jspart" => 'onChange="validation_call(&quot;title&quot;);"',
    "showcounter" => TRUE,
    "prefill" => isset($_SESSION["userformdata"][$number]["title"]) ? hsc($_SESSION["userformdata"][$number]["title"]) : ''
]);
echo_radio([
    "title" => '必須かどうか【必須】',
    "name" => 'required',
    "id" => 'required',
    "choices" => [
        '任意',
        '必須'
    ],
    "values" => ["0", "1"],
    "jspart" => 'onChange="validation_call(&quot;required&quot;);"',
    "prefill" => isset($_SESSION["userformdata"][$number]["required"]) ? hsc($_SESSION["userformdata"][$number]["required"]) : '',
    "detail" => "※必須項目には「必須」と付記されます。"
]);
echo_textarea([
    "title" => '項目詳細（500文字以内）',
    "name" => 'detail',
    "id" => 'detail',
    "showcounter" => TRUE,
    "jspart" => 'onChange="validation_call(&quot;detail&quot;);"',
    "prefill" => isset($_SESSION["userformdata"][$number]["detail"]) ? hsc($_SESSION["userformdata"][$number]["detail"]) : '',
    "detail" => "※入力欄の下に、このようにして小さく表示される文字です。<br>　改行は反映されます（この入力欄で改行すると実際の登録画面でも改行されます）が、HTMLタグはお使いになれません。<br>　ただし、URLを記載すると、自動的にリンクが張られます。<br>※入力が無い場合は、入力欄の下に何も表示されません。"
]);
echo_textbox([
    "title" => 'ファイルの拡張子指定（半角英数字（小文字）とカンマ「,」）【必須】',
    "name" => 'ext',
    "id" => 'ext',
    "jspart" => 'onChange="validation_call(&quot;ext&quot;);"',
    "prefill" => isset($_SESSION["userformdata"][$number]["ext"]) ? hsc($_SESSION["userformdata"][$number]["ext"]) : '',
    "detail" => "※ <code>jpg,png,gif</code> のように、拡張子をカンマ <code>,</code> で区切って指定して下さい（ドット <code>.</code> は付けないで下さい）。<br>※無差別に全ての種類のファイルを受け入れられるようにすると、セキュリティ的に脆弱になる恐れがあります。<br>　項目の用途に応じて、アップロード出来るファイルの種類をある程度制限して下さい。<br>※アップロード出来るファイルの種類を制限しても、悪意あるファイルの全てを防げる訳ではありません。<br>　参加者から送られたファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティ面の準備を万全にしておく事をお勧め致します。"
]);
echo_textbox([
    "title" => '同時にアップロード可能なファイル数（1～100の間の半角数字）',
    "name" => 'filenumber',
    "id" => 'filenumber',
    "jspart" => 'onChange="validation_call(&quot;filenumber&quot;);"',
    "type" => "number",
    "width" => 5,
    "suffix" => "個",
    "prefill" => isset($_SESSION["userformdata"][$number]["filenumber"]) ? hsc($_SESSION["userformdata"][$number]["filenumber"]) : '',
    "detail" => "※この入力欄に添付出来るファイル数を設定します。<br>※入力が無い場合は、100個として設定します。"
]);
echo_textbox([
    "title" => 'アップロード可能な最大サイズ（1～' . FILE_MAX_SIZE . 'の間の半角数字）',
    "name" => 'size',
    "id" => 'size',
    "jspart" => 'onChange="validation_call(&quot;size&quot;);"',
    "type" => "number",
    "width" => 5,
    "suffix" => "MB",
    "prefill" => isset($_SESSION["userformdata"][$number]["size"]) ? hsc($_SESSION["userformdata"][$number]["size"]) : '',
    "detail" => "※システム管理者によって、ファイルのサイズは" . FILE_MAX_SIZE . "MBまでに制限されています。<br>※複数個のファイルをこの入力欄に添付出来る設定にしている場合、この入力欄に添付するファイルの合計サイズが、ここで指定するサイズ以下になっている必要があります。<br>※入力が無い場合は、" . FILE_MAX_SIZE . "MBとして設定します。"
]);
echo_textbox_mlt([
    [
        "prefill" => isset($_SESSION["userformdata"][$number]["reso"][0]) ? hsc($_SESSION["userformdata"][$number]["reso"][0]) : '',
        "jspart" => 'onChange="validation_call(&quot;reso[0]&quot;);"',
        "width" => 5,
        "prefix" => "横（幅）",
        "suffix" => "px"
    ],
    [
        "prefill" => isset($_SESSION["userformdata"][$number]["reso"][1]) ? hsc($_SESSION["userformdata"][$number]["reso"][1]) : '',
        "jspart" => 'onChange="validation_call(&quot;reso[1]&quot;);"',
        "width" => 5,
        "prefix" => "縦（高さ）",
        "suffix" => "px"
    ]
], "動画・画像ファイルの最大解像度（1以上の半角数字）", 'reso', 'reso', '※現在はmp4ファイルのみ解像度のチェックに対応します。今後、対応するファイルの種類を拡充する予定です。<br>※入力が無い場合は、解像度の制限を設けません。', "number");
echo_textbox_mlt([
    [
        "prefill" => isset($_SESSION["userformdata"][$number]["length"]) ? hsc((int)((int)$_SESSION["userformdata"][$number]["length"] / 60)) : '',
        "jspart" => 'onChange="validation_call(&quot;length[0]&quot;);"',
        "width" => 5,
        "suffix" => "分"
    ],
    [
        "prefill" => isset($_SESSION["userformdata"][$number]["length"]) ? hsc((int)$_SESSION["userformdata"][$number]["length"] % 60) : '',
        "jspart" => 'onChange="validation_call(&quot;length[1]&quot;);"',
        "width" => 5,
        "suffix" => "秒"
    ]
], "動画の最大再生時間（0分0秒以上の時間指定）", 'length', 'length', '※現在はmp4ファイルのみ再生時間のチェックに対応します。今後、対応するファイルの種類を拡充する予定です。<br>※複数個のファイルをこの入力欄に添付出来る設定にしている場合、この入力欄に添付するファイルの合計再生時間が、ここで指定する再生時間以下になっている必要があります。<br>※入力が無い場合は、再生時間の制限を設けません。', "number");
echo_check([
    "title" => '入力内容の変更の自動承認について',
    "name" => 'recheck',
    "id" => 'recheck',
    "choices" => ['この項目の入力内容の変更を自動承認する場合は、左のチェックボックスにチェックして下さい。'],
    "values" => ["auto"],
    "jspart" => 'onChange="validation_call(&quot;recheck[]&quot;);"',
    "prefill" => isset($_SESSION["userformdata"][$number]["recheck"][0]) ? [hsc($_SESSION["userformdata"][$number]["recheck"][0])] : [],
    "detail" => "※自動承認する項目のみ変更する場合は、運営メンバーによる確認を経ずに入力内容を変更します。自動承認しない項目も併せて変更する場合は、運営メンバーによる確認が必要となります。"
]);
echo_buttons(["primary", "secondary"], ["submit", "button"], ['<i class="bi bi-check-circle-fill"></i> 設定変更', '<i class="bi bi-x-circle-fill"></i> 変更内容を保存しないで戻る'], NULL, [NULL, "set_link_confirmation_modal('index.php');"]);
?>
</form>
</div>

<script type="text/javascript">
let types = {
    title: 'textbox',
    required: 'radio',
    detail: 'textarea',
    ext: 'textbox',
    filenumber: 'textbox',
    size: 'textbox',
    "reso[0]": 'textbox',
    "reso[1]": 'textbox',
    "length[0]": 'textbox',
    "length[1]": 'textbox',
    "recheck[]": 'check'
};
let rules = {
    title: 'required|max:50',
    required: 'required',
    detail: 'max:500',
    ext: 'required|regex:/^[0-9a-z,]*[0-9a-z]$/',
    filenumber: 'numeric|min:1|max:100',
    size: 'numeric|min:1|max:' + file_size_max,
    "reso[0]": 'required_with:reso[1]|numeric|min:1',
    "reso[1]": 'required_with:reso[0]|numeric|min:1',
    "length[0]": 'required_with:length[1]|numeric|min:0',
    "length[1]": 'required_with:length[0]|numeric|min:0|max:59',
    "recheck[]": 'in:auto'
};
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
