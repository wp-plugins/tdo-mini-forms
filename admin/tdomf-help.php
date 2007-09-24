<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/* Help Page */

// Display a help page
function tdomf_show_help_page() {
  ?>

  <div class="wrap">

    <h2><?php _e('TDOMF Help', 'tdomf') ?></h2>

  <p><?php _e("This plugin allows you to add a form to any page, post or template	that will allow non-registered or subscribers to submit a post that you can approve and publish.","tdomf"); ?></p>

  <h3><?php _e("Adding the form to a post or page","tdomf"); ?></h3>
<p>
<?php _e("When writing any post or page just insert this code in the textbox. The plugin will automatically replace it with the form when you publish the post.","tdomf"); ?>
<!-- &lt;!--tdomf_form1--&gt; -->
<pre>
[tdomf_form1]
</pre>
</p>

  <h3><?php _e("Adding the form to a template","tdomf"); ?></h3>
  <p>
<?php _e("You can use the code below to insert the form in any template.","tdomf"); ?>
<pre>
&lt;?php tdomf_show_form(); ?&gt;
</pre>
</p>

  <h3><?php _e("Showing who submitted the post","tdomf"); ?></h3>
  <p>
<?php _e("You can add this code to your template, within \"the loop\" to show the submitter of a post.","tdomf"); ?>
<pre>
&lt;?php tdomf_the_submitter(); ?&gt;
</pre>
<?php _e("or"); ?>
<pre>
&lt;?php echo tdomf_get_the_submitter(); ?&gt;
</pre>
</p>

</div>

<?php

}

?>
