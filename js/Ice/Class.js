/**
 * @desc Базовый класс.
 * @var function
 */
Ice.Class = function () {};

/**
 * @desc Функция инициализации.
 * @var function
 */
Ice.Class.prototype.__construct = function () {};

/**
 * @desc Идентификаторы функций класса
 * @var integer
 */
Ice.Class.__id = 1;

/**
 * @desc Массив функций
 * @var array
 */ 
Ice.Class.__funcList = [Ice.Class.__construct];

/**
 * @desc Функция для создания дочерних классов.
 * @var function
 */
Ice.Class.extend = function (def)
{
    var classDef = function ()
    {
        if (arguments [0] !== Ice.Class)
        {
            this.__construct.apply (this, arguments);
        }
    };
	
    var proto = new this (Ice.Class);
    var superClass = this.prototype;
	
    for (var n in def)
    {
        var item = def [n];
        if (item instanceof Function)
        {
			if (n in superClass)
			{
				item.__parentId = superClass [n].__id;
			}
			
			Ice.Class.__funcList.push (item);
			
            item.__name = n;
			item.__id = Ice.Class.__id++;
			
        }
        else
        {
            classDef [n] = item;
        }
        proto [n] = item;
    }
	
    classDef.prototype = proto;
	
	/**
	 * @desc Вызываем одноименную функцию родителя
	 */
    classDef.prototype.parent = function ()
	{
		var parent_func_id = arguments.callee.caller.__parentId;
		
		Ice.Class.__funcList [parent_func_id].apply (this, arguments);
	};
	
    classDef.extend = this.extend;
	
	return classDef;
};