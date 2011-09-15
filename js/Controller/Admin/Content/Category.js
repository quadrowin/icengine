/**
 * 
 * @desc Контроллер для администрирования разделов контента
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
var Controller_Admin_Content_Category = {
	
	/**
	 * @desc Состояние видимости контента
	 */
	contentVisibled: {},
	
	/**
	 * @desc Загрузка списка контента для 
	 */
	getContentList: function (category_id, page)
	{
		function callback (result)
		{
			$('#category_' + category_id + '_content').html (result.html);
		}
		
		Controller.call (
			'Admin_Content_Category/getContentList',
			{
				category_id: category_id,
				page: page
			},
			callback
		);
	},
	
	/**
	 * @desc Разворачивает/сворачивает контент
	 */
	toggleSubcontents: function (category_id)
	{
		if (this.contentVisibled [category_id])
		{
			$('#category_' + category_id + '_contents').hide ();
			this.contentVisibled [category_id] = false;
			return;
		}
		
		if (typeof this.contentVisibled == 'undefined')
		{
			this.getContentList (category_id);
		}
		
		$('#category_' + category_id + '_contents').show ();
		this.contentVisibled [category_id] = true;
	}
	
};