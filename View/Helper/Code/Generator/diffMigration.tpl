$modelName = '{$modelName}';
$dataSource = $this->getService('modelScheme')->dataSource($modelName);
$queryBuilder = $this->getService('query');
{foreach from=$migrations key="i" item="m"}
{$data=$m->getPart(Query::ALTER_TABLE)}
{$fullPart=$data[0]}
{$method=key($fullPart)}
{$part=reset($fullPart)} 
{$fieldName=$part[Query::FIELD]}
{if $method==Query::CHANGE}
    {$attr=$part[Query::ATTR]}
    {$fieldName=$attr[Query::NAME]}
{/if}
$field{$i} = new \Model_Field('{$fieldName}');
{if $method!=Query::DROP} 
{$attr=$part[Query::ATTR]}
$field{$i}->setType('{$attr[Model_Field::ATTR_TYPE]}');
{if !empty($attr[Model_Field::ATTR_SIZE])}
$field{$i}->setSize({$attr[Model_Field::ATTR_SIZE]});
{/if}
{if isset($attr[Model_Field::ATTR_DEFAULT])}
$field{$i}->setDefault('{$attr[Model_Field::ATTR_DEFAULT]}');    
{/if}
{if !empty($attr[Model_Field::ATTR_COMMENT])}
$field{$i}->setComment('{$attr[Model_Field::ATTR_COMMENT]}');    
{/if}
{if empty($attr[Model_Field::ATTR_NULL])}
$field{$i}->setNullable(true);
{else}
$field{$i}->setNullable(false);
{/if}
{if !empty($attr[Model_Field::ATTR_AUTO_INCREMENT])}
$field{$i}->setAutoIncrement(true); 
{/if}
{if !empty($attr[Model_Field::ATTR_UNSIGNED])}
$field{$i}->setUnsigned(true);    
{/if}
{/if}
$query{$i} = $queryBuilder
    ->alterTable($modelName)
{if $method==Query::DROP}
    ->drop($field{$i});
{elseif $method==Query::ADD}
    ->add($field{$i});
{else}
    {$oldFieldName=$part[Query::FIELD]}
    ->change('{$oldFieldName}', $field{$i});
{/if}
$dataSource->execute($query{$i});
{/foreach}