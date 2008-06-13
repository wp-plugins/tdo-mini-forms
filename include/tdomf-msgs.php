<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

function tdomf_get_message($key,$form_id = false) {
    $message = "";
    if($form_id === false) {
        $message = get_option($key);
    } else {
        $message = tdomf_get_option_form($key);
    }
    if($message === false) {
        $message = tdomf_get_message_default($key);
    }
    return $message;
}

/*function tdomf_process_var($string, $post_args = array()) {
        
    if(preg_match_all('|'.TDOMF_MACRO_VAR_START.'.*?'.TDOMF_MACRO_END.'|', $string, $matches) > 0) {
        $patterns = array();
        $replacements = array();
        $unused_patterns = array();
        foreach($matches[0] as $match) {
            $var = false;
            $var_found = false;
            
            $var_args = str_replace(TDOMF_MACRO_VAR_START,'',trim($match));
            $var_args = str_replace(TDOMF_MACRO_END,'',$var_args);

            if(strpos($var_args,',') !== false) {
                // contains multiple args
                $var_args = split(',',$var_args);
            } else {
                $var_args = array( $var_args );
            }
            
            foreach($var_args as $var_arg) {
                
                if(strpos($var_arg,':') !== false) {
                    $args = split(':',$var_arg,2);
                    switch($args[0]) {
                       case TDOMF_MACRO_VAR_COOKIE:
                           if(isset($_COOKIE[$args[1]])) {
                               $var = $_COOKIE[$args[1]];
                               $var_found = true;
                           }
                       break;
                       case TDOMF_MACRO_VAR_POST:
                           if(isset($post_args[$args[1]])) {
                               $var = $post_args[$args[1]];
                               $var_found = true;
                           } else if(isset($_POST[$args[1]])) {
                               $var = $_POST[$args[1]];
                               $var_found = true;
                           }
                           break;
                       case TDOMF_MACRO_VAR_DEFAULT:
                           $var = $args[1];
                           $var_found = true;
                           break;
                       default:
                           // error
                       break;
                    }
                } else {
                    // error
                }
                if($var_found) { break; }
            }
            
            if($var_found) {
                 $patterns[] = '/'.trim($match).'/';
                 $replacements[] = $var;
            } else {
                 $unused_patterns[] = '/'.trim($match).'/';
            }
            
            
        }
        if(!empty($patterns)) {
            $string = preg_replace($patterns,$replacements,$string);
        }
        if(!empty($unused_patterns)) {
            $string = preg_replace($unused_patterns,"",$string);
        }
    }
    return $string;
}

function tdomf_process_if($string) {
    if(preg_match_all('|'.TDOMF_MACRO_IF_START.'.*?'.TDOMF_MACRO_FI.'|s', $string, $matches) > 0) {
        $patterns = array();
        $replacements = array();
        foreach($matches[0] as $match) {
            if(preg_match_all('|'.TDOMF_MACRO_START.'(.*?)(?=('.TDOMF_MACRO_END.'))|s', $match, $submatches) > 0) {
                $output = "";
                for($i = 0; $i < count($submatches[1]); $i++) {
                    if(strpos($submatches[0][$i],TDOMF_MACRO_IF_START) === 0 && $i == 0) {
                        $if_eval = str_replace(TDOMF_MACRO_IF_START,'',$submatches[0][$i]);
                        if(eval($if_eval)) {
                            $output = $submatches[1][$i+1];
                            break;
                        } else {
                            $i++;
                        }
                    } else if(strpos($submatches[0][$i],TDOMF_MACRO_ELSEIF_START) === 0) {
                        $elseif_eval = str_replace(TDOMF_MACRO_ELSEIF_START,'',$submatches[0][$i]);
                        if(eval($elseif_eval)) {
                            $output = $submatches[1][$i+1];
                            break;
                        } else {
                            $i++;
                        }
                    } else if(strpos($submatches[0][$i],TDOMF_MACRO_ELSE_START) === 0){
                        $output = $submatches[1][$i+1];
                        break;
                    } else if(strpos($submatches[0][$i],TDOMF_MACRO_FI_START) === 0){
                        $output = "";
                        break;
                    } else {
                        // error
                        $output = "";
                        break;
                    }
                }
                $patterns[] = '/'.trim($match).'/s';
                $replacements[] = $output;
            } else {
                $unused_patterns[] = '/'.trim($match).'/s';
            }
        }
        if(!empty($patterns)) {
            $string = preg_replace($patterns,$replacements,$string);
        }
        if(!empty($unused_patterns)) {
            $string = preg_replace($unused_patterns,"",$string);
        }
    }
    return $string;
}

function tdomf_process_eval($string) {

    if(preg_match_all('|'.TDOMF_MACRO_EVAL_START.'.*?'.TDOMF_MACRO_END.'|s', $string, $matches) > 0) {
        $patterns = array();
        $replacements = array();
        $unused_patterns = array();
        foreach($matches[0] as $match) {

            $eval_args = str_replace(TDOMF_MACRO_EVAL_START,'',trim($match));
            $eval_args = str_replace(TDOMF_MACRO_END,'',$eval_args);

            $eval_ret = eval($eval_args);

            if($eval_ret != false) {
                 $patterns[] = '/'.trim($match).'/s';
                 $replacements[] = $eval_ret;
            } else {
                 $unused_patterns[] = '/'.trim($match).'/s';
            }
            
            
        }
        if(!empty($patterns)) {
            $string = preg_replace($patterns,$replacements,$string);
        }
        if(!empty($unused_patterns)) {
            $string = preg_replace($unused_patterns,"",$string);
        }
    }
    return $string;
} */

