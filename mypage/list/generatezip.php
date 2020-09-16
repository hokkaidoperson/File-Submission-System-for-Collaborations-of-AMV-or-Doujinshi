<?php
require_once('../../set.php');
setup_session();
$titlepart = '一括ダウンロード';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

if ($_SESSION["state"] == 'p') echo '<h1>提出情報の一括ダウンロード</h1>
<p>提出された作品や、参加者の情報を一括ダウンロード出来ます。</p>
<p>以下の「ZIPファイルを生成」ボタンを押した段階でサーバーに保存されているファイルを、ZIPファイルの中にまとめます。</p>
<p>ZIPファイルの最上層には、提出作品の情報と参加者の情報が、それぞれCSVファイルで保存されます。<br>
また、参加者ごとにフォルダが作成され、提出されたファイル類を格納します。</p>
<p><b>外部アップローダのURLで提出されたファイルについては、ダウンロードURL等がCSVファイル内に記載されますが、自動的にダウンロードされません。<br>
該当するファイルにつきましては、CSVファイルを確認し手動でダウンロード願います。</b></p>
';
if ($_SESSION["state"] == 'c') echo '<h1>提出情報の一括ダウンロード</h1>
<p>提出された作品や、参加者の情報を一括ダウンロード出来ます。<br>
自分の情報、及び主催者から閲覧権限を与えられたデータについてダウンロードします。</p>
<p>以下の「ZIPファイルを生成」ボタンを押した段階でサーバーに保存されているファイルを、ZIPファイルの中にまとめます。</p>
<p>ZIPファイルの最上層には、提出作品の情報と参加者の情報が、それぞれCSVファイルで保存されます。<br>
また、参加者ごとにフォルダが作成され、提出されたファイル類を格納します。</p>
<p><b>外部アップローダのURLで提出されたファイルについては、ダウンロードURL等がCSVファイル内に記載されますが、自動的にダウンロードされません。<br>
該当するファイルにつきましては、CSVファイルを確認し手動でダウンロード願います。</b></p>
';
if ($_SESSION["state"] == 'g') echo '<h1>提出情報の一括ダウンロード</h1>
<p>提出された作品や、参加者の情報を一括ダウンロード出来ます。<br>
一般参加者は、自分のデータについてのみダウンロード出来ます。</p>
<p>以下の「ZIPファイルを生成」ボタンを押した段階でサーバーに保存されているファイルを、ZIPファイルの中にまとめます。</p>
<p>ZIPファイルの最上層には、提出作品の情報と参加者（あなた）の情報が、それぞれCSVファイルで保存されます。<br>
また、あなたの名前のフォルダが作成され、提出されたファイル類を格納します。</p>
<p><b>外部アップローダのURLで提出されたファイルについては、ダウンロードURL等がCSVファイル内に記載されますが、自動的にダウンロードされません。<br>
該当するファイルにつきましては、CSVファイルを確認し手動でダウンロード願います。</b></p>
';

?>
<p>※扱うファイルの量によっては、<b>ZIPファイルの生成が完了するまで数十秒掛かる可能性があります</b>。</p>
<h2>生成されるZIPファイルの構造</h2>
<p>※実際のファイル名やフォルダ名ではユーザーIDや作品の内部IDが括弧書きで付与されます（ファイル・フォルダ名の重複防止のため）。<br>
※ファイル・フォルダ名に使用出来ない文字（半角の <code>\</code> <code>/</code> <code>:</code> <code>*</code> <code>?</code> <code>&quot;</code> <code>&lt;</code> <code>&gt;</code> <code>|</code>）がユーザー名や作品名などに含まれる場合、これらを全角文字に変換してファイル・フォルダ名として使用します（CSVファイル上では全角に変換されません）。<br>
※システムの都合上、空のフォルダが出来る可能性があります。予めご了承下さい。</p>
<pre><code><?php echo $eventname; ?> - 一括ダウンロード.zip
├── ユーザーA/
│   ├── 作品A-1/
│   │   ├── 作品A-1の提出ファイル（メイン）
│   │   ├── 作品A-1の添付ファイルa
│   │   ├── 作品A-1の添付ファイルb
│   │   └── ………
│   ├── 作品A-2/
│   │   ├── 作品A-2の提出ファイル（メイン）
│   │   ├── 作品A-2の添付ファイルa
│   │   ├── 作品A-2の添付ファイルb
│   │   └── ………
│   ├── ユーザーAの添付ファイルa
│   ├── ユーザーAの添付ファイルb
│   └── ………
│
├── ユーザーB/
│   ├── 作品B-1/
│   │   ├── 作品B-1の提出ファイル（メイン）
│   │   ├── 作品B-1の添付ファイルa
│   │   ├── 作品B-1の添付ファイルb
│   │   └── ………
│   ├── 作品B-2/
│   │   ├── 作品B-2の提出ファイル（メイン）
│   │   ├── 作品B-2の添付ファイルa
│   │   ├── 作品B-2の添付ファイルb
│   │   └── ………
│   ├── ユーザーBの添付ファイルa
│   ├── ユーザーBの添付ファイルb
│   └── ………
│
└── 提出作品データ.csv
└── 参加者データ.csv
</code></pre>

<form name="form" action="generatezip_exec.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
オプション（任意）
<div class="form-check">
<input id="options-1" class="form-check-input" type="checkbox" name="include_non_accepted" value="1">
<label class="form-check-label" for="options-1">承認されていない（承認待ち・修正待ち・拒否）作品も併せてダウンロードする</label>
</div>
<div class="form-check">
<input id="options-2" class="form-check-input" type="checkbox" name="include_without_submission" value="1">
<label class="form-check-label" for="options-2">作品が1つも承認されていないユーザー（上記にチェックした場合は作品を1つも提出していないユーザー）の情報も併せてダウンロードする</label>
</div>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">ZIPファイルを生成</button>
</div>
<?php
echo_modal_wait("ZIPファイルを生成中です。<br>画面が自動的に推移するまでしばらくお待ち下さい。", "ZIPファイル生成中…");
?>
</form>
<script type="text/javascript">
<!--
function check(){

  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";
  $('#sendingmodal').modal({
    keyboard: false,
    backdrop: "static"
  });
  return true;

}

// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
