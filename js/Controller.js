/**
 * Базовый класс контроллера
 */
var Controller = {
		
	callbacks: {},
	
	callbacksData: {},
	
	lastback: 0,
	/**
	 * Вызов метода контроллера на сервере
	 * @param controller
	 * @param params
	 * @param callback
	 * @param nocache
	 */
	call: function (controller, params, callback, nocache)
	{
		var back = Controller.lastback++;
		Controller.callbacks [back] = callback;
		Controller.callbacksData [back] = params ['back'] ? params ['back'] : null;
		JsHttpRequest.query (
			'/Controller/ajax/',
			{
				'call': controller,
				'params': params,
				'back': back
			},
			this.callback, 
			nocache ? true : false
		);
	},
	/**
	 * Ответ сервера
	 * @param js
	 * @param text
	 */
	callback: function (js, text)
	{
		var back = js.back;
		
		if (!js.result || js.error)
		{
			if (console && console.log)
			{
				console.log ('error_js:', js);
			}
			Helper_Form.stopLoading ();
			return;
		}
		
		var callback = Controller.callbacks [back];
		js.result.back = 
			Controller.callbacksData [back] ? 
			Controller.callbacksData [back] : null;
		
		callback (js.result, text);
	},
	
	removeSelected: function (item)
	{
		var ids = {};
		var n = 0;
		var l = (item + '_del_').length;
		$('input[type="checkbox"]:checked.' + item + '_del').each (
			function () {
				ids[n] = $(this).attr('name').substr(l);
				n++;
			}
		);
		
		function callback (result)
		{
			if (result.code == 200)
			{
				alert ("Удалено: " + result.count);
			}
			else if (result.msg)
			{
				alert (result.msg);
			}
		}
		
		if (confirm ("Будет удалено " + n + " позиций.\nПродолжить?"))
		{
			Controller.call (
				item + '/remove',
				{
					"ids": ids
				},
				callback, true
			);
		}
	}
		
};