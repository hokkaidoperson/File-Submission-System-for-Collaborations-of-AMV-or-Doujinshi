<?php
require_once('../../set.php');
setup_session();
$titlepart = '提出物の一覧';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

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
                    $acldata = json_decode(file_get_contents_repeat($aclplace), true);
                    if (array_search($author . '_' . $id, $acldata) !== FALSE) $allowed = TRUE;
                }
                //breakしない、下へ行く
            case 'g':
                //自分のファイルだけ（共同運営者も同じく）
                if ($author == $_SESSION['userid']) $allowed = TRUE;
            break;
        }
    if (!$allowed) continue;
    $canshow[$author][$id] = json_decode(file_get_contents_repeat($filename), true);
    }
    if ($canshow[$author] == array()) unset($canshow[$author]);
}

//査読一覧
$examtask = FALSE;
if (!no_access_right(array("p", "c"))) {
    //意見収集中（progress） _state=0
    $examlist["p"] = array();
    //意見収集中・自分は回答済み（already）
    $examlist["a"] = array();
    //議論中（discussion） _state=1
    $examlist["d"] = array();
    //理由取りまとめ待ち（reason） _state=4
    $examlist["r"] = array();
    //終了（closed） _state=2（議論後） _state=3（即決）→スキップ
    //権限を持たない（non-allowed）→スキップ

    $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }
    $editmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $editmem);
    if ($key !== FALSE) {
        $editmem[$key] = id_promoter();
    }

    foreach(glob(DATAROOT . 'exam/*.txt') as $filename) {
        $filedata = json_unpack($filename);
        if ($filedata["_state"] == 2 or $filedata["_state"] == 3) continue;
        else if ($filedata["_state"] == 1) $key = "d";
        else if ($filedata["_state"] == 4) $key = "r";
        else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $key = "a";
        else $key = "p";
        if (array_search($_SESSION["userid"], $submitmem) === FALSE) continue;
        $authorandid = $filedata["_realid"];
        if (!file_exists(DATAROOT . 'submit/' . $authorandid . '.txt')) {
            unlink($filename);
            continue;
        }
        $submitdata = json_unpack(DATAROOT . 'submit/' . $authorandid . '.txt');
        $examlist[$key][$authorandid . '/new'] = $submitdata;
        $examlist[$key][$authorandid . '/new']["examrealid"] = basename($filename, ".txt");
        list($author, $id) = explode('/', $authorandid);
        unset($canshow[$author][$id]);
        if ($canshow[$author] == array()) unset($canshow[$author]);
        $examtask = TRUE;
    }
    foreach(glob(DATAROOT . 'exam_edit/*.txt') as $filename) {
        $filedata = json_unpack($filename);
        if (!isset($filedata["_membermode"])) $filedata["_membermode"] = "edit";
        if ($filedata["_membermode"] == "edit") $thismem = $editmem;
        else $thismem = $submitmem;
        if ($filedata["_state"] == 2 or $filedata["_state"] == 3) continue;
        else if ($filedata["_state"] == 1) $key = "d";
        else if ($filedata["_state"] == 4) $key = "r";
        else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $key = "a";
        else $key = "p";
        if (array_search($_SESSION["userid"], $thismem) === FALSE) continue;
        $basename = $filedata["_realid"];
        list($author, $id, $editid) = explode("/", $basename);
        if ($id === "common") {
            $submitdata = array("title" => "【共通情報】", "id" => $editid);
            if ($filedata["_commonmode"] == "new") $editid = "new";
            $examlist[$key][$author . "/" . $id . "/$editid"] = $submitdata;
            $examlist[$key][$author . "/" . $id . "/$editid"]["examrealid"] = basename($filename, ".txt");
            $examtask = TRUE;
            continue;
        }
        if (!file_exists(DATAROOT . 'submit/' . $author . '/' . $id . '.txt')) {
            unlink($filename);
            continue;
        }
        $submitdata = json_decode(file_get_contents_repeat(DATAROOT . 'submit/' . $author . '/' . $id . '.txt'), true);
        $examlist[$key][$basename] = $submitdata;
        $examlist[$key][$basename]["examrealid"] = basename($filename, ".txt");
        unset($canshow[$author][$id]);
        if ($canshow[$author] == array()) unset($canshow[$author]);
        $examtask = TRUE;
    }
}

