<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<p><a href="/cp/db/">База данных</a> / <a href="/cp/table/{$table}/">{$table}</a></p>
	
	<div class="title">
		<h2>Запись</h2>
	</div>
	
	{if $fields}
	<form method="post" action="/cp/row/{$table}/save/">
	<input type="hidden" name="row_id" value="{if $row}{$row->key()}{else}0{/if}" />
	<table>
		{foreach from=$fields item="i" name="i"}
		<tr style="background-color:{if $smarty.foreach.i.iteration mod 2 eq 1}#eee{else}#fff{/if}">
		{assign var="column" value=$i->Field}
			<td style="padding:6px"><b>{if $i->Comment}{$i->Comment}{else}{$i->Field}{/if}:</b> </td> 
			<td style="padding:6px">
				{if isset($i->Values)}
				<select name="column[{$i->Field}]">
					{foreach from=$i->Values item="j"}
					<option value="{$j->key()}"{if $row->$column==$j->key()} selected="selected"{/if}>
						{$j->name}
					</option>
					{/foreach} 
				</select>
				{elseif $i->Type=='tinyint(1)'}
				<input type="checkbox" value="1" name="column[{$i->Field}]"{if !empty($row->$column)} checked="checked"{/if}" />
				{elseif strpos($i->Type,'text')!==false}
				<textarea rows="8" cols="50" name="column[{$i->Field}]">{if isset($row->$column)}{$row->$column|htmlspecialchars}{/if}</textarea>
				{else}
				{if $i->Field==$keyField && $row->$column}
				<b>{$row->$column}</b>
				{else}
				<input size="44" type="text" name="column[{$i->Field}]" value="{if isset($row->$column)}{$row->$column|htmlspecialchars}{/if}" />
				{/if}
				{/if}
			</td>
		</tr>
		{/foreach}
	</table>
	<p><input type="submit" value="Применить" />
	</form>
	{/if}
	
</div>