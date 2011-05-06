<?php

class Authorization_Result
{
    
    const NONE	    = 0;	// не было попытки
    const OK	    = 1;	// успешно
    const MISSING	= 2;	// неверные логин/email
    const WRONGPASS	= 3;	// неверный пароль
    const UNACTIVE  = 4;	// неактивный пользователь
    
}