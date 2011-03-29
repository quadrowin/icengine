<?php
/**
 * 
 * @desc Базовая модель категории контента
 * @author Юрий Шведов
 *
 */
Loader::load ('Model_Child');
class Content_Category extends Model_Child
{
	
	/**
	 * @desc Возвращает название модели контента.
	 * @return string
	 */
	public function contentModel ()
	{
		return 'Content';
	}
	
	/**
	 * @desc Возвращает контент, содержащийся в этом разделе.
	 * @return Model_Collection
	 */
	public function contents ()
	{
		return Model_Collection_Manager::byQuery (
			$this->contentModel (),
			Query::instance ()
				->where ('Content_Category__id', $this->id)
		);
	}
	
	/**
	 * @desc Поменять URL категории если в нем один контент
	 * @return Model
	 */
	public function oneContent ()
	{
		$articles = Helper_Link::linkedItems(
			$this,
			'Content'
		);
		$this->data('content', $articles);
		if ($articles->count () == 1 && !$this->childs())
		{
			$this->url = $articles->first ()->url;
		}
		return $this;
	}
	
}