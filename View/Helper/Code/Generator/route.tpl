<?php


return array(
{if $empty_route}
    'empty_route'   => array(
{foreach from=$empty_route item="i" key="field" name="field"}
        '{$field}'          => {if !is_array($i)}{if is_numeric($i)}{$i}{else}'{$i}'{/if}{if !$smarty.foreach.field.last},
{/if}
{else}array(
{foreach from=$i item="j" key="subfield" name="subfield"}
            '{$subfield}'   => {if is_numeric($j)}{$j}{else}'{$j}'{/if}{if !$smarty.foreach.subfield.last},
            {/if}
{/foreach}

        ){if !$smarty.foreach.field.last},
        {/if}
{/if}
{/foreach}

    ),
{/if}
    'routes'        => array(
{foreach from=$routes item="route" name="routes" key="routeName"}
{if is_numeric($routeName)}
        array(
{else}
        '{$routeName}'   => array(
{/if}

{foreach from=$route item="i" name="field" key="field"}
            '{$field}'          => {if !is_array($i)}{if is_numeric($i)}{$i}{else}'{$i}'{/if}{if !$smarty.foreach.field.last},
{/if}

{else}array(
{foreach from=$i item="j" key="subfield" name="subfield"}
{if is_numeric($subfield)}
                '{$j}'
{else}
                '{$subfield}'   => {if !is_array($j)}{if is_numeric($j)}{$j}{else}'{$j}'{/if}{if !$smarty.foreach.subfield.last},
{/if}
{else}
    array(
{foreach from=$j item="k" key="subSubField" name="subSubField"}
                    '{$subSubField}'    => {if is_numeric($k)}{$k}{else}'{$k}'{/if}{if !$smarty.foreach.subSubField.last},
{/if}

{/foreach}
                ){if !$smarty.foreach.subfield.last},
{/if}
{/if}
{/if}
{/foreach}

            ){if !$smarty.foreach.field.last},
{/if}
{/if}
{/foreach}

        ){if !$smarty.foreach.routes.last},
{/if}
{/foreach}
    )
);