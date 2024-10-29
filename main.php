<?php
/**
 * @package AdUnblock
 * @version 1.0
 */
/*
Plugin Name: AdUnblock
Plugin URI: http://adunblock.com/
Description: AdUnblock affichage un message aux internautes ayant un bloqueur de publicité pour les inciter à désactiver celui-ci.
Author: AdUnblock
Version: 1.1
Author URI: http://adunblock.com/
*/

add_option('adunblock_id');

function adunblock_options_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	if (isset($_POST['adunblock_id'])) {
	  update_option('adunblock_id', $_POST['adunblock_id']);
?>
<div class="updated"><p><strong><?php _e('Paramètres sauvegardés', 'menu-test' ); ?></strong></p></div>
<?php } ?>

<div class="wrap">
  <form name="form1" method="post" action="">
    <?php echo do_settings_sections('adunblock'); ?>  
    <?php echo settings_fields( 'adunblock' ); ?>
    <?php echo submit_button(); ?>
  </form>  
</div>
<?php
}

function adunblock_options_api_init() {
  add_options_page( 'Options AdUnblock', 'AdUnblock', 'manage_options', 'adunblock', 'adunblock_options_page' );
 	
 	add_settings_section('aub_setting_section',
		'Paramètres AdUnblock',
		'aub_setting_section_callback_function',
		'adunblock');
 	
 	add_settings_field('adunblock_id',
		'Identifiant du site',
		'aub_setting_callback_function',
		'adunblock',
		'aub_setting_section');
 	
 	register_setting('adunblock','adunblock_id');
}
 
function aub_setting_section_callback_function() {
 	echo '<p>Inscrivez vous sur <a href="http://adunblock.com/" target="_blank">AdUnblock</a>, ajoutez ce site puis copiez/collez ici son identifiant unique</p>';
}
 
function aub_setting_callback_function() {
 	echo '<input name="adunblock_id" id="adunblock_id" type="text" value="' . get_option('adunblock_id') . '">';
}

add_action('admin_menu', 'adunblock_options_api_init');



function add_adunblock_code() {
  $code = get_transient('code_adunblock');
  $adunblock_id = get_option('adunblock_id');
  $ctx = stream_context_create(array('http' => array('timeout' => 3)));
  if (!$code) {
    $code = @file_get_contents("http://adunblock.com/code/$adunblock_id.html", 0, $ctx);
    if (!$code)
      $code = get_transient('code_adunblock_lt');
    else
      set_transient('code_adunblock_lt', $code, 14 * 86400);
    set_transient('code_adunblock', $code, 6 * 3600);
  }
  echo $code;
}

// ajoute le code dans le footer
add_action('wp_footer','add_adunblock_code');

// redirection vers les options après activation
register_activation_hook(__FILE__, 'my_plugin_activate');
add_action('admin_init', 'my_plugin_redirect');
function my_plugin_activate() {
    add_option('my_plugin_do_activation_redirect', true);
}
function my_plugin_redirect() {
    if (get_option('my_plugin_do_activation_redirect', false)) {
        delete_option('my_plugin_do_activation_redirect');
        if(!isset($_GET['activate-multi']))
        {
            wp_redirect("options-general.php?page=adunblock");
        }
    }
}
