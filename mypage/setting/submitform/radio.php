<?php
require_once('../../../set.php');
setup_session();
$titlepart = 'ファイル提出画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

$number = basename($_GET['number']);

if (!isset($_GET['number']) or !isset($_SESSION["submitformdata"][$number]["id"]) or ('radio' != $_SESSION["submitformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - ラジオボタン</h1>

<div class="border border-primary system-border-spacer">
<form name="form" action="save.php" method="post" onSubmit="return validation_call()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["submitformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="radio">
<?php
echo_textbox([
    "title" => '項目名（50文字以内）【必須】',
    "name" => 'title',
    "id" => 'title',
    "jspart" => 'onChange="validation_call(&quot;title&quot;);"',
    "showcounter" => TRUE,
    "prefill" => isset($_SESSION["submitformdata"][$number]["title"]) ? hsc($_SESSION["submitformdata"][$number]["title"]) : ''
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
echo_check([
    "title" => '選択肢の並べ方',
    "name" => 'arrangement',
    "id" => 'arrangement',
    "choices" => ['選択肢を横に並べる場合は、左のチェックボックスにチェックして下さい。'],
    "values" => ["h"],
    "jspart" => 'onChange="validation_call(&quot;arrangement[]&quot;);"',
    "prefill" => isset($_SESSION["submitformdata"][$number]["arrangement"][0]) ? [hsc($_SESSION["submitformdata"][$number]["arrangement"][0])] : [],
    "detail" => "※チェックが無い場合は、選択肢を縦に並べます。"
]);
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
    required: 'radio',
    detail: 'textarea',
    list: 'textarea',
    "arrangement[]": 'check',
    "recheck[]": 'check'
};
let rules = {
    title: 'required|max:50',
    required: 'required',
    detail: 'max:500',
    list: 'required',
    "arrangement[]": 'in:h',
    "recheck[]": 'in:auto'
};
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
