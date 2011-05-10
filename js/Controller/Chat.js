var $agency_id,
	$name,
	$message,
	$code,
	$join_id,
	$last_id,
	$timer,
	first_join = true;

var Controller_Chat = {
	localizeDate: function (date)
	{
		var tmp = date.split (':');
		var dt = new Date, offset = dt.getTimezoneOffset () / 60;

		var h = parseInt (tmp [0]) - offset;
		
		if (h > 24)
		{
			h = 24 - h; 
		}
		
		return (h < 10 ? '0' : '') + h + ':' + tmp [1];
	},
	getLastDate: function (cont)
	{
		var li = cont.find ('li:first');
		if (li.length)
		{
			var date = li.find ('span.time').html ().split (' ')[0];
			return date;
		}
		return '';
	},
	prepareUri: function (uri)
	{
		uri = uri.replace ('http://', '');
		uri = uri.substr (uri.indexOf ('/'), uri.length);

		return uri;
	},
	getLastMessages: function (cont)
	{
		var last_li = cont.find ('li:last');
		if (last_li.length)
		{
			$last_id = last_li.attr ('id').split ('-')[1];
			if ($last_id > 0)
			{
				Controller_Chat.receiveLast ($join_id, $last_id);
			}
		}
	},
	appendMessage: function (cont, obj)
	{
		if ($('#message-' + obj.id).length)
		{
			return;
		}
		
		var li = $('<li></li>').appendTo (cont).attr ('id', 'message-' + obj.id),
			date = $('<span class="time"></span>').html (obj.date 
					+ '&nbsp;<b>' + obj.name + ':</b>').appendTo (li);
		
		var b = li.html ();
		
		li.html (b + '<p style="padding:4px 1px">' + obj.message + '</p>');
		
		if (obj.join_id != $join_id)
		{
			li.addClass ('adm');
		}
		
		cont.scrollTop (20000); 
	},
	indexChat: function (agency_id)
	{
		function callback (result)
		{			
			if (typeof result.data.code != 'undefined')
			{
				$join_id = result.data.join_id;
				$name	 = result.data.name;
				$code 	 = result.data.code;
				
				Controller_Chat.joinChat (
					$code,
					$name
				);
				
				return;
			}
			
			Helper_Dialog.alert ({
				title: 'Задать вопрос турфирме',
				text: result.html,
				maxWidth: 700,
				maxHeight: 550
			});
		}
		
		Controller.call (
			'Agency_Chat/index',
			{
				agency_id: agency_id,
				uri: Controller_Chat.prepareUri (window.location.href)
			},
			callback,
			true
		);
	},
	createChat: function (agency_id, name, message)
	{
		function callback (result)
		{
			$code = result.data.code;
			
			Controller_Chat.joinChat (
				$code,
				$name
			);
		} 
		
		Controller.call ( 
			'Agency_Chat/create',
			{
				agency_id: agency_id,
				name: name,
				message: message
			},
			callback,
			true
		);
	},
	joinChat: function (code, name)
	{
		function callback (result)
		{
			$join_id = result.data.join_id;
			$name	 = result.data.name;
			$code 	 = result.data.code;
			
			Helper_Dialog.alert ({
				title: 'Задать вопрос турфирме',
				text: result.html,
				maxWidth: 700,
				maxHeight: 550,
				afterShow: function ()
				{
					var cont = $('#chat_messages');
					$timer = setInterval (
						function ()
						{
							Controller_Chat.getLastMessages (cont);
						},
						3000
					)
				},
				afterHide: function ()
				{
					if ($timer)
					{
						clearInterval ($timer);
					}
				}
			});
			
			if (first_join && $message)
			{
				Controller_Chat.sendMessage (
					$join_id,
					$message,
					true
				);
				first_join = false;
				
				if ($('#chat_offline').length)
				{
					Controller_Chat.appendMessage (
						$('#chat_messages'),
						{
							name: 'Сообщение',
							message: 'В данный момент менеджер отсутсвует и ответит вам как только появится.',
							join_id: 0,
							id: 0,
							date: ''
						}
					);
				}
			}
			
			Controller_Chat.receiveAll ($join_id);
			
			$('#chat_message').
				val ('').
				focus (); 
		}
		
		Controller.call (
			'Chat_Session/join',
			{
				code: code,
				name: name,
				uri: Controller_Chat.prepareUri (window.location.href)
			},
			callback,
			true
		);
	},
	receiveAll: function (join_id)
	{
		Controller.call (
			'Chat_Message/roll',
			{
				session_join_id: join_id,
			},
			Controller_Chat.receiveCallback,
			true
		);
	},
	receiveLast: function (join_id, last_id)
	{
		Controller.call (
			'Chat_Message/last',
			{
				session_join_id: join_id,
				last_message_id: last_id
			},
			Controller_Chat.receiveCallback,
			true
		);
	}, 
	sendMessage: function (join_id, message, dont_callback)
	{
		function callback (result)
		{
			var el = $('#chat_messages');
		
			Controller_Chat.appendMessage (
				el, 
				{
					name: $name,
					message: message,
					join_id: $join_id,
					id: result.data.id,
					date: Controller_Chat.localizeDate (result.data.date)
				}
			);
		}
		
		$('#chat_message').
			val ('').
			focus (); 
		
		if (!message)
		{
			return;
		}
		
		Controller.call (
			'Chat_Message/send',
			{
				session_join_id: join_id,
				message: message 
			},
			dont_callback ? function () { } : callback,
			true
		);
	},
	receiveCallback: function (result)
	{
		if (typeof result.data.messages == 'undefined')
		{
			return;
		}
		
		var el = $('#chat_messages');
		
		for (var i = 0, m = result.data.messages, l = m.length; i < l; i++)
		{
			m [i].date = Controller_Chat.localizeDate (m [i].date);
			Controller_Chat.appendMessage (
				el, m [i]
			);
		}
	}
};

$(document).ready (function ()
{
	$agency_id = $('#agency_id').val ();
	
	if ($('#join_id').length)
	{
		$join_id = $('#join_id').val ();
	}
	
	if ($('#code').length)
	{
		$code = $('#code').val ();
	}
	
	if ($('#name').length)
	{
		$name = $('#name').val ();
	}
	
	$('#enter-chat').click (function ()
	{
		if (!$code)
		{
			Controller_Chat.indexChat ($agency_id);
		}
		else
		{
			Controller_Chat.joinChat (
				$code,
				$name
			);
		}
	});
	
	$('#chat_send').live ('click', function ()
	{
		$name = $('#chat_name').val ();
		$message = $('#chat_message').val ();
		
		Modal.hide ();
		
		Controller_Chat.createChat (
			$agency_id,
			$name,
			$message
		);
		
	});
	
	$('#chat_type').live ('click', function ()
	{
		$message = $('#chat_message').val ();
		Controller_Chat.sendMessage (
			$join_id,
			$message
		);
	});
});