<?php
/*
Name: "Content"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: One of the default widgets, allows submitting and editing of post content and title
Version: 1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }
  
  /** 
   * Content Widget. This widget allows users to modify the content and title
   * 
   * @author Mark Cunningham <tdomf@thedeadone.net> 
   * @version 2.0 
   * @since 0.13.0
   * @access public 
   * @copyright Mark Cunningham
   * 
   */ 
  class TDOMF_WidgetContent extends TDOMF_Widget
  {
    /** 
     * Utility class for text area   
     * 
     * @var TDOMF_WidgetFieldTextArea 
     * @access private
     */       
      var $textarea;
      
      /** 
       * Initilise and start widget
       * 
       * @access public
       */ 
      function TDOMF_WidgetContent() {
          $this->textarea = new TDOMF_WidgetFieldTextArea('content-text-');
          $this->enableHack();
          $this->enablePreview();
          $this->enablePreviewHack();
          $this->enableValidate();
          $this->enableValidatePreview();
          $this->enablePost();
          $this->enableAdminEmail();
          $this->enableWidgetTitle();
          $this->enableControl(true,450,720);
          $this->setInternalName('content');
          $this->setDisplayName(__('Content','tdomf'));
          $this->setOptionKey('tdomf_content_widget');
          $this->setModes(array('new','edit'));
          $this->setFields(array('post_content' => __('Post Content','tdomf'),
                                 'post_title' => __('Post Title','tdomf')));
          $this->start();
      }
      
      /**
       * What to display in form
       * 
       * @access public
       * @return String
       */
      function form($args,$options) {
         if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
         extract($args);
         $output = "";
         
         if(TDOMF_Widget::isEditForm($mode,$tdomf_form_id)) {
             $post = get_post($tdomf_post_id);
             if($post) {
                 if(!isset($args['content_title'])) {
                     $content_title = $post->post_title;
                 }
                 $options['content-text-default-text'] = $post->post_content;
             }
         }
         
         if($options['title-enable']) {
              if($options['title-required']) {
                  $output .= '<label for="content_title" class="required">'.__("Post Title (Required): ","tdomf")."<br/></label>\n";
              } else {
                  $output .= '<label for="content_title">'.__("Post Title: ","tdomf")."<br/></label>\n";
              }
              $output .= '<input type="text" name="content_title" id="content_title" size="'.$options['title-size'].'" value="'.htmlentities($content_title,ENT_QUOTES,get_bloginfo('charset')).'" />';
              if($options['text-enable']) {
                  $output .= "<br/><br/>";
              }
          }
          
          if($options['text-enable']) {
            $output .= $this->textarea->form($args,$options);
          }
          return $output;
      }
      
      /**
       * Code for hacking form output
       * 
       * @access public
       * @return String
       */
      function formHack($args,$options) {
          if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
          extract($args);          
         
          $output = "";
          
          if(TDOMF_Widget::isEditForm($mode,$tdomf_form_id)) {
              $output .= "\t\t".'<?php $post = get_post($post_id); if($post) {'."\n";
              if($options['title-enable']) {
                  $output .= "\t\t\t".'if(!isset($content_title)) { $content_title = $post->post_title; }'."\n";
              }
              if($options['text-enable']) {
                  $output .= "\t\t\t".'if(!isset($content_content)) { $content_ta = $post->post_content; }'."\n";
              }
              $output .= "\t\t".'} ?>'."\n";
          }
          
          if($options['title-enable']) {
            if($options['title-required']) {
              $output .= "\t\t".'<label for="content_title" class="required">'.__("Post Title (Required): ","tdomf")."\n\t\t\t<br/>\n";
            } else {
              $output .= "\t\t".'<label for="content_title">'.__("Post Title: ","tdomf")."\n\t\t\t<br/>\n";
            }
            $output .= "\t\t</label>\n";            
            $output .= "\t\t\t".'<input type="text" name="content_title" id="content_title" size="'.$options['title-size'].'" value="';
            $output .= '<?php echo htmlentities($content_title,ENT_QUOTES,get_bloginfo(\'charset\')); ?>" />'."\n";
            if($options['text-enable']) {
              $output .= "\t\t<br/>\n\t\t<br/>\n";
            }
          }
          
          if($options['text-enable']) {
            $output .= $this->textarea->formHack($args,$options);
          }
          return $output;
      }
      
      /**
       * Overrides "getOptions" with defaults for this widget
       * 
       * @access public
       * @return String
       */
      function getOptions($form_id) {
          $defaults = array(   'title-enable' => true,
                               'title-required' => false,
                               'title-size' => 30,
                               'text-enable' => true,
                                # non-user configurable options for textarea
                               'content-text-title' => __('Post Text','tdomf'),
                               'content-text-use-filter' => true,
                               'content-text-filter' => 'the_content',
                               'content-text-kses' => true,
                               'content-text-default_text' => "",
                               );
          $options = TDOMF_Widget::getOptions($form_id); 
          $options = wp_parse_args($options, $defaults);
          
          # convert previous textarea options to new utility textarea options
          
          if(isset($options['text-required'])) {
              $options['content-text-required'] = $options['text-required'];
              unset($options['text-required']);
          }
          
          if(isset($options['text-cols'])) {
              $options['content-text-cols'] = $options['text-cols'];
              unset($options['text-cols']);
          }
          
          if(isset($options['text-rows'])) {
              $options['content-text-rows'] = $options['text-rows'];
              unset($options['text-rows']);
          }
          
          if(isset($options['quicktags'])) {
              $options['content-text-quicktags'] = $options['quicktags'];
              unset($options['quicktags']);
          }
          
          if(isset($options['restrict-tags'])) {
              $options['content-text-restrict-tags'] = $options['restrict-tags'];
              unset($options['restrict-tags']);
          }
          
          if(isset($options['allowable-tags'])) {
              $options['content-text-allowable-tags'] = $options['allowable-tags'];
              unset($options['allowable-tags']);
          }
          
          if(isset($options['char-limit'])) {
              $options['content-text-char-limit'] = $options['char-limit'];
              unset($options['char-limit']);
          }
          
          if(isset($options['word-limit'])) {
              $options['content-text-word-limit'] = $options['word-limit'];
              unset($options['word-limit']);
          }
          
          # now grab defaults for textarea
          
          $options = $this->textarea->getOptions($options);
          
          return $options;
      }   
      
      /**
       * Generate preview of widget
       * 
       * @access public
       * @return String
       */      
      function preview($args,$options) {
          if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
          extract($args);
          $output = "";
          if($options['title-enable']) {
            $output .= "<b>".__("Title: ","tdomf")."</b>";
            $output .= $content_title;
            $output .= "<br/>";
          }
          if($options['text-enable']) {
            $output .= $this->textarea->preview($args,$options);
          }
          return $output;
      }

      /**
       * Generate preview hack code of widget
       * 
       * @access public
       * @return String
       */      
      function previewHack($args,$options) {
          if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
          extract($args);          
          $output = "";
          if($options['title-enable']) {
            $output .= "\t<b>".__("Title: ","tdomf")."</b>";
            $output .= "<?php echo \$content_title; ?>\n";
            $output .= "\t<br/>\n";
          }
          if($options['text-enable']) {
            $output .= $this->textarea->previewHack($args,$options);
          }
          return $output;
      }
      
      /**
       * Process form input for widget
       * 
       * @access public
       * @return Mixed
       */
      function post($args,$options) {
          extract($args);
  
          // if sumbitting a new post (as opposed to editing)
          // make sure to *append* to post_content. For editing, overwrite.
          //
          if(TDOMF_Widget::isSubmitForm($mode)) {
              
              // Grab existing data
              $post = wp_get_single_post($post_ID, ARRAY_A);
              if(!empty($post['post_content'])) {
                $post = add_magic_quotes($post);
              }
              
              // Append
              $post_content = $post['post_content'];
              $post_content .= $this->textarea->post($args,$options,'content_content');
              
          } else { // $mode startswith "edit-"
              // Overwrite 
              $post_content = $this->textarea->post($args,$options,'content_content');
          }

          // Title

          if($options['title-enable'] && !isset($content_title)) {
            $content_title = tdomf_protect_input($post['post_title']);
          }
          
          // Update actual post

          $post = array (
              "ID"                      => $post_ID,
              "post_content"            => $post_content,
          );
          if($options['title-enable']) {
              $post["post_title"] = $content_title;
              $post["post_name"] = sanitize_title($content_title);
          }
        
          $post_ID = wp_update_post($post);
          return NULL;
      }
      
      /**
       * Configuration panel for widget
       * 
       * @access public
       */      
      function control($options,$form_id) {
          
          // Store settings for this widget
          //
          if ( $_POST[$this->internalName.'-submit'] ) {
                 $newoptions['title'] = strip_tags(stripslashes($_POST['content-title']));
                 $newoptions['title-enable'] = isset($_POST['content-title-enable']);
                 $newoptions['title-required'] = isset($_POST['content-title-required']);
                 $newoptions['title-size'] = intval($_POST['content-title-size']); 
                 $newoptions['text-enable'] = isset($_POST['content-text-enable']);
                 $options = wp_parse_args($newoptions, $options);
                 $this->updateOptions($options,$form_id);
          }

          // Display control panel for this widget
          //
          extract($options);
          ?>
<div>          
          <?php $this->controlCommon($options); ?>
     
<h4><?php _e("Title of Post","tdomf"); ?></h4>
<label for="content-title-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="content-title-enable" id="content-title-enable" <?php if($options['title-enable']) echo "checked"; ?> ></label>
<label for="content-title-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="content-title-required" id="content-title-required" <?php if($options['title-required']) echo "checked"; ?> ></label>
<label for="content-title-size" style="line-height:35px;"><?php _e("Size","tdomf"); ?> <input type="textfield" name="content-title-size" id="content-title-size" value="<?php echo htmlentities($options['title-size'],ENT_QUOTES,get_bloginfo('charset')); ?>" size="3" /></label>

<h4><?php _e("Content of Post","tdomf"); ?></h4>
<label for="content-text-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="content-text-enable" id="content-text-enable" <?php if($options['text-enable']) echo "checked"; ?> ></label>

          <?php 
          if(TDOMF_Widget::isSubmitForm($mode,$form_id)) {
              $tashow = array('content-text-cols',
                              'content-text-rows',
                              'content-text-quicktags',
                              'content-text-restrict-tags',
                              'content-text-allowable-tags',
                              'content-text-char-limit',
                              'content-text-word-limit',
                              'content-text-required',
                              'content-text-title',
                              'content-text-default-text');
          } else {
              $tashow = array('content-text-cols',
                              'content-text-rows',
                              'content-text-quicktags',
                              'content-text-restrict-tags',
                              'content-text-allowable-tags',
                              'content-text-char-limit',
                              'content-text-word-limit',
                              'content-text-required',
                              'content-text-title');
          }
          $taoptions = $this->textarea->control($options, $form_id, $tashow); 
          if( $_POST[$this->internalName.'-submit'] ) {
              $options = wp_parse_args($taoptions, $options);
              $this->updateOptions($options,$form_id);
          }
          
          ?>
</div>
        <?php
      }
      
      /**
       * Validate widget input
       * 
       * @access public
       * @return Mixed
       */
      function validate($args,$options,$preview) {
          if(!$options['title-enable'] && !$options['text-enable']) { return ""; }  
          extract($args);
          $output = "";

          if($options['title-enable'] && $options['title-required']
               && (empty($content_title) || trim($content_title) == "")) {
              if($output != "") { $output .= "<br/>"; }
              $output .= __("You must specify a post title.","tdomf");
          }

          if($options['text-enable']) {
              $ta_output = $this->textarea->validate($args,$options,$preview);
              if(!empty($ta_output)) {
                  if($output != "") { $output .= "<br/>"; }
                  $output .= $ta_output;
              }
          }
          
          if(TDOMF_Widget::isEditForm($mode,$tdomf_form_id)) {

               // when it goes to validation, the tdomf_post_id will be the 
               // real post id

              $post = &get_post( $tdomf_post_id );
              
              // for post content, this is probably not the most exact way to do it
              //
              if($options['text-enable'] && $options['content-text-required']) {
                  $post_content = $this->textarea->post($args,$options,$preview);
                  if(trim($post->post_content) == trim($post_content)) {
                      if($output != "") { $output .= "<br/>"; }
                      $output .= __("You must modify the post text.","tdomf");
                  }
              }              
              if($options['title-enable'] && $options['title-required']) {
                  if(trim($post->post_title) == trim($content_title)) {
                      if($output != "") { $output .= "<br/>"; }
                      $output .= __("You must modify the post title.","tdomf");
                  }
              }
          }
          
          // return output if any
          if($output != "") {
              return $output;
          } else {
              return NULL;
          }
      }
  }
  
    
  // Create and start the widget
  //
  global $tdomf_widget_content;
  $tdomf_widget_content = new TDOMF_WidgetContent();

?>
