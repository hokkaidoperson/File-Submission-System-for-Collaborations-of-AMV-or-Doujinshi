<?php
require_once('../../set.php');
setup_session();
$titlepart = '提出物（項目変更）の確認・承認 - 回答画面';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c"), TRUE);

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル確認が出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

$examfilename = basename($_GET["examname"]);
if ($examfilename == "") die_mypage('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam_edit/' . $examfilename . '.txt')) die_mypage('ファイルが存在しません。');
$filedata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $examfilename . '.txt'), true);

list($author, $id, $editid) = explode("/", $filedata["_realid"]);
if ($author == "" or $id == "" or $editid == "") die_mypage('内部パラメーターエラー');
if ($id == "common") die_mypage('内部パラメーターエラー');


//入力内容（before）を読み込む
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
$formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$formsetting["general"] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
$examsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);


//入力内容（after）を読み込む
if ($filedata["_state"] == 0) {
    if (!file_exists(DATAROOT . "edit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
    $changeddata = json_decode(file_get_contents_repeat(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
}

$memberfile = DATAROOT . 'exammember_' . $filedata["_membermode"] . '.txt';

$submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$nopermission = FALSE;
$bymyself = FALSE;
if (array_search($_SESSION["userid"], $submitmem) === FALSE) $nopermission = TRUE;
if ($author == $_SESSION["userid"]) {
    $bymyself = TRUE;
    $nopermission = TRUE;
}

if ($filedata["_state"] == 0) echo '<h1>提出物（項目変更）の確認・承認 - 回答画面</h1>
<p>下記の作品について、変更内容をご確認下さい。<br>
その後、変更内容への判断について、下記の入力フォームに回答して下さい。<br>
変更内容が承認されれば、変更をファイルに適用します。変更内容の承認が見送られれば、変更は適用されず、変更前のファイル内容が維持されます。</p>
<p>回答済みの場合、保存されている回答内容が入力されています。変更する場合は、新しい回答内容に変更し、送信して下さい。</p>
';
else if ($filedata["_state"] == 1) echo '<h1>提出物（項目変更）の確認・承認 - 回答履歴</h1>
<p>この作品の項目変更への意見回答は締め切られました。</p>
<p>この作品は、最終判断について議論中です。<br>
<a href="discuss_edit.php?examname=' . $examfilename . '">議論画面はこちら</a></p>
';
else if ($filedata["_state"] == 2) echo '<h1>提出物（項目変更）の確認・承認 - 回答履歴</h1>
<p>この作品の項目変更への意見回答は締め切られました。</p>
<p>この作品の最終判断について議論が行われ、対応が決定しました。<br>
<a href="discuss_edit.php?examname=' . $examfilename . '">議論履歴はこちら</a></p>
';
else echo '<h1>提出物（項目変更）の確認・承認 - 回答履歴</h1>
<p>この作品の項目変更への意見回答は締め切られました。</p>
<p>回答者の意見が一致し、以下の通り対応が即決しました。</p>
';

if ($filedata["_state"] == 0 and $author != $_SESSION["userid"]) {
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

echo '<h2>作品の詳細</h2>';
if ($filedata["_state"] == 0) echo '<h3>変更前</h3>';

$lists = [];

$lists[] = ['タイトル', hsc($formdata["title"])];
$lists[] = ['提出者', (exam_anonymous() and ($filedata["_state"] == 0 or $filedata["_state"] == 1)) ? '<span class="text-muted">（主催者が、ファイル確認時に提出者名を表示しない設定にしています。）</span>' : hsc(nickname($author))];
if (isset($filedata["_ip"]) and $_SESSION["state"] == 'p') {
    $status = $filedata["_ip"] . "／";
    $remotesearch = gethostbyaddr($filedata["_ip"]);
    if ($filedata["_ip"] !== $remotesearch) $status .= $remotesearch;
    else $status .= '（リモートホスト名の検索に失敗しました）';
    $lists[] = ['提出時のIPアドレス／リモートホスト名（主催者にのみ表示されています）', $status];
}

if ($filedata["_state"] == 0) {
    if (isset($formdata["submit"]) and $formdata["submit"] != array()) {
        $echotext = 'ファイル名をクリックするとそのファイルをダウンロードします。';
        if (exam_anonymous()) $echotext .= '<br>※ファイル確認時に提出者名を表示しない設定になっているため、ファイル名を伏せています。';
        foreach ($formdata["submit"] as $filename => $title){
            if (exam_anonymous()) {
                preg_match('/\.([0-9a-zA-Z]+)$/i', $title, $tmp);
                $title = $tmp[1] . 'ファイル_' . $filename;
            }
            $echotext .= '<br><a href="../fnc/filedld.php?author=_exam-e-' . $examfilename . '&genre=submitmain&id=' . $id . '&partid=' . $filename . '" target="_blank">' . hsc($title) . '</a>';
        }
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
                $echotext = 'ファイル名をクリックするとそのファイルをダウンロードします。';
                if (exam_anonymous()) $echotext .= '<br>※ファイル確認時に提出者名を表示しない設定になっているため、ファイル名を伏せています。';
                foreach ($formdata[$array["id"]] as $filename => $title){
                    if (exam_anonymous()) {
                        preg_match('/\.([0-9a-zA-Z]+)$/i', $title, $tmp);
                        $title = $tmp[1] . 'ファイル_' . $filename;
                    }
                    $echotext .= '<br><a href="../fnc/filedld.php?author=_exam-e-' . $examfilename . '&genre=submitform&id=' . $id . '&partid=' . $array["id"] . '_' . $filename . '" target="_blank">' . hsc($title) . '</a>';
                }
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


if ($filedata["_state"] == 0) {
    $lists = [];
    echo '<h3>変更内容</h3>';
    if (isset($changeddata["title"])) $lists[] = ['タイトル', hsc($changeddata["title"])];
    if (isset($changeddata["submit_add"]) or isset($changeddata["submit_delete"])) {
        $echotext = '';
        if (isset($changeddata["submit_delete"]) and $changeddata["submit_delete"] != array()) {
            $echotext .= '<div>以下のファイルを削除：';
            foreach ($changeddata["submit_delete"] as $filename){
                $title = $formdata["submit"][$filename];
                if (exam_anonymous()) {
                    preg_match('/\.([0-9a-zA-Z]+)$/i', $title, $tmp);
                    $title = $tmp[1] . 'ファイル_' . $filename;
                }
                $echotext .= '<br>' . $title;
            }
            $echotext .= '</div>';
        }
        if (isset($changeddata["submit_add"]) and $changeddata["submit_add"] != array()) {
            $echotext .= '<div>以下のファイルを追加（ファイル名をクリックするとそのファイルをダウンロードします）：';
            if (exam_anonymous()) $echotext .= '<br>※ファイル確認時に提出者名を表示しない設定になっているため、ファイル名を伏せています。';
            foreach ($changeddata["submit_add"] as $filename => $title){
                if (exam_anonymous()) {
                    preg_match('/\.([0-9a-zA-Z]+)$/i', $title, $tmp);
                    $title = $tmp[1] . 'ファイル_' . $filename;
                }
                $echotext .= '<br><a href="../fnc/filedld.php?author=_exam-e-' . $examfilename . '&genre=submitmain_edit&id=' . $id . '&edit=' . $editid . '&partid=' . $filename . '" target="_blank">' . hsc($title) . '</a>';
            }
            $echotext .= '</div>';
        }
        $lists[] = ['提出ファイル', $echotext];
    } else if (isset($changeddata["url"])) {
        $echotext = '<a href="' . hsc($changeddata["url"]) . '" target="_blank" rel="noopener">クリックすると新しいウィンドウで開きます</a>';
        if (isset($changeddata["dldpw"]) and $changeddata["dldpw"] != "") $echotext .= '<br><span class="small">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . hsc($changeddata["dldpw"]) . '</code></span>';
        if (isset($changeddata["due"]) and $changeddata["due"] != "") $echotext .= '<br><span class="small">※ダウンロードURLの有効期限は <strong>' . date('Y年n月j日G時i分', $changeddata["due"]) . '</strong> までです。お早めにダウンロード願います。</span>';
        $echotext .= '<br><span class="small">※<span class="text-decoration-underline">このファイルは、一括ダウンロード機能でダウンロードする事が出来ません</span>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</span>';
        $lists[] = ['提出ファイルダウンロード先', $echotext];
    }

    foreach ($formsetting as $key => $array) {
        if ($key === "general") continue;
        if (isset($changeddata[$array["id"]]) or isset($changeddata[$array["id"] . "_add"]) or isset($changeddata[$array["id"] . "_delete"])) {
            if ($array["type"] == "attach") {
                $echotext = '';
                if (isset($changeddata[$array["id"] . "_delete"]) and $changeddata[$array["id"] . "_delete"] != array()) {
                    $echotext .= '<div>以下のファイルを削除：';
                    foreach ($changeddata[$array["id"] . "_delete"] as $filename){
                        $title = $formdata[$array["id"]][$filename];
                        if (exam_anonymous()) {
                            preg_match('/\.([0-9a-zA-Z]+)$/i', $title, $tmp);
                            $title = $tmp[1] . 'ファイル_' . $filename;
                        }
                        $echotext .= '<br>' . $title;
                    }
                    $echotext .= '</div>';
                }
                if (isset($changeddata[$array["id"] . "_add"]) and $changeddata[$array["id"] . "_add"] != array()) {
                    $echotext .= '<div>以下のファイルを追加（ファイル名をクリックするとそのファイルをダウンロードします）：';
                    if (exam_anonymous()) $echotext .= '<br>※ファイル確認時に提出者名を表示しない設定になっているため、ファイル名を伏せています。';
                    foreach ($changeddata[$array["id"] . "_add"] as $filename => $title){
                        if (exam_anonymous()) {
                            preg_match('/\.([0-9a-zA-Z]+)$/i', $title, $tmp);
                            $title = $tmp[1] . 'ファイル_' . $filename;
                        }
                        $echotext .= '<br><a href="../fnc/filedld.php?author=_exam-e-' . $examfilename . '&genre=submitform_edit&id=' . $id . '&partid=' . $array["id"] . '_' . $filename . '&edit=' . $editid . '" target="_blank">' . hsc($title) . '</a>';
                    }
                    $echotext .= '</div>';
                }
            }
            else {
                $echotext = '';
                for ($answer = 0; $answer < count($changeddata[$array["id"]]); $answer++) {
                    $echotext .= '<div>';
                    if (isset($array["prefix"][$answer]) and $array["prefix"][$answer] != "") $echotext .= '<span class="badge badge-secondary">' . hsc($array["prefix"][$answer]) . '</span> ';
                    $echotext .= give_br_tag($changeddata[$array["id"]][$answer]);
                    if (isset($array["suffix"][$answer]) and $array["suffix"][$answer] != "") $echotext .= ' <span class="badge badge-secondary">' . hsc($array["suffix"][$answer]) . '</span> ';
                    $echotext .= '</div>';
                }
            }
            $lists[] = [hsc($array["title"]), $echotext];
        }
    }
    echo_desc_list($lists);
}
?>
<h2><a data-toggle="collapse" href="#toggle" role="button" aria-expanded="false" aria-controls="detail" class="system-foldable-content-link collapsed">
<i class="bi bi-chevron-double-down"></i> 回答状況（クリック／タップで開閉）</a></h2>
<div class="table-responsive-md collapse" id="toggle">
<table class="table table-hover table-bordered">
<tr>
<?php
if ($filedata["_state"] == 0) echo '<th width="70%">回答者</th><th width="30%">回答状況</th>';
else echo '<th width="30%">回答者</th><th width="30%">回答内容</th><th width="40%">理由</th>';
?>
</tr>
<?php
if ($filedata["_state"] == 0) foreach ($submitmem as $key) {
    if (!user_exists($key)) continue;
    $usdata = id_array($key);
    if ($usdata["state"] == 'g') continue;
    if ($usdata["state"] == 'o') continue;
    if (isset($filedata[$key])) $data = $filedata[$key];
    else $data = array("opinion" => 0, "reason" => "");
    $nickname = nickname($key);
    echo "<tr>\n";
    echo "<td>" . hsc($nickname) . "</td>";
    if ($data["opinion"] != 0) echo '<td class="text-success">回答済み</td>';
    else echo '<td>未回答</td>';
    echo "</tr>\n";
} else foreach ($filedata as $key => $data) {
    if (strpos($key, '_') !== FALSE) continue;
    $nickname = nickname($key);
    echo "<tr>\n";
    echo "<td>" . hsc($nickname) . "</td>";
    // opinion 0...未回答　1...承認 2...拒否
    switch ($data["opinion"]) {
        case 1:
            echo '<td>承認する</td>';
        break;
        case 2:
            echo '<td>承認を見送る</td>';
        break;
        default:
            echo '<td>未回答</td>';
        break;
    }
    echo '<td>' . give_br_tag($data["reason"]) . '</td>';
    echo "</tr>\n";
}
if (isset($filedata["_result"])) {
    echo '<tr class="table-primary"><th>最終結果</th>';
    switch ($filedata["_result"]["opinion"]) {
      case 1:
          echo '<td><strong>承認</strong></td>';
      break;
      case 2:
          echo '<td><strong>承認見送り</strong></td>';
      break;
    }
    echo '<td>' . give_br_tag($filedata["_result"]["reason"]) . '</td>';
    echo '</tr>';
}
?>
</table>
</div>
<?php if ($filedata["_state"] != 0) die_mypage(); ?>
<h2>回答する</h2>
<?php if (!$nopermission) { ?>
<form name="form" action="do_edit_handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="subject" value="<?php echo $examfilename; ?>">
<div class="form-group">
この変更を承認してもよいと思いますか？
<div class="form-check">
<input id="ans-1" class="form-check-input" type="radio" name="ans" value="1" <?php
if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] == 1) echo 'checked="checked"';
?> onChange="check_individual(&quot;ans&quot;);">
<label class="form-check-label" for="ans-1">はい、問題ありません。</label>
</div>
<div class="form-check">
<input id="ans-2" class="form-check-input" type="radio" name="ans" value="2" <?php
if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] == 2) echo 'checked="checked"';
?> onChange="check_individual(&quot;ans&quot;);">
<label class="form-check-label" for="ans-2">いいえ、問題があります。</label>
</div>
<div id="ans-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
<label for="reason">「問題がある」と答えた場合は、その理由を入力して下さい。（500文字以内）</label>
<textarea id="reason" name="reason" rows="5" class="form-control" onkeyup="show_length(value, &quot;reason-counter&quot;);" onChange="check_individual(&quot;reason&quot;);"><?php
if (isset($filedata[$_SESSION["userid"]]["reason"])) echo hsc($filedata[$_SESSION["userid"]]["reason"]);
?></textarea>
<div id="reason-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="reason-errortext" class="system-form-error"></div>
<small class="form-text"><?php
if ($examsetting["reason"] == "notice") echo "※<strong>ここで記入した理由は、ファイル提出者本人宛に送信するメールに記載される可能性があります。</strong>";
else echo "※ここで記入した理由は、ファイル提出者本人宛に送信するメールに直接的に記載されません。";
?></small>
</div>
<br>
<button type="submit" class="btn btn-primary">回答を送信する</button>
</div>
<?php
echo_modal_confirm("<p>入力内容に問題は見つかりませんでした。</p><p>現在の回答内容を登録してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p>");
?>
</form>
<?php } else {
    echo '<div class="border border-primary system-border-spacer">';
    if ($bymyself) echo 'あなたはこのファイルの提出者であるため、「問題無い」に自動投票されています。';
else echo 'あなたはファイル確認の権限を持っていません。';
    echo '</div>';
} ?>
<?php
$echoforceclose = FALSE;
$leader = id_leader($filedata["_membermode"]);
if ($leader != NULL) {
    if ($leader == $_SESSION["userid"] and isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($noprom) {
    if (!($nopermission and !$bymyself) and isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
} else if ($_SESSION["state"] == 'p') {
    if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if ($echoforceclose) { ?>
<h2>投票を強制的に締め切る</h2>
<p><strong>原則としては、メンバー全員の投票が終わるのを待って下さい。</strong><br>
<span class="text-decoration-underline">メンバーの誰かが投票をしておらず、かつそのメンバーと連絡が取れない場合</span>は、作業を長引かせないために、以下のボタンを押して、投票を終了して下さい。</p>
<p><strong>この機能は、あくまでも最終手段としてご利用願います。</strong></p>
<p>※この機能は、原則としてファイル確認のリーダー（リーダーが設定されていない場合は主催者）にのみ開放されています。ファイル確認メンバーにリーダーも主催者もいない場合には、共同運営者に開放されています。</p>
<form name="form_forceclose" action="do_edit_forceclose.php" method="post" onSubmit="$('#forceclosemodal').modal(); return false;" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="examname" value="<?php echo $examfilename; ?>">
<button type="submit" class="btn btn-danger">投票を強制的に締め切る</button>
<?php echo_modal_confirm("<p>投票を強制的に締め切ります。よろしければ「OK」を押して下さい。<br>この操作を取りやめる場合は「戻る」を押して下さい。</p><p><strong>一旦OKボタンを押下すると、この操作を取り消す事が出来なくなりますので、ご注意下さい</strong>。</p>", "操作確認", null, null, "OK", "danger", "forceclosemodal", "forceclosebtn", 'document.getElementById("forceclosebtn").disabled = "disabled"; document.form_forceclose.submit();'); ?>
</form>
<?php }
?>
<script type="text/javascript">

function check_individual(id){
    var valid = 1;

    if (id === "ans") {
        document.getElementById("ans-errortext").innerHTML = "";
        var f = document.getElementsByName("ans");
        if(document.form.ans.value === ""){
            document.getElementById("ans-errortext").innerHTML = "いずれかを選択して下さい。";
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-invalid");
                f[j].classList.remove("is-valid");
            }
        } else {
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-valid");
                f[j].classList.remove("is-invalid");
            }
        }
        return;
    }

    if (id === "reason") {
        document.getElementById("reason-errortext").innerHTML = "";
        if(document.form.reason.value === ""){
            if(document.form.ans.value === "2"){
                valid = 0;
                document.getElementById("reason-errortext").innerHTML = "「問題がある」と答えた場合は、入力が必要です。";
            }
        } else if(document.form.reason.value.length > 500){
            valid = 0;
            document.getElementById("reason-errortext").innerHTML = "文字数が多すぎます。500文字以内に抑えて下さい。";
        }
        if (valid) {
            document.form.reason.classList.add("is-valid");
            document.form.reason.classList.remove("is-invalid");
        } else {
            document.form.reason.classList.add("is-invalid");
            document.form.reason.classList.remove("is-valid");
        }
        return;
    }
}


function check(){

    var problem = 0;
    var valid = 1;

    document.getElementById("ans-errortext").innerHTML = "";
    var f = document.getElementsByName("ans");
    if(document.form.ans.value === ""){
        problem = 1;
        document.getElementById("ans-errortext").innerHTML = "いずれかを選択して下さい。";
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-invalid");
            f[j].classList.remove("is-valid");
      	}
    } else {
        for(var j = 0; j < f.length; j++ ){
      	    f[j].classList.add("is-valid");
            f[j].classList.remove("is-invalid");
      	}
    }

    document.getElementById("reason-errortext").innerHTML = "";
    if(document.form.reason.value === ""){
        if(document.form.ans.value === "2"){
            problem = 1;
            valid = 0;
            document.getElementById("reason-errortext").innerHTML = "「問題がある」と答えた場合は、入力が必要です。";
        }
    } else if(document.form.reason.value.length > 500){
        problem = 1;
        valid = 0;
        document.getElementById("reason-errortext").innerHTML = "文字数が多すぎます。500文字以内に抑えて下さい。";
    }
    if (valid) {
        document.form.reason.classList.add("is-valid");
        document.form.reason.classList.remove("is-invalid");
    } else {
        document.form.reason.classList.add("is-invalid");
        document.form.reason.classList.remove("is-valid");
    }



    if ( problem == 0 ) {
        $('#confirmmodal').modal();
        $('#confirmmodal').on('shown.bs.modal', function () {
            document.getElementById("submitbtn").focus();
        });
    }
    return false;

}

</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
