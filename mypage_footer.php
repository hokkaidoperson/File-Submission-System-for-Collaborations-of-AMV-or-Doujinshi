<?php
//※必ず、mypage_header.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

switch ($_SESSION["state"]) {
    case "p":
        $feedback_attr = "主催者";
        break;
    case "c":
        $feedback_attr = "共同運営者";
        break;
    case "g":
        $feedback_attr = "一般参加者";
        break;
    default:
        $feedback_attr = "非参加者";
        break;
}

?>
</div>
</div>
<nav class="navbar navbar-light system-nav-mypage justify-content-center">
<span class="navbar-text system-footer">
<?php echo $eventname; ?> Powered by <a href='https://www.hkdyukkuri.space/filesystem/' target="_blank" rel="noopener">MAD合作・合同誌向けファイル提出システム</a> (Ver. <?php echo VERSION; ?>) and supported by <a href="https://getbootstrap.jp/" target="_blank" rel="noopener">Bootstrap4<br></a>
</span>
</nav>
<a href="<?php echo feedback_url($feedback_attr); ?>" target="_blank" rel="noopener" role="button" class="btn btn-outline-info px-2 px-md-3 py-md-2 rounded-pill d-inline-flex align-items-center system-btn-feedback"><i class="bi bi-chat-left-dots system-btn-feedback-icon"></i><span class="pl-1"> フィードバック</span></a>
</div>
<div class="modal fade" id="form_confirmation_modal" tabindex="-1" role="dialog" aria-labelledby="form_confirmation_modal_title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="form_confirmation_modal_title">送信確認</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body" id="form_confirmation_modal_body">
<p>入力内容に問題は見つかりませんでした。</p>
<p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>
入力内容の修正を行う場合は「戻る」を押して下さい。</p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="bi bi-x"></i> 戻る</button>
<button type="button" class="btn btn-primary" id="form_confirmation_modal_btn" data-sys-formid="system_form" onClick='form_confirmation_modal_function();'><i class="bi bi-check-circle-fill"></i> 送信する</button>
</div>
</div>
</div>
</div>
<div class="modal fade" id="link_confirmation_modal" tabindex="-1" role="dialog" aria-labelledby="link_confirmation_modal_title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="link_confirmation_modal_title">取消確認</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body" id="link_confirmation_modal_body">
<p>現在の設定内容を保存せず、メニューに戻ります。よろしければ「取り消す」を押して下さい。<br>
入力を続ける場合は「戻る」を押して下さい。</p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="bi bi-x"></i> 戻る</button>
<a href="#" class="btn btn-warning" id="link_confirmation_modal_btn"><i class="bi bi-trash"></i> 取り消す</a>
</div>
</div>
</div>
</div>
<script>if (navigator.cookieEnabled) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/jquery-3.4.1.js"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/bs/bootstrap.bundle.min.js?<?php echo urlencode(VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/vld/validator.js?<?php echo urlencode(VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>js/validation.js?<?php echo urlencode(VERSION); ?>"></script>
</body>
</html>
