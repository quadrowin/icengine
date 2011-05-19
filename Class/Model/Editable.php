<?php

/**
 *
 * @author dp
 */
interface Model_Editable
{
    public function edit ($value, $key, $id = null);

    public function editAuthor ($user_id, $edit_ids, $fio = null, $phone = null, $status = null);
}

?>
