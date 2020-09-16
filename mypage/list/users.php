<?php
require_once('../../set.php');
setup_session();
$titlepart = '参加者の一覧';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c"), TRUE);

$canshow = array();

foreach(users_array() as $author => $data) {
    //ファイルの閲覧権があるか確認
    $allowed = FALSE;
    switch($_SESSION["state"]) {
        case 'p':
            //主催者は基本的にアクセスおｋ
            $allowed = TRUE;
        break;
        case 'c':
            //主催がアクセス権を与えていたらおｋ
            $aclplace = DATAROOT . 'fileacl/' . $_SESSION["userid"] . '.txt';
            if (file_exists($aclplace)) {
                $acldata = json_decode(file_get_contents_repeat($aclplace), true);
                if (array_search($author . '_userform', $acldata) !== FALSE) $allowed = TRUE;
            }
            //breakしない、下へ行く
        case 'g':
            //自分のファイルだけ（共同運営者も同じく）
            if ($author == $_SESSION['userid']) $allowed = TRUE;
        break;
    }
    if (!$allowed) continue;
    $canshow[$author] = $data;
}

if ($_SESSION["state"] == 'p') echo '<h1>参加者・作品の一覧 - 参加者</h1>
<p>本イベントのポータルサイトに登録されているユーザーの一覧です（ファイル未提出者も含む）。<br>
ユーザーの名前をクリックするとユーザーが共通情報として入力した内容を確認出来ます。</p>
<p><a href="index.php">提出作品の一覧はこちら</a></p>
';
if ($_SESSION["state"] == 'c') echo '<h1>参加者の一覧</h1>
<p>主催者から閲覧権限を与えられたユーザー情報の一覧です。<br>
ユーザーの名前をクリックするとユーザーが共通情報として入力した内容を確認出来ます。</p>
<p><a href="index.php">提出作品の一覧はこちら</a></p>
';
?>

<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th width="70%">ユーザー</th><th width="30%">立場</th>
</tr>
<?php
foreach ($canshow as $author => $array) {
    $nickname = nickname($author);
    echo '<tr>';
    echo '<td>';
    echo '<a href="detail.php?author=' . $author . '&id=userform">' . hsc($nickname) . '</a>';
    if (blackuser($author)) echo '<span class="text-danger">（凍結ユーザー）</span>';
    echo '</td>';

    switch ($array["state"]) {
        case 'p':
            echo '<td>主催者</td>';
        break;
        case 'c':
            echo '<td>共同運営者</td>';
        break;
        case 'g':
            echo '<td>一般参加者</td>';
        break;
        case 'o':
            echo '<td>非参加者</td>';
        break;
    }
    echo "</tr>\n";
}
if ($canshow == array()) echo '<tr><td colspan="2">現在、表示出来るユーザーはありません。</td></tr>';
?>
</table>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
