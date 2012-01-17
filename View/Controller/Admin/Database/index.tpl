<h2 style="font-size:26px; margin-bottom:10px">Таблицы</h2>

{if $tables}
<ul style="line-height:20px">
	{foreach from=$tables item="i"}
	<li style="list-style-type:square; margin-left:20px"><a href="/cp/table/{$i->Name}/">{if $i->Comment} <span style="font-size:16px">{$i->Comment}</span> <span style="font-size:12px">({$i->Name})</span>{else}{$i->Name}{/if}</a></li>
	{/foreach}
</ul>
{/if}
