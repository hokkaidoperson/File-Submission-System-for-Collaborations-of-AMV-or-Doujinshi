<?php
//※必ず、guest_header.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

?>

</div>
</div>
<script>if (navigator.cookieEnabled) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/jquery-3.4.1.js"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/bs/bootstrap.bundle.min.js?<?php echo urlencode(VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>plugins/vld/validator.js?<?php echo urlencode(VERSION); ?>"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>js/validation.js?<?php echo urlencode(VERSION); ?>"></script>
</body>
</html>
