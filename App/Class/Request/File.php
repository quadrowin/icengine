<?php

namespace Ice;

/**
 *
 * @desc Загружаемый на сервер файл
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Request_File
{

	/**
	 * @desc Элемент массива $_FILES
	 * @var array
	 */
	public $file;

	/**
	 * @desc Имя исходного файла
	 * @var string
	 */
	public $name;

	/**
	 * @desc Расширение исходного файла
	 * @var extension
	 */
	public $extension;

	/**
	 *
	 * @var string
	 */
	public $type;

	/**
	 * @desc Размер
	 * @var integer
	 */
	public $size;

	/**
	 * @desc Имя временного файла
	 * @var string
	 */
	public $tmp_name;

	/**
	 * @desc Ошибки загрузки
	 * @var integer
	 */
	public $error;

	/**
	 * @desc Путь к конечному файлу на сервере
	 * @var string
	 */
	public $destination = false;

	/**
	 *
	 * @param array $file элемент из $_FILES
	 */
	public function __construct (array $file)
	{
		$this->file		= $file;
		$this->name		= $file ['name'];
		$this->type		= $file ['type'];
		$this->size		= $file ['size'];
		$this->tmp_name	= $file ['tmp_name'];
		$this->error	= $file ['error'];

		$this->extension = strtolower (substr (strrchr ($this->name, '.'), 1));
	}

	/**
	 * @return boolean
	 */
	public function hasError ()
	{
		return $this->isUploaded () && $this->error != UPLOAD_ERR_OK;
	}

	/**
	 * @return boolean
	 */
	function isUploaded ()
	{
		return $this->error != UPLOAD_ERR_NO_FILE;
	}

	/**
	 * Сохранить файл в $destination
	 * @param string $destination Путь к файлу
	 * @return boolean
	 */
	function save ($destination)
	{
		$this->destination = $destination;
		return move_uploaded_file ($this->tmp_name, $destination);
	}

	/**
	 *
	 * @param string $path
	 * @param boolean|string $extension true - сохранить расширение, false - без расширения, str - переданное расширение
	 * @return string|false Имя файла, false - в случае неудачи
	 */
	function saveUniq ($path = 'uploads/', $extension = true)
	{
		$fn = uniqid ();
		if ($extension === true)
		{
			// добавляем расширение исходного файла
			$fn .= '.' . $this->extension;
		}
		elseif ($extension !== false)
		{
			// добавляем переданное расширение
			$fn .= '.' . $extension;
		}

		if ($this->save ($path . $fn))
		{
			return $fn;
		}
		else
		{
			return false;
		}
	}

}
