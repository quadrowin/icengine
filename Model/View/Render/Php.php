<?php
/**
 * 
 * @desc Рендер с использованием шаблона на Php.
 * @author Yury Shvedov, Denis Shestakov
 * @package IcEngine
 *
 */
class View_Render_Php extends View_Render_Abstract {

    protected function _afterConstruct() {
        $config = $this->config();
        $this->_templatesPathes = array_reverse(
                $config ['templates_path']->__toArray()
                );
    }

    public function fetch($tpl) {
        $result = '';
        $tpl .= '.php';
        foreach ($this->_templatesPathes as $path) {
            $file = $path . $tpl;
//			if (file_exists($file))
//			{
            ob_start();
            foreach ($this->_vars as $key => $value) {
                $$key = $value;
//                echo $key . ' = ' . $value . '<br>';
            }
            require $file;
            $result = ob_get_contents();
            ob_end_clean();
            break;
//			}
        }
        return $result;
    }

    public function display($tpl) {
        $tpl .= '.php';

        foreach ($this->_templatesPathes as $path) {

            $file = $path . $tpl;
//			if (file_exists ($file))
//			{
            foreach ($this->_vars as $key => $value) {
                $$key = $value;
            }
            require $file;
            return;
//			}
        }
    }

    public function addHelper($helper, $method) {
        
    }

}