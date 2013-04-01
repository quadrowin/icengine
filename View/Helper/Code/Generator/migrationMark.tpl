<?php

return array(
{if $config}
{foreach from=$config item="value" key="migrationName" name="migrations"}
    '{$migrationName}' => true{if !$smarty.foreach.migrations.last},
    {/if}
{/foreach}
{/if}
);