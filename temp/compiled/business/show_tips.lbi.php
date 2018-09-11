

<?php if ($this->_var['info']['suppliers_id'] && $this->_var['info']['user_id'] == 0): ?>
<div class="show_tips">
	<div class="tips_box">
		<div id="qrcode"></div>
		<p>请扫描二维码绑定店主</p>
	</div>
</div>
<?php endif; ?>
<script type="text/javascript">
var suppliers_id = <?php echo $this->_var['info']['suppliers_id']; ?>;
var uri = <?php if ($this->_var['info']['user_id']): ?>'unbind'<?php else: ?>'bind'<?php endif; ?>;
var qrcode = new QRCode(document.getElementById("qrcode"), {
    text: location.origin + "/"+uri+".php?suppliers_id="+suppliers_id,
    width: 128,
    height: 128,
    colorDark : "#000000",
    colorLight : "#ffffff",
    correctLevel : QRCode.CorrectLevel.H
});
</script>
<script>
	$(function(){
		$('.tips_box').click(function(){
			$(this).hide();
			$('.show_tips').hide();
		});
	});
</script>