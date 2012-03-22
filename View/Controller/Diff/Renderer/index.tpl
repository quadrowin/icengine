<div id="edits">
	<ul class="fields">
	{foreach from=$fields item=field}
		<li>
			<h3 class="field-title-{$field->name}">{$field->type->config()->title}</h3>
			{$field->renderer->render($field,$parent)}
		</li>
	{/foreach}
	</ul>
</div>