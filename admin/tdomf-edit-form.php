<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////////////////////////////////////
// Code for the tdomf edit form menu (with drag and drop!) //
/////////////////////////////////////////////////////////////

// Hacked the drag and drop from the widget admin menu in WP2.2!

// Only load edit scripts as needed!
//
function tdomf_load_edit_form_scripts() {
  // Need these scripts for drag and drop but only on this page, not every page!
  wp_enqueue_script( 'scriptaculous-effects' );
  wp_enqueue_script( 'scriptaculous-dragdrop' );
}
add_action("load-tdomf_page_tdomf_show_form_menu","tdomf_load_edit_form_scripts");

// Stuff to do in the header of the page
//
function tdomf_form_admin_head() {
   global $tdomf_form_widgets, $tdomf_form_widgets_control;
   if(preg_match('/tdomf_show_form_menu/',$_SERVER[REQUEST_URI])) {
?>
   <link rel="stylesheet" href="widgets.css?version=<?php bloginfo('version'); ?>" type="text/css" />
	<!--[if IE 7]>
	<style type="text/css">
	#palette {float:left;}
	</style>
	<![endif]-->
   <!-- TODO: how do you calculate w&h? -->
	<style type="text/css">
		.dropzone ul { height: 385px; }
		#sbadmin #zones { width: 263px; }
	</style>
   <script type="text/javascript">
   // <![CDATA[
	var cols = ['tdomf_form-1'];
	var widgets = [<?php foreach($tdomf_form_widgets as $id => $w) { ?>'<?php echo $id; ?>',<?php } ?> ];
	var controldims = new Array;
	<?php foreach($tdomf_form_widgets_control as $id => $w) { ?>
	    controldims['<?php echo $id; ?>control'] = new Array();
    	controldims['<?php echo $id; ?>control']['width'] = <?php echo $w['width']; ?>;
	    controldims['<?php echo $id; ?>control']['height'] = <?php echo $w['height']; ?>;
	<?php } ?>

      function initWidgets() {
        <?php foreach($tdomf_form_widgets_control as $id => $w) { ?>
		$('<?php echo $id; ?>popper').onclick = function() {popControl('<?php echo $id; ?>control');};
		$('<?php echo $id; ?>closer').onclick = function() {unpopControl('<?php echo $id; ?>control');};
		new Draggable('<?php echo $id; ?>control', {revert:false,handle:'controlhandle',starteffect:function(){},endeffect:function(){},change:function(o){dragChange(o);}});
		if ( true && window.opera )
			$('<?php echo $id; ?>control').style.border = '1px solid #bbb';

        <?php } ?>

		if ( true && window.opera )
			$('shadow').style.background = 'transparent';
		new Effect.Opacity('shadow', {to:0.0});
		widgets.map(function(o) {o='widgetprefix-'+o; Position.absolutize(o); Position.relativize(o);} );
		$A(Draggables.drags).map(function(o) {o.startDrag(null); o.finishDrag(null);});
		for ( var n in Draggables.drags ) {
			if ( Draggables.drags[n].element.id == 'lastmodule' ) {
				Draggables.drags[n].destroy();
				break;
			}
		}
		resetPaletteHeight();
	}
	function resetDroppableHeights() {
		var max = 6;
		cols.map(function(o) {var c = $(o).childNodes.length; if ( c > max ) max = c;} );
		var height = 35 * ( max + 1);
		cols.map(function(o) {h = (($(o).childNodes.length + 1) * 35); $(o).style.height = (h > 280 ? h : 280) + 'px';} );
	}
	function resetPaletteHeight() {
		var p = $('palette'), pd = $('palettediv'), last = $('lastmodule');
		p.appendChild(last);
		if ( Draggables.activeDraggable && last.id == Draggables.activeDraggable.element.id )
			last = last.previousSibling;
		var y1 = Position.cumulativeOffset(last)[1] + last.offsetHeight;
		var y2 = Position.cumulativeOffset(pd)[1] + pd.offsetHeight;
		var dy = y1 - y2;
		pd.style.height = (pd.offsetHeight + dy + 9) + "px";
	}
	function maxHeight(elm) {
		htmlheight = document.body.parentNode.clientHeight;
		bodyheight = document.body.clientHeight;
		var height = htmlheight > bodyheight ? htmlheight : bodyheight;
		$(elm).style.height = height + 'px';
	}
	function dragChange(o) {
		el = o.element ? o.element : $(o);
		var p = Position.page(el);
		var right = p[0];
		var top = p[1];
		var left = $('shadow').offsetWidth - (el.offsetWidth + right);
		var bottom = $('shadow').offsetHeight - (el.offsetHeight + top);
		if ( right < 1 ) el.style.left = 0;
		if ( top < 1 ) el.style.top = 0;
		if ( left < 1 ) el.style.left = (left + right) + 'px';
		if ( bottom < 1 ) el.style.top = (top + bottom) + 'px';
	}
	function popControl(elm) {
		el = $(elm);
		el.style.width = controldims[elm]['width'] + 'px';
		el.style.height = controldims[elm]['height'] + 'px';
		var x = ( document.body.clientWidth - controldims[elm]['width'] ) / 2;
		var y = ( document.body.parentNode.clientHeight - controldims[elm]['height'] ) / 2;
		el.style.position = 'absolute';
		el.style.right = '' + x + 'px';
		el.style.top = '' + y + 'px';
		el.style.zIndex = 1000;
		el.className='control';
		$('shadow').onclick = function() {unpopControl(elm);};
	    window.onresize = function(){maxHeight('shadow');dragChange(elm);};
		popShadow();
	}
	function popShadow() {
		maxHeight('shadow');
		var shadow = $('shadow');
		shadow.style.zIndex = 999;
		shadow.style.display = 'block';
	    new Effect.Opacity('shadow', {duration:0.5, from:0.0, to:0.2});
	}
	function unpopShadow() {
	    new Effect.Opacity('shadow', {to:0.0});
		$('shadow').style.display = 'none';
	}
	function unpopControl(el) {
		$(el).className='hidden';
		unpopShadow();
	}
	function serializeAll() {
			$('tdomf_form-1order').value = Sortable.serialize('tdomf_form-1');
		}
	function updateAll() {
		resetDroppableHeights();
		resetPaletteHeight();
		cols.map(function(o){
			var pm = $(o+'placematt');
			if ( $(o).childNodes.length == 0 ) {
				pm.style.display = 'block';
				Position.absolutize(o+'placematt');
			} else {
				pm.style.display = 'none';
			}
		});
	}
	function noSelection(event) {
		if ( document.selection ) {
			var range = document.selection.createRange();
			range.collapse(false);
			range.select();
			return false;
		}
	}
	addLoadEvent(updateAll);
	addLoadEvent(initWidgets);
	Event.observe(window, 'resize', resetPaletteHeight);
// ]]>
</script>
<?php
   }
}
add_action( 'admin_head', 'tdomf_form_admin_head' );

