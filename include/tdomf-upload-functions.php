<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////
// Upload Files Core //
///////////////////////

// 1. User uploads files to a temporary area. Files will be deleted within an
//    hour if not "claimed"
// 2. User submits post. 
// 3. Widget copies the files from a temporary area to their proper location and
//    updates post with info about claimed files.
// 
// * If post is deleted, files are automatically deleted
// * No direct links to files are exposed (as long as the admins specify a 
//   location not directly exposed to the web)

// Figure out the storage path for this user/ip and thusly create it
//
function tdomf_create_tmp_storage_path() {
  global $current_user;
  $options = tdomf_widget_upload_get_options(); 
  get_currentuserinfo();
  if(is_user_logged_in()) {  
    $storagepath = $options['path'].DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$current_user->user_login;
  } else {
    $ip =  $_SERVER['REMOTE_ADDR'];
    $storagepath = $options['path'].DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$ip;
  }
  if(!file_exists($storagepath)) {
    tdomf_log_message("$storagepath does not exist. Creating it.");
    #mkdir($storagepath,'0777',true); <-- the permissions do not get set correctly with this method
    tdomf_recursive_mkdir($storagepath,TDOMF_UPLOAD_PERMS);
  } 
  return $storagepath;
}

// Turn file size in bytes to an intelligable format 
// Taken from http://www.phpriot.com/d/code/strings/filesize-format/index.html
//
function tdomf_filesize_format($bytes, $format = '', $force = '')
    {
        $force = strtoupper($force);
        $defaultFormat = '%01d %s';
        if (strlen($format) == 0)
            $format = $defaultFormat;
 
        $bytes = max(0, (int) $bytes);
 
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
 
        $power = array_search($force, $units);
 
        if ($power === false)
            $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
 
        return sprintf($format, $bytes / pow(1024, $power), $units[$power]);
    }

// Delete a temp file. Function used to clean out upload files after 1 hour.
//
function tdomf_delete_tmp_file($filepath) {
  #tdomf_log_message("tdomf_delete_tmp_file for $filepath");
  if(file_exists($filepath)) {
     tdomf_log_message("Attempting to delete $filepath...");
     if(unlink($filepath)) {
       tdomf_log_message("Deleted $filepath!");
     } else {
       tdomf_log_message("Could not delete $filepath",TDOMF_LOG_ERROR);
     }
  }
}
add_action( 'tdomf_delete_tmp_file_hook', 'tdomf_delete_tmp_file' );

