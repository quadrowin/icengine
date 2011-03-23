{if !$registered}<div id="type_hint">Пользователя с таким email’ом ещё не зарегестрировано.</div>{/if}
<div class="capt"><b>ПАРОЛЬ</b>:</div>
<div>
	<input type="password" name="auth_password" class="hover" value=""/>
</div>
<div class="tools">
	<a>Забыли пароль?</a>
	{*<span class="show-qwe"><input type="checkbox" checked="checked" /><label>Спрятать пароль</label></span>*}
</div>
<div class="result-msg hidden"></div>
<div class="btns_block">
	{if $registered}
		<a class="submit btn_green"><span>ВХОД</span></a>
	{else}
		<a class="submit btn_green"><span>РЕГИСТРАЦИЯ</span></a>
	{/if}
</div>