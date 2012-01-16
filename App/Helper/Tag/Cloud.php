<?php

namespace Ice;

/**
 * @desc Хэлпер для облака тэгов
 */
class Helper_Tag_Cloud
{
	/**
	 * @desc Определить размеры шрифта по полю count модели. Шрифты записываются в data('font_size') модели.
	 * @param Model_Collection $collection Коллекция
	 * @param integer $minSize минимальный размер шрифта.
	 * @param integer $maxSize максимальный размер шрифта.
	 * @param integer $sizeStep Шаг шрифта (сколько прибавляется в каждом диапазоне)
	 * @return Model_Collection
	 */
	public static function fontSize (Model_Collection $collection, $minSize = 12,
		$maxSize = 30, $sizeStep = 2)
	{
		$tags = $collection;

		if (!$tags)
		{
			return;
		}

		// Количество шагов(диапазонов)
		$steps	= ($maxSize - $minSize) / $sizeStep;
		$range	= 1;
		//$range = ceil(count($tags)/$steps); // Диапазон

		$size	= $minSize;
		$start	= 1;

		for($i = 0; $i <= $steps; $i++)
		{
			$end = $start + $range;

			$sizeArray [$size] = array(
				'start' => $start,
				'end'   => $end
			);

			$end++;
			$start = $end;
			$size  = $size + $sizeStep;
		}

		foreach($tags as $tag)
		{
			if ($tag->count <= $sizeArray[$maxSize]['end'])
			{
				foreach ($sizeArray as $key => $size)
				{
					if (
						$tag->count >= $size['start'] &&
						$tag->count <= $size['end']
					)
					{
						$tag->data ('font_size', $key);
					}
				}
			}
			else
			{
				$tag->data ('font_size', $maxSize);
			}
		}
		return $tags;
	}
}