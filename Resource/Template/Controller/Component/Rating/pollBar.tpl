<div class="rating_bar_{$model->modelName()}_{$model->key()}">
	<a href="javascript:void(0);" onclick="Ice.Controller_Rating.vote ('{$model->modelName()}', '{$model->key()}', 1);">За</a>
	<a href="javascript:void(0);" onclick="Ice.Controller_Rating.vote ('{$model->modelName()}', '{$model->key()}', -1);">Против</a>
</div>