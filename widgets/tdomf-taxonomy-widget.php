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
              'required' => '0', // 0 or false means no limit
              // user can select more than one item
              'max' => false,
              // default terms (use slugs)
              //'default' => array(),
              'default' => '',
              // display existing terms:
              'display' => true,
              // - display terms as
              // -- dropdown, list, checkboxes as list, (tag cloud?)
              'display_type' => 'dropdown', // list, checkbox (tag_cloud?)
              // -- order by id, name or number of posts
              'orderby' => 'id', // name, (count: number of posts?)
              // -- order in ascending or descending order
              'order' => 'asc', // DESC
              // -- limit to first X terms (not compatible with hierarchical mode)
              'limit' => '0', // 0 or false means no limit
              // -- hierarchical mode (if applicable, for taxonomy, dropdown, list or checkboxes)
              'hierarchical' => true,
              // - exclude or limit to
              'filter' => 'none', // 'exclude', 'limit'
              // -- list of terms (use slugs)
              //'filter_list' => array(),
              'filter_list' => ''
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

<p><?php _e("A taxonomy is a grouping mechanism for some posts (or links). Categories and Tags are two builtin taxonomies, but you can <a href=\"http://codex.wordpress.org/Custom_Taxonomies\">create your own with Wordpress 3.x</a>. You can use this widget to display and use taxonomies (including Categories and Tags) on your form.","tdomf"); ?></p>

        <?php
        
        $use_page = tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id);
        if($use_page) { $object_type = 'page'; }
        else { $object_type = 'post'; }
        // sadly get_taxonomies does not filter on object_type (3.0.1)
        $taxonomies = get_object_taxonomies($object_type, 'objects');
        $taxonomies = wp_filter_object_list($taxonomies, array('public' => true, 'show_ui' => true));
        ?>
        
