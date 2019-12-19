<?php
require_once('../../set.php');
session_start();
$titlepart = '提出期間外の操作権限';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

if (file_exists(DATAROOT . 'form/submit/done.txt')) {
    $general = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);
    if ($general["from"] <= time() and $general["until"] > time()) die_mypage('現在、ファイル提出期間中です。この機能は、ファイル提出期間外に使用出来ます。');
}

?>

<h1>提出期間外の操作権限</h1>
<p>現在、ファイルの提出期間外のため、ファイルの新規提出や、ユーザー情報・ファイル情報の操作が制限されています。<br>
しかし、この機能を用いて、特定のユーザーに、ファイルの遅刻提出や情報修正を許可する事が出来ます。</p>
<p>この機能で操作を許可すると、決まった時間、指定したファイルの編集・新規提出機能を、そのユーザーだけに時間制限付きで解禁します。<br>
指定した時間を過ぎた後は、編集・提出機能が自動的にロックされます。</p>
<p>非参加者（イベントに参加していないシステム管理者）は特にファイルを提出していないため、以下にリストアップされていません。<br>
また、主催者自身はこの機能に関わらず情報の操作が常時可能なため、以下にリストアップされていません。</p>
<p>まず、権限の確認・操作を行いたいユーザーを選択して下さい。</p>
<?php
$choices = users_array();

$exclude = array_merge(id_state('o'), id_state('p'));

foreach ($exclude as $subject) {
    unset($choices[$subject]);
}

if ($choices == array()) die_mypage('<p><div class="border border-danger" style="padding:10px;">ユーザーがいません。</div></p>');

echo '<ul>';
foreach ($choices as $key => $choice) {
    $disp = htmlspecialchars($choice["nickname"]);
    echo '<li>';
    echo '<a href="outofterm_selector.php?userid=' . $key . '">' . $disp . '</a>';
    echo '</li>';
}
echo '</ul>';

require_once(PAGEROOT . 'mypage_footer.php');
