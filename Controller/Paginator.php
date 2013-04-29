<?php

/**
 * Контроллер пагинатор.
 *
 * @author goorus, neon
 */
class Controller_Paginator extends Controller_Abstract
{
    /**
     * (non-PHPdoc)
     * @see Controller_Abstract::index()
     */
    public function index($instance, $template, $tpl)
    {
        /* @var $paginator Paginator */
        $instance->buildPages();
        $this->output->send('paginator', $instance);
        if ($template) {
            $this->task->setTemplate($template);
        }
        if ($tpl) {
            $this->task->setClassTpl(__METHOD__, $tpl);
        }
    }
}