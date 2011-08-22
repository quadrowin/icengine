<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<div class="title">
		<h2>Таблицы</h2>
	</div>
	
	{if $tables}
	<form method="post" action="/cp/acl/role/save/">
		<input type="hidden" name="role_id" value="{$role_id}" />
		{foreach from=$tables item="i"}
		<br /><h2><input type="checkbox" onclick="Controller_Admin_Database.multiCheck ($(this), 'Table/{$i.table->Name}/');" />{$i.table->Name}{if $i.table->Comment} ({$i.table->Comment}){/if}</h2><br />
			{foreach from=$i.fields item="j"}
			<li><input type="checkbox" name="resources[]" value="{$j.resource}"{if $j.on} checked{/if} /> 
				{$j.field->Field}{if $j.field->Comment} ({$j.field->Comment}){/if}
			</li>
			{/foreach}
		</ul>
		{/foreach}
		<br />
		<input type="submit" value="Применить" />
	</form>
	{/if}
	
</div>