<?php
//※必ず、mypage_header.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

?>

</div>
<nav class="navbar navbar-light bg-light justify-content-center">
<span class="navbar-text">
<font size="1">
<?php echo $eventname; ?> Powered by <a href='https://www.hkdyukkuri.space/filesystem/' target="_blank">MAD合作・合同誌向けファイル提出システム</a> (Ver. <?php echo VERSION; ?>) and supported by <a href="https://getbootstrap.jp/" target="_blank">Bootstrap4<br></a>
</font>
</span>
</nav>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="<?php echo $siteurl; ?>js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>js/bootstrap.bundle.js"></script>
</body>
</html>
