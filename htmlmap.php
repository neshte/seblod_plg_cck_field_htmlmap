<?php
/**
* @version 			SEBLOD 3.x More
* @package			SEBLOD (App Builder & CCK) // SEBLOD nano (Form Builder)
* @url				http://www.seblod.com
* @editor			Octopoos - www.octopoos.com
* @copyright		Copyright (C) 2013 SEBLOD. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

defined( '_JEXEC' ) or die;

// Plugin
class plgCCK_FieldHtmlmap extends JCckPluginField
{
	protected static $type		=	'htmlmap';
	protected static $path;
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Construct
	
	// onCCK_FieldConstruct
	public function onCCK_FieldConstruct( $type, &$data = array() )
	{
		if ( self::$type != $type ) {
			return;
		}
		parent::g_onCCK_FieldConstruct( $data );
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Prepare
	
	// onCCK_FieldPrepareContent
	public function onCCK_FieldPrepareContent( &$field, $value = '', &$config = array() )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		parent::g_onCCK_FieldPrepareContent( $field, $config );
		
		// Set
		$field->value	=	$value;
	}
	
	// onCCK_FieldPrepareForm
	public function onCCK_FieldPrepareForm( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		self::$path	=	parent::g_getPath( self::$type.'/' );
		parent::g_onCCK_FieldPrepareForm( $field, $config );
		
		// Init
		if ( count( $inherit ) ) {
			$id		=	( isset( $inherit['id'] ) && $inherit['id'] != '' ) ? $inherit['id'] : $field->name;
			$name	=	( isset( $inherit['name'] ) && $inherit['name'] != '' ) ? $inherit['name'] : $field->name;
		} else {
			$id		=	$field->name;
			$name	=	$field->name;
		}

		$field->defaultvalue = '{"imageUrl":"","areas":[{"href":"","coords":[]}]}';
		$value		= ( json_decode($value) != NULL ) ? json_decode($value) : json_decode($field->defaultvalue);
		$value_raw 	= json_encode($value);
		
		// Validate
		$validate	=	'';
		if ( $config['doValidation'] > 1 ) {
			plgCCK_Field_ValidationRequired::onCCK_Field_ValidationPrepareForm( $field, $id, $config );
			parent::g_onCCK_FieldPrepareForm_Validation( $field, $id, $config );
			$validate	=	( count( $field->validate ) ) ? ' validate['.implode( ',', $field->validate ).']' : '';
		}
		
		// Prepare
		$class	=	'inputbox text'.$validate . ( $field->css ? ' '.$field->css : '' );
		//$maxlen	=	( $field->maxlength > 0 ) ? ' maxlength="'.$field->maxlength.'"' : '';
		
		$xml	=	'
					<form>
						<field
							type="media"
							name="'.$name.'"
							id="img_'.$id.'"
							label="'.htmlspecialchars( $field->label ).'"
							class="'.$class.'"
							size="10"
						/>
					</form>
				';
		
		$imgurl = "";
		if(isset($value->imageUrl)){
			$imgurl = $value->imageUrl;
		}
		
		$imginput	=	JForm::getInstance( $id, $xml );
		$imginput	=	$imginput->getInput( $name, '', $imgurl );
		
		$attr	=	'class="'.$class.'" size="'.$field->size.'"'./*$maxlen .*/ ( $field->attributes ? ' '.$field->attributes : '' );
		$form	=	'<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.htmlentities($value_raw).'" '.$attr.' />';
		$form	=	'
					<div id="imgmap_'.$id.'">
						<div class="htmlimgmap_img">'.$imginput.'</div>
						<div>'.$form.'</div>
						<div class="htmlimgmap_ctrls">
							<button class="btn remove"><span class="icon-remove"></span> remove</button>
							<button class="btn reset"><span class="icon-loop"></span> reset</button>
							<button class="btn add"><span class="icon-save-new"></span> add</button>
						</div>
						<div class="htmlimgmap"></div>
						<div class="htmlimgmap_layers">
							<ul class="sortable"></ul>
						</div>
					</div>
					';
		
		// Set
		if ( ! $field->variation ) {
			$field->form	=	$form;
			self::_addScripts( $id, array(), $config, $value_raw );
			if ( $field->script ) {
				parent::g_addScriptDeclaration( $field->script );
			}
		} else {
			parent::g_getDisplayVariation( $field, $field->variation, $value, $value, $form, $id, $name, '<input', '', '', $config );
		}
		$field->value	=	$value;
		
		// Return
		if ( $return === true ) {
			return $field;
		}
	}
	
	// onCCK_FieldPrepareSearch
	public function onCCK_FieldPrepareSearch( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		
		// Prepare
		self::onCCK_FieldPrepareForm( $field, $value, $config, $inherit, $return );
		
		// Return
		if ( $return === true ) {
			return $field;
		}
	}
	
	// onCCK_FieldPrepareStore
	public function onCCK_FieldPrepareStore( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		
		// Init
		if ( count( $inherit ) ) {
			$name	=	( isset( $inherit['name'] ) && $inherit['name'] != '' ) ? $inherit['name'] : $field->name;
		} else {
			$name	=	$field->name;
		}
		
		// Validate
		parent::g_onCCK_FieldPrepareStore_Validation( $field, $name, $value, $config );
		
		// Set or Return
		if ( $return === true ) {
			return $value;
		}
		$field->value	=	$value;
		parent::g_onCCK_FieldPrepareStore( $field, $name, $value, $config );
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Render
	
	// onCCK_FieldRenderContent
	public static function onCCK_FieldRenderContent( $field, &$config = array() )
	{
		return parent::g_onCCK_FieldRenderContent( $field );
	}
	
	// onCCK_FieldRenderForm
	public static function onCCK_FieldRenderForm( $field, &$config = array() )
	{
		return parent::g_onCCK_FieldRenderForm( $field );
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Special Events
	
	// onCCK_FieldBeforeRenderContent
	public static function onCCK_FieldBeforeRenderContent( $process, &$fields, &$storages, &$config = array() )
	{
	}
	
	// onCCK_FieldBeforeRenderForm
	public static function onCCK_FieldBeforeRenderForm( $process, &$fields, &$storages, &$config = array() )
	{
	}
	
	// onCCK_FieldBeforeStore
	public static function onCCK_FieldBeforeStore( $process, &$fields, &$storages, &$config = array() )
	{
	}
	
	// onCCK_FieldAfterStore
	public static function onCCK_FieldAfterStore( $process, &$fields, &$storages, &$config = array() )
	{
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Stuff & Script
	
	// _addScripts
	protected static function _addScripts( $id, $params = array(), &$config = array(), $value_raw )
	{
		JHtml::_('jquery.ui');
		JHtml::_('jquery.ui', array('sortable'));
		$doc	=	JFactory::getDocument();
		$lang	=	JFactory::getLanguage();
		
		$js		=	'
					(function($){
						$(document).ready(function(){
							var defaultval = '.$value_raw.';
							function refreshMapsState(){
								var imageUrl = htmlmap.htmlimagemap("getImageUrl");
			    				var areas = htmlmap.htmlimagemap("getAreas");
								var value = {
									imageUrl: imageUrl,
									areas: areas
								};
			    				$("#'.$id.'").val(JSON.stringify(value));
			    			}
			    			function resetLayers(){
			    				$("#imgmap_'.$id.' .htmlimgmap_layers .sortable li").remove();
				    			for(var i=0; i<defaultval.areas.length; i++){
				    				$("#imgmap_'.$id.' .htmlimgmap_layers .sortable").append($("<li>").append($("<span>",{"class":"icon-menu"})).append($("<input>",{"type":"text","placeholder":"http://www.example.com/","data-coords":defaultval.areas[i].coords.join()}).val(defaultval.areas[i].href)));
				    			}
			    			}
			    			function hide(duration){
			        			if($("#imgmap_'.$id.' .htmlimgmap_ctrls").is(":visible")){
			        				$("#imgmap_'.$id.' .htmlimgmap_ctrls").hide(duration);
			        			}
			        			if($("#imgmap_'.$id.' .htmlimgmap").is(":visible")){
			        				$("#imgmap_'.$id.' .htmlimgmap").hide(duration);
			        			}
			        			if($("#imgmap_'.$id.' .htmlimgmap_layers").is(":visible")){
			        				$("#imgmap_'.$id.' .htmlimgmap_layers").hide(duration);
			        			}
			        		}
			        		function show(duration){
			        			if(!$("#imgmap_'.$id.' .htmlimgmap_ctrls").is(":visible")){
			        				$("#imgmap_'.$id.' .htmlimgmap_ctrls").show(duration);
			        			}
			        			if(!$("#imgmap_'.$id.' .htmlimgmap").is(":visible")){
			        				$("#imgmap_'.$id.' .htmlimgmap").show(duration);
			        			}
			        			if(!$("#imgmap_'.$id.' .htmlimgmap_layers").is(":visible")){
			        				$("#imgmap_'.$id.' .htmlimgmap_layers").show(duration);
			        			}
			        		}
			    			var htmlmap = $("#imgmap_'.$id.' .htmlimgmap").htmlimagemap({
			    				imageUrl: defaultval.imageUrl,
			    				areas: defaultval.areas,
			    				onMove: function(area){
			        			},
			    				onUpdateArea: function(area){
			        				$("#imgmap_'.$id.' .htmlimgmap_layers .sortable li").eq(htmlmap.htmlimagemap("getActiveAreaIndex")).find("input").attr("data-coords",area.coords.join());
			        				refreshMapsState();
			        			}
							});
			        		$("#img_'.$id.'")[0].onchange = function(){
								var imgurl = $("#img_'.$id.'").val();
								if(typeof htmlmap != "undefined" && imgurl != ""){
									show("fast");
									htmlmap.htmlimagemap("setImageUrl","/"+$("#img_'.$id.'").val());
								}else{
									hide("fast");
									htmlmap.htmlimagemap("setImageUrl","");
									htmlmap.htmlimagemap("setAreas",defaultval.areas);
								}
								refreshMapsState();
							};
			    			$("#imgmap_'.$id.' .remove").click(function(e){
			    				e.preventDefault();
			    				var i = htmlmap.htmlimagemap("getActiveAreaIndex");
			    				htmlmap.htmlimagemap("removeActiveArea");
			    				if($("#imgmap_'.$id.' .htmlimgmap_layers .sortable li").length > 1){
			    					$("#imgmap_'.$id.' .htmlimgmap_layers .sortable li").eq(i).remove();
			    				}
			    			});
			    			$("#imgmap_'.$id.' .reset").click(function(e){
			    				e.preventDefault();
			    				htmlmap.htmlimagemap("resetActiveArea");
			    			});
			    			$("#imgmap_'.$id.' .add").click(function(e){
			    				e.preventDefault();
			    				var i = htmlmap.htmlimagemap("getAreas").length;
			    				htmlmap.htmlimagemap("setActiveAreaIndex",i);
			    				$("#imgmap_'.$id.' .sortable").append($("<li>").append($("<span>",{"class":"icon-menu"})).append($("<input>",{"type":"text","placeholder":"http://www.example.com/","data-coords":htmlmap.htmlimagemap("getActiveArea").coords.join()}).val(htmlmap.htmlimagemap("getActiveArea").href)));
			    			});
			    			$("#imgmap_'.$id.' .htmlimgmap_layers .sortable").on("input focus propertychange change keyup paste", "input", function(){
			    				var i = $(this).parent().index();
			    				var area = htmlmap.htmlimagemap("getArea",i);
			    				area.href = $(this).val();
			    				htmlmap.htmlimagemap("setActiveAreaIndex",i);
			    				htmlmap.htmlimagemap("setArea",i,area);
			    				refreshMapsState();
			    			});
			    			$("#imgmap_'.$id.' .htmlimgmap_layers .sortable").sortable({
			    				update: function(e,ui){
			    					var areas = [];
			    					$("#imgmap_'.$id.' .htmlimgmap_layers .sortable li").each(function(i,v){
			    						var area = htmlmap.htmlimagemap("getArea",i);
			    						var coords = $(v).find("input").attr("data-coords").split(\',\');
			    						if(coords.length % 2 == 0){
			    							area.coords = $.map(coords, function(value){
			        							return parseInt(value, 10);
			    							});
			    						}else{
			    							area.coords = [];
			    						}
			    						area.href = $(v).find("input").val();
			    						areas.push(area);
			    					});
			    					htmlmap.htmlimagemap("setAreas",areas);
			    					refreshMapsState();
			        			}
			    			});
			    			
				    		resetLayers();
			    			if(htmlmap.htmlimagemap("getImageUrl") != ""){
			    				show(0);
			    			}else{
			    				hide(0);
			    			}
			    			refreshMapsState();
						});
					})(jQuery);
			    	
					';
		
		$css = '
				.htmlimgmap_ctrls{
					padding:5px 0;
				}
				.htmlimgmap_layers{
					padding:5px 0;
				}
				.htmlimgmap_layers ul{
					list-style:none;
					padding:0;
					margin:0;
				}
				.htmlimgmap_img input{
					font-size:13px !important;
				}
		';
	
		if ( isset( $config['tmpl'] ) && $config['tmpl'] == 'ajax' ) {
			echo '<style type="text/css">'.$css.'</style>';
			echo '<script type="text/javascript">'.$js.'</script>';
		} else {
			$doc->addStyleDeclaration( $css );
			$doc->addScript( self::$path.'assets/js/excanvas.js' );
			$doc->addScript( self::$path.'assets/js/jquery.canvasAreaDraw.js' );
			$doc->addScriptDeclaration( $js );
		}
	}
	
	//
}
?>