
<footer>

	<div class="inner-wrapper">

		This is the footer area

	</div>

</footer>

<script>
		
	var	CMS = 	{
					"SERVER_URL" 	: "<?php echo CMS_SERVER_URL; ?>",
					"PAGE_PATH" 	: "<?php echo $pageRequest -> urlPath; ?>",
					"MODULE_TARGET"	: (typeof MODULE != "undefined" ? MODULE.TARGET : '')
				};	
		
</script>