// Download handler
//
function tdomf_upload_download_handler(){
   global $current_user;
   $post_ID = $_GET['tdomf_download'];
   $file_ID = $_GET['id'];
   $use_thumb = isset($_GET['thumb']);
   
   // Security check
   get_currentuserinfo();   
   if(!current_user_can("publish_posts")) {
     $post = get_post($post_ID);
     if($post->post_status != 'publish') {
       return;
     }
   }
   
   if($use_thumb) {
      $filepath = get_post_meta($post_ID, TDOMF_KEY_DOWNLOAD_THUMB.$file_ID, true);   
   } else {
      $filepath = get_post_meta($post_ID, TDOMF_KEY_DOWNLOAD_PATH.$file_ID, true);
   }
   if(!empty($filepath)) {

     if(!$use_thumb) {
        $type = get_post_meta($post_ID, TDOMF_KEY_DOWNLOAD_TYPE.$file_ID, true);
     }
     $name = get_post_meta($post_ID, TDOMF_KEY_DOWNLOAD_NAME.$file_ID, true);

     // Check if file exists
     //
     if(file_exists($filepath)) {
       
       @ignore_user_abort();
       @set_time_limit(600);
       if(!empty($type)) {
          $mimetype = $type;
       } else if(function_exists('mime_content_type')) { // set mime-type
          $mimetype = mime_content_type($filepath);
       } else {
          // default
          $mimetype = 'application/octet-stream';         
       }
      
       if(!$use_thumb) {       

       // Other stuff we could track...
       //
       //$referer = $_SERVER['HTTP_REFERER'];
       //ip = $_SERVER['REMOTE_ADDR'];
       //$now = date('Y-m-d H:i:s');

       // Update count
       //
       // This includes partial downloads! If wanted only full downloads
       // we would track it afterwards
       //
       $count = intval(get_post_meta($post_ID, TDOMF_KEY_DOWNLOAD_COUNT.$file_ID, true));
       $count++;
       update_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_COUNT.$file_ID,$count);
       
       }

       // Pass file       
       $handle = fopen($filepath, "rb"); // now let's get the file!
       #header("Pragma: "); // Leave blank for issues with IE
       #header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
       header("Content-Type: $mimetype");
       #header("Content-Disposition: attachment; filename=\"".basename($filepath)."\"");
       header("Content-Length: " . (filesize($filepath)));
       sleep(1);
       fpassthru($handle);
       return;
     } else {
       tdomf_log_message("File $filepath does not exist!",TDOMF_LOG_ERROR);
     }
   } else {
     tdomf_log_message("No file found on post with that id!",TDOMF_LOG_ERROR);
   }
   header("HTTP/1.0 404 Not Found");
   exit();
}
if(isset($_GET['tdomf_download'])) { 
  add_action('init', 'tdomf_upload_download_handler');
}

// Create path recursivily
//
function tdomf_recursive_mkdir($path, $mode = 0777) {
    $dirs = explode(DIRECTORY_SEPARATOR , $path);
    $count = count($dirs);
    $path = '';
    for ($i = 0; $i < $count; ++$i) {
        $path .= DIRECTORY_SEPARATOR . $dirs[$i];
        if (!is_dir($path) && !mkdir($path, $mode)) {
            return false;
        }
    }
    return true;
}

// Preview handler
//
function tdomf_upload_preview_handler(){
   $id = $_GET['tdomf_upload_preview'];
   $key = $_GET['key'];

   // Session start
   //
   if (!isset($_SESSION)) session_start();
   
   // Security check   
   if($_SESSION['tdomf_upload_preview_key'] != $key) {
     return;
   }
   
   if(!isset($_SESSION['uploadfiles'][$id])) {
     tdomf_log_message("(preview) No file with that id! $id",TDOMF_LOG_ERROR);
     return;
   }
      
   $filepath = $_SESSION['uploadfiles'][$id]['path'];
   if(!empty($filepath)) {

     $type = $_SESSION['uploadfiles'][$id]['type'];
     
     // Check if file exists
     //
     if(file_exists($filepath)) {
       
       @ignore_user_abort();
       @set_time_limit(600);
       if(function_exists('mime_content_type')) { // set mime-type
          $mimetype = mime_content_type($filepath);
       } else if(!empty($type)) {
          $mimetype = $type;
       } else {
          // default
          $mimetype = 'application/octet-stream';         
       }
      
       // Pass file       
       $handle = fopen($filepath, "rb"); // now let's get the file!
       #header("Pragma: "); // Leave blank for issues with IE
       #header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
       header("Content-Type: $mimetype");
       #header("Content-Disposition: attachment; filename=\"".basename($filepath)."\"");
       header("Content-Length: " . (filesize($filepath)));
       sleep(1);
       fpassthru($handle);
       return;
     } else {
       tdomf_log_message("(preview) File $filepath does not exist!",TDOMF_LOG_ERROR);
     }
   } else {
     tdomf_log_message("(preview) No file found on post with that id!",TDOMF_LOG_ERROR);
   }
   header("HTTP/1.0 404 Not Found");
   exit();
}
if(isset($_GET['tdomf_upload_preview'])) { 
  add_action('init', 'tdomf_upload_preview_handler');
}

