<?php
//※必ず、help_header.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

?>
</div>
</div>
<nav class="navbar navbar-light justify-content-center system-nav-help">
<span class="navbar-text system-footer">
<?php echo $eventname; ?> Powered by <a href='https://www.hkdyukkuri.space/filesystem/' target="_blank" rel="noopener">MAD合作・合同誌向けファイル提出システム</a> (Ver. <?php echo VERSION; ?>) and supported by <a href="https://getbootstrap.jp/" target="_blank" rel="noopener">Bootstrap4<br></a>
</span>
</nav>
</div>
<script>if (navigator.cookieEnabled) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/jquery-3.4.1.js"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/bs/bootstrap.bundle.min.js?<?php echo urlencode(VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/vld/validator.js?<?php echo urlencode(VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>js/validation.js?<?php echo urlencode(VERSION); ?>"></script>
</body>
</html>
