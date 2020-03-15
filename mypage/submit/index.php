<?php
require_once('../../set.php');
session_start();
$titlepart = 'ファイル提出';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'file_submitted') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
ファイルの提出が完了しました。<br>
ファイル内容を運営チームが確認するまでしばらくお待ち願います。<br><br>
ファイル確認の結果、ファイルの再提出が必要になる可能性がありますので、<b>制作に使用した素材などは、しばらくの間消去せずに残しておいて下さい</b>。<br><br>
続けて提出する場合は、再びこの画面から提出して下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'file_submitted_auto_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
ファイルの提出が完了しました。<br>
ファイル確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、この作品は<b>自動的に承認されました</b>。<br><br>
続けて提出する場合は、再びこの画面から提出して下さい。
</div>';
    $_SESSION["situation"] = '';
}

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

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
    echo '<div class="row">
<a href="direct_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-header">
ファイルをサーバーに直接アップロードする
</div>
<div class="card-body">
<span class="text-decoration-none text-body">ポータルサイトのサーバーに直接アップロード出来るファイルの最大サイズは <b>' . $maxsize  . 'MB</b> です。</span>
</div>
</div>
</a>
<a href="url_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-header">
外部のファイルアップロードサービスを利用して送信する
</div>
<div class="card-body">
<span class="text-decoration-none text-body">アップロードしたいファイルのサイズが' . $maxsize  . 'MBを上回っている場合はこちらを選択して下さい。</span>
</div>
</div>
</a>
</div>';
}
require_once(PAGEROOT . 'mypage_footer.php');
