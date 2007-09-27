<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////////////////////////////////////
// Logging function to aid debugging and tracking activity //
/////////////////////////////////////////////////////////////

// Some pre-defined types/colours
//
define('TDOMF_LOG_ERROR',   "red");
define('TDOMF_LOG_GENERAL', "gray");
define('TDOMF_LOG_SYSTEM',  "blue");
define('TDOMF_LOG_GOOD',    "green");
define('TDOMF_LOG_BAD',     "black");

// Returns a formatted date and time stamp for log messages
//
function tdomf_get_log_timestamp(){
   return date('d-m-y')."(".date('G:i:s').")";
}

// Returns a formatted user-name stamp for log messages
//
function tdomf_get_log_userstamp(){
   global $current_user;

   if(!function_exists("get_currentuserinfo")) {
      return $_SERVER['REMOTE_ADDR'];
   }

   get_currentuserinfo();

   if(is_user_logged_in()) {
      $user_id = get_option(TDOMF_DEFAULT_AUTHOR);
      // if dummy author, use IP instead
      if($user_id != $current_user->ID) {
        return $current_user->user_login;
   	  }
   }

   return $_SERVER['REMOTE_ADDR'];

}

//////////////////////////////
// The actual logging function
//
function tdomf_log_message($message,$color=TDOMF_LOG_GENERAL){
   $timestamp = tdomf_get_log_timestamp();
   $userstamp = tdomf_get_log_userstamp();
   $msg = "";
   if(isset($color)){
      $msg .= "<font color=\"".$color."\">";
   }
   $msg .= "[$userstamp][$timestamp] $message";
   if(isset($color)){
      $msg .= "</font>";
   }
   $msg .= "<br>";
   $current_log = get_option(TDOMF_LOG);
   if($current_log != false) {
      $current_log .= "\n".$msg;
      update_option(TDOMF_LOG,$current_log);
   } else {
      add_option(TDOMF_LOG,$msg);
   }
}

// Clear/Empty the log
//
function tdomf_clear_log(){
	if(get_option(TDOMF_LOG) != false) {
		delete_option(TDOMF_LOG);
	}
	tdomf_log_message('Log cleared');
}
//
// Get the log or the last X lines of log
//
function tdomf_get_log($limit=0){
  $log = get_option(TDOMF_LOG);
  if($log != false) {
     if($limit<=0) {
        return $log;
     } else {
        // limit the log to the last $limit lines
        $lines=split("\n",$log);
        $lines=array_reverse($lines);
        $limited_log = join("\n",array_slice($lines,0,$limit));
        // undo reverse
        $lines=split("\n",$limited_log);
        $lines=array_reverse($lines);
        $limited_log = join("\n",$lines);
        // pass back limited, correctly ordered log
        return $limited_log;
     }
  }
  return "The log is currently empty!";
}

?>
