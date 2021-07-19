<?php
require_once('../../../set.php');
setup_session();
$titlepart = '共通情報入力画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

$number = basename($_GET['number']);

if (!isset($_GET['number']) or !isset($_SESSION["userformdata"][$number]["id"]) or ('textarea' != $_SESSION["userformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - テキストエリア</h1>

<div class="border border-primary system-border-spacer">
<form name="form" action="save.php" method="post" onSubmit="return validation_call()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["userformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="textarea">
<?php
$disable_status = [];
$quantity = isset($_SESSION["userformdata"][$number]["quantity"]) ? (int)$_SESSION["userformdata"][$number]["quantity"] : 1;
for ($i = 0; $i < 5; $i++) {
    $disable_status[] = ($i >= $quantity);
}

echo_textbox([
    "title" => '項目名（50文字以内）【必須】',
    "name" => 'title',
    "id" => 'title',
    "jspart" => 'onChange="validation_call(&quot;title&quot;);"',
    "showcounter" => TRUE,
    "prefill" => isset($_SESSION["userformdata"][$number]["title"]) ? hsc($_SESSION["userformdata"][$number]["title"]) : ''
]);
echo_dropdown([
    "title" => '入力欄の個数【必須】',
    "name" => 'quantity',
    "id" => 'quantity',
    "choices" => ["1つ", "2つ", "3つ", "4つ", "5つ"],
    "values" => ["1", "2", "3", "4", "5"],
    "width" => 3,
    "jspart" => 'onChange="validation_call(&quot;quantity&quot;); form_setting_enable_form(this.value, multiple_items);"',
    "prefill" => isset($_SESSION["userformdata"][$number]["quantity"]) ? hsc($_SESSION["userformdata"][$number]["quantity"]) : '',
    "detail" => "※文字数制限や接頭辞などは入力欄毎に設定可能です。"
]);
echo_radio([
    "title" => '必須かどうか【必須】',
    "name" => 'required',
    "id" => 'required',
    "choices" => [
        '任意',
        '必須（いずれかの入力欄のみ）',
        '必須（全ての入力欄）'
    ],
    "values" => ["0", "1", "2"],
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
$form_setting = [];
foreach($disable_status as $key => $disable) {
    $form_setting[] = [
        "prefill" => isset($_SESSION["userformdata"][$number]["max"][$key]) ? hsc($_SESSION["userformdata"][$number]["max"][$key]) : '',
        "jspart" => 'onChange="validation_call(&quot;max[' . $key . ']&quot;);"',
        "width" => 5,
        "prefix" => ($key + 1) . "つ目の入力欄",
        "disabled" => $disable_status[$key]
    ];
}
echo_textbox_mlt($form_setting, "最大文字数（1～9999の間の半角数字）", 'max', 'max', '※入力内容が、ここで指定する文字数を超えている場合に、警告を発して再入力を促します。<br>　入力が無い場合は、9999文字が最大となります。', "number");
$form_setting = [];
foreach($disable_status as $key => $disable) {
    $form_setting[] = [
        "prefill" => isset($_SESSION["userformdata"][$number]["min"][$key]) ? hsc($_SESSION["userformdata"][$number]["min"][$key]) : '',
        "jspart" => 'onChange="validation_call(&quot;min[' . $key . ']&quot;);"',
        "width" => 5,
        "prefix" => ($key + 1) . "つ目の入力欄",
        "disabled" => $disable_status[$key]
    ];
}
echo_textbox_mlt($form_setting, "最小文字数（1～9999の間の半角数字）", 'min', 'min', '※入力内容が、ここで指定する文字数を下回っている場合に、警告を発して再入力を促します。<br>　入力が無い場合は、最小文字数を設けません。', "number");
$form_setting = [];
foreach($disable_status as $key => $disable) {
    $form_setting[] = [
        "prefill" => isset($_SESSION["userformdata"][$number]["width"][$key]) ? hsc($_SESSION["userformdata"][$number]["width"][$key]) : '',
        "jspart" => 'onChange="validation_call(&quot;width[' . $key . ']&quot;);"',
        "width" => 5,
        "prefix" => ($key + 1) . "つ目の入力欄",
        "suffix" => "em",
        "disabled" => $disable_status[$key]
    ];
}
echo_textbox_mlt($form_setting, "入力欄の幅（1以上の半角数字）", 'width', 'width', '※指定が無い場合は、入力欄は画面の端から端まで表示されます。<br>　数文字を入力するだけの欄など、入力欄が短くてもよい場合は、ここで調節して下さい。<br>※「em」はフォントサイズを基準とした単位です（1em＝大体1文字分　と認識してよいと思います）。', "number");
$form_setting = [];
foreach($disable_status as $key => $disable) {
    $form_setting[] = [
        "prefill" => isset($_SESSION["userformdata"][$number]["height"][$key]) ? hsc($_SESSION["userformdata"][$number]["height"][$key]) : '',
        "jspart" => 'onChange="validation_call(&quot;height[' . $key . ']&quot;);"',
        "width" => 5,
        "prefix" => ($key + 1) . "つ目の入力欄",
        "suffix" => "行",
        "disabled" => $disable_status[$key]
    ];
}
echo_textbox_mlt($form_setting, "入力欄の高さ（1以上の半角数字）", 'height', 'height', '※指定が無い場合は、入力欄の高さは5行となります。', "number");
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
    quantity: 'dropdown',
    required: 'radio',
    detail: 'textarea',
    "max[0]": 'textbox',
    "min[0]": 'textbox',
    "width[0]": 'textbox',
    "height[0]": 'textbox',
    "max[1]": 'textbox',
    "min[1]": 'textbox',
    "width[1]": 'textbox',
    "height[1]": 'textbox',
    "max[2]": 'textbox',
    "min[2]": 'textbox',
    "width[2]": 'textbox',
    "height[2]": 'textbox',
    "max[3]": 'textbox',
    "min[3]": 'textbox',
    "width[3]": 'textbox',
    "height[3]": 'textbox',
    "max[4]": 'textbox',
    "min[4]": 'textbox',
    "width[4]": 'textbox',
    "height[4]": 'textbox',
    "recheck[]": 'check'
};
let rules = {
    title: 'required|max:50',
    quantity: 'required',
    required: 'required',
    detail: 'max:500',
    "max[0]": 'numeric|max:9999|min:1',
    "max[1]": 'numeric|max:9999|min:1',
    "max[2]": 'numeric|max:9999|min:1',
    "max[3]": 'numeric|max:9999|min:1',
    "max[4]": 'numeric|max:9999|min:1',
    "min[0]": 'numeric|max:9999|min:1',
    "min[1]": 'numeric|max:9999|min:1',
    "min[2]": 'numeric|max:9999|min:1',
    "min[3]": 'numeric|max:9999|min:1',
    "min[4]": 'numeric|max:9999|min:1',
    "width[0]": 'numeric|min:1',
    "width[1]": 'numeric|min:1',
    "width[2]": 'numeric|min:1',
    "width[3]": 'numeric|min:1',
    "width[4]": 'numeric|min:1',
    "height[0]": 'numeric|min:1',
    "height[1]": 'numeric|min:1',
    "height[2]": 'numeric|min:1',
    "height[3]": 'numeric|min:1',
    "height[4]": 'numeric|min:1',
    "recheck[]": 'in:auto'
};

let multiple_items = ["max", "min", "width", "height"];

</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