function tdomf_prepare_string($message, $form_id = false, $mode = "", $post_id = false, $errors = "", $post_args = array()) {
    global $current_user;
    if($post_id !== false) {
        $post = &get_post($post_id);
        
        $patterns = array ( '/'.TDOMF_MACRO_SUBMISSIONURL.'/',
                            '/'.TDOMF_MACRO_SUBMISSIONDATE.'/',
                            '/'.TDOMF_MACRO_SUBMISSIONTIME.'/',
                            '/'.TDOMF_MACRO_SUBMISSIONTITLE.'/');
        $replacements = array( get_permalink($post_id),
                               gmdate(get_option('date_format'), strtotime($post->post_date)),
                               gmdate(get_option('time_format'), strtotime($post->post_date)),
                               $post->post_title);
                
        $message = preg_replace($patterns,$replacements,$message);
    }
    
    if(!empty($errors)) {
        $message = preg_replace('/'.TDOMF_MACRO_SUBMISSIONERRORS.'/',$errors,$message);
    }
    
    if(is_user_logged_in()) {
        get_currentuserinfo();
        $message = preg_replace('/'.TDOMF_MACRO_USERNAME.'/',$current_user->display_name,$message);
    } else if( $post_id !== false) {
        $message = preg_replace('/'.TDOMF_MACRO_USERNAME.'/',get_post_meta($post_id,TDOMF_KEY_NAME,true),$message);
    } else {
        $message = preg_replace('/'.TDOMF_MACRO_USERNAME.'/',__("Unregistered","tdomf"),$message);
    }
    
    $message = preg_replace('/'.TDOMF_MACRO_IP.'/',$_SERVER['REMOTE_ADDR'],$message);
    
    if($form_id !== false) {
        
        # @TODO: Form Key and Widgets need to be done on demand only

        $patterns = array ( '/'.TDOMF_MACRO_FORMURL.'/',
                            '/'.TDOMF_MACRO_FORMID.'/',
                            '/'.TDOMF_MACRO_FORMNAME.'/',
                            '/'.TDOMF_MACRO_FORMDESCRIPTION.'/' );
        $replacements = array ( $_SERVER['REQUEST_URI'].'#tdomf_form'.$form_id,
                                $form_id,
                                tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id),
                                tdomf_get_option_form(TDOMF_OPTION_DESCRIPTION,$form_id) );
        $message = preg_replace($patterns,$replacements,$message);
    }

    // execute any PHP code in the message    
    ob_start();
    extract($post_args,EXTR_PREFIX_INVALID,"tdomf_");
    $message = @eval("?>".$message);
    $message = ob_get_contents();
    ob_end_clean();
    
    return $message;
}

function tdomf_get_message_instance($key, $form_id = false, $mode = "", $post_id = false, $errors = "") {
    global $current_user;
    $message = tdomf_get_message($key,$form_id);
    if(!empty($message) || $message !== false) {
        return tdomf_prepare_string($message, $form_id, $mode, $post_id, $errors);
    }
    return "";
}

function tdomf_get_message_default($key) {
    switch($key) {
        case TDOMF_OPTION_MSG_SUB_PUBLISH:
            $retVal = __("Your submission \"%%SUBMISSIONTITLE%%\" has been automatically published. You can see it <a href='%%SUBMISSIONURL%%'>here</a>. Thank you for using this service.","tdomf");
            break;
        case TDOMF_OPTION_MSG_SUB_FUTURE:
            $retVal = __("Your submission has been accepted and will be published on %%SUBMISSIONDATE%% at %%SUBMISSIONTIME%%. Thank you for using this service.","tdomf");
            break;
        case TDOMF_OPTION_MSG_SUB_SPAM:
            $retVal = __("Your submission is being flagged as spam! Sorry","tdomf");
            break;
        case TDOMF_OPTION_MSG_SUB_MOD:
            $retVal = __("Your post submission has been added to the moderation queue. It should appear in the next few days. Thank you for using this service.","tdomf");
            break;
        case TDOMF_OPTION_MSG_SUB_ERROR:
            $retVal = __("Your submission contained errors:<br/><br/>%%SUBMISSIONERRORS%%<br/><br/>Please correct and resubmit.","tdomf");
            break;
        case TDOMF_OPTION_MSG_PERM_BANNED_USER:
            $retVal = __("You (%%USERNAME%%) are banned from using this form.","tdomf");
            break;
        case TDOMF_OPTION_MSG_PERM_BANNED_IP:
            $retVal = __("Your IP %%IP%% does not currently have permissions to use this form.","tdomf");
            break;
        case TDOMF_OPTION_MSG_PERM_THROTTLE:
            $retVal = __("You have hit your submissions quota. Please wait until your existing submissions are approved.","tdomf");
            break;
        case TDOMF_OPTION_MSG_PERM_INVALID_USER:
            $retVal = __("You (%%USERNAME%%) do not currently have permissions to use this form.","tdomf");
            break;
        case TDOMF_OPTION_MSG_PERM_INVALID_NOUSER:
            $retVal = __("Unregistered users do not currently have permissions to use this form.","tdomf");
            break;            
        default:
            $retVal = "";
            break;
    }
    return $retVal;
}

?>
