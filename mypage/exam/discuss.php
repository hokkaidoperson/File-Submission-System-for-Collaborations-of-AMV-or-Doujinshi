<?php
require_once('../../set.php');
setup_session();
$titlepart = '提出物の確認・承認 - 議論画面';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c"), TRUE);

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル確認が出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


$examfilename = basename($_GET["examname"]);
if ($examfilename == "") die_mypage('パラメーターエラー');

if (!file_exists(DATAROOT . 'exam/' . $examfilename . '.txt')) die_mypage('ファイルが存在しません。');
$filedata = json_decode(file_get_contents_repeat(DATAROOT . 'exam/' . $examfilename . '.txt'), true);

list($author, $id) = explode("/", $filedata["_realid"]);

if ($author == "" or $id == "") die_mypage('内部パラメーターエラー');


//入力内容を読み込む
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

$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

$nopermission = FALSE;
if (array_search($_SESSION["userid"], $submitmem) === FALSE) $nopermission = TRUE;

if ($filedata["_state"] == 1) echo '<h1>提出物の確認・承認 - 議論画面</h1>
<p>この作品への対応について、意見が分かれたため、以下の簡易チャットを用いて議論を行って下さい。</p>
<p>意見がまとまったら、最終的な対応を入力して下さい。<br>
最終的な対応の入力は、原則としてファイル確認のリーダー（リーダーが設定されていない場合は主催者）が行えます。ファイル確認メンバーにリーダーも主催者もいない場合には、共同運営者が対応を入力します。</p>
';
else if ($filedata["_state"] == 2) echo '<h1>提出物の確認・承認 - 議論履歴</h1>
<p>この作品への対応について議論し、対応を決定しました。<br>
最終的な対応及び議論の履歴を以下に表示します。</p>
';
else die_mypage('この作品への議論は行われていません。');

