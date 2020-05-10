<?php
require_once('../set.php');
$titlepart = 'ご利用ガイド（主催者向け）';
require_once(PAGEROOT . 'help_header.php');
?>

<h1>ご利用ガイド（主催者向け）</h1>
<h2>基本的な使い方</h2>
<ul>
    <li><a href="guide/basic.php">システムの基本的な使い方</a></li>
</ul>
<h2>提出受付開始前に設定する事</h2>
<ul>
    <li><a href="guide/invite_co.php">共同運営者の追加（主催者とは別に運営スタッフが居る場合）</a></li>
    <li><a href="guide/before_reception.php">各種入力事項の設定／ファイル確認に関する設定</a></li>
    <li><a href="guide/set_mail_schedule.php">受付開始・締切メールの配信設定（必要に応じて）</a></li>
</ul>
<h2>ファイルや情報の提出・確認</h2>
<ul>
    <li><a href="guide/submission.php">ファイル提出の仕方</a></li>
    <li><a href="guide/list.php">提出済みファイルの閲覧・編集・削除</a></li>
    <li><a href="guide/list_users.php">参加者一覧の確認</a></li>
    <li><a href="guide/common.php">共通情報について</a></li>
    <li><a href="guide/exam.php">ファイル確認を行う</a></li>
    <li><a href="guide/generatezip.php">一括ダウンロードの仕方</a></li>
</ul>
<h2>共同運営者・参加者とのやり取り</h2>
<ul>
    <li><a href="guide/message.php">メッセージ機能について</a></li>
</ul>
<h2>こんな時は</h2>
<ul>
    <li><a href="guide/fileacl.php">共同運営者にファイル・情報の閲覧権限を与えるには</a></li>
    <li><a href="guide/outofterm.php">提出期間外にファイルや情報の提出・編集を認めるには</a></li>
    <li><a href="guide/block_users.php">特定のユーザーを凍結するには</a></li>
    <li><a href="guide/block_ip.php">アカウント新規作成を制限するには</a></li>
    <li><a href="guide/leave_promoter.php">主催者を辞退するには</a></li>
</ul>

<?php
require_once(PAGEROOT . 'help_footer.php');
