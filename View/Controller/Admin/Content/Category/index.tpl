{literal}
<style type="text/css">
	.child
	{
		margin-left:20px;
	}
</style>
{/literal}

{if $categories->count()}
	<ul id="tree">
	{foreach from=$categories item="category"}
		<li class="level-0">
			<span onclick="Controller_Admin_Content_Category.toggleSubcategories(this,{$category->key()},0)" id="category-{$category->key()}">{$category->title}</span>
		</li>
	{/foreach}
	</ul>
{/if}