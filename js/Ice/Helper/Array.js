/**
 * 
 * @desc Помощник для работы с массивами.
 * @package IcEngine
 * 
 */
Ice.Helper_Array = {
	
	/**
	 * @desc Перемешивает массив.
	 * @param a Array Массив, элементы которого будут перемешаны
	 * @param rnd Number [optional] 
	 */
	shuffle: function (a, rnd)
	{
		while (!rnd || rnd == 0)
		{
			rnd = Math.random ();
		}
		rnd = rnd.toString ().replace ('.', '');
		while (rnd.length < 10)
		{
			rnd *= 7;
		}
		var p = 0;
		a.sort (function () {
			++p;
			if (p >= rnd.length)
			{
				p = 0;
			}
			return rnd [p] > 4;
		});
	}
	
};