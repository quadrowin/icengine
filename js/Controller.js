var Controller = {
		
	callbacks: {},
	
	callbacksData: {},
	
	lastback: 0,
		
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
	
	callback: function (js, text)
	{
		var back = js.back;
		
		if (js.error)
		{
			alert (js.error);
			return;
		}
		
		if (!js.result)
		{
			if (console && console.log)
			{
				console.log ('error js', js);
			}
			return;
		}
		
		var callback = Controller.callbacks [back];
		js.result.back = 
			Controller.callbacksData [back] ? 
			Controller.callbacksData [back] : null;
		callback (js.result, text);
	}
		
};