// Delete a folder and contents
// Taken from http://ie2.php.net/manual/en/function.rmdir.php
//
function tdomf_deltree($path) {
  if (is_dir($path)) {
    $entries = scandir($path);
    foreach ($entries as $entry) {
      if ($entry != '.' && $entry != '..') {
        tdomf_deltree($path.DIRECTORY_SEPARATOR.$entry);
      }
    }
    rmdir($path);
  } else {
    unlink($path);
  }
}

// Delete files associated with a post when a post is deleted
//
function tdomf_upload_delete_post_files($post_ID) {
  // get first file, if it exists. Get directory. Delete directory and contents.
  $filepath = get_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_PATH.'0',true);
  $dirpath = dirname($filepath);
  if(file_exists($dirpath)) {
    tdomf_deltree($dirpath);
  }
}
add_action('delete_post', 'tdomf_upload_delete_post_files');

////////////////////////////////////////////////////////////////////////////////
//                                           Default Widget: "Upload Files"   //
////////////////////////////////////////////////////////////////////////////////

// Required for creating images using attachments
//
include_once(ABSPATH . 'wp-admin/includes/admin.php');

// Get Options for this widget
//
function tdomf_widget_upload_get_options() {
  $options = get_option('tdomf_upload_widget');
    if($options == false) {
       $options = array();
       $options['title'] = '';
       $options['path'] = ABSPATH.DIRECTORY_SEPARATOR.'wp-content'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
       $options['types'] = ".txt .doc .pdf .jpg .gif .zip";
       $options['size'] = 1048576;
       $options['min'] = 0;
       $options['max'] = 1;
       $options['cmd'] = "";
       $options['attach'] = true;
       $options['a'] = true;
       $options['img'] = false;
       $options['custom'] = true;
       $options['custom-key'] = __("Download Link","tdomf");
       $options['post-title'] = false;
       $options['attach-a'] = false;
       $options['attach-thumb-a'] = false;
       $options['thumb-a'] = false;
    }
  return $options;
}

