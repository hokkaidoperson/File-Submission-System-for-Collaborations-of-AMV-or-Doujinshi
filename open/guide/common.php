<?php
require_once('../../set.php');
$titlepart = '共通情報について';
require_once(PAGEROOT . 'help_header.php');

$userformdata = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}
?>

<h1>共通情報について</h1>
<p>イベントにおいて<b>ユーザー1人につき1つ必要な情報</b>は、提出する作品とは別に共通情報として入力する事となります（「提出する各作品に共通する情報」という事から、「共通情報」という名前になっています）。</p>
<p>共通情報として、下記事項の入力が必要です。</p>
<ul>
<?php
foreach ($userformdata as $data) {
    echo '<li>' . hsc($data["title"]);
    switch ($data["required"]) {
        case 1:
            echo "【必須】";
            break;
        case 2:
            echo "【いずれか必須】";
            break;
        default:
            echo "【任意】";
    }
    echo '</li>';
}
if ($userformdata == array()) echo '<li>（現在、設定が必要な項目はありません。）</li>';
?>
</ul>
<p>ここでは、その共通情報の入力・編集方法をご紹介します。</p>
<p>マイページトップから、「共通情報の入力・編集」を選択して下さい。</p>
<p>必要事項を入力（変更する際は変更したい項目のみ変更）し、<b>最後に必ず「送信する」を押下して下さい</b>（「送信する」を押下しないと送信されません。共通情報の入力・編集を取りやめる場合は、そのまま入力画面を離れて下さい。）。</p>
<p>「共通情報の変更が完了しました。」というメッセージが表示されれば、変更完了です。</p>

<?php
require_once(PAGEROOT . 'help_footer.php');
