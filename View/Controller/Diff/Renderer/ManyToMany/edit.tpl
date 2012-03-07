<ul>
	{foreach from=$renderer->getList() item=listItem}
		<li>
			<input type="checkbox" name="{$parent}{$field->name}-new-value[]" value="{$listItem->id}"{if in_array($listItem->id,$value->asArray())} checked{/if}/>
			<label for="{$field->name}_list_choice_{$listItem->id}">{$listItem->name}</label>
		</li>
	{/foreach}
</ul>