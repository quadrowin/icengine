<?php

namespace Ice;

Loader::load ('Content_Abstract');

/**
 *
 * @desc Базовая модель для расширений контента.
 * Не допускается, чтобы модель состояла из одного поля - первичного ключа.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Content_Extending extends Content_Abstract
{

	/**
	 * @desc Возвращает контент, к которому привязано.
	 * @return Content
	 */
	public function content ()
	{
		return Model_Manager::byKey ('Content', $this->id);
	}

	/**
	 * @desc Первое сохранение. Необходимо для инициализации полей, задания
	 * им значений по умолчанию.
	 * @return Content_Extending
	 */
	public function firstSave ()
	{
		return $this->save (true);
	}

	/**
	 * (non-PHPdoc)
	 * @see Model::save()
	 */
	public function save ($hard_insert = false)
	{
		$this->set ('title', $this->content ()->title);
		return parent::save ($hard_insert);
	}

}