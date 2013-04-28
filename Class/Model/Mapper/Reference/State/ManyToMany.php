<?php

/**
 * Состояния связи для связи типа "многие-ко-многим"
 * 
 * @author morph
 */
class Model_Mapper_Reference_State_ManyToMany extends 
    Model_Mapper_Reference_State_Abstract
{
    /**
     * Фильтры перед загрузкой
     * 
     * @var array
     */
    protected $preFilters = array();
    
    /**
     * Добавляет модель в коллекцию
     * 
     * @param Model $model
     * @param boolean $mustLoad
     * @param array $data
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function add($model, $mustLoad = false, $data = array())
    {
        $modelScheme = $this->getService('modelScheme');
        $keyField = $modelScheme->keyField($this->dto->Target);
        if ($mustLoad) {
            if (!$this->collection) {
                $this->load();
            }
            if ($this->collection->filter(array($keyField => $model->key()))) {
                return $this;
            }
            $this->collection->add($model);
        }
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $existsQuery = $queryBuilder
            ->select($this->dto->JoinColumn[0])
            ->from($this->dto->JoinTable)
            ->where($this->dto->JoinColumn[0], $this->model->key())
            ->where($this->dto->JoinColumn['on'], $model->key())
            ->limit(1);
        $exists = $dds->execute($existsQuery)->getResult()->asRow();
        if ($exists) {
            return $this;
        }
        $query = $queryBuilder
            ->insert($this->dto->JoinTable)
            ->values(
                array_merge($data, array(
                    $this->dto->JoinColumn[0]       => $this->model->key(),
                    $this->dto->JoinColumn['on']    => $model->key()
                ))
             );
        $dds->execute($query);
        return $this;
    }
    
    /**
     * @inhertdoc
     * 
     * @return Model_Collection
     */
    public function collection()
    {
        return $this->getService('collectionManager')->create(
            $this->dto->Target);
    }
    
    /**
     * Удаляет модель
     * 
     * @param Model $model
     * @param boolean $mustLoad
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function delete($model, $mustLoad = false)
    {
        if ($mustLoad) {
            if (!$this->collection) {
                $this->load();
            }
            foreach ($this->collection as $i => $item) {
                if ($item->key() == $model->key()) {
                    $this->collection->unset($i);
                }
            }
        }
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $query = $queryBuilder
            ->delete()
            ->from($this->dto->JoinTable)
            ->where($this->dto->JoinColumn[0], $this->model->key())
            ->where($this->dto->JoinColumn['on'], $model->key());
        $dds->execute($query);
        return $this;
    }
    
    /**
     * Получить схему для таблицы связи
     * 
     * @param array $joinColumn
     */
    protected function getSchemeForJoinTable()
    {
        $dto = $this->getService('dto')->newInstance('Model_Scheme');
        $dto->setFields(array(
            'id'                            => array(
                'Int', array(
                    'Size'      => 11,
                    'Not_Null',
                    'Auto_Increment'
                )
            ),
            $this->dto->JoinColumn[0]       => array(
                'Int', array(
                    'Size'  => 11,
                    'Not_Null'
                )
            ),
            $this->dto->JoinColumn['on']    => array(
                'Int', array(
                    'Size'  => 11,
                    'Not_Null'
                )
            ) 
        ));
        $dto->setIndexes(array(
            'id'                            => array('Primary', array('id')),
            $this->dto->JoinColumn[0]       => array(
                'Key', array($this->dto->JoinColumn[0])
            ),
            $this->dto->JoinColumn['on']    => array(
                'Key', array($this->dto->JoinColumn['on'])
            )
        ));
        return $dto;
    }
    
    /**
     * @inheritdoc
     */
    public function load()
    {
        $modelScheme = $this->getService('modelScheme');
        $keyField = $modelScheme->keyField($this->dto->Target);
        $this->collection = $this->collection();
        if (!$modelScheme->scheme($this->dto->JoinTable)->fields) {
            $dto = $this->getSchemeForJoinTable();
            $this->getService('helperModelScheme')->create(
                $this->dto->JoinTable, $dto
            );
            $this->getService('helperModelTable')->create(
                $this->dto->JoinTable
            );
        } else {
            $queryBuilder = $this->getService('query');
            $dds = $this->getService('dds');
            $query = $queryBuilder
                ->select($this->dto->JoinColumn['on'])
                ->from($this->dto->JoinTable)
                ->where($this->dto->JoinColumn[0], $this->model->key());
            if ($this->preFilters) {
                foreach ($this->preFilters as $fieldName => $value) {
                    $query->where($fieldName, $value);
                }
            }
            $ids = $dds->execute($query)->getResult()->asColumn();
            $this->collection->query()->where($keyField, $ids);
        }
        parent::load();
    }
    
    /**
     * @inheritdoc
     */
    public function registerFilter($field, $value)
    {
        if (strpos($field, '::') === 0) {
            $field = substr($field, 2);
            $this->preFilters[$field] = $value;
        } else {
            $this->filters[$field] = $value;
        }
    }
    
    /**
     * Обновить данные связи
     * 
     * @param Model $model
     * @param array $data
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function update($model, $data)
    {
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $query = $queryBuilder
            ->update($this->dto->JoinTable)
            ->values($data)
            ->where($this->dto->JoinColumn[0], $this->model->key())
            ->where($this->dto->JoinColumn['on'], $model->key());
        $dds->execute($query);
        return $this;
    }
}