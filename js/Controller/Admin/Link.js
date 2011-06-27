var cont;

var Controller_Admin_Link = {
	appendItems: function (result)
	{
		var items = result.data.items;
		
		cont.parent ().show ();	
		
		cont [0].options.length = 0;
		
		for (var i = 0, l = items.length; i < l; i++)
		{
			cont [0].options [i] = new Option (
				items [i].id + '. ' + items [i].name,
				items [i].id,
				false,
				false
			);	
		}
		
		$('#table2_id').parent ().parent ().show ();
	},
	appendLinkedItems: function (result)
	{
		var items = result.data.items;
		
		cont.parent ().show ();
		
		cont.empty ();
		
		for (var i = 0, l = items.length; i < l; i++)
		{
			var p = $('<p></p>').appendTo (cont);
			
			var check = $('<input type="checkbox" class="model-id" ' + (items [i].linked > 0 ? ' checked' : '') + ' />').appendTo (p);
			
			check.val (items [i].id);
			
			check.attr ('id', 'ch' + items [i].id)
			
			p.html (p.html () + ' ' + items [i].id + '. ' + items [i].name);
		}
		
	},
	items: function (table)
	{
		Controller.call (
			'Admin_Link/items',
			{
				table: table
			},
			Controller_Admin_Link.appendItems,
			true
		);
	},
	linkRoll: function (table)
	{
		Controller.call (
			'Admin_Link/linkRoll',
			{
				table1: $('#table1_id').val (),
				table2: table,
				row1:   $('#model1').val ()
			},
			Controller_Admin_Link.appendLinkedItems,
			true
		);
	},
	save: function ()
	{
		var ids = [];
		
		$('input[type=checkbox].model-id').each (function ()
		{
			if ($(this).attr ('checked'))
			{
				ids.push ($(this).val ());
			}
		});
		
		Controller.call (
			'Admin_Link/save',
			{
				table1: $('#table1_id').val (),
				table2: $('#table2_id').val (),
				row1: $('#model1').val (),
				models2: ids
			},
			function (result)
			{
				window.location.reload (true);
			},
			true
		);
	}
};

$(document).ready (function ()
{
	$('#table1_id').change (function ()
	{
		cont = $('#model1');
		
		Controller_Admin_Link.items ($(this).val ()); 
	});	
	
	$('#table2_id').change (function ()
	{
		cont = $('#model2');
		
		Controller_Admin_Link.linkRoll ($(this).val ()); 
	});	
	
	$('#model-save').click (function ()
	{
		Controller_Admin_Link.save ();
	})
})