<?php
require_once('../../set.php');
setup_session();
$titlepart = 'イベント情報編集';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

?>

<h1>イベント情報編集</h1>
<p>ここでは、共通情報として入力を求める事項や、ファイル提出の期間・提出時の入力事項、ファイル確認に関する項目を設定出来ます。</p>
<p>「一言コメント」や「プロフィール画像」など、参加者1人につき1つ必要な情報については、共通情報の記入事項として登録して下さい。<br>
「作品詳細」や「親作品」など、作品ごとに必要な情報については、ファイル提出時の記入事項として登録して下さい。</p>
<?php
//フォーム登録してない場合
if (!file_exists(DATAROOT . 'form/userinfo/done.txt')) echo '<p><strong>共通情報の記入事項が登録されていません</strong>。この画面から、登録を完了させて下さい。</p>';
if (!file_exists(DATAROOT . 'form/submit/done.txt')) echo '<p><strong>ファイル提出時の記入事項や、ファイル提出期間が登録されていない</strong>ため、このままではファイル提出を受け付け出来ません。この画面から、登録を完了させて下さい。</p>';
if (!file_exists(DATAROOT . 'examsetting.txt')) echo '<p><strong>ファイル確認に関する設定をしていない</strong>ため、このままではファイル提出・共通情報設定を受け付け出来ません。この画面から、登録を完了させて下さい。</p>';
?>
<div class="system-carditems">
<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="userform/index.php" class="stretched-link">共通情報の記入事項を編集</a>
</div>
</div>
<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="submitform/index.php" class="stretched-link">ファイル提出時の記入事項・提出期間を編集</a>
</div>
</div>
<div class="card system-cardindv">
<div class="card-body d-flex align-items-center">
<a href="exam/index.php" class="stretched-link">ファイル確認に関する設定</a>
</div>
</div>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
