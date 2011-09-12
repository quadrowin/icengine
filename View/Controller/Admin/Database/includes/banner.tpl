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
		Controller_Admin_Banner.loadInterface (
			'{/literal}{$table}{literal}',
			'{/literal}{if $row}{$row->key()}{else}0{/if}{literal}'
		);
		/*LoadBannerInterface (
			'{/literal}{$table}{literal}',
			'{/literal}{if $row}{$row->key()}{else}0{/if}{literal}',
			'table-banners'
		);*/
	});
	</script>
	{/literal}

</div>