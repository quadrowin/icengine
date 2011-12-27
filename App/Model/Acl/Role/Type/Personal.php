<?php

namespace Ice;

if (!class_exists ('Acl_Role_Type_Abstract'))
{
    include __DIR__ . '/Abstract.php';
}

class Acl_Role_Type_Personal extends Acl_Role_Type_Abstract
{

    const ID = 1;

}