<?php
require_once('../../set.php');
setup_session();
$titlepart = 'データ詳細';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

//ファイル提出者のユーザーID
$author = basename($_GET["author"]);

//提出ID
$id = basename($_GET["id"]);

if ($author == "" or $id == "") die_mypage('パラメーターエラー');

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
if (!$allowed) die_mypage('このファイルの閲覧権限がありません。');


//入力内容を読み込む
switch($id) {
    case 'userform':
        if (!file_exists(DATAROOT . "users/" . $author . ".txt")) die_mypage('ファイルが存在しません。');
        $formdata = json_decode(file_get_contents_repeat(DATAROOT . "users/" . $author . ".txt"), true);
    break;
    default:
        if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
        $formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
}

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
if (file_exists(DATAROOT . 'form/submit/general.txt')) $formsetting["general"] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
else $formsetting["general"] = [];

//ユーザーフォーム設定ファイル読み込み
$userformdata = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}

switch($id) {
    case 'userform':
        $disp = hsc(nickname($author)) . " の共通情報";
    break;
    default:
        $disp = hsc($formdata["title"]);
}

if ($author != $_SESSION["userid"]) {
    if (!isset($_SESSION["dld_caution"])) {
        echo '<div class="border border-warning system-border-spacer">
<strong>【第三者のファイルをダウンロードするにあたっての注意事項】</strong><br>
第三者が作成したファイルのダウンロードには、セキュリティ上のリスクを孕んでいる可能性があります。<br>
アップロード出来るファイルの拡張子を制限する事により、悪意あるファイルをある程度防いでいますが、悪意あるファイルの全てを防げる訳ではありません。<br>
<span class="text-decoration-underline">第三者が作成したファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティを万全に整える事をお勧め致します</span>。
</div>';
        $_SESSION["dld_caution"] = 'ok';
    }
}
if (blackuser($author)) echo '<div class="border border-danger system-border-spacer">
このユーザーは凍結されています。
</div>';
?>

<h1><?php echo $disp; ?></h1>
<?php
$lists = [];

