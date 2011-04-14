{if $hide_erros}
	{literal}
		<script type="text/javascript">
    		function ymapifault (err) {if (console && console.log) console.log ("yandex fail: ", err);};
		</script>
	{/literal}
{/if}
<script src="http://api-maps.yandex.ru/1.1/index.xml?{if $load_by_require}loadByRequire=1&{/if}{if $hide_erros}onerror=ymapifault&{/if}{if $wizard}wizard={$wizard}&{/if}key={$key}" type="text/javascript"></script>