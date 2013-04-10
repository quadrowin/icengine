<?php

/**
 * Контроллер категорий контента
 *
 * @author morph, goorus
 */
class Controller_Content_Category extends Controller_Abstract
{
	/**
	 * Удалить категорию
	 */
	public function remove($categoryId)
	{
		$this->_task->setTemplate(null);
		$modelManager = $this->getService('modelManager');
		$category = $modelManager->byKey('Content_Category', $categoryId);
		if (!$category) {
			return;
		}
		$user = $this->getService('user')->getCurrent();
		if (!$user->hasRole('editor') && $category->User__id != $user->key()) {
			return;
		}
		$category->delete();
	}

	/**
	 * Вывести список категорий
	 */
	public function roll($parentId)
	{
		$modelManager = $this->getService('modelManager');
		$parent = $modelManager->byKey('Content_Category', $parentId);
		if (!$parent) {
			return $this->replaceAction('Error', 'notFound');
		}
		$collectionManager = $this->getService('collectionManager');
		$categoryCollection = $collectionManager->create(
			'Contnet_Category'
		)->addOptions(
			'::Active',
			array(
				'name'	=> '::Parent',
				'id'	=> $parentId
			)
		);
		$this->output->send(array(
			'parent'		=> $parent,
			'categories'	=> $categoryCollection
		));
	}

	/**
	 * Сохранение категории
	 */
	public function save($parentId, $categoryId, $title, $sort, $active,
		$url, $class)
	{
		$this->_task->setTemplate(null);
		$modelManager = $this->getService('modelManager');
		$parent = $modelManager->byKey('Content_Category', $parentId);
		if (!$parent) {
			return;
		}
		$user = $this->getService('user')->getCurrent();
		if (!$user->hasRole('editor') || $user->key() != $parent->User__id) {
			return;
		}
		$category = $modelManager->get('Content_Category', $categoryId);
		$category->set(array(
			'parentId'	=> $parent->key(),
			'title'		=> $title,
			'sort'		=> $sort,
			'User__id'	=> $user->key(),
			'active'	=> $active,
			'url'		=> $url,
			'class'		=> $class,
			'name'			=> $parent->name,
			'controller'	=> $parent->controller
		));
		$category->save();
	}
}