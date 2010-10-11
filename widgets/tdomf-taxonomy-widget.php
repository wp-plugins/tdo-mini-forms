<?php
/*
Name: "Taxonomy"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: Use a custom taxonomy
Version: 1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

  /** 
   * Taxonomy
   * 
   * @author Mark Cunningham <tdomf@thedeadone.net> 
   * @version 1 
   * @since 0.13.9
   * @access public 
   * @copyright Mark Cunningham
   * 
   */ 
  class TDOMF_WidgetTaxonomy extends TDOMF_Widget
  {
      /** 
       * Initilise and start widget
       * 
       * @access public
       */ 
      function TDOMF_WidgetTaxonomy() {
          //$this->enableHack();
          //$this->enablePreview();
          //$this->enablePreviewHack();
          //$this->enableValidate();
          //$this->enableValidatePreview();
          //$this->enablePost();
          //$this->enableAdminEmail();
          //$this->enableAdminError();
          $this->setInternalName('taxonomy','-');
          $this->enableWidgetTitle();
          $this->enableControl(true, 500, 520);
          $this->setDisplayName(__('Taxonomy','tdomf')); 
          $this->enableMultipleInstances(true,__('Taxonomy %d','tdomf'));
          $this->start();
      }

      /**
       * Overrides "getOptions" with defaults for this widget
       * 
       * @access public
       * @return array
       */
      function getOptions($form_id,$postfix='') {
          $defaults = array( 
              // taxonomy this widget uses
              'taxonomy' => false, 
              // overwrite previousily set terms for this taxonomy
              'overwrite' => false,
              // allowed to add new terms
              'user' => false,
              // must select or add at least X term
              'required' => false, // 0 or false means no limit
              // default terms (use slugs)
              'default' => array(),
              // display existing terms:
              'display' => true,
              // - display terms as
              // -- dropdown, list, checkboxes as list, (tag cloud?)
              'display_type' => 'dropdown', // list, checkbox (tag_cloud?)
              // -- order by id, name or number of posts
              'orderby' => 'ID', // name, (number of posts?)
              // -- order in ascending or descending order
              'order' => 'asc', // DESC
              // -- limit to first X terms (not compatible with hierarchical mode)
              'limit' => false, // 0 or false means no limit
              // -- hierarchical mode (if applicable, for taxonomy, dropdown, list or checkboxes)
              'hierarchical' => true,
              // - exclude or limit to
              'filter' => 'none', // 'exclude', 'limit'
              // -- list of terms (use slugs)
              'filter_list' => array(),     
          );
          $options = TDOMF_Widget::getOptions($form_id,$postfix); 
          $options = wp_parse_args($options, $defaults);
          return $options;
      }   
      
      /**
       * Configuration panel for widget
       * 
       * @access public
       */      
      function control($options,$form_id,$postfixOptionKey='',$postfixInternalName='') {
          
          // Store settings for this widget
          //
          if ( $_POST[$this->internalName.$postfixInternalName.'-submit'] ) {
                 $newoptions['overwrite'] = $_POST[$this->internalName.'overwrite'.$postfixOptionKey];
                 $options = wp_parse_args($newoptions, $options);
                 $this->updateOptions($options,$form_id,$postfixOptionKey);
          }

          // Display control panel for this widget
          //
          extract($options);
        ?>
<div>

<?php $this->controlCommon($options,$postfixOptionKey); ?>

<br/>

        <?php
        
        $use_page = tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id);
        if($use_page) { $object_type = 'page'; }
        else { $object_type = 'post'; }
        // sadly get_taxonomies does not filter on object_type (3.0.1)
        $taxonomies = get_object_taxonomies($object_type, 'objects');
        $taxonomies = wp_filter_object_list($taxonomies, array('public' => true, 'show_ui' => true));
        ?>
        
<p><?php _e("What are taxonomies?","tdomf"); ?></p>
<br/><br/>

        <?php if(count($taxonomies) == 0) { ?>
            
<p><strong><?php _e("No taxonomies avaliable for this post type!","tdomf"); ?></strong></p>

        <?php } else { ?>
            
        <!-- <pre><?php var_dump($taxonomies); ?></pre> -->
         
        <label for="<?php echo $this->internalName?>taxonomy<?php echo $postfixOptionKey?>">   
        <?php _e('Taxonomy:','tdomf'); ?>
        </label>
        
        <select name="<?php echo $this->internalName?>taxonomy<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>taxonomy<?php echo $postfixOptionKey?>"" size="1" >
            <?php foreach( $taxonomies as $tax ) {?>
                <option value="<?php echo $tax->name; ?>"><?php echo $tax->labels->name; ?></option>
            <?php } ?>
        </select>
        
<?php /*
<label for="Taxonomy-message<?php echo $postfixOptionKey; ?>" ><?php _e("Message to Taxonomy to post content:","tdomf"); ?><br/>
</label>
<textarea cols="50" rows="6" id="Taxonomy-message<?php echo $postfixOptionKey; ?>" name="Taxonomy-message<?php echo $postfixOptionKey; ?>" ><?php echo htmlentities($options['message'],ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
*/ ?>

        <?php } ?>

</div>
<?php
      }
      
    /** 
     * What the widget does when the submission is being posted
     * 
     * @return Mixed 
     * @access public
     */        
    function post($args,$options,$postfix='') {
        extract($args);
        // todo
        return NULL;
    }         
      
  }

  // Create and start the widget
  //
  global $tdomf_widget_taxonomy;
  $tdomf_widget_taxonomy = new TDOMF_WidgetTaxonomy();

?>
