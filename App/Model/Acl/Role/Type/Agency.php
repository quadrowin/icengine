<?php

namespace Ice;

if (!class_exists ('Acl_Role_Type_Abstract'))
{
    include __DIR__ . '/Abstract.php';
}

class Acl_Role_Type_Agency extends Acl_Role_Type_Abstract
{
    /**
     *
     * @var integer
     */
    const ID = 100;
}