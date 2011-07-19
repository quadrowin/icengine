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
            item._name = n;
        }
        else
        {
            classDef [n] = item;
        }
        proto [n] = item;
    }
    classDef.prototype = proto;
    classDef.prototype.parent = function ()
	{
        var name = arguments.callee.caller._name;
        superClass [name].apply (this, arguments);
	};
    classDef.extend = this.extend;
	
	return classDef;
};