if ($id != "userform") {
    $lists[] = ['提出者', hsc(nickname($author))];
    $lists[] = ['提出日時', date('Y年n月j日G時i分s秒', $id)];
    $lists[] = ['最終更新日時', isset($formdata["editdate"]) ? date('Y年n月j日G時i分s秒', $formdata["editdate"]) : date('Y年n月j日G時i分s秒', $id)];
    if (isset($formdata["editing"]) and $formdata["editing"] == 1) $echotext = '項目編集の承認待ち<br>※変更後の内容は上記表に反映されていません。';
    else switch ($formdata["exam"]) {
        case 0:
            $echotext = '承認待ち';
        break;
        case 1:
            $echotext = '<strong class="text-success">承認</strong>';
        break;
        case 2:
            $echotext = '<strong class="text-warning">修正待ち</strong>';
        break;
        case 3:
            $echotext = '<strong class="text-danger">承認見送り</strong>';
        break;
    }
    $lists[] = ['承認の状態', $echotext];

    if (isset($formdata["author_ip"]) and $_SESSION["state"] == 'p') {
        $status = $formdata["author_ip"] . "／";
        $remotesearch = gethostbyaddr($formdata["author_ip"]);
        if ($formdata["author_ip"] !== $remotesearch) $status .= $remotesearch;
        else $status .= '（リモートホスト名の検索に失敗しました）';
        $lists[] = ['最終更新時のIPアドレス／リモートホスト名（主催者にのみ表示されています）', $status];
    }

    if (isset($formdata["submit"]) and $formdata["submit"] != array()) {
        $echotext = 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
        foreach ($formdata["submit"] as $filename => $title)
        $echotext .= '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitmain&id=' . $id . '&partid=' . $filename . '" target="_blank">' . hsc($title) . '</a><br>';
        $lists[] = ['提出ファイル', $echotext];
    } else {
        $echotext = '<a href="' . hsc($formdata["url"]) . '" target="_blank" rel="noopener">クリックすると新しいウィンドウで開きます</a>';
        if (isset($formdata["dldpw"]) and $formdata["dldpw"] != "") $echotext .= '<br><span class="small">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . hsc($formdata["dldpw"]) . '</code></span>';
        if (isset($formdata["due"]) and $formdata["due"] != "") $echotext .= '<br><span class="small">※ダウンロードURLの有効期限は <strong>' . date('Y年n月j日G時i分', $formdata["due"]) . '</strong> までです。お早めにダウンロード願います。</span>';
        $echotext .= '<br><span class="small">※<span class="text-decoration-underline">このファイルは、一括ダウンロード機能でダウンロードする事が出来ません</span>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</span>';
        $lists[] = ['提出ファイルダウンロード先', $echotext];
    }

    foreach ($formsetting as $key => $array) {
        if ($key === "general") continue;
        if (!isset($formdata[$array["id"]])) {
            $lists[] = [hsc($array["title"]), ''];
            continue;
        }
        if ($array["type"] == "attach") {
            if ($formdata[$array["id"]] != array()) {
                $echotext = 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
                foreach ($formdata[$array["id"]] as $filename => $title)
                $echotext .= '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitform&id=' . $id . '&partid=' . $array["id"] . '_' . $filename . '" target="_blank">' . hsc($title) . '</a><br>';
            }
        }
        else {
            $echotext = '';
            for ($answer = 0; $answer < count($formdata[$array["id"]]); $answer++) {
                $echotext .= '<div>';
                if (isset($array["prefix"][$answer]) and $array["prefix"][$answer] != "") $echotext .= '<span class="badge badge-secondary">' . hsc($array["prefix"][$answer]) . '</span> ';
                $echotext .= give_br_tag($formdata[$array["id"]][$answer]);
                if (isset($array["suffix"][$answer]) and $array["suffix"][$answer] != "") $echotext .= ' <span class="badge badge-secondary">' . hsc($array["suffix"][$answer]) . '</span> ';
                $echotext .= '</div>';
            }
        }
        $lists[] = [hsc($array["title"]), $echotext];
    }
} else {
    if (isset($formdata["common_acceptance"])) {
        if (isset($formdata["common_editing"]) and $formdata["common_editing"] == 1) $echotext = '項目編集の承認待ち<br>※変更後の内容は上記に反映されていません。';
        else switch ($formdata["common_acceptance"]) {
            case 0:
                $echotext = '承認待ち';
            break;
            case 1:
                $echotext = '<strong class="text-success">承認</strong>';
            break;
            case 2:
                $echotext = '<strong class="text-danger">承認見送り</strong>';
            break;
        }
    } else $echotext = '未入力';
    $lists[] = ['共通情報の承認状態', $echotext];

    if (isset($formdata["createip"]) and $_SESSION["state"] == 'p') {
        $echotext = $formdata["createip"] . "／";
        $remotesearch = gethostbyaddr($formdata["createip"]);
        if ($formdata["createip"] !== $remotesearch) $echotext .= $remotesearch;
        else $echotext .= '（リモートホスト名の検索に失敗しました）';
        $lists[] = ['アカウント作成時のIPアドレス／リモートホスト名（主催者にのみ表示されています）', $echotext];
    }

    if (isset($formdata["common_ip"]) and $_SESSION["state"] == 'p') {
        $echotext = $formdata["common_ip"] . "／";
        $remotesearch = gethostbyaddr($formdata["common_ip"]);
        if ($formdata["common_ip"] !== $remotesearch) $echotext .= $remotesearch;
        else $echotext .= '（リモートホスト名の検索に失敗しました）';
        $lists[] = ['共通情報の最終更新時のIPアドレス／リモートホスト名（主催者にのみ表示されています）', $echotext];
    }

    foreach ($userformdata as $key => $array) {
        if (!isset($formdata[$array["id"]])) {
            $lists[] = [hsc($array["title"]), ''];
            continue;
        }
        if ($array["type"] == "attach") {
            if ($formdata[$array["id"]] != array()) {
                $echotext = 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
                foreach ($formdata[$array["id"]] as $filename => $title)
                $echotext .= '<a href="../fnc/filedld.php?author=' . $author . '&genre=userform&id=' . $array["id"] . '_' . $filename . '" target="_blank">' . hsc($title) . '</a><br>';
            }
        }
        else {
            $echotext = '';
            for ($answer = 0; $answer < count($formdata[$array["id"]]); $answer++) {
                $echotext .= '<div>';
                if (isset($array["prefix"][$answer]) and $array["prefix"][$answer] != "") $echotext .= '<span class="badge badge-secondary">' . hsc($array["prefix"][$answer]) . '</span> ';
                $echotext .= give_br_tag($formdata[$array["id"]][$answer]);
                if (isset($array["suffix"][$answer]) and $array["suffix"][$answer] != "") $echotext .= ' <span class="badge badge-secondary">' . hsc($array["suffix"][$answer]) . '</span> ';
                $echotext .= '</div>';
            }
        }
        $lists[] = [hsc($array["title"]), $echotext];
    }

}

echo_desc_list($lists);

if ($id == "userform" or $author != $_SESSION["userid"]) die_mypage();
?>
<h2>ファイル操作</h2>
<?php
if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;

if (!in_term() and !$outofterm) die_mypage('<div class="border border-danger system-border-spacer">
現在、ファイル提出期間外のため、ファイル操作（編集・削除）は行えません。
</div>');
else {
    if (!in_term() and $_SESSION["state"] == 'p') echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、主催者は常時ファイル操作（編集・削除）が可能です。
</div>';
    else if (!in_term()) echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、あなたは主催者からこのファイルの操作（編集・削除）を許可されています（' . date('Y年n月j日G時i分s秒', outofterm($id)) . 'まで）。
</div>';
}

if ($formdata["exam"] == 0 or $formdata["editing"] == 1) echo '<div class="border border-primary system-border-spacer">
現在、ファイルの確認待ちです。確認が完了するまでは、ファイルの編集が出来ません（削除は出来ます）。
</div><p>';
else echo '<p><a href="edit.php?author=' . $author . '&id=' . $id . '" class="btn btn-primary" role="button"><i class="bi bi-pencil-square"></i> 内容を編集する</a> ';
?>
<a href="delete.php?author=<?php echo $author; ?>&id=<?php echo $id; ?>" class="btn btn-danger" role="button"><i class="bi bi-trash"></i> この作品を削除する</a></p>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
