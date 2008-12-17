<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/** 
* Super class for widget classes 
* 
* @author Mark Cunningham <tdomf@thedeadone.net> 
* @version 1.0 
* @since 0.13.0
* @access public 
* @copyright Mark Cunningham
* 
*/ 
class TDOMF_Widget {
    
    /** 
     * Determines if widget can be hacked on the form   
     * 
     * @var boolean 
     * @access public 
     * @see enableHack() 
     */ 
    var $hack = false;
    
    /** 
     * Enables or disables widget hacking on the form
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enableHack($hack = true) {
        if($this->started && $this->hack != $hack) {
           if($hack) {
               tdomf_register_form_widget_hack($this->internalName,$this->displayName, array($this, '_form_hack'), $this->modes);
           } # remove not supported
        }
        $this->hack = $hack;
        return true;
    }
    
    /** 
     * Determines if widget has a preview   
     * 
     * @var boolean 
     * @access public 
     * @see enablePreview() 
     */
    var $preview = false;

    /** 
     * Enables or disables widget preview
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enablePreview($preview = true) {
        if($this->started && $this->preview != $preview) {
           if($preview) {
               tdomf_register_form_widget_preview($this->internalName,$this->displayName, array($this, '_preview'), $this->modes);
           } # remove not supported
        }        
        $this->preview = $preview;
        return true;
    }

    /** 
     * Determines if widget preview can be hacked
     * 
     * @var boolean 
     * @access public 
     * @see enablePreviewHack() 
     */
    var $previewHack = false;

    /** 
     * Enables or disables widget preview hack
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enablePreviewHack($previewHack = true) {
        if($this->started && $this->previewHack != $previewHack) {
           if($previewHack) {
               tdomf_register_form_widget_preview_hack($this->internalName,$this->displayName, array($this, '_previewHack'), $this->modes);
           } # remove not supported
        }
        $this->previewHack = $previewHack;
        return true;
    }
    
    /** 
     * Determines if widget input will be validated
     * 
     * @var boolean 
     * @access public 
     * @see enableValidate() 
     */
    var $validate = false;

    /** 
     * Enables or disables widget validation
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enableValidate($validate = true) {
        if($this->started && $this->validate != $validate) {
           if($validate) {
               tdomf_register_form_widget_validate($this->internalName,$this->displayName, array($this, '_validate'), $this->modes);
           } # remove not supported
        }
        $this->validate = $validate;
        return true;
    }

    /** 
     * Determines if widget input should be validated on preview
     * 
     * @var boolean 
     * @access public 
     * @see enableValidatePreview() 
     */
    var $validatePreview = true;

    /** 
     * Enables or disables widget validation
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enableValidatePreview($validatePreview = true) {
        $this->validatePreview = $validatePreview;
        return true;
    }    
    
    /** 
     * Determines if widget modifies actual post
     * 
     * @var boolean 
     * @access public 
     * @see enablePost() 
     */
    var $post = false;

    /** 
     * Enables or disables widget post modification
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enablePost($post = true) {
        if($this->started && $this->post != $post) {
           if($post) {
               tdomf_register_form_widget_preview_hack($this->internalName,$this->displayName, array($this, '_post'), $this->modes);
           } # remove not supported
        }
        $this->post = $post;
        return true;
    }
    
    /** 
     * Determines if widget sends admin email
     * 
     * @var boolean 
     * @access public 
     * @see enableAdminEmail() 
     */
    var $adminEmail = false;

    /** 
     * Enables or disables widget sending admin email
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enableAdminEmail($adminEmail = true) {
        if($this->started && $this->adminEmail != $adminEmail) {
           if($adminEmail) {
               tdomf_register_form_widget_adminemail($this->internalName,$this->displayName, array($this, '_adminEmail'), $this->modes);
           } # remove not supported
        }        
        $this->adminEmail = $adminEmail;
        return true;
    }

    /** 
     * Enables support for displaying an error message
     * 
     * @var boolean 
     * @access public 
     * @see enableAdminError() 
     */
    var $adminError = false;

    /** 
     * Enables or disables support for error message
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enableAdminError($adminError = true) {
        if($this->started && $this->adminError != $adminError) {
           if($adminError) {
               tdomf_register_form_widget_admin_error($this->internalName,$this->displayName, array($this, '_adminError'), $this->modes);
           } # remove not supported
        }              
        $this->adminError = $adminError;
        return true;
    }
    
    /** 
     * Determines if widget can be configured
     * 
     * @var boolean 
     * @access public 
     * @see enableControl() 
     */    
    var $control = true;
    