//////////////////////////////
// Display the widget! 
//
function tdomf_widget_upload($args) {
  extract($args);
  $options = tdomf_widget_upload_get_options();
  
  $output  = $before_widget;  
  if($options['title'] != "") {
    $output .= $before_title.$options['title'].$after_title;
  }
  $inline_path = TDOMF_URLPATH."tdomf-upload-inline.php";
  // my best guestimate
  $height = 160 + (intval($options['max']) * 30);
  $output .= "<iframe id='uploadfiles_inline' name='uploadfiles_inline' frameborder='0' marginwidth='0' marginheight='0' width='100%' height='$height' src='$inline_path'></iframe>";
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('Upload Files', 'tdomf_widget_upload');

//////////////////////////////
// Post-post stuff
//
// Post is submitted, move files to correct area and update post with links 
//
function tdomf_widget_upload_post($args) {
  extract($args);
  $options = tdomf_widget_upload_get_options();
  
  $modifypost = false;
  if($options['post-title'] || $options['a'] || $options['img']) {
    // Grab existing data
    $post = wp_get_single_post($post_ID, ARRAY_A);
    //$post = add_magic_quotes($post); 
    $content = $post['post_content'];
    $title = $post['post_title'];
    $cats = $post['post_category'];
  }
  
  $filecount = 0;
  $theirfiles = $_SESSION['uploadfiles'];
  for($i =  0; $i < $options['max']; $i++) {
    if(!file_exists($theirfiles[$i]['path'])) {
      unset($theirfiles[$i]);
    } else {
      $filecount++;
      // move file
      $postdir = $options['path'].DIRECTORY_SEPARATOR.$post_ID.DIRECTORY_SEPARATOR;
      $newpath = $postdir.$theirfiles[$i]['name'];
      tdomf_recursive_mkdir($postdir,TDOMF_UPLOAD_PERMS);
      if(rename($theirfiles[$i]['path'], $newpath)) {
       
        // store info about files on post
        //        
        add_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_COUNT.$i,0,true);
        add_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_TYPE.$i,$theirfiles[$i]['type'],true);
        add_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_PATH.$i,$newpath,true);
        add_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_NAME.$i,$theirfiles[$i]['name'],true);
        
        tdomf_log_message( "File ".$theirfiles[$i]['name']." saved from tmp area to ".$newpath." with type ".$theirfiles[$i]['type']." for post $post_ID" );
        
        // Execute user command
        //
        if($options['cmd'] != "") {
          $cmd_output = shell_exec ( $options['cmd'] . " " . $newpath );
          tdomf_log_message("Executed user command on file $newpath<br/><pre>$cmd_output</pre>");
          add_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_CMD_OUTPUT.$i,$cmd_output,true);
        }
        
        $uri = get_bloginfo('wpurl').'/?tdomf_download='.$post_ID.'&id='.$i;
        
        // Modify Post
        //
        
        // modify post title
        if($options['post-title']) {
          $modifypost = true;
          $title = $theirfiles[$i]['name'];
        }
        // add download link (inc name and file size)
        if($options['a']) {
          $modifypost = true;
          $content .= "<p><a href=\"$uri\">".$theirfiles[$i]['name']." (".tdomf_filesize_format(filesize($newpath)).")</a></p>";
        }
        // add image link (inc name and file size)
        if($options['img']) {
          $modifypost = true;
          $content .= "<p><img src=\"$uri\" /></p>";
        }
        
        // Use user-defined custom key 
        if($options['custom'] && !empty($options['custom-key'])) {
          add_post_meta($post_ID,$options['custom-key'],$uri);
        }

        // Insert upload as an attachment to post!
        if($options['attach']) {
          
          // Create the attachment (not sure if these values are correct)
          //
          $attachment = array (
           "post_content"   => "",
           "post_title"     => $theirfiles[$i]['name'],
           "post_name"      => sanitize_title($theirfiles[$i]['name']),
           "post_status"    => 'inherit',
           "post_parent"    => $post_ID,
           "guid"           => $uri,
           "post_type"      => 'attachment',          
           "post_mime_type" => $theirfiles[$i]['type'],
           "menu_order"     => $i,
           "post_category"  => $cats,
          );
          $attachment_ID = wp_insert_attachment($attachment, $newpath, $post_ID);
          
          // Generate meta data (which includes thumbnail!)
          // 
          $attachment_metadata = wp_generate_attachment_metadata( $attachment_ID, $newpath );

          // add link to attachment page
          if($options['attach-a']) {
            $content .= "<p><a href=\"".get_permalink($attachment_ID)."\">".$theirfiles[$i]['name']." (".tdomf_filesize_format(filesize($newpath)).")</a></p>";
          }
          
          // Did Wordpress generate a thumbnail?
          if(isset($attachment_metadata['thumb'])) {
             // Wordpress 2.3 uses basename and generates only the "name" of the thumb,
             // in general it creates it in the same place as the file!
             $thumbpath = $postdir.$attachment_metadata['thumb'];
             if(file_exists($thumbpath)) {
                
                add_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_THUMB.$i,$thumbpath,true);
                
                // WARNING: Don't modify the 'thumb' as this is used by Wordpress to know
                // if there is a thumb by using basename and the file path of the actual file
                // attachment
                //
                $thumburi = get_bloginfo('wpurl').'/?tdomf_download='.$post_ID.'&id='.$i.'&thumb';
                
                //$attachment_metadata['thumb'] = $thumb_uri;
                //$attachment_metadata['thumb'] = $thumbpath;
                
                // add thumbnail link to attachment page
                if($options['attach-thumb-a']) {
                  $modifypost = true;
                  $content .= "<p><a href=\"".get_permalink($attachment_ID)."\"><img src=\"$thumburi\" alt=\"".$theirfiles[$i]['name']." (".tdomf_filesize_format(filesize($newpath)).")\" /></a></p>";
                }
                // add thumbnail link directly to file
                if($options['thumb-a']) {
                  $modifypost = true;
                  $content .= "<p><a href=\"$uri\"><img src=\"$thumburi\" alt=\"".$theirfiles[$i]['name']." (".tdomf_filesize_format(filesize($newpath)).")\" /></a></p>";
                }
             } else {
                tdomf_log_message("Could not find thumbnail $thumbpath!",TDOMF_LOG_ERROR);
             }
          } 

          // Add meta data
          // 
          wp_update_attachment_metadata( $attachment_ID, $attachment_metadata );

          tdomf_log_message("Added " . $theirfiles[$i]['name'] . " as attachment");
        }
        
      } else {
        tdomf_log_message("Failed to move " . $theirfiles[$i]['name'] . "!",TDOMF_LOG_ERROR);
        return $before_widget.__("Failed to move uploaded file from temporary location!","tdomf").$after_widget;
      }
    }
  }
  
  if($modifypost) {
    tdomf_log_message("Attempting to update post with file upload info");
    $post = array (
      "ID"                      => $post_ID,
      "post_content"            => $content,
      "post_title"              => $title,
      "post_name"               => sanitize_title($title),
    );
    sanitize_post($post,"db");
    wp_update_post($post);
  }
 
  return NULL;
}
tdomf_register_form_widget_post('Upload Files', 'tdomf_widget_upload_post');

