{assign var="deep" value=0}
{assign var="last_level" value=0}
{assign var="parents" value=''|explode:','}
{foreach from=$categories item="category"}
	{if $category->data('level') > $last_level}
		{assign var="deep" value=$deep+1}
		{assign var="last_level" value=$category->data('level')}
		{assign var="temp" value=$parent|array_push:$category->parentKey()}
	{elseif $category->data('level') < $last_level}
		{assign var="deep" value=$deep-1}
		{assign var="last_level" value=$category->data('level')}
		{assign var="temp" vale=$parent|array_pop}
	{/if}
	<div id="category_{$category->key()}" style="padding-left: {$category->data('level')*20}px; {if $last_level > $expand_level}display:none;{/if}">
		<p>
			<a href="javascript:void(0);" onclick="Controller_Admin_Content_Category.toggleSubcontents({$category->key()});">content ({$category->contentCount()})</a>&nbsp;
			<a href="/cp/table/{$category->table()}/{$category->key()}/">edit</a>&nbsp;
			{$category->title}
		</p>
		<div id="category_{$category->key()}_contents" class="subcontents" style="display:none;"></div>
	</div>
{/foreach}