    /** 
     * Width of Control Panel
     * 
     * @var integer 
     * @access public 
     * @see enableControl() 
     */        
    var $controlWidth = 100;
    
    /** 
     * Height of Control Panel
     * 
     * @var integer 
     * @access public 
     * @see enableControl() 
     */    
    var $controlHeight = 100;

    /** 
     * Enables or disables widget control panel
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enableControl($control = true, $width = 100, $height = 100) {
        if($width <= 0 || $height <= 0) {
            return false;
        }
        
        if($this->started && $this->control != $control) {
           if($control) {
               tdomf_register_form_widget_adminemail($this->internalName,$this->displayName, array($this, '_adminEmail'), $width, $height, $this->modes);
           } # remove not supported
        }              
        $this->control = $control;
        $this->controlWidth = $width;
        $this->controlHeight = $height;
        return true;
    }
    
    /** 
     * Modes the widget supports
     * 
     * @var Array 
     * @access public 
     * @see setModes() 
     */    
    var $modes = array();
    
    /** 
     * Sets modes widget supports. Must be done before widget is started
     * 
     * @return Boolean 
     * @access public 
     */ 
    function setModes($modes = array()) {
        $retVal = false;
        if(!$this->started || !is_array($modes)) {
            $retVal = true;
            $this->modes = $modes;
        }
        return $retVal;
    }   
    
    /** 
     * Widget supports title
     * 
     * @var boolean 
     * @access public 
     * @see enableWidgetTitle() 
     */
    var $widgetTitle = false;
    
    /** 
     * Enables support for title in widget display
     * 
     * @return Boolean 
     * @access public 
     */ 
    function enableWidgetTitle($widgetTitle = true) {
        $this->widgetTitle = $widgetTitle;
        return true;
    }

    /** 
     * Internal name of widget
     * 
     * @var boolean 
     * @access public 
     * @see setInternalName() 
     */    
    var $internalName = false;

    /** 
     * Set internal name of widget. Must be done before widget is started
     * 
     * @return Boolean 
     * @access public 
     */ 
    function setInternalName($name,$prefix = "tdomf_widget_") {
        $retVal = false;
        if(!$this->started) {
            $retVal = true;
            $this->internalName = $prefix.$name;
            if(!$this->optionKey) {
                $this->optionKey = $this->internalName;
            }
        }
        return $retVal;
    }    
    
    /** 
     * Name of widget displayed to user. 
     * 
     * @var boolean 
     * @access public 
     * @see setDisplayName() 
     */        
    var $displayName = false;
    
    /** 
     * Set display name of widget. Must be done before widget is started.
     * 
     * @return Boolean 
     * @access public 
     */ 
    function setDisplayName($name) {
        $retVal = false;
        if(!$this->started) {
            $retVal = true;
            $this->displayName = $name;
        }
        return $retVal;
    }   
    
    /** 
     * Name of options stored in database. Normally genreated from internal
     * name but can be overwritten
     * 
     * @var boolean 
     * @access public 
     * @see setOptionKey() 
     */    
    var $optionKey = false;

    /** 
     * Set option key string.. Must be done before widget is started.
     * 
     * @return Boolean 
     * @access public 
     */ 
    function setOptionKey($key) {
        $retVal = false;
        if(!$this->started) {
            $retVal = true;
            $this->optionKey = $key;
        }
        return $retVal;
    }   
    
    /** 
     * Flags if the widget has been started yet
     * 
     * @return Boolean 
     * @access private 
     */     
    var $started = false;
    
    function TDOMF_Widget() {
        /* do nothing */
    }
    
    function start() {
       $retVal = false;
       if(!$this->started || !$this->internalName || !$this->displayName)
       {
           $retVal = true;
           
           tdomf_register_form_widget($this->internalName, $this->displayName, array($this, '_form'), $this->modes);
           
           if($this->hack)
               tdomf_register_form_widget_hack($this->internalName,$this->displayName, array($this, '_formHack'), $this->modes);
           
           if($this->control)
               tdomf_register_form_widget_control($this->internalName, $this->displayName, array($this, '_control'), $this->controlWidth, $this->controlHeight, $this->modes);
           
           if($this->preview)
               tdomf_register_form_widget_preview($this->internalName, $this->displayName, array($this, '_preview'), $this->modes);
           
           if($this->previewHack)
               tdomf_register_form_widget_preview_hack($this->internalName, $this->displayName, array($this, '_previewHack'), $this->modes);
           
           if($this->validate)
               tdomf_register_form_widget_validate($this->internalName, $this->displayName, array($this, '_validate'), $this->modes);
           
           if($this->post)
               tdomf_register_form_widget_post($this->internalName, $this->displayName, array($this, '_post'), $this->modes);
           
           if($this->adminEmail)
               tdomf_register_form_widget_adminemail($this->internalName, $this->displayName, array($this, '_adminEmail'), $this->modes);
           
           if($this->adminError)
               tdomf_register_form_widget_admin_error($this->internalName, $this->displayName, array($this, '_adminError'), $this->modes);
       }
        return $retVal;
    }

