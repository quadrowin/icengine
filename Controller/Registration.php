<?php

/**
 * Регистрация 
 * 
 * @author goorus, morph
 */
class Controller_Registration extends Controller_Abstract
{
    /**
     * Проверяет, существует ли указанная почта
     */
    public function check($email, $context)
    {
        $this->task->setTemplate(null);
        $emailQuery = $context->queryBuilder
            ->select('id')
            ->from('User')
            ->where('email', $email);
        $emailExists = (bool) $context->dds->execute($emailQuery)->getResult()
            ->asValue();
        $this->output->send(array(
            'data'  => array(
                'exists'    => $emailExists
            )
        ));
    }
    
    /**
     * Подтверждение регистрации через письмо
     */
    public function confirm($code, $context)
    {
        $this->task->setTemplate(null);
        $currentUser = $context->user->getCurrent();
        if ($currentUser->key()) {
            return;
        }
        $query = $context->queryBuilder->where('code', $code);
        $registration = $context->modelManager->byQuery('Registration', $query);
        if (!$registration) {
            return;
        }
        $user = $registration->User;
        $user->update(array(
            'active'    => 1
        ));
        $user->authorize();
        $registration->delete();
        $resource = new Session_Resource('Registration');
        $resource->success = true;
        $this->output->send(array(
            'data'  => array(
                'redirectUrl'   => $user->url()
            )
        ));
    }
    
    /**
     * Первый шаг регистрации
     * 
     * @Context("helperDate", "helperUnique")
     */
    public function index($email, $password, $retypePassword, $context)
    {
        $this->task->setTemplate(null);
        $currentUser = $context->user->getCurrent();
        if ($currentUser->key()) {
            return;
        }
        $emailQuery = $context->queryBuilder
            ->select('id')
            ->from('User')
            ->where('email', $email);
        $emailExists = (bool) $context->dds->execute($emailQuery)->getResult()
            ->asValue();
        if ($emailExists) {
            return;
        }
        if ($password != $retypePassword) {
            return;
        }
        $cryptManager = $this->getService('cryptManager');
        $password = $cryptManager->get('RSA2')->encrypt($password);
        $createdAt = $context->helperDate->toUnix();
        $user = $context->modelManager->create('User', array(
            'active'    => 0,
            'password'  => $password,
            'email'     => $email,
            'createdAt' => $createdAt
        ));
        $user->save();
        $code = $context->helperUnique->hash();
        $registration = $context->modelManager->create('Registration', array(
            'code'      => $code,
            'createdAt' => $createdAt,
            'User__id'  => $user->key()
        ));
        $registration->save();
        $resource = new Session_Resource('Registration');
        $resource->code = $code;
        $this->output->send(array(
            'data'  => array(
                'code'  => $code
            )
        ));
    }
}