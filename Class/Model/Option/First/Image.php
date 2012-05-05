<?php

/**
 * @desc Получить в дату для элементов коллекции по первому изображению
 */
class Model_Option_First_Image extends Model_Option
{
	public function after ()
	{
		$keys = isset ($this->params ['keys'])
			? $this->params ['keys']
			: array ('smallUrl');

		$model_name = $this->collection->modelName ();

		$ids = $this->collection->column (Model_Scheme::keyField ($model_name));

		$query = Query::instance ()
			->select ('id, rowId')
			->from ('Component_Image')
			->where ('table', $model_name)
			->where ('rowId', $ids)
			->group ('rowId');
		$images = DDS::execute ($query)->getResult ()->asTable ();

		if (!$images)
		{
			return;
		}

		$image_attrs = array ();
		$image_ids = array ();

		foreach ($images as $image)
		{
			$image_ids [$image ['rowId']] = $image ['id'];
		}

		$query = Query::instance ()
			->select ('Attribute.value, Attribute.key, Attribute.rowId')
			->from ('Attribute')
			->where ('Attribute.table', 'Component_Image')
			->where ('Attribute.rowId', $image_ids)
			->where ('Attribute.key', $keys);

		$attrs = DDS::execute ($query)->getResult ()->asTable ();

		foreach ($attrs as $attr)
		{
			if (!isset ($image_attrs [$attr ['rowId']]))
			{
				$image_attrs [$attr ['rowId']] = array ();
			}
			$image_attrs [$attr ['rowId']][$attr ['key']] =
				json_decode ($attr ['value']);
		}

		foreach ($this->collection as $item)
		{
			if (!isset ($image_ids [$item->key ()]))
			{
				continue;
			}
			$image_id = $image_ids [$item->key ()];
			if (!isset ($image_attrs [$image_id]))
			{
				continue;
			}
			foreach ($image_attrs [$image_id] as $key => $attr)
			{
				$item->data ($key, $attr);
			}
		}
	}
}