<div id="tab-banner">
	<div id="banner-edit-dialog" title="Настройка баннера"></div>
	<div id="banner-ajax-buffer"></div>
	
	<div id = "table-banners"></div>
	
	{literal}
	<script type="text/javascript">
		var admAjaxPath = '/admin/ajax';
		var bannerInterfaceLoaded = false;
	</script>
	
	<script src="/admin/js/bannerworks.js"></script>
	
	<script type="text/javascript">
	$(function ()
	{
		LoadBannerInterface (
			'{/literal}{$table}{literal}', 
			'{/literal}{$row->key()}{literal}', 
			'table-banners'
		);
	});
	</script>
	{/literal}
	
</div>