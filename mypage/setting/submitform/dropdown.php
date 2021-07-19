<?php
require_once('../../../set.php');
setup_session();
$titlepart = 'ファイル提出画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

$number = basename($_GET['number']);

if (!isset($_GET['number']) or !isset($_SESSION["submitformdata"][$number]["id"]) or ('dropdown' != $_SESSION["submitformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - ドロップダウンリスト</h1>

<div class="border border-primary system-border-spacer">
<form name="form" action="save.php" method="post" onSubmit="return validation_call()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["submitformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="dropdown">
<?php
$disable_status = [];
$quantity = isset($_SESSION["submitformdata"][$number]["quantity"]) ? (int)$_SESSION["submitformdata"][$number]["quantity"] : 1;
for ($i = 0; $i < 5; $i++) {
    $disable_status[] = ($i >= $quantity);
}

echo_textbox([
    "title" => '項目名（50文字以内）【必須】',
    "name" => 'title',
    "id" => 'title',
    "jspart" => 'onChange="validation_call(&quot;title&quot;);"',
    "showcounter" => TRUE,
    "prefill" => isset($_SESSION["submitformdata"][$number]["title"]) ? hsc($_SESSION["submitformdata"][$number]["title"]) : ''
]);
echo_dropdown([
    "title" => '入力欄の個数【必須】',
    "name" => 'quantity',
    "id" => 'quantity',
    "choices" => ["1つ", "2つ", "3つ", "4つ", "5つ"],
    "values" => ["1", "2", "3", "4", "5"],
    "width" => 3,
    "jspart" => 'onChange="validation_call(&quot;quantity&quot;); form_setting_enable_form(this.value, multiple_items);"',
    "prefill" => isset($_SESSION["submitformdata"][$number]["quantity"]) ? hsc($_SESSION["submitformdata"][$number]["quantity"]) : '',
    "detail" => "※接頭辞などは入力欄毎に設定可能です。"
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
    "prefill" => isset($_SESSION["submitformdata"][$number]["required"]) ? hsc($_SESSION["submitformdata"][$number]["required"]) : '',
    "detail" => "※必須項目には「必須」と付記されます。"
]);
echo_textarea([
    "title" => '項目詳細（500文字以内）',
    "name" => 'detail',
    "id" => 'detail',
    "showcounter" => TRUE,
    "jspart" => 'onChange="validation_call(&quot;detail&quot;);"',
    "prefill" => isset($_SESSION["submitformdata"][$number]["detail"]) ? hsc($_SESSION["submitformdata"][$number]["detail"]) : '',
    "detail" => "※入力欄の下に、このようにして小さく表示される文字です。<br>　改行は反映されます（この入力欄で改行すると実際の登録画面でも改行されます）が、HTMLタグはお使いになれません。<br>　ただし、URLを記載すると、自動的にリンクが張られます。<br>※入力が無い場合は、入力欄の下に何も表示されません。"
]);
echo_textarea([
    "title" => '選択肢のリスト【必須】',
    "name" => 'list',
    "id" => 'list',
    "jspart" => 'onChange="validation_call(&quot;list&quot;);"',
    "prefill" => isset($_SESSION["submitformdata"][$number]["list"]) ? hsc($_SESSION["submitformdata"][$number]["list"]) : '',
    "detail" => "※選択肢をこの入力欄に、1行につき1つ入力して下さい。選択肢は、ここで入力した順に並びます。<br>　例えば、<br>　　　りんご<br>　　　みかん<br>　　　ぶどう<br>　と入力した場合、「りんご」「みかん」「ぶどう」の中から1つ選ぶ項目になります。<br>※ラジオボタンは、「●●●●の場合左にチェックして下さい」のような使い方は出来ません（チェックボックスをご利用下さい）。"
]);
$form_setting = [];
foreach($disable_status as $key => $disable) {
    $form_setting[] = [
        "prefill" => isset($_SESSION["submitformdata"][$number]["prefix"][$key]) ? hsc($_SESSION["submitformdata"][$number]["prefix"][$key]) : '',
        "jspart" => 'onChange="validation_call(&quot;prefix[' . $key . ']&quot;);"',
        "showcounter" => TRUE,
        "prefix" => ($key + 1) . "つ目の入力欄",
        "disabled" => $disable_status[$key]
    ];
}
echo_textbox_mlt($form_setting, "選択欄の前に表示する文字（接頭辞）（50文字以内）", 'prefix', 'prefix', '※例えば、「利用規約に」と指定すると、選択欄は、<br>　　利用規約に[　　　▽]<br>　のような見た目となります。');
$form_setting = [];
foreach($disable_status as $key => $disable) {
    $form_setting[] = [
        "prefill" => isset($_SESSION["submitformdata"][$number]["suffix"][$key]) ? hsc($_SESSION["submitformdata"][$number]["suffix"][$key]) : '',
        "jspart" => 'onChange="validation_call(&quot;suffix[' . $key . ']&quot;);"',
        "showcounter" => TRUE,
        "prefix" => ($key + 1) . "つ目の入力欄",
        "disabled" => $disable_status[$key]
    ];
}
echo_textbox_mlt($form_setting, "選択欄の後に表示する文字（接尾辞）（50文字以内）", 'suffix', 'suffix', '※例えば、「月」と指定すると、選択欄は、<br>　　[　　　▽]月<br>　のような見た目となります。<br>※接頭辞と接尾辞を組み合わせる事も出来ます。例えば、接頭辞に「動画内で」、接尾辞に「を利用しました。」と指定すると、<br>　　動画内で[　　　▽]を利用しました。<br>　のような見た目となります。');
echo_check([
    "title" => '入力内容の変更の自動承認について',
    "name" => 'recheck',
    "id" => 'recheck',
    "choices" => ['この項目の入力内容の変更を自動承認する場合は、左のチェックボックスにチェックして下さい。'],
    "values" => ["auto"],
    "jspart" => 'onChange="validation_call(&quot;recheck[]&quot;);"',
    "prefill" => isset($_SESSION["submitformdata"][$number]["recheck"][0]) ? [hsc($_SESSION["submitformdata"][$number]["recheck"][0])] : [],
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
    list: 'textarea',
    "prefix[0]": 'textbox',
    "suffix[0]": 'textbox',
    "prefix[1]": 'textbox',
    "suffix[1]": 'textbox',
    "prefix[2]": 'textbox',
    "suffix[2]": 'textbox',
    "prefix[3]": 'textbox',
    "suffix[3]": 'textbox',
    "prefix[4]": 'textbox',
    "suffix[4]": 'textbox',
    "recheck[]": 'check'
};
let rules = {
    title: 'required|max:50',
    quantity: 'required',
    required: 'required',
    detail: 'max:500',
    list: 'required',
    "prefix[0]": 'max:50',
    "prefix[1]": 'max:50',
    "prefix[2]": 'max:50',
    "prefix[3]": 'max:50',
    "prefix[4]": 'max:50',
    "suffix[0]": 'max:50',
    "suffix[1]": 'max:50',
    "suffix[2]": 'max:50',
    "suffix[3]": 'max:50',
    "suffix[4]": 'max:50',
    "recheck[]": 'in:auto'
};

let multiple_items = ["prefix", "suffix"];
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
