<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

////////////////
// About Page //
////////////////

// Display a help page
//
function tdomf_show_about_page() {
  
  ?>

  <div class="wrap">

    <h2><?php _e('Supporting TDOMF', 'tdomf') ?></h2>

    <p><?php _e("TDOMF originally started as feature I wanted to add to a site which had Wordpress at it's core. It was only when I asked on the Wordpress forums about a particular problem I was having that I discovered other people were looking for this kind of plugin. A year later and here we are at version 0.7. It's not finished yet, there is a long way to go. I hope you find it useful!","tdomf"); ?></p>
    
    <p><?php _e("TDOMF is provided free, however if you wish to offer thanks or support there is a number of options. None are mandatory.","tdomf"); ?></p>
    
    <ul>
    
    <li><?php printf(__("Use the plugin and give me feedback and bug reports on the forums, either on <a href='%s'>thedeadone.net</a> or on <a href='%s'>wordpress.org</a>. You can also tell me how you are using it. I love seeing what uses people put it too. Without this kind of knowledge, I'm not sure what direction the plugin should take.","tdomf"),"http://thedeadone.net/forum","http://wordpress.org/tags/tdo-mini-forms"); ?></li>
    
    <li><?php printf(__("Drop a link to the <a href='%s'>plugin homepage</a> or even <a href='%s'>my website</a>","tdomf"),"http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/","http://thedeadone.net"); ?></li>

    <li><?php _e("If you can, send me code changes and bug fixes, so I can improve the code!","tdomf"); ?></li>

    <li><?php _e('<a href="http://codex.wordpress.org/Translating_WordPress">Translate this plugin to your langauge.</a>',"tdomf"); ?></li>
    
    <li><?php _e("If you wish, you can donate via paypal. This approach has the added benefit of encouraging me to do more work on the plugin.","tdomf"); ?>
    
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>

    </li>

    <li><?php printf(__("Buy me a book: <a href='%s'>My Amazon Wishlist</a>.","tdomf"),"http://www.amazon.co.uk/gp/registry/23S7OL9W6Q4JT"); ?></li> 
    
  </ul>

<?php /* <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&encrypted=-----BEGIN+PKCS7-----MIIIWQYJKoZIhvcNAQcEoIIISjCCCEYCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBuEC47mJwumB8%2FXIQIyehLoyT5ueMyjGzjeTnWxYcjdY3rAgkJteuOvqnnYNG7R8x9g2NVIJYHleMRt7OWrwQKY3PRAU29Mlotfg0T4k4N9ZU2mCD%2FhLDXEGE0SiP3RNCSWWSU3b%2B3gcnFrk3Tfv%2Bj97HXg6IgT87o7HHQxpQIcTELMAkGBSsOAwIaBQAwggHVBgkqhkiG9w0BBwEwFAYIKoZIhvcNAwcECCKhj3P2B%2FixgIIBsKfdpYC59PyYwHpGqFfrO%2FqUglhIaUTp%2FL9Bz0a2txlpgxzrPqAlQp8%2BMkKkB8SKt9hXe4hPX4Kv5WsNiYzFeJImsg2PjCBmUTJVQaSBcznf58UUezjUFC0kouic5DzxRPm57ABeoth3aHVexw5M%2BPYPxmhB87xlohxUt3L7%2Fmo270G5LXlB3kDR9IpbMEYZTw8mNa3DcMVGfv6pM7GKAy%2FwBEb6bShA4VRiVWchoPSHEEs%2BYVknSo9rQAdFbLXCwUMUS6NJbHG4pq8It%2F7IEDgpcVnrRSKjclnluPG73i%2FClyq36VfhejOu0WK77G90Z6Y4eOtP4UDyXuMJH%2FOypHLaPT4dclpH8ps%2FodGJ018%2BmjdV6CNqHukuchdQgx%2BwEPCyP8qaHLBMAThsPbD4hnc3Ezc8END2f49HTAQlT0aFIktnVqkF5hMj2ERdVVqYly6S9qgvtnHROQilFVUpQnWjfWbAQGhLqEWNvv0%2Fh1Pm6tgkXW3EUqVvJF2tyWiP40IMla3g93vhLpYcR2SnUlw6zqVgMHuYH21VgkLSi2y6FSEkjgeG49FGgLq5fvqog6CCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW%2BR017%2BEmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2%2FZa%2BGJ%2FqwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr%2F9j%2FiKG4Thia%2FOflx4TdL%2BIFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI%2BHnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ%2BYcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTA3MDkyNjE0MzQwM1owIwYJKoZIhvcNAQkEMRYEFClTuUrBDEQ7H6sAZIN8yB9qKifJMA0GCSqGSIb3DQEBAQUABIGAGr%2BklEj8FgUscdaxj%2FkalFxvuQnSznQDFmsPvJZfwa7Wur3EnF75m7%2BqvQOeFSZ56a3aXjSELI9ej1vXXz8mjZqUQYEeFLqvulKl3KVHS32KprXTj5iqp3TapPbeoSsMggxVxJ1HjmakNJm3UwhqlEIoc0qjf1wHPIIWSBJcAug%3D-----END+PKCS7-----%0A">direct link</a> */ ?>

    <p><?php _e('Thanks for your attention, <a href="http://thedeadone.net">Mark Cunningham</a> <img src="../wp-includes/images/smilies/icon_smile.gif" />',"tdomf"); ?></p>

</div>

<?php

}

?>
