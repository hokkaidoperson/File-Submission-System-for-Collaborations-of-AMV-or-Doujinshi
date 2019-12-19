<?php
require_once('../../set.php');
session_start();
$titlepart = 'イベント情報編集';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

?>

<h1>イベント情報編集</h1>
<p>ここでは、ユーザー登録時に求める入力事項や、ファイル提出の期間・ファイル検査に関する項目・提出時の入力事項を設定出来ます。</p>
<p>「一言コメント」や「プロフィール画像」など、参加者1人につき1つ必要な情報については、ユーザー登録時の記入事項として登録して下さい。<br>
「作品詳細」や「親作品」など、作品ごとに必要な情報については、ファイル提出時の記入事項として登録して下さい。</p>
<?php
//フォーム登録してない場合
if (!file_exists(DATAROOT . 'form/userinfo/done.txt')) echo '<p><b>ユーザー登録時の記入事項が登録されていない</b>ため、このままではユーザー登録を受け付け出来ません。この画面から、登録を完了させて下さい。</p>';
if (!file_exists(DATAROOT . 'form/submit/done.txt')) echo '<p><b>ファイル提出時の記入事項や、ファイル提出期間などが登録されていない</b>ため、このままではファイル提出を受け付け出来ません。この画面から、登録を完了させて下さい。</p>';
?>
<div class="row">
<a href="userform/index.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
ユーザー登録時の記入事項を編集
</div>
</div>
</a>
<a href="submitform/index.php">
<div class="card" style="width: 20rem; margin: 0.5rem;">
<div class="card-body">
ファイル提出時の記入事項・提出期間などを編集
</div>
</div>
</a>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