////////////////////////////////
// Validate uploads if possible
//
function tdomf_widget_upload_validate($args) {
  extract($args);
  $options = tdomf_widget_upload_get_options();
  if(!isset($_SESSION)) {
    return $before_widget.__("SESSION has not be started! Something is wrong with your TDOMF installation.","tdomf").$after_widget;
  }
  if($options['min'] > 0 && !isset($_SESSION['uploadfiles'])) {
    return $before_widget.sprintf(__("No files have been uploaded yet. You must upload a minimum of %d files.","tdomf"),$options['min']).$after_widget;
  }
  $theirfiles = $_SESSION['uploadfiles'];
  $filecount = 0;
  for($i =  0; $i < $options['max']; $i++) {
    if(!file_exists($theirfiles[$i]['path'])) {
      unset($theirfiles[$i]);
    } else {
      $filecount++;
    }
  }
  if($filecount < $options['min']) {
    return $before_widget.sprintf(__("You must upload a minimum of %d files.","tdomf"),$options['min']).$after_widget;
  }
  return NULL;
}
tdomf_register_form_widget_validate('Upload Files', 'tdomf_widget_upload_validate');

//////////////////////////////
// Preview uplaods if possible
//
function tdomf_widget_upload_preview($args) {
  extract($args);
  $options = tdomf_widget_upload_get_options();

  $random_string = tdomf_random_string(100);
  $_SESSION['tdomf_upload_preview_key'] = $random_string;
  
  $output = $before_widget;
  $theirfiles = $_SESSION['uploadfiles'];
  for($i =  0; $i < $options['max']; $i++) {
    if(file_exists($theirfiles[$i]['path'])) {
      $uri = get_bloginfo('wpurl').'/?tdomf_upload_preview='.$i."&key=".$random_string;
      if($options['a']) {
        $output .= "<p><a href=\"$uri\">".$theirfiles[$i]['name']." (".tdomf_filesize_format(filesize($theirfiles[$i]['path'])).")</a></p>";
      }
      if($options['img']) {
        $output .= "<p><img src=\"$uri\" /></p>";
      }
    }
  }
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_preview('Upload Files', 'tdomf_widget_upload_preview');

////////////////////////////////////
// Add info on files to admin email 
//
function tdomf_widget_upload_adminemail($args) {
  extract($args);
  $options = tdomf_widget_upload_get_options();
  
  $output = "";
  for($i =  0; $i < $options['max']; $i++) {
    $filepath = get_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_PATH.$i,true);
    if(file_exists($filepath)) {
      $name = get_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_NAME.$i,true);
      $uri = get_bloginfo('wpurl').'/?tdomf_download='.$post_ID.'&id='.$i;
      $size = tdomf_filesize_format(filesize($filepath));
      $cmd = get_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_CMD_OUTPUT.$i,true);
      $type = get_post_meta($post_ID,TDOMF_KEY_DOWNLOAD_TYPE.$i,true);
      $output .= sprintf(__("File %s was uploaded with submission.\r\nPath: %s\r\nSize: %s\r\nType: %s\r\nURL (can only be accessed by administrators until post published):\r\n%s\r\n\r\n","tdomf"),$name,$filepath,$size,$type,$uri);
      if($cmd != false && !empty($cmd)) {
        $output .= sprintf(__("User Command:\r\n\"%s %s\"\r\n\r\n%s\r\n\r\n","tdomf"),$options['cmd'],$filepath,$cmd);
      }
    }
  }
  if($output != "") {
    return $before_widget.$output.$after_widget;
  }
  return  $before_widget.__("No files uploaded with this post!","tdomf").$after_widget;
}
tdomf_register_form_widget_adminemail('Upload Files', 'tdomf_widget_upload_adminemail');

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_upload_control() {
  $options = tdomf_widget_upload_get_options();
  
  // Store settings for this widget
  if ( $_POST['upload-files-submit'] ) {
      $newoptions = array();
      $newoptions['title'] = $_POST['upload-files-title'];
      $newoptions['path'] = $_POST['upload-files-path'];
      $newoptions['types'] = $_POST['upload-files-types'];
      $newoptions['size'] = intval($_POST['upload-files-size']);
      $newoptions['min'] = intval($_POST['upload-files-min']);
      $newoptions['max'] = intval($_POST['upload-files-max']);
      $newoptions['cmd'] = $_POST['upload-files-cmd'];
      $newoptions['attach'] = isset($_POST['upload-files-attach']);
      $newoptions['a'] = isset($_POST['upload-files-a']);
      $newoptions['img'] = isset($_POST['upload-files-img']);
      $newoptions['custom'] = isset($_POST['upload-files-custom']);
      $newoptions['custom-key'] = $_POST['upload-files-custom-key'];
      $newoptions['post-title'] = isset($_POST['upload-files-post-title']);
      $newoptions['attach-a'] = isset($_POST['upload-files-attach-a']);
      $newoptions['attach-thumb-a'] = isset($_POST['upload-files-attach-thumb-a']);
      $newoptions['thumb-a'] = isset($_POST['upload-files-thumb-a']);      
      
      if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_upload_widget', $options);
     }
  }

   // Display control panel for this widget
  
        ?>
