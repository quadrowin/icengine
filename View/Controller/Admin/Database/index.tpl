<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<div class="title">
		<h2>Таблицы</h2>
	</div>
	
	{if $tables}
	<ul>
		{foreach from=$tables item="i"}
		<li><a href="/cp/table/{$i->Name}/">{$i->Comment}</a></li>
		{/foreach}
	</ul>
	{/if}
	
</div>