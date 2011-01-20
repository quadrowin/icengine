var Helper_Form = {
	
	asArray: function ($form)
	{
		var data = {};
		$form
			.find ('input,select,textarea')
			.filter (':not(.nosubmit)')
			.each (function () {
				data [this.name] = this.value;
			});
		return data;
	},
	
	defaultCallback: function ($form, result)
	{
		if (result.html)
		{
			if (result.data && result.data.error)
			{
				var $div = $('.result-msg', $form.parent ());
				$div.html (result.html);
				$div.removeClass ('hidden');
			}
			else
			{
				$form.parent ().html (result.html);
			}
		}
		
		if (result && result.data)
		{
			if (result.data.removeForm)
			{
				$form.parent ().remove ();
			}
			if (result.data.redirect)
			{
				setTimeout (
					function () {
						window.location.href = result.data.redirect;
					},
					1000
				);
			}
		}
	},
	
	initFileUpload: function ($owner)
	{
		var $input = $('input[type="file"]', $owner);
		var is_new = false;
		
		if ($input.length == 0)
		{
			$input = $('<input type="file" />');
			is_new = true;			
		}
		
		$input.css({
			position: "absolute",
			opacity: 0.0,
			cursor: "pointer",
			display: "inherit",
			margin: 0,
			padding: 0,
			width: "220px"
		});
		
		if (is_new)
		{
			$owner.append ($input);
		}
		
		$owner.data ('input_file_upload', $input);
		$owner.css ({
			overflow: "hidden",
			position: "relative"
		});
		
		$owner.bind ('mousemove', function (event) {
			var $input = $(event.currentTarget).data ('input_file_upload');
			
			var y = event.pageY - $owner.offset ().top - 5 + 'px';
			var x = event.pageX - $owner.offset ().left - 170 + 'px';
			
			$input.css ({left: x, top: y});
		});
		
		return $input;
	}
		
};