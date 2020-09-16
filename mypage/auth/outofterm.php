<?php
require_once('../../set.php');
setup_session();
$titlepart = '提出期間外の操作権限';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

if (file_exists(DATAROOT . 'form/submit/done.txt')) {
    $general = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
    if ($general["from"] <= time() and $general["until"] > time()) die_mypage('現在、ファイル提出期間中です。この機能は、ファイル提出期間外に使用出来ます。');
}

?>

<h1>提出期間外の操作権限</h1>
<p>現在、ファイルの提出期間外のため、ファイルの新規提出や、共通情報（ニックネーム含む）・ファイル情報の操作が制限されています。<br>
しかし、この機能を用いて、特定のユーザーに、ファイルの遅刻提出や情報修正を許可する事が出来ます。</p>
<p>この機能で操作を許可すると、指定したファイル・情報の編集・新規提出機能を、そのユーザーだけに時間制限付きで解禁します。<br>
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

if ($choices == array()) die_mypage('<div class="border border-danger system-border-spacer">ユーザーがいません。</div>');

echo '<ul>';
foreach ($choices as $key => $choice) {
    $disp = hsc($choice["nickname"]);
    echo '<li>';
    echo '<a href="outofterm_selector.php?userid=' . $key . '">' . $disp . '</a>';
    echo '</li>';
}
echo '</ul>';

require_once(PAGEROOT . 'mypage_footer.php');
