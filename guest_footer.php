<?php
//※必ず、guest_header.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

?>

</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="<?php echo $siteurl; ?>js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="<?php echo $siteurl; ?>js/bootstrap.bundle.min.js?<?php echo urlencode(VERSION); ?>"></script>
</body>
</html>
