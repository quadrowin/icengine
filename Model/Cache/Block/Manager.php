<?php

/**
 * Менеджер блоков кэша
 * 
 * @author morph
 * @Service("cacheBlockManager")
 */
class Cache_Block_Manager extends Manager_Abstract
{
    /**
     * Блоки для загрузки
     * 
     * @var array
     * @Generator
     */
    protected $blockVector;
    
    /**
     * Результат
     * 
     * @var array
     */
    protected $data;
    
    /**
     * Добавить блоки для загрузки
     * 
     * @param array $params
     * @param array $blocks
     */
    public function addBlocks($params, $blocks)
    {
        $hash = $this->getHash($params);
        if (!isset($this->blockVector[$hash])) {
            $this->blockVector[$hash] = array();
        }
        $this->blockVector[$hash] = array_merge(
            $this->blockVector[$hash], (array) $blocks
        );
    }
    
    /**
     * Получить блок кэша
     * 
     * @param string $controllerAction
     * @param array $params
     * @return array
     */
    public function get($controllerAction, $params = array())
    {
        $hash = $this->getHash($params);
        if (isset($this->data[$controllerAction])) {
            if (!is_array($this->data[$controllerAction])) {
                $this->data[$controllerAction] = json_decode(
                    urldecode($this->data[$controllerAction]), true
                );
            }
            return $this->data[$controllerAction];
        } elseif ($this->data) {
            $this->blockVector = array();
        }
        $query = $this->getService('query')
            ->select('json', 'controllerAction')
            ->from('Cache_Block')
            ->where('hash', $hash);
        if (!empty($this->blockVector[$hash])) {
            $query->where('controllerAction', $this->blockVector[$hash]);
        }
        $data = $this->getService('dds')->execute($query)->getResult()
            ->asTable();
        if (!$data) {
            $this->data[$controllerAction] = array();
            return null;
        }
        foreach ($data as $row) {
            $this->data[$row['controllerAction']] = $row['json'];
        }
        if (!is_array($this->data[$controllerAction])) {
            $this->data[$controllerAction] = json_decode(
                urldecode($this->data[$controllerAction]), true
            );
        }
        return $this->data[$controllerAction];
    }
    
    /**
     * Получить хэш
     * 
     * @param array $params
     * @return string
     */
    public function getHash($params)
    {
        ksort($params);
        return md5(json_encode($params));
    }
    
    /**
     * Удалить блок
     * 
     * @param array $params
     * @param string $controllerAction
     */
    public function reset($params, $controllerAction)
    {
        $hash = $this->getHash($params);
        if (!isset($this->blockVector[$hash])) {
            return null;
        }
        foreach ($this->blockVector[$hash] as $i => $blockAction) {
            if ($controllerAction == $blockAction) {
                unset($this->blockVector[$hash][$i]);
            }
        }
    }
    
    /**
     * Изменить блок
     * 
     * @param string $controllerAction
     * @param array $json
     * @param array $params
     * @param boolean $throwUnitOfWork
     */
    public function set($controllerAction, $json, $params = array(), 
        $throwUnitOfWork = false)
    {
        $hash = $this->getHash($params);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $unitOfWork = $this->getService('unitOfWork');
        $deleteQuery = $queryBuilder
            ->delete()
            ->from('Cache_Block')
            ->where('hash', $hash)
            ->where('controllerAction', $controllerAction);
        if ($throwUnitOfWork) {
            $unitOfWork->push($deleteQuery);
        } else {
            $dds->execute($deleteQuery);
        }
        $insertQuery = $queryBuilder
            ->insert('Cache_Block')
            ->values(array(
                'controllerAction'  => $controllerAction,
                'hash'              => $hash,
                'json'              => urlencode(json_encode($json)),
                'createdAt'         => date('Y-m-d H:i:s')
            ));
        if ($throwUnitOfWork) {
            $unitOfWork->push($insertQuery);
        } else {
            $dds->execute($insertQuery);
        }
    }
}