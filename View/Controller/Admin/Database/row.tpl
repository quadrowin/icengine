<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<p><a href="/cp/db/">База данных</a> / <a href="/cp/table/{$table}/">{$table}</a></p>
	
	<div class="title">
		<h2>Запись</h2>
	</div>
	
	{if $fields}
	<table>
		{foreach from=$fields item="i"}
		<tr>
		{assign var="column" value=$i->Field}
			<td><b>{if $i->Comment}{$i->Comment}{else}{$i->Field}{/if}:</b> </td> 
			<td>
				{if isset($i->Values)}
				<select name="column[{$i->Column}]">
					{foreach from=$i->Values item="j"}
					<option value="{$j->key()}"{if $row->$column==$j->key()} selected="selected"{/if}>
						{$j->name}
					</option>
					{/foreach}
				</select>
				{elseif $i->Type=='tinyint(1)'}
				<input type="checkbox" name="column[{$i->Column}]"{if !empty($row->$column)} checked="checked"{/if}" />
				{else}
				<input type="text" name="column[{$i->Column}]" value="{if isset($row->$column)}{$row->$column}{/if}" />
				{/if}
			</td>
		</tr>
		{/foreach}
	</table>
	{/if}
	
</div>