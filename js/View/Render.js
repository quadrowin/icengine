/**
 * Рендер представлений.
 */
var View_Render = {
	
	/**
	 * Переменные шаблонизатора
	 * @var object
	 */
	tplVars: {
		'is_js': true
	},
	
	/**
	 * Шаблоны
	 * @var object
	 */
	templates: {
		'empty': ""
	},
	
	/**
	 * Установить значение шаблона	
	 * @param key
	 * 		Массив значений или имя переменной.
	 * @param value [optional]
	 * 		Если первый параметр имя переменной - значение переменной.
	 */
	assign: function (key, value)
	{
		if (typeof (input) == 'object' && (input instanceof Array))
		{
			$.extend (View_Render.tplVars, key);
		}
		else
		{
			View_Render.tplVars [key] = value;
		}
	},
	
	/**
	 * Компилировать шаблон в строку
	 * @param tpl
	 * 		Название шаблона.
	 */
	fetch: function (tpl)
	{
		if (!View_Render.templates [tpl])
		{
			Debug.echo ('No template: ', tpl);
			return null;
		}
		
		return Helper_Render_Smarty.processDOMTemplate (
			View_Render.templates [tpl],
			View_Render.tplVars
		);
	},
	
	/**
	 * Получить значение переменной из шаблона
	 * @param key
	 * 		Название переменной.
	 * @returns
	 * 		Значение переменной.
	 */
	getVar: function (key)
	{
		return View_Render.tplVars [key];
	}
	
};