<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/* About Page */

// Display a help page
//
function tdomf_show_about_page() {
  ?>

  <div class="wrap">

    <h2><?php _e('TDOMF About', 'tdomf') ?></h2>

    <p> TBD </p>

    <p> Delete Plugin Settings (includes settings info on individual posts)</p>

</div>

<?php

}

?>
