<?php
require_once('../../set.php');
setup_session();
$titlepart = '共通情報の確認・承認 - 理由入力画面';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c"), TRUE);

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル確認が出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


$examfilename = basename($_GET["examname"]);
if ($examfilename == "") die_mypage('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam_edit/' . $examfilename . '.txt')) die_mypage('ファイルが存在しません。');
$filedata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $examfilename . '.txt'), true);

list($author, $id, $editid) = explode("/", $filedata["_realid"]);
if ($author == "" or $id == "" or $editid == "") die_mypage('内部パラメーターエラー');
if ($id != "common") die_mypage('内部パラメーターエラー');


//入力内容（before）を読み込む
if (!file_exists(DATAROOT . "users/" . $author . ".txt")) die_mypage('ファイルが存在しません。');
$formdata = json_decode(file_get_contents_repeat(DATAROOT . "users/" . $author . ".txt"), true);

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}

//入力内容（after）を読み込む
if (!file_exists(DATAROOT . "edit/" . $author . "/common.txt")) die_mypage('ファイルが存在しません。');
$changeddata = json_decode(file_get_contents_repeat(DATAROOT . "edit/" . $author . "/common.txt"), true);


$permitted = FALSE;
$leader = id_leader("edit");
if ($leader != NULL) {
    if ($leader == $_SESSION["userid"]) $permitted = TRUE;
}

if ($filedata["_state"] == 4) echo '<h1>共通情報の確認・承認 - 議論画面</h1>
<p>この共通情報への対応について、下記の通り意見が一致しました。</p>
<p>情報の提出者への通知についてはまだ行われていません。メンバーが「問題がある」とした理由をこの画面で取りまとめ、提出者に理由と共に通知します。<br>
以下の入力欄に、提出者宛てのメールに記載する理由文を入力して下さい。</p>
';
else die_mypage('現在受け付けていない操作です。');

if (!isset($_SESSION["dld_caution"])) {
    echo '<div class="border border-warning system-border-spacer">
<strong>【第三者のファイルをダウンロードするにあたっての注意事項】</strong><br>
第三者が作成したファイルのダウンロードには、セキュリティ上のリスクを孕んでいる可能性があります。<br>
アップロード出来るファイルの拡張子を制限する事により、悪意あるファイルをある程度防いでいますが、悪意あるファイルの全てを防げる訳ではありません。<br>
<span class="text-decoration-underline">第三者が作成したファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティを万全に整える事をお勧め致します</span>。
</div>';
    $_SESSION["dld_caution"] = 'ok';
}
?>
<h2><a data-toggle="collapse" href="#toggle" role="button" aria-expanded="false" aria-controls="detail" class="system-foldable-content-link collapsed">
<i class="bi bi-chevron-double-down"></i> 情報の詳細（クリック／タップで開閉）</a></h2>
<div class="collapse" id="toggle">
<?php
if ($filedata["_commonmode"] == "edit") echo '<h3>変更前</h3>';

$lists = [];

$lists[] = ['提出者', exam_anonymous() ? '<span class="text-muted">（主催者が、ファイル確認時に提出者名を表示しない設定にしています。）</span>' : hsc(nickname($author))];
if (isset($filedata["_ip"]) and $_SESSION["state"] == 'p') {
    $status = $filedata["_ip"] . "／";
    $remotesearch = gethostbyaddr($filedata["_ip"]);
    if ($filedata["_ip"] !== $remotesearch) $status .= $remotesearch;
    else $status .= '（リモートホスト名の検索に失敗しました）';
    $lists[] = ['提出時のIPアドレス／リモートホスト名（主催者にのみ表示されています）', $status];
}

    foreach ($formsetting as $key => $array) {
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
                    $echotext .= '<br><a href="../fnc/filedld.php?author=_exam-c-' . $examfilename . '&genre=userform&id=' . $array["id"] . '_' . $filename . '" target="_blank">' . hsc($title) . '</a>';
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

echo_desc_list($lists);

if ($filedata["_commonmode"] == "edit") {
    $lists = [];
    echo '<h3>変更内容</h3>';
    foreach ($formsetting as $key => $array) {
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
                        $echotext .= '<br><a href="../fnc/filedld.php?author=_exam-c-' . $examfilename . '&genre=userform_edit&id=' . $array["id"] . '_' . $filename . '&edit=' . $editid . '" target="_blank">' . hsc($title) . '</a>';
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
</div>
<h2>メンバーの回答</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>回答者</th><th>回答内容</th><th>理由</th>
</tr>
<?php
foreach ($filedata as $key => $data) {
    if (strpos($key, '_') !== FALSE) continue;
    $nickname = nickname($key);
    echo "<tr>\n";
    echo "<td>" . hsc($nickname) . "</td>";
    switch ($data["opinion"]) {
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

<h2>理由文入力</h2>
<?php
if (!$permitted) die_mypage('<div class="border border-danger system-border-spacer">この操作を行えるのはファイル確認のリーダーです。</div>');
?>
<form name="form" action="frame_common_handle.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="subject" value="<?php echo $examfilename; ?>">
<div class="form-group">
<label for="reason">各メンバーが入力した理由を取りまとめ、提出者宛てのメールに記載する理由文を作成して下さい。（500文字以内）</label>
<textarea id="reason" name="reason" rows="5" class="form-control" onkeyup="show_length(value, &quot;reason-counter&quot;);" onChange="check_individual(&quot;reason&quot;);"></textarea>
<div id="reason-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="reason-errortext" class="system-form-error"></div>
</div>
<br>
<button type="submit" class="btn btn-primary">通知を送信する</button>
</div>
<?php
echo_modal_confirm("<p>入力内容に問題は見つかりませんでした。</p><p>現在の入力内容をメールに記載し、提出者への通知を行います。よろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p>");
?>
</form>
<script type="text/javascript">
function check_individual(){
    var valid = 1;

    document.getElementById("reason-errortext").innerHTML = "";
    if(document.form.reason.value === ""){
        valid = 0;
        document.getElementById("reason-errortext").innerHTML = "入力されていません。";
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


function check(){

    var problem = 0;
    var valid = 1;

    document.getElementById("reason-errortext").innerHTML = "";
    if(document.form.reason.value === ""){
        problem = 1;
        valid = 0;
        document.getElementById("reason-errortext").innerHTML = "入力されていません。";
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
