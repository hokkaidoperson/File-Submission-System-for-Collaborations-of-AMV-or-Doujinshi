<?php
require_once('../../set.php');
session_start();
$titlepart = 'ファイル提出';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'file_submitted') {
    echo '<p><div class="border border-success" style="padding:10px;">
ファイルの提出が完了しました。<br>
ファイル内容を運営チームが確認するまでしばらくお待ち願います。<br><br>
ファイル確認の結果、ファイルの再提出が必要になる可能性がありますので、<b>制作に使用した素材などは、しばらくの間消去せずに残しておいて下さい</b>。<br><br>
続けて提出する場合は、再びこの画面から提出して下さい。
</div></p>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'file_submitted_auto_accept') {
    echo '<p><div class="border border-success" style="padding:10px;">
ファイルの提出が完了しました。<br>
ファイル確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、この作品は<b>自動的に承認されました</b>。<br><br>
続けて提出する場合は、再びこの画面から提出して下さい。
</div></p>';
    $_SESSION["situation"] = '';
}

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

if (!file_exists(DATAROOT . 'form/submit/done.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル提出を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

$general = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

if (outofterm('submit') != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if (!in_term() and $_SESSION["state"] == 'p') $outofterm = TRUE;
?>

<h1>ファイル提出</h1>
<p>本イベントに対し、ファイルを<b>新規提出</b>します（提出済みファイルの編集は、作品一覧の画面から行って下さい）。</p>
<p>提出されたファイルは、運営チーム（主催者・共同運営者）によって確認されます。確認結果（承認・拒否）は、登録メールアドレス宛に送信されます。</p>
<p><b>ファイルは、1作品ごとに送信して下さい</b>（複数作品のファイルをまとめて送信しないで下さい）。<br>
ファイル提出後はこの画面に戻って来ますので、複数作品を送信したい場合はこの画面から改めて送信願います。</p>
<p>ファイルの提出期間は、<b><?php echo date('Y年n月j日G時i分s秒', $general["from"]) . '～' . date('Y年n月j日G時i分s秒', $general["until"]); ?></b>です。</p>
<?php
if ($general["from"] > time() and !$outofterm) echo '<p><div class="border border-danger" style="padding:10px;">
提出期間前です。
</div></p>';
else if ($general["until"] <= time() and !$outofterm) echo '<p><div class="border border-danger" style="padding:10px;">
提出は締め切られました。
</div></p>';
else {
    if ($outofterm and $_SESSION["state"] == 'p') echo '<p><div class="border border-primary" style="padding:10px;">
現在ファイル提出期間外ですが、主催者は常時ファイルの提出が可能です。
</div></p>';
    else if ($outofterm) echo '<p><div class="border border-primary" style="padding:10px;">
現在ファイル提出期間外ですが、あなたは主催者からファイル提出を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('submit')) . 'まで）。
</div></p>';
    echo '<div class="row">
<a href="direct_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-header">
ファイルをサーバーに直接アップロードする
</div>
<div class="card-body">
<span class="text-decoration-none text-body">ポータルサイトのサーバーに直接アップロード出来るファイルの最大サイズは <b>' . $general["size"]  . 'MB</b> です。</span>
</div>
</div>
</a>
<a href="url_unit.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-header">
外部のファイルアップロードサービスを利用して送信する
</div>
<div class="card-body">
<span class="text-decoration-none text-body">アップロードしたいファイルのサイズが' . $general["size"]  . 'MBを上回っている場合はこちらを選択して下さい。</span>
</div>
</div>
</a>
</div>';
}
require_once(PAGEROOT . 'mypage_footer.php');
