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
  wp_enqueue_script( 'interface' );
}
add_action("load-tdomf_page_tdomf_show_form_menu","tdomf_load_edit_form_scripts");

// Stuff to do in the header of the page
//
function tdomf_form_admin_head() {
   global $tdomf_form_widgets, $tdomf_form_widgets_control;
   if(preg_match('/tdomf_show_form_menu/',$_SERVER[REQUEST_URI])) {
?>
   <?php if(function_exists('wp_admin_css')) {
            wp_admin_css( 'css/widgets' ); 
         } else { 
            // pre-Wordpress 2.3
            ?>
            <link rel="stylesheet" href="widgets.css?version=<?php bloginfo('version'); ?>" type="text/css" />
   <?php } ?>
        <!--[if IE 7]>
        <style type="text/css">
                #palette { float: <?php echo ( get_bloginfo( 'text_direction' ) == 'rtl' ) ? 'right' : 'left'; ?>; }
        </style>
        <![endif]-->
   <script type="text/javascript">
   // <![CDATA[
	var cols = ['tdomf_form-1'];
	var widgets = [<?php foreach($tdomf_form_widgets as $id => $w) { ?>'<?php echo $id; ?>',<?php } ?> ];
	var controldims = new Array;
	<?php foreach($tdomf_form_widgets_control as $id => $w) { ?>
      controldims['#<?php echo $id; ?>control'] = new Array;
      controldims['#<?php echo $id; ?>control']['width'] = <?php echo $w['width']; ?>;
      controldims['#<?php echo $id; ?>control']['height'] = <?php echo $w['height']; ?>;
	<?php } ?>

      function initWidgets() {
        <?php foreach($tdomf_form_widgets_control as $id => $w) { ?>
          jQuery('#<?php echo $id; ?>popper').click(function() {popControl('#<?php echo $id; ?>control');});
          jQuery('#<?php echo $id; ?>closer').click(function() {unpopControl('#<?php echo $id; ?>control');});
          jQuery('#<?php echo $id; ?>control').Draggable({handle: '.controlhandle', zIndex: 1000});
          if ( true && window.opera )
            jQuery('#<?php echo $id; ?>control').css('border','1px solid #bbb');
        <?php } ?>

        jQuery('#shadow').css('opacity','0');
        jQuery(widgets).each(function(o) {o='#widgetprefix-'+o; jQuery(o).css('position','relative');} );
	}
	function resetDroppableHeights() {
		var max = 6;
		jQuery.map(cols, function(o) {
			var c = jQuery('#' + o + ' li').length;
			if ( c > max ) max = c;
		});
		var maxheight = 35 * ( max + 1);
		jQuery.map(cols, function(o) {
			height = 0 == jQuery('#' + o + ' li').length ? maxheight - jQuery('#' + o + 'placemat').height() : maxheight;
			jQuery('#' + o).height(height);
		});
	}
	function maxHeight(elm) {
		htmlheight = document.body.parentNode.clientHeight;
		bodyheight = document.body.clientHeight;
		var height = htmlheight > bodyheight ? htmlheight : bodyheight;
		jQuery(elm).height(height);
	}
	function getViewportDims() {
		var x,y;
		if (self.innerHeight) { // all except Explorer
			x = self.innerWidth;
			y = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			x = document.documentElement.clientWidth;
			y = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			x = document.body.clientWidth;
			y = document.body.clientHeight;
		}
		return new Array(x,y);
	}
	function dragChange(o) {
		var p = getViewportDims();
		var screenWidth = p[0];
		var screenHeight = p[1];
		var elWidth = parseInt( jQuery(o).css('width') );
		var elHeight = parseInt( jQuery(o).css('height') );
		var elLeft = parseInt( jQuery(o).css('left') );
		var elTop = parseInt( jQuery(o).css('top') );
		if ( screenWidth < ( parseInt(elLeft) + parseInt(elWidth) ) )
			jQuery(o).css('left', ( screenWidth - elWidth ) + 'px' );
		if ( screenHeight < ( parseInt(elTop) + parseInt(elHeight) ) )
			jQuery(o).css('top', ( screenHeight - elHeight ) + 'px' );
		if ( elLeft < 1 )
			jQuery(o).css('left', '1px');
		if ( elTop < 1 )
			jQuery(o).css('top', '1px');
	}
	function popControl(elm) {
		var x = ( document.body.clientWidth - controldims[elm]['width'] ) / 2;
		var y = ( document.body.parentNode.clientHeight - controldims[elm]['height'] ) / 2;
		jQuery(elm).css({display: 'block', width: controldims[elm]['width'] + 'px', height: controldims[elm]['height'] + 'px', position: 'absolute', right: x + 'px', top: y + 'px', zIndex: '1000' });
		jQuery(elm).attr('class','control');
		jQuery('#shadow').click(function() {unpopControl(elm);});
		window.onresize = function(){maxHeight('#shadow');dragChange(elm);};
		popShadow();
	}
	function popShadow() {
		maxHeight('#shadow');
		jQuery('#shadow').css({zIndex: '999', display: 'block'});
		jQuery('#shadow').fadeTo('fast', 0.2);
	}
	function unpopShadow() {
		jQuery('#shadow').fadeOut('fast', function() {jQuery('#shadow').hide()});
	}
	function unpopControl(el) {
		jQuery(el).attr('class','hidden');
		jQuery(el).hide();
		unpopShadow();
	}
	function serializeAll() {
			var serial1 = jQuery.SortSerialize('tdomf_form-1');
		jQuery('#tdomf_form-1order').attr('value',serial1.hash.replace(/widgetprefix-/g, ''));
		}
	function updateAll() {
		jQuery.map(cols, function(o) {
			if ( jQuery('#' + o + ' li').length )
				jQuery('#'+o+'placemat span.handle').hide();
			else
				jQuery('#'+o+'placemat span.handle').show();
		});
		resetDroppableHeights();
	}
	jQuery(document).ready( function() {
		updateAll();
		initWidgets();
	});
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

  <?php do_action( 'tdomf_widget_page_top' ); ?>


<div class="wrap">
		<h2>Form Arrangement</h2>

		<p><?php _e('You can drag-drop, order and configure "widgets" for your form below.',"tdomf"); ?></p>

		<form id="sbadmin" method="post" onsubmit="serializeAll();">
			<p class="submit">
				<input type="submit" value="Save Changes &raquo;" />
			</p>
			<div id="zones">
							<input type="hidden" id="tdomf_form-1order" name="tdomf_form-1order" value="" />

				<div class="dropzone">
					<h3>Your Form</h3>

					<div id="tdomf_form-1placemat" class="placemat">
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

  		</div>

			<div id="palettediv">
				<h3>Available Widgets</h3>

				<ul id="palette">
        				
				<?php foreach($tdomf_form_widgets as $id => $w) {
					if ( !is_array( $widget_order ) || !in_array($id,$widget_order)) {?>
					<li class="module" id="widgetprefix-<?php echo $id; ?>"><span class="handle"><?php echo $w['name']; ?> <?php if(isset($tdomf_form_widgets_control[$id])) { ?><div class="popper" id="<?php echo $id; ?>popper" title="<?php _e("Configure","tdomf"); ?>">&#8801;</div><?php } ?></span></li>
				<?php } } ?>
				</ul>
			</div>

			<script type="text/javascript">
			// <![CDATA[
				jQuery(document).ready(function(){
								jQuery('ul#palette').Sortable({
						accept: 'module', activeclass: 'activeDraggable', opacity: 0.8, revert: true, onStop: updateAll
					});
								jQuery('ul#tdomf_form-1').Sortable({
						accept: 'module', activeclass: 'activeDraggable', opacity: 0.8, revert: true, onStop: updateAll
					});
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
						<?php $w['cb']($tdomf_form_widgets_control[$id]['params']); ?>
                  <input type="hidden" id="<?php echo $id; ?>-submit" name="<?php echo $id; ?>-submit" value="1" />
					</div>
				</div>
               <?php } ?>
         </form>

		<br class="clear" />
	</div>

	<div id="shadow"> </div>

  <?php do_action( 'tdomf_widget_page_bottom' ); ?>
  
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
					tdomf_log_message_extra("Saved widget settings for form-1: ".$_POST['tdomf_form-1order'],TDOMF_LOG_GOOD);
				} else {
					$widget_order = tdomf_get_form_widget_default_order();
					delete_option(TDOMF_OPTION_FORM_ORDER);
					tdomf_log_message("Restored default settings for form-1");
				}
        if(get_option(TDOMF_OPTION_YOUR_SUBMISSIONS)) {
                ?> <div id="message" class="updated fade"><p><?php printf(__("Saved Settings. <a href='%s'>See your form &raquo</a>","tdomf"),"users.php?page=tdomf_your_submissions#tdomf_form1"); ?></p></div> <?php
        } else {
                ?> <div id="message" class="updated fade"><p><?php _e("Saved Settings.","tdomf"); ?></p></div> <?php
        }
				break;
	 	}
	}
}

?>
