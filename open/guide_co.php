<?php
require_once('../set.php');
$titlepart = 'ご利用ガイド（共同運営者向け）';
require_once(PAGEROOT . 'help_header.php');
?>

<h1>ご利用ガイド（共同運営者向け）</h1>
<h2>基本的な使い方</h2>
<ul>
    <li><a href="guide/basic.php">システムの基本的な使い方</a></li>
</ul>
<h2>ファイルや情報の提出・確認</h2>
<ul>
    <li><a href="guide/submission.php">ファイル提出の仕方</a></li>
    <li><a href="guide/list.php">提出済みファイルの閲覧・編集・削除</a></li>
    <li><a href="guide/common.php">共通情報について</a></li>
    <li><a href="guide/exam.php">ファイル確認を行う</a></li>
    <li><a href="guide/generatezip.php">一括ダウンロードの仕方</a></li>
    <li><a href="guide/fileacl_allowed.php">主催者から他者のファイルや情報の閲覧を許可されたら</a></li>
</ul>
<h2>主催者・参加者とのやり取り</h2>
<ul>
    <li><a href="guide/message.php">メッセージ機能について</a></li>
</ul>
<h2>こんな時は</h2>
<ul>
    <li><a href="guide/leave_co.php">共同運営者を辞退するには</a></li>
</ul>

<?php
require_once(PAGEROOT . 'help_footer.php');
