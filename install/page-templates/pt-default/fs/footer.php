
<footer>


	<!-- place here the footer stuff -->

</footer>

<script>
		
	var	CMS = 	{	// Information for JS if required
					"SERVER_URL" 	: "<?php echo CMS_SERVER_URL; ?>",
					"PAGE_PATH" 	: "<?php echo $pageRequest -> urlPath; ?>",
					"MODULE_TARGET"	: (typeof MODULE != "undefined" ? MODULE.TARGET : '')
				};	
		
</script>