{dblbracer}
<script type="text/javascript">
	var mapLoaded = false, table, row_id;

	// Показываем карту
	function ShowMap (cont_id, t, rid)
	{
		table = t;
		row_id = rid;

		if (!mapLoaded)
		{
			editor.init (
				'#' + cont_id, 
				{{$long}}, 
	{{$lat}}, 
				11
			);
			
			mapLoaded = true;
		}
	}
	
	{{assign var="cdir" value=$smarty.current_dir}}
	
	var this_record_layer = {
		objects: [	
			{{if $geo_points->count()}}
	{{include file="$cdir/includes/points.tpl"
					type="Placemark"
					source=$geo_points
	}}
			
	{{if $geo_polylines->count() || $geo_polygons->count()}},{{/if}}
			
	{{/if}}			
			
	{{if $geo_polylines->count()}}
	{{include file="$cdir/includes/points.tpl"
					type="Polyline"
					source=$geo_polylines
	}}
				
	{{if $geo_polygons->count()}},{{/if}}
			
	{{/if}}
				
	{{if $geo_polygons->count()}}
	{{include file="$cdir/includes/points.tpl"
					type="Polygon"
					source=$geo_polygons
	}}
	{{/if}}
		],
		
		styles: [
	{{include file="$cdir/includes/styles.tpl"
				source=$styles	
	}}
		]
	};
	
	var editor = 
	{
		// predifined placemarks styles
		predefinedStyles : [
			{{foreach from=$style_collection item="style" name="style"}} {
				style:'{{$style->name}}',
				url:'{{$style->data}}'
			},
	{{if !$smarty.foreach.style.iteration}},{{/if}}
	{{/foreach}}
		],
	{{include file="$cdir/includes/js.tpl"}}
	}
	
	$(function ()
	{
		ShowMap ('editor', '{{$table}}', {{$row_id}});

		setTimeout (
			function ()
	{
				$('#editor').prependTo ($('#tab-geo'));
	},
			1000
		);
	});
</script>
{/dblbracer}

<style>
	#editor {
float:left;
width: 70%;
	}
	#editor-edit {
float:right;
width: 30%;
	}

	#editor-edit * {
margin: 5px;
	}
	#editor-clear {
clear:both;
	}
</style>

<div id="editor" style="height:600px"></div> 

<div id="tab-geo"> 
	<div id="editor-edit">
		<p>Редактирование объекта:</p>

		<div id="toolsBox" style="background-color:#f3f3f3;"> 
			<form action="javascript:return false;" onsubmit="return false;"> 
				<div id="tools" style="padding:2pt;"> 
					<p class="layer" style="background-color:#f3f3f3;margin:0;/*padding:5px;*/"> 
						<input class="save" type="hidden" value="Сохранить слой" style="width:185px;" /> 
					<p><span id = "saveGeoObject" class = "pseudo-link">Cохранить геоданные</span></p>
					<div><input type="text" name="layerName" class="layerName" size="30" style="width:185px;" /></div>
					<div><input type="text" name="layerContent" class="layerContent" /></div>
					</p> 
					<p class="baloon"> 
					<p>Имя <input type="text" name="name" class="overlayName" size="30" style="width:185px;" /></p>    
					<p>Описание <textarea name="desc" class="overlayDescription mceNoEditor" rows="2" cols="30" style="width:185px;"></textarea></p> 

					</p> 
					<table style="width:185px;" class="remover"> 
						<tbody> 
							<tr> 
								<td colspan="2">Объект</td> 
							</tr> 

							<tr> 
								<td style="text-align:left;"><input class="show" type="button" value="Показать" /></td> 
								<td style="text-align:right;"><input class="remove" type="button" value="Удалить" /></td> 
							</tr> 
						</tbody> 
					</table> 
				</div> 
			</form> 
		</div> 
	</div>
	<div id="editor-clear">&nbsp;</div>
</div> 
