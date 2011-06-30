<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<p><a href="/cp/db/">База данных</a></p>
	
	<div class="title">
		<h2>Записи</h2>
	</div>
	
	<p><a href="/cp/row/{$table}/0/">Добавить</a></p>
	
	{if $collection}
		
	{if !$fields}
	<ul>
		{foreach from=$collection item="i"}
		<li style="
		{if $styles}
		{foreach from=$styles item="column" key="c"}
		{foreach from=$column item="style" key="v"}
		{if $i->$c==$v}{$style};{/if}	
		{/foreach}
		{/foreach}
		{/if}	
		"><b style="display:inline-block;width:20px"><a href="/cp/row/{$table}/{$i->key()}/">{$i->key()}</a></b>. <a href="/cp/row/{$table}/{$i->key()}/">{$i->title()}</a></li>
		{/foreach}
	</ul>
	{else}
	<table cellpadding="5" cellspacing="0" border="1" width="100%">
		<thead>
			<tr> 
				{foreach from=$fields item="field"}
				<th>{if $field->Comment}{$field->Comment}{else}{$field->Field}{/if}</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach from=$collection item="row"}
			<tr>
				{foreach from=$fields item="field"}
					{assign var="field" value=$field->Field}
					<td style="
						{if $styles}
							{foreach from=$styles item="column" key="c"}
								{foreach from=$column item="style" key="v"}
									{if $row->$c==$v}{$style};{/if}	
								{/foreach}
							{/foreach}
						{/if}	
					">
					<b><a href="/cp/row/{$table}/{$row->key()}/">{$row->key()}</a></b>. 
					{if $field == $title || in_array($field,$links)}
					<a href="/cp/row/{$table}/{$row->key()}/">{if $field==$title}{$row->title()}{else}{$row->$field}{/if}</a>
					{else}{$row->$field}{/if}</td>
				{/foreach}
			</tr>
			{/foreach}
		</tbody>
	</table>
	{/if}
	{/if}
	
	{$paginator_html}
	
</div>