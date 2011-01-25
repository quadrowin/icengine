var Helper_Form = {
	
	_toggleTextForPassword: function ($password)
	{
		if ($password.data ('tps_input'))
		{
			return $password.data ('tps_input');
		}
		
		var elPosition = $password.position ();
		var $input = $('<input/>', {
	    	'class': "hidden",
	    	css: {
	    		display: 'none',
	    		left: elPosition.left,
	    		top: elPosition.top
	    	},
	    	type: 'text'
	    });
		
		$input.data ('tps_password', $password);
		$password.data ('tps_input', $input);
		$input.appendTo ($password.parent ());
		
		return $input;
	},
	
	ajaxPost: function ($form, url, callback)
	{
		var data = Helper_Form.asArray ($form);
		
		JsHttpRequest.query (url, data, callback, true);
	},
	
	asArray: function ($form)
	{
		var data = {};
		
		$form
			.find ('input,select,textarea')
			.filter (':not(.nosubmit)')
			.each (function () {
				var a;
				if (this.tagName.toLowerCase() == 'input')
				{
					if (this.type == "file")
					{
						data [this.name] = this;
					}
					else if (this.type == "checkbox" && this.checked)
					{
						data [this.name] = 'on';
					}
					else
					{
						data [this.name] = this.value;
					}
				}
				else
				{
					data [this.name] = this.value;
				}
			});
		
		return data;
	},
	
	defaultCallback: function ($form, result)
	{
		if (result && result.html)
		{
			if (result.data && result.data.removeForm)
			{
				$form.parent ().html (result.html);
			}
			else
			{
				var $div = $('.result-msg', $form.parent ());
				$div.html (result.html);
				$div.removeClass ('hidden');
			};
		};
		
		if (result && result.data)
		{
			if (result.data.removeForm && !result.html)
			{
				$form.parent ().remove ();
			};
			if (result.data.redirect)
			{
				setTimeout (
					function () {
						window.location.href = result.data.redirect;
					},
					1000
				);
			};
		};
	},
	
	initFileUpload: function ($owner)
	{
		var $input = $('input[type="file"]', $owner);
		var is_new = false;
		
		if ($input.length == 0)
		{
			$input = $('<input type="file" />');
			is_new = true;			
		};
		
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
		};
		
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
	},
	
	initTogglePasswordStars: function ($from)
	{
		if (!$from)
		{
			$form = $('body');
		}
		
		$checkboxes = $('input[type="checkbox"].tps_checkbox', $from); 
		
		$checkboxes.each (function () {
			$(this).bind (
				'click',
				function ()
				{
					var $this = $(this);
					
					if ($this.data ('tps_text'))
					{
						Helper_Form.togglePasswordStars ($this, $this.data ('tps_text'));
						return;
					}
					
					var $parent = $this.parent ();
					while ($parent.length)
					{
						var $tps_password = $parent.find ('.tps_password');
						
						if ($tps_password.length)
						{
							var $tps_input = Helper_Form._toggleTextForPassword ($tps_password);
							$this.data ('tps_password', $tps_password);
							$this.data ('tps_input', $tps_input);
							Helper_Form.togglePasswordStars ($this);
							return;
						}
						$parent = $parent.parent ();
					}
				}
			);
		});
	},
	
	/**
	 * Генерация уникальных ID для элементов
	 * Необходимо для чекбоксов в IE: если 2 чекбокса имеют одинаковый id,
	 * значение checked возможно установить только для первого.
	 * @param $collection
	 */
	initUniqueIds: function ($collection)
	{
		var m = 0;
		$collection.each (function () {
			m++;
			var id = "u" + new Date ().getTime () + "m" + m;
			$(this).attr ('id', id);
		});
	},
	
	/**
	 * Показать/скрыть пароль за звездочками
	 * 
	 * Если не передан второй атрибут, то его id должен быть равен 
	 * "tps_%id_чекбокса%".
	 * 
	 * Если первый атрибут null, второй атрибут обязателен. Состояние 
	 * поля будет изменено на противоположное.
	 */
	togglePasswordStars: function ($checkbox, $input)
	{
		var checked;
		var $tps_input;
		var $tps_password;
		
		if ($checkbox)
		{
			$tps_input = $checkbox.data ('tps_input');
			$tps_password = $checkbox.data ('tps_password');
			checked = $checkbox.attr ('checked');
		}
		else
		{
			checked = $input.attr ('type') == 'password';
			if (checked)
			{
				$tps_password = $input;
				$tps_input = Helper_Form._toggleTextForPassword ($tps_password);
			}
			else
			{
				$tps_input = $input;
				$tps_password = $input.data ('tps_password');
			}
		};
		
		if (checked)
		{
			$tps_password.val ($tps_input.val ()).show ();
			$tps_input.hide ();
		}
		else
		{
			$tps_input.val ($tps_password.val ()).show ();
			$tps_password.hide ();
		}
	}
		
};