if ($_SESSION["state"] == 'p') echo '<h1>提出物の一覧 - 提出済み作品</h1>
<p>本イベントに対し提出された作品の一覧です（修正待ち・承認見送りの作品を含む）。<br>
青字になっている提出者名や作品名を選択すると、詳細を確認出来ます。</p>
<p>あなた自身の作品については、作品情報のページから編集を行えます。<br>
他者および他者の作品については編集出来ません（メッセージ機能で、該当者に編集を依頼して下さい。<strong>該当者のユーザーIDやパスワードを直接尋ねる行為はおやめ下さい。</strong>）。</p>
<p><a href="users.php">参加者の一覧（ファイル未提出者も含む）はこちら</a></p>
<p><a href="generatezip.php">提出者情報・提出ファイル・ファイル情報の一括ダウンロード（ZIPファイル）はこちら</a></p>
<p><a href="../exam/index.php">提出物確認の全履歴はこちら</a></p>
';
if ($_SESSION["state"] == 'c') echo '<h1>提出物の一覧</h1>
<p>あなたが提出した作品、及び主催者から閲覧権限を与えられた作品の一覧です（修正待ち・承認見送りの作品を含む）。<br>
青字になっている提出者名や作品名を選択すると、詳細を確認出来ます。</p>
<p>あなた自身の作品については、作品情報のページから編集を行えます。<br>
他者および他者の作品については編集出来ません（入力内容に何か懸案事項があれば、主催者にご相談下さい）。</p>
<p><a href="users.php">閲覧可能な提出者情報はこちら</a></p>
<p><a href="generatezip.php">提出者情報・提出ファイル・ファイル情報の一括ダウンロード（ZIPファイル）はこちら</a></p>
<p><a href="../exam/index.php">提出物確認の全履歴はこちら</a></p>
';
if ($_SESSION["state"] == 'g') echo '<h1>提出物の一覧</h1>
<p>あなたが提出した作品の一覧です（修正待ち・承認見送りの作品を含む）。<br>
作品名を選択すると詳細を確認出来ます。また、そこから作品情報の編集を行えます。</p>
<p><a href="generatezip.php">提出者情報・提出ファイル・ファイル情報の一括ダウンロード（ZIPファイル）はこちら</a></p>
';

