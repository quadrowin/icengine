<div id="tab-attribute">
	<div id="attrs"></div>
</div>
	
{literal}
<script type="text/javascript">
	$(function () {
		
		function callback (result)
		{
			$('#attrs').html (result.html);
		};		
		
		Controller.call (
			'Admin_Attribute/index',
			{ 
				table : '{/literal}{$row->modelName()}{literal}',
				rowId : '{/literal}{$row->key()}{literal}'
			},
			callback
		);
	});
</script>
{/literal}	