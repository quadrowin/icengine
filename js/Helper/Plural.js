/**
 * 
 * @desc Хелпер для подбора формы слова.
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
var Helper_Plural = {
	
	/**
	 * @desc Возвращает слово в соответствующей форме.
	 * @param integer value Значение
	 * @param string|arrya forms Формы слова
	 */
	get: function (n, forms)
	{
		if (typeof forms == "string")
		{
			forms = forms.split (',');
		}
		
		plural = (n % 10 == 1 && n % 100 != 11 ? 0 : (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2));
		
		if (forms [plural])
		{
			return forms [plural];
		}
		
		return forms.shift ();
	}
		
};