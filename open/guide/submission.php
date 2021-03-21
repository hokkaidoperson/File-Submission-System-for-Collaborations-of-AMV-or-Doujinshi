<?php
require_once('../../set.php');
$titlepart = 'ファイル提出の仕方';
require_once(PAGEROOT . 'help_header.php');

$general = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

if (isset($general["size"]) and $general["size"] != "") $maxsize = $general["size"];
else $maxsize = FILE_MAX_SIZE;

$submitformdata = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}

?>

<h1>ファイル提出の仕方</h1>
<p>マイページトップから、「作品を提出する」を選択して下さい。</p>
<p>ポータルサイトのサーバーに直接アップロード出来るファイルの最大サイズは <strong><?php echo $maxsize; ?>MB</strong> です（複数ファイルを提出する場合、それらの合計サイズが<?php echo $maxsize; ?>MB以下である必要があります）。<br>
アップロードしたいファイルのサイズが<?php echo $maxsize; ?>MB以下の場合は「<strong>ファイルをサーバーに直接アップロードする</strong>」を、<?php echo $maxsize; ?>MBを上回っている場合は「<strong>外部のファイルアップロードサービスを利用して送信する</strong>」を選択して下さい。</p>
<p>「外部のファイルアップロードサービスを利用して送信する」を選択した場合は、GoogleドライブやOneDriveなど、ファイルアップロードサービス上にファイルをアップロードしてから、その共有URLを発行し、本システム上に入力する事となります。</p>
<p>ファイル提出の際、下記事項の入力が必要です。</p>
<ul>
    <li>提出ファイル、もしくはそのダウンロードURL【必須】</li>
    <li>タイトル【必須】</li>
<?php
foreach ($submitformdata as $data) {
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
?>
</ul>
<p>必要事項を入力し、<strong>最後に必ず「送信する」を押下して下さい</strong>（「送信する」を押下しないと送信されません。ファイルの提出を取りやめる場合は、そのまま提出画面を離れて下さい。）。</p>
<p>「ファイルの提出が完了しました。」というメッセージが表示されれば、提出完了です。</p>

<?php
require_once(PAGEROOT . 'help_footer.php');
