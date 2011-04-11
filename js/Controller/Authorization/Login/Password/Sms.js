/**
 * @desc Контроллер для авторизации в универсальной админке
 */
var Authorization_Login_Password_Sms_Providers = 
	[ 'Sms_Littlesms', 'Sms_Dcnk', 'Sms_Yakoon' ];
var Authorization_Login_Password_Sms_Provider = -1;
var Authorization_Login_Password_Sms_Form = null;                                 
var Authorization_Login_Password_Sms = {
	/**
	 * @desc Авторизация или отправка кода
	 * @param $form
	 */
	login: function ($form)
	{
		Authorization_Login_Password_Sms_Form = $form;
		var code = $form.find ('input[name=code]').val ();
		var $btn = $form.find ('input[name=btnSendCode]');
		$btn.nextAll ('div').remove ();
		
		if (!code)
		{
			$btn.hide ();
		}
		
		function callback (result)
		{
			if (result && result.data && result.data.activation_id)
			{
				$form.find ('input[name=activation_id]').val (
					result.data.activation_id
				);
			}
			
			if (result.html)
			{
				$btn.after ('<div>' + result.html + '<div>');
			}
			
			if (result.error)
			{
				$btn.show ();
				alert (result.error);
				return ;
			}
			
			if (result.redirect)
			{
				window.location.href = result.redirect;
			}
		}
		
		var provider = 0;
		
		if (typeof Authorization_Login_Password_Sms_Providers
				 [Authorization_Login_Password_Sms_Provider] !== 'undefined')
		{
			provider = Authorization_Login_Password_Sms_Providers
			 [Authorization_Login_Password_Sms_Provider];
		}
		
		Controller.call (
			'Authorization_Login_Password_Sms/login',
			{
				name: $form.find ('input[name=name]').val (),
				pass: $form.find ('input[name=pass]').val (),
				a_id: $form.find ('input[name=activation_id]').val (),
				code: code,
				href: window.location.href,
				provider: provider
			},
			callback, true
		);
	},
	
	/**
	 * @desc Деавторизация
	 */
	logout: function ()
	{
		function callback (result)
		{
			window.location.href =
				result.redirect ?
					result.redirect :
					window.location.href;
		}
		
		Controller.call (
			'Authorization_Login_Password_Sms/logout',
			{
				href: window.location.href
			},
			callback, true
		);
	},
	
	rotate: function ()
	{
		if (Authorization_Login_Password_Sms_Provider < 0)
		{
			Authorization_Login_Password_Sms_Provider = 0;
		}
		Authorization_Login_Password_Sms_Provider++;
		if (Authorization_Login_Password_Sms_Provider >= 
			Authorization_Login_Password_Sms_Providers.length)
		{
			Authorization_Login_Password_Sms_Provider = 0;
		}
		this.login (Authorization_Login_Password_Sms_Form);
	}
};