<p style="text-align:left;">

<label for="upload-files-title">
<?php _e("Title: ","tdomf"); ?>
<input type="text" id="upload-files-title" name="upload-files-title" value="<?php echo $options['title']; ?>" />
</label><br/><br/>

<label for="upload-files-path" ><?php _e("Path to store uploads (should not be publically accessible):","tdomf"); ?><br/>
<input type="textfield" size="40" id="upload-files-path" name="upload-files-path" value="<?php echo $options['path']; ?>" />
</label><br/><br/>

<label for="upload-files-types" ><?php _e("Allowed File Types:","tdomf"); ?><br/>
<input type="textfield" size="40" id="upload-files-types" name="upload-files-types" value="<?php echo $options['types']; ?>" />
</label><br/><br/>

<label for="upload-files-post-title">
<input type="checkbox" name="upload-files-post-title" id="upload-files-post-title" <?php if($options['post-title']) echo "checked"; ?> >
<?php _e("Use filename as post title (as long as the content widget doesn't set it)","tdomf"); ?>
</label><br/><br/>

<label for="upload-files-size">
<input type="textfield" name="upload-files-size" id="upload-files-size" value="<?php echo $options['size']; ?>" size="10" />
<?php printf(__("Max File Size in bytes. Example: 1024 = %s, 1048576 = %s","tdomf"),tdomf_filesize_format(1024),tdomf_filesize_format(1048576)); ?> 
</label><br/><br/>