//議論ログ
if (!file_exists(DATAROOT . 'exam_discuss/')) {
    if (!mkdir(DATAROOT . 'exam_discuss/')) die_mypage('ディレクトリの作成に失敗しました。');
}
if (file_exists(DATAROOT . 'exam_discuss/' . $examfilename . '.txt')) $discussdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_discuss/' . $examfilename . '.txt'), true);
else {
    $discussdata = array(
        "read" => array(),
        "comments" => array()
    );
    foreach ($submitmem as $key) {
        $discussdata["read"][$key] = 0;
    }
    $filedatajson = json_encode($discussdata);
    if (file_put_contents_repeat(DATAROOT . 'exam_discuss/' . $examfilename . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');
}
//read...既読？（0未読　1既読）　comments...ログ

//既読の処理
if (!$nopermission) {
    $discussdata["read"][$_SESSION["userid"]] = 1;
    $filedatajson = json_encode($discussdata);
    if (file_put_contents_repeat(DATAROOT . 'exam_discuss/' . $examfilename . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');
}

if ($filedata["_state"] == 1 and $author != $_SESSION["userid"]) {
if (!isset($_SESSION["dld_caution"])) {
    echo '<div class="border border-warning system-border-spacer">
<strong>【第三者のファイルをダウンロードするにあたっての注意事項】</strong><br>
第三者が作成したファイルのダウンロードには、セキュリティ上のリスクを孕んでいる可能性があります。<br>
アップロード出来るファイルの拡張子を制限する事により、悪意あるファイルをある程度防いでいますが、悪意あるファイルの全てを防げる訳ではありません。<br>
<u>第三者が作成したファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティを万全に整える事をお勧め致します</u>。
</div>';
    $_SESSION["dld_caution"] = 'ok';
}
}
?>

<h2>作品の詳細</h2>
<p><a class="btn btn-primary" data-toggle="collapse" href="#toggle" role="button" aria-expanded="false" aria-controls="toggle">
展開する
</a></p>
<div class="table-responsive-md collapse" id="toggle">
<table class="table table-hover table-bordered">
<?php
if ($filedata["_state"] == 1) {

if (isset($formdata["submit"]) and $formdata["submit"] != array()) {
    echo '<tr><th>提出ファイル</th><td>ファイル名をクリックするとそのファイルをダウンロードします。<br>';
    foreach ($formdata["submit"] as $filename => $title)
    echo '<a href="../fnc/filedld.php?author=_exam-s-' . $examfilename . '&genre=submitmain&id=' . $id . '&partid=' . $filename . '" target="_blank">' . hsc($title) . '</a><br>';
    echo '</td></tr>';
} else {
    echo '<tr>
<th>提出ファイルダウンロード先</th><td><a href="' . hsc($formdata["url"]) . '" target="_blank" rel="noopener">クリックすると新しいウィンドウで開きます</a>';
    if (isset($formdata["dldpw"]) and $formdata["dldpw"] != "") echo '<br><span class="small">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . hsc($formdata["dldpw"]) . '</code></span>';
    if (isset($formdata["due"]) and $formdata["due"] != "") echo '<br><span class="small">※ダウンロードURLの有効期限は <strong>' . date('Y年n月j日G時i分', $formdata["due"]) . '</strong> までです。お早めにダウンロード願います。</span>';
    echo '<br><span class="small">※<u>このファイルは、一括ダウンロード機能でダウンロードする事が出来ません</u>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</span>';
    echo '</td></tr>';
}

}
?>
<tr>
<th width="30%">提出者</th><td width="70%"><?php
if (exam_anonymous() and $filedata["_state"] == 1) echo '<span class="text-muted">（主催者が、ファイル確認時に提出者名を表示しない設定にしています。）</span>';
else echo hsc(nickname($author));
?></td>
</tr>
<tr>
<th>タイトル</th><td><?php echo hsc($formdata["title"]); ?></td>
</tr>
<?php
if (isset($filedata["_ip"]) and $_SESSION["state"] == 'p') {
    echo '<tr><th>提出時のIPアドレス／リモートホスト名（主催者にのみ表示されています）</th><td>';
    echo $filedata["_ip"] . "／";
    $remotesearch = gethostbyaddr($filedata["_ip"]);
    if ($filedata["_ip"] !== $remotesearch) echo $remotesearch;
    else echo '（リモートホスト名の検索に失敗しました）';
    echo '</td></tr>';
}

if ($filedata["_state"] == 1) {

foreach ($formsetting as $key => $array) {
    if ($key === "general") continue;
    echo "<tr>\n";
    echo "<th>" . hsc($array["title"]) . "</th>";
    echo "<td>";
    if (!isset($formdata[$array["id"]]) and !isset($formdata[$array["id"] . "-1"]) and !isset($formdata[$array["id"] . "-2"])) {
        echo '</td>';
        echo "</tr>\n";
        continue;
    }
    if ($array["type"] == "attach") {
        if (isset($formdata[$array["id"]]) and $formdata[$array["id"]] != array()) {
            echo 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
            foreach ($formdata[$array["id"]] as $filename => $title)
            echo '<a href="../fnc/filedld.php?author=_exam-s-' . $examfilename . '&genre=submitform&id=' . $id . '&partid=' . $array["id"] . '_' . $filename . '" target="_blank">' . hsc($title) . '</a><br>';
        }
    }
    else if ($array["type"] == "check") {
        $dsp = implode("\n", $formdata[$array["id"]]);
        $dsp = hsc($dsp);
        echo str_replace("\n", '<br>', $dsp);
    } else if ($array["type"] == "textbox2") {
        if ($formdata[$array["id"] . "-1"] != "") {
            echo '<div>';
            if (isset($array["prefix_a"]) and $array["prefix_a"] != "") echo '<span class="badge badge-secondary">' . hsc($array["prefix_a"]) . '</span> ';
            echo hsc($formdata[$array["id"] . "-1"]);
            if (isset($array["suffix_a"]) and $array["suffix_a"] != "") echo ' <span class="badge badge-secondary">' . hsc($array["suffix_a"]) . '</span> ';
            echo '</div>';
        }
        if ($formdata[$array["id"] . "-2"] != "") {
            echo '<div>';
            if (isset($array["prefix_b"]) and $array["prefix_b"] != "") echo '<span class="badge badge-secondary">' . hsc($array["prefix_b"]) . '</span> ';
            echo hsc($formdata[$array["id"] . "-2"]);
            if (isset($array["suffix_b"]) and $array["suffix_b"] != "") echo ' <span class="badge badge-secondary">' . hsc($array["suffix_b"]) . '</span> ';
            echo '</div>';
        }
    } else {
        if (isset($array["prefix_a"]) and $array["prefix_a"] != "") echo '<span class="badge badge-secondary">' . hsc($array["prefix_a"]) . '</span> ';
        echo give_br_tag($formdata[$array["id"]]);
        if (isset($array["suffix_a"]) and $array["suffix_a"] != "") echo ' <span class="badge badge-secondary">' . hsc($array["suffix_a"]) . '</span> ';
    }
    echo '</td>';
    echo "</tr>\n";
}

}
?>
</table>
</div>
<h2>メンバーの回答</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<?php
echo '<th width="30%">回答者</th><th width="30%">回答内容</th><th width="40%">理由</th>';
?>
</tr>
<?php
foreach ($filedata as $key => $data) {
    if (strpos($key, '_') !== FALSE) continue;
    $nickname = nickname($key);
    echo "<tr>\n";
    echo "<td>" . hsc($nickname) . "</td>";
    // opinion 0...未回答　1...承認 2...修正求む 3...拒否
    switch ($data["opinion"]) {
        case 1:
            echo '<td>承認しても問題無い</td>';
        break;
        case 2:
            echo '<td>軽微な修正が必要</td>';
        break;
        case 3:
            echo '<td>内容等の問題が多い・重大な問題がある</td>';
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
          echo '<td><strong>修正待ち</strong></td>';
      break;
      case 3:
          echo '<td><strong>拒否</strong></td>';
      break;
    }
    echo '<td>' . give_br_tag($filedata["_result"]["reason"]) . '</td>';
    echo '</tr>';
}
?>
</table>
</div>
<h2>簡易チャット</h2>
<div class="border border-primary system-border-spacer">
<?php
if ($discussdata["comments"] != array()) {
    echo "<ul>";
    foreach ($discussdata["comments"] as $key => $log) {
        list($comid, $date) = explode('_', $key);
        $log = hsc($log);
        $log = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $log);
        $log = str_replace(array("\r\n", "\r", "\n"), "\n", $log);
        $log = str_replace("\n", "<br>", $log);
        if ($comid == "-system") $nickname = "<u>システム</u>";
        else $nickname = hsc(nickname($comid));

        echo "<li>";
        echo "<strong>" . $nickname . "</strong>（" . date('Y/m/d H:i:s', $date) . "）<br>" . $log;
        echo "</li>";
    }
    echo "</ul>";
}
if ($filedata["_state"] != '1') die_mypage('</div>');
if ($nopermission) echo 'あなたはファイル確認の権限を持っていません。';
else {
?>
<form name="form" action="discuss_handle.php" method="post" onSubmit="return check()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="subject" value="<?php echo $examfilename; ?>">
<div class="form-group">
<label for="add">新規コメント追加（500文字以内）</label>
<textarea id="add" name="add" rows="5" class="form-control" onkeyup="ShowLength(value, &quot;add-counter&quot;);" onChange="check_individual();"></textarea>
<div id="add-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="add-errortext" class="system-form-error"></div>
<small class="form-text">※改行は反映されます（この入力欄で改行すると実際のコメントでも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。</small>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">コメントを追加</button>
</form>
<?php } ?>
</div>
<script type="text/javascript">

//文字数カウント　参考　https://www.nishishi.com/javascript-tips/input-counter.html
function ShowLength(str, resultid) {
   document.getElementById(resultid).innerHTML = "現在 " + str.length + " 文字";
}

function check_individual(){
    var problem = 0;

    document.getElementById("add-errortext").innerHTML = "";
    if(document.form.add.value === ""){
        problem = 1;
        document.getElementById("add-errortext").innerHTML = "入力されていません。";
    } else if(document.form.add.value.length > 500){
        problem = 1;
        document.getElementById("add-errortext").innerHTML = "文字数が多すぎます。500文字以内に抑えて下さい。";
    }
    if (!problem) {
        document.form.add.classList.add("is-valid");
        document.form.add.classList.remove("is-invalid");
    } else {
        document.form.add.classList.add("is-invalid");
        document.form.add.classList.remove("is-valid");
    }
}

function check(){

    var problem = 0;

    document.getElementById("add-errortext").innerHTML = "";
    if(document.form.add.value === ""){
        problem = 1;
        document.getElementById("add-errortext").innerHTML = "入力されていません。";
    } else if(document.form.add.value.length > 500){
        problem = 1;
        document.getElementById("add-errortext").innerHTML = "文字数が多すぎます。500文字以内に抑えて下さい。";
    }
    if (!problem) {
        document.form.add.classList.add("is-valid");
        document.form.add.classList.remove("is-invalid");
    } else {
        document.form.add.classList.add("is-invalid");
        document.form.add.classList.remove("is-valid");
    }

    if ( problem == 1 ) return false;

    document.getElementById("submitbtn").disabled = "disabled";
    return true;

}

</script>

<?php
$leader = id_leader("submit");
if ($leader != NULL) {
    if ($leader != $_SESSION["userid"]) die_mypage();
} else if ($_SESSION["state"] != 'p' and $noprom == FALSE) die_mypage();
?>
<h2>最終判断</h2>
<p>結論が固まりましたら、以下にその結論を入力して、議論を終了して下さい。<br>
トラブル防止のため、結論が固まっていない段階で入力を行うのはお控え下さい。</p>
<form name="form_decide" action="discuss_decide.php" method="post" onSubmit="return check_decide()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="subject" value="<?php echo $examfilename; ?>">
<div class="form-group">
議論の末の判断を以下から選んで下さい。
<div class="form-check">
<input id="ans-1" class="form-check-input" type="radio" name="ans" value="1" onChange="check_decide_individual(&quot;ans&quot;);">
<label class="form-check-label" for="ans-1">この作品を承認する</label>
</div>
<div class="form-check">
<input id="ans-2" class="form-check-input" type="radio" name="ans" value="2" onChange="check_decide_individual(&quot;ans&quot;);">
<label class="form-check-label" for="ans-2">この作品の軽微な修正を提出者に求める</label>
</div>
<div class="form-check">
<input id="ans-3" class="form-check-input" type="radio" name="ans" value="3" onChange="check_decide_individual(&quot;ans&quot;);">
<label class="form-check-label" for="ans-3">この作品を拒否する</label>
</div>
<div id="ans-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
<label for="reason">「軽微な修正を求める」もしくは「拒否する」と答えた場合は、その理由を入力して下さい。（500文字以内）</label>
<textarea id="reason" name="reason" rows="5" class="form-control" onkeyup="ShowLength(value, &quot;reason-counter&quot;);" onChange="check_decide_individual(&quot;reason&quot;);"></textarea>
<div id="reason-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>
<div id="reason-errortext" class="system-form-error"></div>
<small class="form-text"><?php
if ($examsetting["reason"] == "notice") echo "※<strong>ここで記入した理由は、ファイル提出者本人宛に送信するメールに記載されます。</strong>";
else echo "※ここで記入した理由は、ファイル提出者本人宛に送信するメールに直接的に記載されません。";
?></small>
</div>
<br>
<button type="submit" class="btn btn-warning">回答を送信し、議論を終了する</button>
</div>
<?php
echo_modal_confirm("<p>入力内容に問題は見つかりませんでした。</p><p>現在の回答内容を登録し、議論を終了します。よろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p>", null, null, null, null, null, null, "submitbtn_decide", 'document.getElementById("submitbtn_decide").disabled = "disabled"; document.form_decide.submit();');
?>
</form>
<script type="text/javascript">

function check_decide_individual(id){
    var valid = 1;

    if (id === "ans") {
        document.getElementById("ans-errortext").innerHTML = "";
        var f = document.getElementsByName("ans");
        if(document.form_decide.ans.value === ""){
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
        if(document.form_decide.reason.value === ""){
            if(document.form_decide.ans.value === "2" || document.form_decide.ans.value === "3"){
                valid = 0;
                document.getElementById("reason-errortext").innerHTML = "「軽微な修正を求める」もしくは「拒否する」と答えた場合は、入力が必要です。";
            }
        } else if(document.form_decide.reason.value.length > 500){
            valid = 0;
            document.getElementById("reason-errortext").innerHTML = "文字数が多すぎます。500文字以内に抑えて下さい。";
        }
        if (valid) {
            document.form_decide.reason.classList.add("is-valid");
            document.form_decide.reason.classList.remove("is-invalid");
        } else {
            document.form_decide.reason.classList.add("is-invalid");
            document.form_decide.reason.classList.remove("is-valid");
        }
        return;
    }
}


function check_decide(){

    var problem = 0;
    var valid = 1;

    document.getElementById("ans-errortext").innerHTML = "";
    var f = document.getElementsByName("ans");
    if(document.form_decide.ans.value === ""){
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
    if(document.form_decide.reason.value === ""){
        if(document.form_decide.ans.value === "2" || document.form_decide.ans.value === "3"){
            problem = 1;
            valid = 0;
            document.getElementById("reason-errortext").innerHTML = "「軽微な修正を求める」もしくは「拒否する」と答えた場合は、入力が必要です。";
        }
    } else if(document.form_decide.reason.value.length > 500){
        problem = 1;
        valid = 0;
        document.getElementById("reason-errortext").innerHTML = "文字数が多すぎます。500文字以内に抑えて下さい。";
    }
    if (valid) {
        document.form_decide.reason.classList.add("is-valid");
        document.form_decide.reason.classList.remove("is-invalid");
    } else {
        document.form_decide.reason.classList.add("is-invalid");
        document.form_decide.reason.classList.remove("is-valid");
    }



    if ( problem == 0 ) {
        $('#confirmmodal').modal();
        $('#confirmmodal').on('shown.bs.modal', function () {
            document.getElementById("submitbtn_decide").focus();
        });
    }
    return false;

}

</script>

<?php
require_once(PAGEROOT . 'mypage_footer.php');
