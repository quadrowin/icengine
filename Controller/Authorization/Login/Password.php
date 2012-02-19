<?php

/**
 *
 * @desc Контроллер для авторизации по логину и паролю
 * @author Denis Shestakov
 * @package IcEngine
 *
 */
class Controller_Authorization_Login_Password extends Controller_Abstract {

    /**
     * @desc Вовзращает модель авторизации.
     * @return Authorization_Login_Password
     */
    protected function _authorization() {
        return Model_Manager::byQuery(
                        'Authorization', Query::instance()
                                ->where('name', 'Login_Password')
        );
    }

    /**
     * (non-PHPdoc)
     * @see Controller_Abstract::index()
     */
    public function index() {
        // Просто форма авторизации
    }

    /**
     * @desc Авторизация
     * @param string $name Емейл пользователя
     * @param string $pass Пароль
     */
    public function login() {
        list (
                $login,
                $password,
                $redirect
                ) = $this->_input->receive(
                'email', 'password', 'href'
        );

        $user = $this->_authorization()->authorize(array(
            'login' => $login,
            'password' => $password
                ));

        if (!is_object($user)) {
            // Пользователя не существует
            $this->_sendError(
                    'authorization error: ' . $user, $user ? $user : __METHOD__, $user ? null : '/passwordIncorrect'
            );
            return;
        }

        Loader::load('Helper_Uri');
        $redirect = Helper_Uri::validRedirect($redirect);
        
        $this->_output->send(array(
            'redirect' => $redirect,
            'data' => array(
                'redirect' => $redirect
            )
        ));
    }

}