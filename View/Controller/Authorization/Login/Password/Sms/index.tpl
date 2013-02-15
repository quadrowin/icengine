<form style="color:white" method="post" action="javascript:void(0);" onsubmit="Authorization_Login_Password_Sms.login ($(this));">
	<table style="border-collapse:separate; border-spacing: 8px; margin: 20px auto;">
		<tr>
			<td><label for="name" style="font-size:18px; margin-top:-5px">Логин</label></td>
			<td><input name="name" type="text" style="font-size: 170%; width: 500px;" /></td>
		</tr>
		<tr>
			<td><label for="pass" style="font-size:18px; margin-top:-5px"">Пароль</label></td>
			<td><input name="pass" type="password" style="font-size: 170%; width: 370px;" /> 
                <input style="margin-left:10px; margin-top:-10px" class="btn btn-success" name="btnSendCode" type="submit" value="Отправить код" /></td>
		</tr>
		<tr>
			<td><label for="code" style="font-size:18px; margin-top:-5px"">Смс код</label></td>
			<td><input name="code" type="text" value="" style="font-size: 170%; width: 500px;" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
                <input name="activation_id" type="hidden" value="" />
                <input class="btn btn-primary btn-large" type="submit" value="Авторизация" style="font-size: 170%;" />
            </td>
		</tr>
	</table>
</form>