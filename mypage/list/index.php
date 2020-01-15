<?php
require_once('../../set.php');
session_start();
$titlepart = '提出作品の一覧';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'edit_nochange') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
登録情報の変更はありませんでした。</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'edit_autoaccept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
登録情報を変更しました。<br>
自動承認される項目のみ変更されていたため、変更は完全に自動承認されました。</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'edit_submitted') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
ファイルの編集が完了しました。<br>
変更内容を運営チームが確認するまでしばらくお待ち願います。<br><br>
ファイル確認の結果、ファイルの再提出が必要になる可能性がありますので、<b>制作に使用した素材などは、しばらくの間消去せずに残しておいて下さい</b>。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'edit_submitted_auto_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
ファイルの編集が完了しました。<br>
ファイル確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、変更内容は<b>自動的に承認されました</b>。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'edit_deleted') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
作品を削除しました。</div>';
    $_SESSION["situation"] = '';
}

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

$canshow = array();

foreach(glob(DATAROOT . 'submit/*', GLOB_MARK | GLOB_ONLYDIR) as $dirname) {
    $author = basename($dirname);
    $canshow[$author] = array();
    foreach(glob($dirname . '*.txt') as $filename) {
        $id = basename($filename, '.txt');

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
                    $acldata = json_decode(file_get_contents($aclplace), true);
                    if (array_search($author . '_' . $id, $acldata) !== FALSE) $allowed = TRUE;
                }
                //breakしない、下へ行く
            case 'g':
                //自分のファイルだけ（共同運営者も同じく）
                if ($author == $_SESSION['userid']) $allowed = TRUE;
            break;
        }
    if (!$allowed) continue;
    $canshow[$author][$id] = json_decode(file_get_contents($filename), true);
    }
    if ($canshow[$author] == array()) unset($canshow[$author]);
}

if ($_SESSION["state"] == 'p') echo '<h1>参加者・作品の一覧 - 提出済み作品</h1>
<p>本イベントに対し提出された作品の一覧です（修正待ち・拒否の作品を含む）。<br>
提出者の名前をクリックすると提出者の情報（ユーザー登録時に入力された情報）を確認出来ます。<br>
作品名をクリックすると作品の情報（ファイル提出時に入力された情報）を確認出来ます。</p>
<p>あなた自身の作品については、作品情報のページから編集を行えます。<br>
他者および他者の作品については編集出来ません（メッセージ機能で、該当者に編集を依頼して下さい。<b>該当者のユーザーIDやパスワードを直接尋ねる行為はおやめ下さい。</b>）。</p>
<p><a href="users.php">参加者の一覧（ファイル未提出者も含む）はこちら</a></p>
<p><a href="generatezip.php">提出者情報・提出ファイル・ファイル情報の一括ダウンロード（ZIPファイル）はこちら</a></p>
';
if ($_SESSION["state"] == 'c') echo '<h1>提出済み作品の一覧</h1>
<p>あなたが提出した作品、及び主催者から閲覧権限を与えられた作品の一覧です（修正待ち・拒否の作品を含む）。<br>
提出者の情報（ユーザー登録時に入力された情報）の閲覧権限がある場合、提出者の名前がクリック出来るようになっています。クリックすると確認出来ます。<br>
作品名をクリックすると作品の情報（ファイル提出時に入力された情報）を確認出来ます。</p>
<p>あなた自身の作品については、作品情報のページから編集を行えます。<br>
他者および他者の作品については編集出来ません（入力内容に何か懸案事項があれば、主催者にご相談下さい）。</p>
<p><a href="users.php">閲覧可能な提出者情報はこちら</a></p>
<p><a href="generatezip.php">提出者情報・提出ファイル・ファイル情報の一括ダウンロード（ZIPファイル）はこちら</a></p>
';
if ($_SESSION["state"] == 'g') echo '<h1>提出済み作品の一覧</h1>
<p>あなたが提出した作品の一覧です（修正待ち・拒否の作品を含む）。<br>
作品名をクリックすると作品の情報（ファイル提出時に入力された情報）を確認出来ます。また、そこから作品情報の編集を行えます。</p>
<p><a href="generatezip.php">提出ファイル・ファイル情報の一括ダウンロード（ZIPファイル）はこちら</a></p>
';


if (!in_term() and $_SESSION["state"] == 'p') echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外ですが、主催者は常時提出内容の編集が可能です。
</div></p>';
else if (!in_term()) echo '<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在、ファイル提出期間外です。提出内容の確認は出来ますが、原則、編集は出来ません。<br>
ただし、主催者が特定の作品について編集を認めている場合は、編集画面に移れます。
</div>';
?>

<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>提出者</th><th>作品名</th><th>承認の状態</th>
</tr>
<?php
foreach ($canshow as $author => $array) {
    $nickname = nickname($author);
    //ユーザー情報表示リンクを張るかどうか
    $showlink = FALSE;
    switch($_SESSION["state"]) {
        case 'p':
            //主催者は基本的にアクセスおｋ
            $showlink = TRUE;
        break;
        case 'c':
            //主催がアクセス権を与えていたらおｋ
            $aclplace = DATAROOT . 'fileacl/' . $_SESSION["userid"] . '.txt';
            if (file_exists($aclplace)) {
                $acldata = json_decode(file_get_contents($aclplace), true);
                if (array_search($author . '_userform', $acldata) !== FALSE) $showlink = TRUE;
            }
        break;
    }
    if ($showlink) $namepart = '<a href="detail.php?author=' . $author . '&id=userform">' . htmlspecialchars($nickname) . '</a>';
    else $namepart = htmlspecialchars($nickname);
    if (blackuser($author)) $namepart .= '<span class="text-danger">（凍結ユーザー）</span>';

    foreach ($array as $id => $data) {
        echo "<tr>\n";
        echo "<td>" . $namepart . "</td>";
        echo '<td><a href="detail.php?author=' . $author . '&id=' . $id . '">' . htmlspecialchars($data["title"]) . '</a></td>';
        if (isset($data["editing"]) and $data["editing"] == 1) echo '<td>項目編集の承認待ち</td>';
        else switch ($data["exam"]) {
            case 0:
                echo '<td>承認待ち</td>';
            break;
            case 1:
                echo '<td class="text-success"><b>承認</b></td>';
            break;
            case 2:
                echo '<td class="text-warning"><b>修正待ち</b></td>';
            break;
            case 3:
                echo '<td class="text-danger"><b>承認見送り</b></td>';
            break;
        }
        echo "</tr>\n";

    }
}
if ($canshow == array()) echo '<tr><td colspan="3">現在、表示出来る作品はありません。</td></tr>';
?>
</table>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
