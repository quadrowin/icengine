<ul>
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-skip" checked value="skip"/>
		<label for="{$field->name}-skip">Отложить решение</label>
	</li>
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-leave-as-now"  value="leave-as-now"/>
		<label for="{$field->name}-leave-as-now">Оставить как есть:</label>
		<span class="grey">{include file=$renderer->valueTemplate() value=$field->value}</span>
	</li>
	{foreach from=$field->edits item=edit key=edit_id}
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-edit-{$edit_id}" value="change-{$edit_id}" />
		<label for="{$field->name}-edit-{$edit_id}">Правка <a href="#">пользователя #{$edit->edit->User__id}</a>:
			{include file=$renderer->valueTemplate() value=$edit->value}
			
		</label>
	</li>
	{/foreach}
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-set" value="set-own" />
		<label for="{$field->name}-set">
			Указать свой вариант:
			{include file=$renderer->editTemplate() value=$field->value}
		</label>
	</li>	
</ul>
