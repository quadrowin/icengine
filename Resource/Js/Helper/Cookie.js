/**
 *
 * @desc Помощник для работы с плюшками
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
var Helper_Cookie = {

	/**
	 * @desc Возвращает значение плюшки
	 * @param name string
	 * @return string
	 */
	get: function (name) {
		var cookie = " " + document.cookie;
		var search = " " + name + "=";
		var setStr = null;
		var offset = 0;
		var end = 0;
		if (cookie.length > 0) {
			offset = cookie.indexOf(search);
			if (offset != -1) {
				offset += search.length;
				end = cookie.indexOf(";", offset);
				if (end == -1) {
					end = cookie.length;
				}
				setStr = unescape(cookie.substring(offset, end));
			}
		}
		return (setStr);
	},

	/**
	 * @desc Устанавливает значение плюшки
	 * @param name string
	 * @param value string
	 * @param expires string
	 * @param path string
	 * @param domain string
	 * @param secure string
	 */
	set: function (name, value, expires, path, domain, secure) {
		document.cookie = name + "=" + escape(value) +
			((expires) ? "; expires=" + expires : "") +
			((path) ? "; path=" + path : "") +
			((domain) ? "; domain=" + domain : "") +
			((secure) ? "; secure" : "");
	}

};