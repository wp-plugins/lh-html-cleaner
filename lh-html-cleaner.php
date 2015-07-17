<?php 
/*
Plugin Name: LH HTML Cleaner
Plugin URI: http://lhero.org/plugins/lh-html-cleaner/
Description: Removes all inline style tags from the content of posts/pages/custom post types on save.
Author: Peter Shaw
Version: 1.0
Author URI: http://shawfactor.com
Network: true
*/

class LH_html_cleaner_plugin {

var $opt_name = "lh_html_cleaner-options";
var $hidden_field_name = 'lh_html_cleaner-submit_hidden';
var $options;
var $filename;
var $blacklisted_tags_field_name = "blacklisted_tags_field";
var $blacklisted_attributes_field_name = "blacklisted_attributes_field";


private function array_fix( $array )    {
        return array_filter(array_map( 'trim', $array ));

}

private function removeElementsByTagNames($tagNames, $document) {
foreach($tagNames as $tagName ){
  $nodeList = $document->getElementsByTagName($tagName);
  for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0; ) {
    $node = $nodeList->item($nodeIdx);
    $node->parentNode->removeChild($node);
  }
}
}


private function removeAttributeByAttributeNames($attributeNames, $document) {
foreach($attributeNames as $attributeName ){
foreach($document->getElementsByTagName('*') as $element ){
if ($element->getAttribute($attributeName)){
$element->removeAttribute($attributeName);
}
}
}
}

function plugin_menu() {
add_options_page('LH HTML Cleaner', 'LH HTML Cleaner', 'manage_options', $this->filename, array($this,"plugin_options"));

}

function plugin_options() {

if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}


   
 // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'

if( isset($_POST[  $this->hidden_field_name ]) && $_POST[  $this->hidden_field_name ] == 'Y' ) {

$blacklisted_tags_pieces = explode(",", sanitize_text_field($_POST[ $this->blacklisted_tags_field_name ]));

if (is_array($blacklisted_tags_pieces)){

$options[ $this->blacklisted_tags_field_name ] = $this->array_fix($blacklisted_tags_pieces);

}

$blacklisted_attributes_pieces = explode(",", sanitize_text_field($_POST[ $this->blacklisted_attributes_field_name ]));

if (is_array($blacklisted_attributes_pieces)){

$options[ $this->blacklisted_attributes_field_name ] = $this->array_fix($blacklisted_attributes_pieces);

}

if (update_site_option( $this->opt_name, $options )){


$this->options = get_site_option($this->opt_name);

?>
<div class="updated"><p><strong><?php _e('HTML settings saved', 'menu-test' ); ?></strong></p></div>
<?php


}



} 

  // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __('LH HTML Cleaner', 'menu-test' ) . "</h2>";

    // settings form
    
    ?>

<form name="lh_login_page-backend_form" method="post" action="">
<input type="hidden" name="<?php echo $this->hidden_field_name; ?>" value="Y" />

<p><?php _e("Blacklisted tags;", 'menu-test' ); ?> 
<input type="text" name="<?php echo $this->blacklisted_tags_field_name; ?>" id="<?php echo $this->blacklisted_tags_field_name; ?>" value="<?php echo implode(",", $this->options[ $this->blacklisted_tags_field_name ]); ?>" size="60" placeholder="enter a comma separated list of blacklisted tags e.g.: script,object etc" />
</p>

<p><?php _e("Blacklisted attributes;", 'menu-test' ); ?> 
<input type="text" name="<?php echo $this->blacklisted_attributes_field_name; ?>" id="<?php echo $this->blacklisted_attributes_field_name; ?>" value="<?php echo implode(",", $this->options[ $this->blacklisted_attributes_field_name ]); ?>" size="60" placeholder="enter a comma separated list of blacklisted attributes e.g.: style,font etc" />
</p>


<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>



</div>

<?php





}

public function sanitize_content( $content ) {
$doc = new DOMDocument();

// load the HTML into the DomDocument object (this would be your source HTML)
$doc->loadHTML(stripslashes($content));

// Remove blacklisted elements
$this->removeElementsByTagNames($this->options[ $this->blacklisted_tags_field_name ], $doc);
// Remove blacklisted attributes
$this->removeAttributeByAttributeNames($this->options[ $this->blacklisted_attributes_field_name ], $doc);

// return cleaned html

$content = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $doc->saveHtml());

return $content;

}


// add a settings link next to deactive / edit
public function add_settings_link( $links, $file ) {

	if( $file == $this->filename ){
		$links[] = '<a href="'. admin_url( 'options-general.php?page=' ).$this->filename.'">Settings</a>';
	}
	return $links;
}



function __construct() {

$this->options = get_site_option($this->opt_name);

$this->filename = plugin_basename( __FILE__ );

add_action('admin_menu', array($this,"plugin_menu"));

add_filter('content_save_pre' , array($this,"sanitize_content"));

add_filter('plugin_action_links', array($this,"add_settings_link"), 10, 2);

}

}


$lh_html_cleaner = new LH_html_cleaner_plugin();




?>