<br/>

        <?php if(count($taxonomies) == 0) { ?>
            
<p><strong><?php _e("No taxonomies avaliable for this post type!","tdomf"); ?></strong></p>

        <?php } else { ?>
        
        <?php $deftax = current($taxonomies);
              foreach( $taxonomies as $tax ) {
                  if($tax->name == $options['taxonomy']) { 
                      $deftax = $tax; 
                      break;
                  }
              } ?>
            
            
        <?php $this->controlCommon($options,$postfixOptionKey); ?>
        
        <!-- <pre><?php var_dump($taxonomies); ?></pre> -->
         
        <br/>
        
        <script type="text/javascript">
         //<![CDATA[
        function tdomf_<?php echo $this->internalName; ?>updateTaxonomy<?php echo $postfixOptionKey; ?>(taxSel) {
           var tax = taxSel.options[taxSel.options.selectedIndex].value;
           var heirarchical = true;
           <?php foreach( $taxonomies as $t ) {?>
           if(tax == "<?php echo $t->name; ?>")
           {
           <?php if($t->hierarchical == true) { ?>
              heirarchical = true;
           <?php } else { ?>
              heirarchical = false;
           <?php } ?>   
           }
           <?php } ?>
           // enable/disable heirarchical mode if the taxonomy supports/does not support it
           document.getElementById("<?php echo $this->internalName?>hierarchical<?php echo $postfixOptionKey?>").disabled = !heirarchical;
        }
        //-->
        </script>
        
        <label for="<?php echo $this->internalName?>taxonomy<?php echo $postfixOptionKey?>">   
        <?php _e('Taxonomy:','tdomf'); ?>
        </label>
        
        <select name="<?php echo $this->internalName?>taxonomy<?php echo $postfixOptionKey?>" 
                id="<?php echo $this->internalName?>taxonomy<?php echo $postfixOptionKey?>" 
                size="1" 
                onChange="tdomf_<?php echo $this->internalName?>updateTaxonomy<?php echo $postfixOptionKey?>(this);">
            <?php foreach( $taxonomies as $tax ) {?>
                <option value="<?php echo $tax->name; ?>"<?php if($tax->name == $deftax->name) { ?> selected="selected"<?php } ?>><?php echo $tax->labels->name; ?></option>
            <?php } ?>
        </select>

        <br/><br/>
        
        <input type="checkbox" name="<?php echo $this->internalName?>overwrite<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>overwrite<?php echo $postfixOptionKey?>" <?php if($options['overwrite']) echo "checked"; ?> >
        <label for="<?php echo $this->internalName?>overwrite<?php echo $postfixOptionKey?>">
            <?php _e("Overwrite defaults","tdomf"); ?>
            <br/><small><?php _e("(Enabling this overwrites any defaults set by other widgets, plugins or Wordpress. Disabling it means the selected terms are added to the defaults)"); ?></small>
        </label>
        <br/>       
        
        <input type="checkbox" name="<?php echo $this->internalName?>user<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>user<?php echo $postfixOptionKey?>" <?php if($options['user']) echo "checked"; ?> >
        <label for="<?php echo $this->internalName?>user<?php echo $postfixOptionKey?>" style="line-height:35px;">
            <?php _e("Allow the submitter to add new terms","tdomf"); ?>
        </label>
        <br/>
        
        <!-- @todo convert default from array to text -->
        
        <label for="<?php echo $this->internalName?>default<?php echo $postfixOptionKey?>">
            <?php _e("Defaults: ","tdomf"); ?>
            <br/><small><?php _e("These terms will be added to any post submitted using this form. Use the slugs for the terms and separate multiple terms with commas: cats, pet food, dogs","tdomf"); ?></small>
        </label>
        <input type="textfield" id="<?php echo $this->internalName?>default<?php echo $postfixOptionKey?>" name="<?php echo $this->internalName?>default<?php echo $postfixOptionKey?>" value="<?php echo $options['default']; ?>" /></label>
        <br/>
        
        
        <!-- @todo Disable/enable fields based on display value -->
        
        <br/>
        <input type="checkbox" name="<?php echo $this->internalName?>display<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>display<?php echo $postfixOptionKey?>" <?php if($options['display']) echo "checked"; ?> >
        <label for="<?php echo $this->internalName?>display<?php echo $postfixOptionKey?>" style="line-height:35px;">
            <?php _e("Display existing terms for selection by submitter","tdomf"); ?>
        </label>
        <br/>
        
        <input type="checkbox" name="<?php echo $this->internalName?>max<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>max<?php echo $postfixOptionKey?>" <?php if($options['max']) echo "checked"; ?> >
        <label for="<?php echo $this->internalName?>max<?php echo $postfixOptionKey?>" style="line-height:35px;">
            <?php _e("Allow submitter to select more than one term","tdomf"); ?>
        </label>
        <br/>

        <input type="checkbox" 
               name="<?php echo $this->internalName?>hierarchical<?php echo $postfixOptionKey?>" 
               id="<?php echo $this->internalName?>hierarchical<?php echo $postfixOptionKey?>" 
               <?php if($options['hierarchical']) echo "checked"; ?> 
               <?php if($deftax->hierarchical == false) { echo "disabled"; } ?> 
               >
        <label for="<?php echo $this->internalName?>hierarchical<?php echo $postfixOptionKey?>" style="line-height:35px;">
            <?php _e("Display in hierarchical mode","tdomf"); ?>
        </label>
        <br/>
        
        <label for="<?php echo $this->internalName?>required<?php echo $postfixOptionKey?>">
            <?php _e("Required number of terms: ","tdomf"); ?>
            <br/><small><?php _e("This number indicates the minimum number of terms the submitter must add or select. A value of 0 means it's optional","tdomf"); ?></small>
            <br/>
        </label>
        <input type="textfield" id="<?php echo $this->internalName?>required<?php echo $postfixOptionKey?>" name="<?php echo $this->internalName?>required<?php echo $postfixOptionKey?>" value="<?php echo $options['required']; ?>" /></label>
        <br/>
        
        <br/>
        <label for="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" >
        <?php _e("Display terms as:",'tdomf'); ?><br/></label>
        <input type="radio" name="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" value="dropdown" <?php if($options['display_type'] == 'dropdown'){ ?> checked <?php } ?>><?php _e("Drop-down List","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" value="list" <?php if($options['display_type'] == 'list'){ ?> checked <?php } ?>><?php _e("Select List","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" value="checkbox" <?php if($options['display_type'] == 'checkbox'){ ?> checked <?php } ?>><?php _e("Radio Buttons/Check Boxes","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>display_type<?php echo $postfixOptionKey?>" value="tag_cloud" <?php if($options['display_type'] == 'tag_cloud'){ ?> checked <?php } ?>><?php _e("Tag Cloud","tdomf"); ?><br>
        <br/>
    
       
        <label for="<?php echo $this->internalName?>orderby<?php echo $postfixOptionKey?>" >
        <?php _e("Order terms by:",'tdomf'); ?><br/></label>
        <input type="radio" name="<?php echo $this->internalName?>orderby<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>orderby<?php echo $postfixOptionKey?>" value="id" <?php if($options['orderby'] == 'id'){ ?> checked <?php } ?>><?php _e("Numeric ID of term","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>orderby<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>orderby<?php echo $postfixOptionKey?>" value="name" <?php if($options['orderby'] == 'name'){ ?> checked <?php } ?>><?php _e("Name of term","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>orderby<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>orderby<?php echo $postfixOptionKey?>" value="count" <?php if($options['orderby'] == 'count'){ ?> checked <?php } ?>><?php _e("Number of posts with term","tdomf"); ?><br>
        <br/>
        
        <label for="<?php echo $this->internalName?>order<?php echo $postfixOptionKey?>" >
        <?php _e("Order terms by:",'tdomf'); ?><br/></label>
        <input type="radio" name="<?php echo $this->internalName?>order<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>order<?php echo $postfixOptionKey?>" value="asc" <?php if($options['order'] == 'asc'){ ?> checked <?php } ?>><?php _e("Ascending","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>order<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>order<?php echo $postfixOptionKey?>" value="desc" <?php if($options['order'] == 'desc'){ ?> checked <?php } ?>><?php _e("Descending","tdomf"); ?><br>
        <br/>
        
        <label for="<?php echo $this->internalName?>limit<?php echo $postfixOptionKey?>">
            <?php _e("Limit number of terms displayed: ","tdomf"); ?>
            <br/><small><?php _e("This number limits the number of terms displayed. 0 means all terms will be displayed","tdomf"); ?></small>
            <br/>
        </label>
        <input type="textfield" id="<?php echo $this->internalName?>limit<?php echo $postfixOptionKey?>" name="<?php echo $this->internalName?>limit<?php echo $postfixOptionKey?>" value="<?php echo $options['limit']; ?>" /></label>
        <br/>
        
        <!-- @todo Disable/enable filter_list based on filter setting -->
        
        <br/>
        <label for="<?php echo $this->internalName?>filter<?php echo $postfixOptionKey?>" >
        <?php _e("Filter terms:",'tdomf'); ?><br/></label>
        <input type="radio" name="<?php echo $this->internalName?>filter<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>filter<?php echo $postfixOptionKey?>" value="none" <?php if($options['filter'] == 'none'){ ?> checked <?php } ?>><?php _e("Do not filter","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>filter<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>filter<?php echo $postfixOptionKey?>" value="exclude" <?php if($options['filter'] == 'exclude'){ ?> checked <?php } ?>><?php _e("Exclude these terms","tdomf"); ?><br>
        <input type="radio" name="<?php echo $this->internalName?>filter<?php echo $postfixOptionKey?>" id="<?php echo $this->internalName?>filter<?php echo $postfixOptionKey?>" value="limit" <?php if($options['filter'] == 'limit'){ ?> checked <?php } ?>><?php _e("Restrict to these terms","tdomf"); ?><br>
        
        <label for="<?php echo $this->internalName?>filter_list<?php echo $postfixOptionKey?>">
            <br/><small><?php _e("These terms will be used to filter the terms displayed. Use the slugs for the terms and separate multiple terms with commas: cats, pet food, dogs","tdomf"); ?></small>
        </label>
        <input type="textfield" id="<?php echo $this->internalName?>filter_list<?php echo $postfixOptionKey?>" name="<?php echo $this->internalName?>filter_list<?php echo $postfixOptionKey?>" value="<?php echo $options['filter_list']; ?>" /></label>
        <br/>
        
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
