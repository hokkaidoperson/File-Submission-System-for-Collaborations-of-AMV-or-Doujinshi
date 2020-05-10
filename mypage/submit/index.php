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

$general = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

if (outofterm('submit') != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if (!in_term() and $_SESSION["state"] == 'p') $outofterm = TRUE;

if (isset($general["size"]) and $general["size"] != "") $maxsize = $general["size"];
else $maxsize = FILE_MAX_SIZE;
?>

<h1>ファイル提出</h1>
<p>本イベントに対し、ファイルを<b>新規提出</b>します（提出済みファイルの編集は、作品一覧の画面から行って下さい）。</p>
<p>提出されたファイルは、運営チーム（主催者・共同運営者）によって確認されます。確認結果（承認・拒否）は、登録メールアドレス宛に送信されます。</p>
<p><b>ファイルは、1作品ごとに送信して下さい</b>（複数作品のファイルをまとめて送信しないで下さい）。<br>
ファイル提出後はこの画面に戻って来ますので、複数作品を送信したい場合はこの画面から改めて送信願います。</p>
<p>ファイルの提出期間は、<b><?php echo date('Y年n月j日G時i分s秒', $general["from"]) . '～' . date('Y年n月j日G時i分s秒', $general["until"]); ?></b>です。</p>
<div class="border border-warning" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<b>【ファイルの提出・情報の編集は時間に余裕を持って行って下さい】</b><br>
システムの仕様上、入力途中またはファイル送信中に提出締め切りを迎えた場合、締め切り後に送信しようとしたと見なされ送信が遮断されます。<br>
提出・編集したいファイルや情報がある場合、なるべく早めに提出・編集を行って下さい。これは共通情報（ニックネームを含む）についても同様です。
</div>
<?php
if ($general["from"] > time() and !$outofterm) echo '<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
提出期間前です。
</div>';
else if ($general["until"] <= time() and !$outofterm) echo '<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
提出は締め切られました。
</div>';
else {
    if ($outofterm and $_SESSION["state"] == 'p') echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外ですが、主催者は常時ファイルの提出が可能です。
</div>';
    else if ($outofterm) echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外ですが、あなたは主催者からファイル提出を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('submit')) . 'まで）。
</div>';
    if (isset($general["worknumber"]) and $general["worknumber"] != "") {
        $myworks = count_works();
        $submitleft = (int)$general["worknumber"] - $myworks;
        if ($submitleft <= 0) die_mypage('<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
提出可能な作品数の上限に達しています（' . $general["worknumber"] . '作品まで提出可能）。提出済みの作品を削除しないと、新規提出を行えません。
</div>');
        echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
あと <b>' . $submitleft . '作品</b> 提出出来ます（' . $general["worknumber"] . '作品まで提出可能）。
</div>';
    }
    echo '<div class="row" style="padding:10px;">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-header">
<a href="direct_unit.php" class="stretched-link">ファイルをサーバーに直接アップロードする</a>
</div>
<div class="card-body">
<span class="text-decoration-none text-body">ポータルサイトのサーバーに直接アップロード出来るファイルの最大サイズは <b>' . $maxsize  . 'MB</b> です。</span>
</div>
</div>
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-header">
<a href="url_unit.php" class="stretched-link">外部のファイルアップロードサービスを利用して送信する</a>
</div>
<div class="card-body">
<span class="text-decoration-none text-body">アップロードしたいファイルのサイズが' . $maxsize  . 'MBを上回っている場合はこちらを選択して下さい。</span>
</div>
</div>
</div>';
}
require_once(PAGEROOT . 'mypage_footer.php');
