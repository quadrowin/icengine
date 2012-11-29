<?php
/**
 *
 * @desc Данные по OpenID пользователя
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class User_Loginza extends Model
{

	/**
	 * @desc находит данные пользователя по полученному ключу
	 * @param Authorization_Loginza_Token $token.
	 * @param boolean $email_search Искать по email. Необходимо, чтобы
	 * $token содержал не пустое поле email.
	 * @param boolean $user_search Создать модель, если таковой не существует
	 * (будет произведен поиск по полю email в таблице User). Необходимо, чтобы
	 * $token содержал не пустое поле email.
	 * @return User_Loginza
	 */
	public static function byToken (Authorization_Loginza_Token $token,
		$email_search = true, $user_search = true)
	{
		if (!$token->identity) {
			return null;
		}
		$modelManager = $this->getService('modelManager');
		$query = $this->getService('query');
		$loginza = $modelManager->byQuery(
			__CLASS__,
			$query->where('identity', $token->identity)
		);
		if (!$loginza && $email_search && $token->email) {
			$other_loginza = $modelManager->byQuery(
				__CLASS__,
				$query->where('email', $token->email)
			);
			if ($other_loginza) {
				$loginza = new self (array (
					'User__id'	=> $other_loginza->User__id,
					'identity'	=> $token->identity,
					'email'		=> $token->email,
					'provider'	=> $token->provider,
					'data'		=> $token->data,
					'createdAt'	=> Helper_Date::toUnix ()
				));
				return $loginza->save ();
			}
		}

		if (!$loginza && $user_search && $token->email)
		{
			$user = Model_Manager::byQuery (
				'User',
				Query::instance ()
					->where ('email', $token->email)
			);

			if ($user)
			{
				$loginza = new self (array (
					'User__id'		=> $user->id,
					'identity'		=> $token->identity,
					'email'			=> $token->email,
					'provider'		=> $token->provider,
					'data'			=> $token->data,
					'createdAt'		=> Helper_Date::toUnix ()
				));
				return $loginza->save ();
			}
		}

		if (!$loginza)
		{
			$loginza = new self (array (
				'User__id'		=> 0,
				'identity'		=> $token->identity,
				'email'			=> $token->email,
				'provider'		=> $token->provider,
				'data'			=> $token->data,
				'createdAt'		=> Helper_Date::toUnix ()
			));
			$loginza->save ();
		}

		return $loginza;
	}

}