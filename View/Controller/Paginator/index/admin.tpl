<p>{foreach from=$paginator->pages item="page"}
{if $page.selected}<b>{$page.title}</b>{else}<a href="{$page.href}">{$page.title}</a>{/if} 
{/foreach}</p>