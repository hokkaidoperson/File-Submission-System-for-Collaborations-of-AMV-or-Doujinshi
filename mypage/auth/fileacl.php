<?php
require_once('../../set.php');
setup_session();
$titlepart = '共同運営者の他者ファイル閲覧権限';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

?>

<h1>共同運営者の他者ファイル閲覧権限</h1>
<p>特定の共同運営者に、他者の提出ファイルや共通情報の閲覧を許可する事が出来ます。<br>
許可されたユーザーは、マイページから該当の情報を閲覧・ダウンロード出来るようになります。</p>
<p>まず、権限の確認・操作を行いたい共同運営者を選択して下さい。</p>
<?php
$choices = id_state('c');

if ($choices == array()) die_mypage('<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">共同運営者がいません。</div>');

echo '<ul>';
foreach ($choices as $choice) {
    $disp = hsc(nickname($choice));
    echo '<li>';
    echo '<a href="fileacl_selector.php?userid=' . $choice . '">' . $disp . '</a>';
    echo '</li>';
}
echo '</ul>';

require_once(PAGEROOT . 'mypage_footer.php');
