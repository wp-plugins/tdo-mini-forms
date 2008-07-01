<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

function tdomf_get_message($key,$form_id = false) {
    $message = "";
    if($form_id === false) {
        $message = get_option($key);
    } else {
        $message = tdomf_get_option_form($key,$form_id);
    }
    if($message === false) {
        $message = tdomf_get_message_default($key);
    }
    return $message;
}

function tdomf_protect_input($message) {
    # This function passes the string through Wordpress kses filters,
    # this should pull out javascript hacks and php code. It should be used
    # on any input
    #
    global $allowedposttags;
    #if(!current_user_can('unfiltered_html')) {
        $message = wp_kses($message,$allowedposttags);
    #}   
    return $message;
}

function tdomf_prepare_string($message, $form_id = false, $mode = "", $post_id = false, $errors = "", $post_args = array()) {
    global $current_user;
    if($post_id !== false) {
        $post = &get_post($post_id);
        
        // url, date and time are safe but title is not: scrub
        $patterns = array ( '/'.TDOMF_MACRO_SUBMISSIONURL.'/',
                            '/'.TDOMF_MACRO_SUBMISSIONDATE.'/',
                            '/'.TDOMF_MACRO_SUBMISSIONTIME.'/',
                            '/'.TDOMF_MACRO_SUBMISSIONTITLE.'/');
        $replacements = array( get_permalink($post_id),
                               mysql2date(get_option('date_format'),$post->post_date),
                               mysql2date(get_option('time_format'),$post->post_date),
                               tdomf_protect_input($post->post_title));
                
        $message = preg_replace($patterns,$replacements,$message);
    }
    
    if(!empty($errors)) {
        $message = preg_replace('/'.TDOMF_MACRO_SUBMISSIONERRORS.'/',$errors,$message);
    }
    
    if(is_user_logged_in()) {
        get_currentuserinfo();
        // might not be safe
        $message = preg_replace('/'.TDOMF_MACRO_USERNAME.'/',tdomf_protect_input($current_user->display_name),$message);
    } else if( $post_id !== false) {
        // may not be safe at all
        $message = preg_replace('/'.TDOMF_MACRO_USERNAME.'/',tdomf_protect_input(get_post_meta($post_id,TDOMF_KEY_NAME,true)),$message);
    } else {
        $message = preg_replace('/'.TDOMF_MACRO_USERNAME.'/',__("Unregistered","tdomf"),$message);
    }
    
    $message = preg_replace('/'.TDOMF_MACRO_IP.'/',$_SERVER['REMOTE_ADDR'],$message);
    
    if($form_id !== false) {
         
        // these macros are inputed by form admin so are considered safe
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