<label for="upload-files-min">
<input type="textfield" name="upload-files-min" id="upload-files-min" value="<?php echo $options['min']; ?>" size="2" />
<?php _e("Minimum File Uploads <i>(0 indicates file uploads optional)</i>","tdomf"); ?> 
</label><br/>

<label for="upload-files-size">
<input type="textfield" name="upload-files-max" id="upload-files-max" value="<?php echo $options['max']; ?>" size="2" />
<?php _e("Maximum File Uploads","tdomf"); ?> 
</label><br/>

<br/>

<label for="upload-files-cmd" ><?php _e("Command to execute on file after file uploaded successfully (result will be added to log). Leave blank to do nothing:","tdomf"); ?><br/>
<input type="textfield" size="40" id="upload-files-cmd" name="upload-files-cmd" value="<?php echo $options['cmd']; ?>" />
</label><br/><br/>

<label for="upload-files-attach">
<input type="checkbox" name="upload-files-attach" id="upload-files-attach" <?php if($options['attach']) echo "checked"; ?> >
<?php _e("Insert Uploaded Files as Attachments on post (this will also generate a thumbnail using Wordpress core if upload is an image)","tdomf"); ?>
</label><br/>

&nbsp;&nbsp;&nbsp;

<label for="upload-files-attach-a">
<input type="checkbox" name="upload-files-attach-a" id="upload-files-attach-a" <?php if($options['attach-a']) echo "checked"; ?> >
<?php _e("Add link to Attachment page to post content","tdomf"); ?>
</label><br/>

&nbsp;&nbsp;&nbsp;

<label for="upload-files-attach-thumb-a">
<input type="checkbox" name="upload-files-attach-thumb-a" id="upload-files-attach-thumb-a" <?php if($options['attach-thumb-a']) echo "checked"; ?> >
<?php _e("Add thumbnail link to Attachment page to post content (if thumbnail avaliable)","tdomf"); ?>
</label><br/>

&nbsp;&nbsp;&nbsp;

<label for="upload-files-thumb-a">
<input type="checkbox" name="upload-files-thumb-a" id="upload-files-thumb-a" <?php if($options['thumb-a']) echo "checked"; ?> >
<?php _e("Add thumbnail as download link to post content (if thumbnail avaliable)","tdomf"); ?>
</label><br/>

       
<br/>

<label for="upload-files-a">
<input type="checkbox" name="upload-files-a" id="upload-files-a" <?php if($options['a']) echo "checked"; ?> >
<?php _e("Add download link to post content","tdomf"); ?>
</label><br/>

<label for="upload-files-img">
<input type="checkbox" name="upload-files-img" id="upload-files-img" <?php if($options['img']) echo "checked"; ?> >
<?php _e("Add download link as image tag to post content","tdomf"); ?>
</label><br/>

<br/>

<label for="upload-files-custom">
<input type="checkbox" name="upload-files-custom" id="upload-files-custom" <?php if($options['custom']) echo "checked"; ?> >
<?php _e("Add Download Link as custom value","tdomf"); ?>
</label><br/>

<label for="upload-files-custom-key" ><?php _e("Name of Custom Key:","tdomf"); ?><br/>
<input type="textfield" size="40" id="upload-files-custom-key" name="upload-files-custom-key" value="<?php echo $options['custom-key']; ?>" />
</label>

</p>
        <?php 
}
tdomf_register_form_widget_control('Upload Files', 'tdomf_widget_upload_control', 500, 700);

?>