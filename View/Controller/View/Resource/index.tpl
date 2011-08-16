{foreach from=$reses key=name item=res}
	{if $res.url}
		{assign var="cdir" value=$smarty.template|dirname}
		{include file="$cdir/index/`$res.type`.tpl"}
	{/if}
{/foreach}