if ($examtask) {
    echo '<h2>確認未完了の提出物</h2>
<p><strong>以下の提出物については、確認・承認が済んでいません</strong>。作品を選択すると、回答や議論の画面に移れます。<br>
内容の確認を行い、問題が無ければ「承認」に、軽微な修正（動画の音量バランス修正など）が必要な場合は「修正待ち」に、問題点が多い・重大な問題点がある場合は「承認見送り」となります。</p>
<p>ファイル確認を行う者は、「ファイル確認に関する設定」で指定した担当者です。ただし、あなた自身の提出作品については、あなたの回答は自動的に「承認しても問題無い」になります。</p>
<p>全員の回答が出揃う前であれば、回答は何度でも変更出来ます。ただし、全員の回答が出揃った瞬間にあなたの回答は確定し、変更不可になります。</p>
<p>ファイル確認を行う者が2人以上いる場合、全員の意見が一致すればその意見が通ります。意見が分かれた場合は、簡易チャットでの議論を経て、最終判断を下します。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>';
    if (exam_anonymous()) {
        echo '<th width="60%">作品名</th><th width="20%">種別</th><th width="20%">状況</th>';
        $colspan = "2";
    }
    else {
        echo '<th width="20%">提出者</th><th width="40%">作品名</th><th width="20%">種別</th><th width="20%">状況</th>';
        $colspan = "3";
    }
    echo '</tr>';
    foreach ($examlist["p"] as $authorandid => $data) {
        list($author, $submitid, $edit) = explode('/', $authorandid);
        $nickname = nickname($author);
        echo "<tr>";
        if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
        if ($submitid === 'common') echo '<td><a href="../exam/do_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else if ($edit == 'new') echo '<td><a href="../exam/do.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else echo '<td><a href="../exam/do_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        if ($edit == 'new') echo "<td>新規提出</td>";
        else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
        echo '<td class="text-warning">未回答</td>';
        echo "</tr>";
    }
    foreach ($examlist["d"] as $authorandid => $data) {
        list($author, $submitid, $edit) = explode('/', $authorandid);
        $nickname = nickname($author);
        echo "<tr>";
        if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
        if ($submitid === 'common') echo '<td><a href="../exam/discuss_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else if ($edit == 'new') echo '<td><a href="../exam/discuss.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else echo '<td><a href="../exam/discuss_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        if ($edit == 'new') echo "<td>新規提出</td>";
        else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
        echo '<td class="text-warning">議論未完了</td>';
        echo "</tr>";
    }
    foreach ($examlist["r"] as $authorandid => $data) {
        list($author, $submitid, $edit) = explode('/', $authorandid);
        $nickname = nickname($author);
        echo "<tr>";
        if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
        if ($submitid === 'common') echo '<td><a href="../exam/frame_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else if ($edit == 'new') echo '<td><a href="../exam/frame.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else echo '<td><a href="../exam/frame_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        if ($edit == 'new') echo "<td>新規提出</td>";
        else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
        echo '<td class="text-warning">非承認理由が未入力</td>';
        echo "</tr>";
    }
    foreach ($examlist["a"] as $authorandid => $data) {
        list($author, $submitid, $edit) = explode('/', $authorandid);
        $nickname = nickname($author);
        echo "<tr>";
        if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
        if ($submitid === 'common') echo '<td><a href="../exam/do_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else if ($edit == 'new') echo '<td><a href="../exam/do.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        else echo '<td><a href="../exam/do_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
        if ($edit == 'new') echo "<td>新規提出</td>";
        else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
        echo '<td>回答済み（他者の回答待ち）</td>';
        echo "</tr>";
    }
    echo '</table></div><h2>確認済みの提出物</h2>';
}


if (!in_term() and $_SESSION["state"] == 'p') echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、主催者は常時提出内容の編集が可能です。
</div></p>';
else if (!in_term()) echo '<div class="border border-danger system-border-spacer">
現在、ファイル提出期間外です。提出内容の確認は出来ますが、原則、編集は出来ません。<br>
ただし、主催者が特定の作品について編集を認めている場合は、編集画面に移れます。
</div>';
?>

<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th width="30%">提出者</th><th width="50%">作品名</th><th width="20%">承認の状態</th>
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
                $acldata = json_decode(file_get_contents_repeat($aclplace), true);
                if (array_search($author . '_userform', $acldata) !== FALSE) $showlink = TRUE;
            }
        break;
    }
    if ($showlink) $namepart = '<a href="detail.php?author=' . $author . '&id=userform">' . hsc($nickname) . '</a>';
    else $namepart = hsc($nickname);
    if (blackuser($author)) $namepart .= '<span class="text-danger">（凍結ユーザー）</span>';

    foreach ($array as $id => $data) {
        echo "<tr>\n";
        echo "<td>" . $namepart . "</td>";
        echo '<td><a href="detail.php?author=' . $author . '&id=' . $id . '">' . hsc($data["title"]) . '</a></td>';
        if (isset($data["editing"]) and $data["editing"] == 1) echo '<td>項目編集の承認待ち</td>';
        else switch ($data["exam"]) {
            case 0:
                echo '<td>承認待ち</td>';
            break;
            case 1:
                echo '<td class="text-success"><strong>承認</strong></td>';
            break;
            case 2:
                echo '<td class="text-warning"><strong>修正待ち</strong></td>';
            break;
            case 3:
                echo '<td class="text-danger"><strong>承認見送り</strong></td>';
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
