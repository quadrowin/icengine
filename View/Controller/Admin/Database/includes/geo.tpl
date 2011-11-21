{assign var="rowId" value=$row->sfield($row->keyField())}
{if $rowId==null}
	{assign var="rowId" value=0}
{/if}

{Controller call="Yandex_Map/includeScript"}
{Controller call="Admin_Database_Map/index" table=$table row_id=$rowId}