    /** 
     * Wraps form output of the widget
     * 
     * @return String 
     * @access private 
     */ 
    function _form($args) {
        extract($args);
        $options = $this->getOptions($tdomf_form_id);

        $output = $before_widget;
        if($this->widgetTitle && $options['tdomf-title'] != "") {
            $output .= $before_title.$options['tdomf-title'].$after_title;
        }
        $output .= $this->form($args,$options);
        $output .= $after_widget;
        return $output;
    }
    
    /** 
     * Individual widgets should override this function
     * 
     * @return String 
     * @access public
     */    
    function form($args,$options) {
        # do nothing
        return "";
    }

    /** 
     * Wraps post of the widget
     * 
     * @return Mixed 
     * @access private 
     */     
    function _post($args) {
        extract($args);
        $options = $this->getOptions($tdomf_form_id);
        return $this->post($args,$options);
    }
    
    /** 
     * Individual widgets that implement post should override this function
     * 
     * @return Mixed 
     * @access public
     */        
    function post($args,$options) {
        # do nothing
        return NULL;
    }   
    
    /** 
     * Wraps preview output of the widget
     * 
     * @return String
     * @access private 
     */         
    function _preview($args) {
        extract($args);
        $options =  $this->getOptions($tdomf_form_id);
    
        $output = "";    
        $widget_output = $this->preview($args,$options);
        if($widget_output && !empty($widget_output)) {
          $output  = $before_widget;
          if($this->widgetTitle && $options['tdomf-title'] != '') {
              $output .= $before_title.$options['tdomf-title'].$after_title;
          }
          $output .= $widget_output;  
          $output .= $after_widget;
        }
        return $output;
    }
    
    /** 
     * Individual widgets that implement preview should override this function
     * 
     * @return Mixed 
     * @access public
     */        
    function preview($args,$options) {
        # do nothing
        return false;
    }
    
    /** 
     * Wraps validation of the widget input
     * 
     * @return Mixed
     * @access private 
     */      
    function _validate($args,$preview) {
        extract($args);
        $options = $this->getOptions($tdomf_form_id);
        
        if(!$preview || $this->validatePreview) {
            $output = $this->validate($args,$options,$preview);
            if($output != NULL && !empty($output)) {
                return $before_widget.$output.$after_widget;
            }
        }
        
        return NULL;
    }
    
    /** 
     * Individual widgets that implement validation should override this function
     * 
     * @return Mixed 
     * @access public
     */            
    function validate($args,$options,$preview) {
        # do nothing
        return NULL;
    }
    
    /** 
     * Wraps admin email of the widget input
     * 
     * @return String
     * @access private 
     */        
    function _adminEmail($args){
        extract($args);
        $options = $this->getOptions($tdomf_form_id);
        $output = "";    
        $widget_output = $this->adminEmail($args,$options,$post_ID);
        if($widget_output && !empty($widget_output)) {
          $output  = $before_widget;
          if($this->widgetTitle && $options['tdomf-title'] != '') {
              $output .= $before_title.$options['tdomf-title'].$after_title;
          }
          $output .= $widget_output;  
          $output .= $after_widget;
        }
        return $output;
    }
    
    /** 
     * Individual widgets that implement admin should override this function
     * 
     * @return String
     * @access public
     */      
    function adminEmail($args,$options,$post_ID) {
        # do nothing
        return '';
    }
    
    /** 
     * Wraps configuration panel of widget
     * 
     * @access private 
     */       
    function _control($form_id) {
        $options = $this->getOptions($form_id);
        if ( $_POST[$this->internalName.'-submit'] ) {
            if($this->widgetTitle) {
                $newoptions['tdomf-title'] = $_POST[$this->internalName.'-tdomf-title'];
            }
            if($this->hack) {
                $newoptions['tdomf-hack'] = isset($_POST[$this->internalName.'-tdomf-hack']);
            }
            if($this->previewHack) {
                $newoptions['tdomf-preview-hack'] = isset($_POST[$this->internalName.'-tdomf-preview-hack']);
            }
            if ( $options != $newoptions ) {
                $options = $newoptions;
                $this->updateOptions($options,$form_id);
            }
        }
        $this->control($options,$form_id);
    }
    
