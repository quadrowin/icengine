<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<p><a href="/cp/db/">База данных</a></p>
	
	<div class="title">
		<h2>Записи</h2>
	</div>
	
	{if $collection}
	<ul>
		{foreach from=$collection item="i"}
		<li><a href="/cp/row/{$table}/{$i->id}/">{$i->name}</a></li>
		{/foreach}
	</ul>
	{/if}
	
</div>