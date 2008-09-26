<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

class TdomfWidgetField {
    var $name = "tdomf_generic";

    function set_option(&$options, $id, $def) {
		if(isset($options[$id])) {
         	return $options[$id];
        }
        $options[$id] = $def;
        return $def;
    }
}

class TdomfWidgetTextarea extends TdomfWidgetField {
   var $cols = 40;
   var $rows = 10;
   var $quicktags = false;
   var $tags_restrict = true;
   var $tags_allowable = "<p><b><em><u><strong><a><img><table><tr><td><blockquote><ul><ol><li><br><sup>";
   var $limit_char = false;
   var $limit_word = false;

   function TdomfWidgetTextarea () {
      // todo
   }

   function set_options(&$options,$prefix = "") {
      if(is_array($options)) {
         $this->cols = intval(set_option($options, $prefix.'cols', 40));
         $this->rows = intval(set_option($options, $prefix.'rows', 10));
         $this->quicktags = set_option($options, $prefix.'quicktags', false);
         $this->tags_restrict = set_option($options, $prefix.'restrict-tags', false);
		 $this->tags_allowable = set_option($options, $prefix.'allowable-tags', false);
	     $this->limit_char = intval(set_option($options, $prefix.'char-limit', 40));
         $this->limit_word = intval(set_option($options, $prefix.'word-limit', 10));
      }
      return $options;
   }

   function show($args = array()) {
      $default = "";
      if(isset($args[$this->name])) { $default = $args[$this->name]; }
      $output = "";
      if($this->tags_allowable != "" && $this->tags_restrict) {
	     $output .= sprintf(__("<small>Allowable Tags: %s</small>","tdomf"),htmlentities($this->tags_allowable))."<br/>";
	  }
	  if($this->limit_word > 0) {
	     $output .= sprintf(__("<small>Max Word Limit: %d</small>","tdomf"),$this->limit_word)."<br/>";
	  }
	  if($this->limit_char > 0) {
	     $output .= sprintf(__("<small>Max Character Limit: %d</small>","tdomf"),$this->limit_char)."<br/>";
	  }
	  if($this->quicktags) {
	     $qt_path = TDOMF_URLPATH."tdomf-quicktags.js.php?postfix=$this->name";
	     if($this->tags_allowable != "" && $this->tags_restrict) {
	        $qt_path = TDOMF_URLPATH."tdomf-quicktags.js.php?postfix=$this->name&allowed_tags=".urlencode($this->tags_allowable);
	     }
	     $output .= "\n<script src='$qt_path' type='text/javascript'></script>\n";
	     $output .= "\n<script type='text/javascript'>edToolbar".$this->name."();</script>\n";
	  }
	  $output .= '<textarea title="true" rows="'.$this->rows.'" cols="'.$this->cols.'" name="'.$this->name.'" id="'.$this->name.'" >'.$default.'</textarea>';
	  if($this->quicktags) {
	        $output .= "\n<script type='text/javascript'>var edCanvas".$this->name." = document.getElementById('".$this->name."');</script>\n";
      }
      return $output;
   }

   function hack($args = array() {
      // todo
      return NULL;
   }

   function preview($args = array() {
      // todo
      return NULL;
   }

   function validate($args = array() {
      // todo
      return NULL;
   }

   function control() {
      // todo
   }
}

?>