    /** 
     * Individual widgets that implement a control panel should override this function
     * 
     * @access public
     */     
    function control() {
        # do nothing
    }

    /** 
     * Displays common configuration options
     * 
     * @access public
     */    
    function controlCommon($options) {

        if($this->widgetTitle) { ?>
<label for="<?php echo $this->internalName; ?>-title" style="line-height:35px;"><?php _e("Widget Title: ","tdomf"); ?></label>
<input type="textfield" id="<?php echo $this->internalName; ?>-title" name="<?php echo $this->internalName; ?>-title" value="<?php echo htmlentities($options['tdomf-title'],ENT_QUOTES,get_bloginfo('charset')); ?>" /></label>
<br/>
        <?php  }
        if($this->hack) { ?>
<input type="checkbox" name="<?php echo $this->internalName; ?>-hack" id="<?php echo $this->internalName; ?>-hack" <?php if($options['tdomf-hack']) echo "checked"; ?> >
<label for="<?php echo $this->internalName; ?>-hack" style="line-height:35px;"><?php _e("This widget can be modified in the form hacker","tdomf"); ?></label>
<br/>
        <?php }
       if($this->hack) { ?>
<input type="checkbox" name="<?php echo $this->internalName; ?>-preview-hack" id="<?php echo $this->internalName; ?>-preview-hack" <?php if($options['tdomf-preview-hack']) echo "checked"; ?> >
<label for="<?php echo $this->internalName; ?>-preview-hack" style="line-height:35px;"><?php _e("This widget's preview can be modified in the form hacker","tdomf"); ?></label>
<br/>
        <?php }
    }
    
    /** 
     * Wraps hacked form output of the widget
     * 
     * @return String 
     * @access private 
     */ 
    function _formHack($args) {
        extract($args);
        $options = $this->getOptions($tdomf_form_id);
        if($options['tdomf-hack']) {
            $output = $before_widget;
            if($this->widgetTitle && $options['tdomf-title'] != "") {
                $output .= $before_title.$options['tdomf-title'].$after_title;
            }
            $output .= $this->formHack($args,$options);
            $output .= $after_widget;
            return $output;
        }
        return TDOMF_MACRO_WIDGET_START.$this->internalName.TDOMF_MACRO_END;
    }
    
    /** 
     * Individual widgets that implement hacked form should override this function
     * 
     * @access public
     * @return String 
     */      
     function formHack($args,$options) {
         return TDOMF_MACRO_WIDGET_START.$this->internalName.TDOMF_MACRO_END;
     }
    
    /** 
     * Wraps hacked form preview output of the widget
     * 
     * @return String 
     * @access private 
     */ 
    function _previewHack($args) {
        extract($args);
        $options = $this->getOptions($tdomf_form_id);
        if($options['tdomf-hack']) {
            $output = $before_widget;
            if($this->widgetTitle && $options['tdomf-title'] != "") {
                $output .= $before_title.$options['tdomf-title'].$after_title;
            }
            $output .= $this->previewHack($args,$options);
            $output .= $after_widget;
            return $output;
        }
        return TDOMF_MACRO_WIDGET_START.$this->internalName.TDOMF_MACRO_END;        
    }
    
    /** 
     * Individual widgets that implement hacked preview should override this function
     * 
     * @access public
     * @return String 
     */      
     function previewHack($args,$options) {
         return TDOMF_MACRO_WIDGET_START.$this->internalName.TDOMF_MACRO_END;
     }  
     
    /** 
     * Wraps error handler of the widget
     * 
     * @return String 
     * @access private 
     */ 
     function _adminError($form_id) {
        $options = $this->getOptions($form_id);
        return $this->adminError($options,$form_id);
     }
     
    /** 
     * Individual widgets that implement an error handler should override this function
     * 
     * @access public
     * @return String 
     */       
     function adminError($options,$form_id) {
         return "";
     }

    /** 
     * Returns the options for this widget
     * 
     * @return Array
     * @access public
     */       
    function getOptions($form_id) {
        $defaults = array('tdomf-title' => $this->displayName,
                          'tdomf-hack' => $this->hack,
                          'tdomf-preview-hack' => $this->previewHack );
        $options = tdomf_get_option_widget($this->optionKey,$form_id);
        if($options == false) { $options = array(); }
        $options = wp_parse_args($options, $defaults);
        return $options;
    }
    
    function updateOptions($options,$form_id) {
        $options = tdomf_set_option_widget($this->optionKey,$options,$form_id);
    }
}

?>