// Show the page!
//
function tdomf_show_form_menu() {
  global $wpdb, $wp_roles, $tdomf_form_widgets, $tdomf_form_widgets_control;

  tdomf_handle_editformmenu_actions();

  $widget_order = get_option(TDOMF_OPTION_FORM_ORDER);

  ?>

<div class="wrap">
		<h2>Form Arrangement</h2>

		<p><?php _e('You can drag-drop, order and configure "widgets" for your form below.',"tdomf"); ?></p>

		<form id="sbadmin" method="post" onsubmit="serializeAll();">
			<div id="zones">
							<input type="hidden" id="tdomf_form-1order" name="tdomf_form-1order" value="" />

				<div class="dropzone">
					<h3>Your Form</h3>

					<div id="tdomf_form-1placematt" class="module placematt">
						<span class="handle">
							<h4>Default Form</h4>
							<?php _e("Your form will be displayed using the default widget order. Dragging widgets into this box will replace the default with your customized form.","tdomf"); ?></span>
					</div>

					<ul id="tdomf_form-1">
					<?php
					if ( is_array( $widget_order ) ) {
						foreach ( $widget_order as $id ) {
						    if(isset($tdomf_form_widgets[$id]['name'])) { ?>
							<li class="module" id="widgetprefix-<?php echo $id; ?>"><span class="handle"><?php echo $tdomf_form_widgets[$id]['name']; ?> <?php if(isset($tdomf_form_widgets_control[$id])) { ?><div class="popper" id="<?php echo $id; ?>popper" title="<?php _e("Configure","tdomf"); ?>">&#8801;</div><?php } ?></span></li>
							<?php }
						}
					} ?>
					</ul>
				</div>

			<br class="clear" />

			</div>

			<div id="palettediv">
				<h3>Available Widgets</h3>

				<ul id="palette">
				<?php foreach($tdomf_form_widgets as $id => $w) {
					if ( !is_array( $widget_order ) || !in_array($id,$widget_order)) {?>
					<li class="module" id="widgetprefix-<?php echo $id; ?>"><span class="handle"><?php echo $w['name']; ?> <?php if(isset($tdomf_form_widgets_control[$id])) { ?><div class="popper" id="<?php echo $id; ?>popper" title="<?php _e("Configure","tdomf"); ?>">&#8801;</div><?php } ?></span></li>
				<?php } } ?>
             <li id="lastmodule"><span></span></li>
				</ul>
			</div>

			<script type="text/javascript">
			// <![CDATA[
							Sortable.create("palette", {
					dropOnEmpty: true, containment: ["palette","tdomf_form-1"],
					handle: 'handle', constraint: false, onUpdate: updateAll,
					format: /^widgetprefix-(.*)$/
				});
							Sortable.create("tdomf_form-1", {
					dropOnEmpty: true, containment: ["palette","tdomf_form-1"],
					handle: 'handle', constraint: false, onUpdate: updateAll,
					format: /^widgetprefix-(.*)$/
				});
						// ]]>
			</script>

			<p class="submit">
			<?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-save-widget-order'); } ?>

            <input type="hidden" name="action" id="action" value="save_widget_order" />
				<input type="submit" value="Save Changes &raquo;" />
			</p>

			<div id="controls">
               <?php foreach($tdomf_form_widgets_control as $id => $w) { ?>
			   <div class="hidden" id="<?php echo $id; ?>control">
				   <span class="controlhandle"><?php echo $tdomf_form_widgets_control[$id]['name']; ?></span>
					<span id="<?php echo $id; ?>closer" class="controlcloser">&#215;</span>
					<div class="controlform">
						<?php $w['cb'](); ?>
                  <input type="hidden" id="<?php echo $id; ?>-submit" name="<?php echo $id; ?>-submit" value="1" />
					</div>
				</div>
               <?php } ?>
         </form>

		<br class="clear" />
	</div>

	<div id="shadow"> </div>

  <?php
}

// Handle actions
//
function tdomf_handle_editformmenu_actions() {
  
  if (get_magic_quotes_gpc()) {
      function stripslashes_array($array) {
          return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
      }
      #$_COOKIE = stripslashes_array($_COOKIE);
      #$_FILES = stripslashes_array($_FILES);
      #$_GET = stripslashes_array($_GET);
      $_POST = stripslashes_array($_POST);
      #$_REQUEST = stripslashes_array($_REQUEST);
    }
  
	if ( isset( $_POST['action'] ) ) {
		switch( $_POST['action'] ) {
			case 'save_widget_order' :
			    check_admin_referer('tdomf-save-widget-order');
			    if(isset($_POST['tdomf_form-1order']) && !empty($_POST['tdomf_form-1order'])) {
					parse_str($_POST['tdomf_form-1order'],$widget_order);
					$widget_order = $widget_order['tdomf_form-1'];
	                update_option(TDOMF_OPTION_FORM_ORDER,$widget_order);
					tdomf_log_message("Saved widget settings for form-1: ".$_POST['tdomf_form-1order'],TDOMF_LOG_GOOD);
				} else {
					$widget_order = tdomf_get_form_widget_default_order();
					delete_option(TDOMF_OPTION_FORM_ORDER);
					tdomf_log_message("Restored default settings for form-1");
				}
                ?> <div id="message" class="updated fade"><p><?php printf(__("Saved Settings. <a href='%s'>See your form &raquo</a>","tdomf"),"users.php?page=tdomf_your_submissions#tdomf_form1"); ?></p></div> <?php
				break;
	 	}
	}
}

?>
