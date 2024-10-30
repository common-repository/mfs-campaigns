<?php
/*
Plugin Name: Mindfire Campaigns 
Plugin URI: http://www.mindfiresolutions.com/
Description: MFS campaigns plugin lets you create multiple campaigns on your website and display them randomly on your website.
Author: Mindfire-Solutions
Version: 1.0
Author URI: http://www.mindfiresolutions.com/
*/

//Enable the plugin by default

register_activation_hook( __FILE__, 'mfs_campaign_install' );

function mfs_campaign_install(){
	 //Enable only if its first activation
	 if(get_option('campaign_enabled') == '')
			add_option("campaign_enabled",1);
	 
	 //Flush rewrite rules so that it won't show 404 error for custom post type single pages.
	 flush_rewrite_rules();
}


//Add required js/css files.
add_action( "wp_enqueue_scripts" , "mfs_campaign_scripts" );
function mfs_campaign_scripts(){
	 if(get_option('campaign_enabled')){
	    wp_enqueue_script( 'alert-js', plugins_url( 'resources/js/alert.js', __FILE__ ) , array( 'jquery' ) );
	    wp_enqueue_style( 'my-plugin-script', plugins_url( 'resources/css/alert.css', __FILE__ ) );
	 }
}


//Create Campaign custom post type.
add_action( 'init', 'mfs_register_cpt_campaign' );

function mfs_register_cpt_campaign() {

    $labels = array( 
        'name' => _x( 'Campaigns', 'campaign' ),
        'singular_name' => _x( 'Campaign', 'campaign' ),
        'add_new' => _x( 'Add New', 'campaign' ),
        'add_new_item' => _x( 'Add New Campaign', 'campaign' ),
        'edit_item' => _x( 'Edit Campaign', 'campaign' ),
        'new_item' => _x( 'New Campaign', 'campaign' ),
        'view_item' => _x( 'View Campaign', 'campaign' ),
        'search_items' => _x( 'Search Campaign', 'campaign' ),
        'not_found' => _x( 'No Campaign found', 'campaign' ),
        'not_found_in_trash' => _x( 'No Campaign found in Trash', 'campaign' ),
        'parent_item_colon' => _x( 'Parent Campaign:', 'campaign' ),
        'menu_name' => _x( 'Campaigns', 'campaign' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        
        'supports' => array( 'title', 'thumbnail','editor'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon' => plugins_url( 'resources/img/campaign.png', __FILE__ ),
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'campaign', $args );
}

//Enqueue color picker to be used for the metabox
add_action( 'admin_enqueue_scripts', 'mfs_campaign_colour_picker' );
function mfs_campaign_colour_picker(){
	  wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker-script', plugins_url( 'resources/js/campaign.js', __FILE__ ) , array( 'wp-color-picker' ), false, true );

}


//Register Metaboxes for the campaign
add_action("admin_init", "mfs_campaign_metabox");

function mfs_campaign_metabox() {    
		add_meta_box("campaign_details",
								 "Campaign Information", 
								 "mfs_campaign_information", 
								 "campaign", 
								 "side", 
								 "low");
}

function mfs_campaign_information() {
	global $post;
	$campaign_url = get_post_meta($post->ID,'campaign_url',true);
  $campaign_url_text = get_post_meta($post->ID,'campaign_url_text',true);
  $link_color = get_post_meta($post->ID,'link_color',true) == '' ? '#16a085' : get_post_meta($post->ID,'link_color',true);
  $alert_color = get_post_meta($post->ID,'alert_color',true) == '' ? '#34495e' : get_post_meta($post->ID,'alert_color',true);
	$new_tab = get_post_meta($post->ID,'new_tab',true);
  ?>
	<table>
	 <tr>
			<td>
				 <label for="campaign-url">
					 Campaign Link:
				 </label>
	 </tr>
	 <tr>
			<td>
				 <input type="text" id="campaign-url" name="campaign_url" placeholder="Default is permalink" value="<?php echo $campaign_url; ?>" class="widefat">
			</td>
	 </tr>
   
	<tr>
	 <td>  <label for="campaign-url-text"> Link Text: </label>
			</td>
	</tr>
	
	<tr>
	 <td>
			<input type="text" id="campaign-url-text" name="campaign_url_text" placeholder="View Campaign" value="<?php echo $campaign_url_text; ?>" class="widefat">
	 </td>
	</tr>
  
	<tr>
	 <td>
			<label for="link_color"> Link Background:</label>
	 </td>
	</tr>
  
	<tr>
	 <td>
			<input type="text" id="link_color" name="link_color" value="<?php echo $link_color; ?>" class="wp-color-picker-field widefat">
	 </td>
	</tr>
  
	<tr>
	 <td>
			<label for="alert_color"> Notification bar background: </label>
	 </td>
	</tr>
  
	<tr>
	 <td>
			<input type="text" id="alert_color" name="alert_color" value="<?php echo $alert_color; ?>" class="wp-color-picker-field widefat">
	 </td>
	</tr>
  
  <tr>
	 <td> <input type="checkbox" id="new_tab" value="1" name="new_tab" <?php checked( $new_tab, 1 ); ?> >
				 <label for="new_tab"> Open in new tab. </label> </td>
	</tr>
	
	</table>
<?php
}

//Save Meta Details
add_action('save_post', 'mfs_save_campaign_meta');

function mfs_save_campaign_meta() {
  
  global $post;
  
  $post_type = get_post_type( $post->ID );
  
  if( $post_type != 'campaign' )
    return;
  
  if(isset($_POST['campaign_url']))
    update_post_meta( $post->ID,"campaign_url",$_POST["campaign_url"] );
      
  if(isset($_POST['campaign_url_text']))
    update_post_meta( $post->ID,"campaign_url_text", $_POST["campaign_url_text"] );
  
  if(isset($_POST['link_color']))
    update_post_meta( $post->ID,"link_color", $_POST["link_color"] );
  
  if(isset($_POST['alert_color']))
    update_post_meta( $post->ID,"alert_color",$_POST["alert_color"] );
      
  if(isset($_POST['new_tab']))
    update_post_meta( $post->ID,"new_tab",1 );
  else
    update_post_meta( $post->ID,"new_tab",0 );
}

//Hook to the wp_footer and prepend the HTML to body.
add_action( "wp_footer", "mfs_campaign_prepend_html" );
function mfs_campaign_prepend_html(){
	 if(!get_option("campaign_enabled"))
			return;
	 
  $posts = get_posts( 'orderby=rand&numberposts=1&post_type=campaign' );
  $id = $posts[0]->ID;  
  $campaign_url = get_post_meta( $id,'campaign_url',true) == '' ? get_permalink( $id ) : get_post_meta( $id,'campaign_url',true);
  
  $campaign_url_text = get_post_meta( $id,'campaign_url_text',true) == '' ? 'View Campaign' : get_post_meta( $id,'campaign_url_text',true);
  
  $link_color = get_post_meta( $id,'link_color',true ) == '' ? '#16a085' : get_post_meta( $id,'link_color',true);

  $alert_color = get_post_meta( $id,'alert_color',true ) == '' ? '#34495e' : get_post_meta( $id,'alert_color',true);
  
  $link_target = get_post_meta( $id,'new_tab',true ) == 1 ? '_blank' : '_self' ;
	  
  $text = get_the_post_thumbnail( $id, 'thumbnail' ) .'<h4>'. $posts[0]->post_title .'</h4>'. '<a href="'.$campaign_url . '" target="'.$link_target.'" class="btn btn-default" style="background:' . $link_color . '">' . $campaign_url_text . '</a>';

  $html = '<div class="alert alert-danger" style="background:' . $alert_color . '"><a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a><div class="container-campaigns">' . $text . '</div></div>';
  ?>
    <script type="text/javascript">
      jQuery(function(){
        jQuery("body").prepend('<?php echo $html; ?>');
      });
    </script> 
 <?php     
}

//Add Setting for the campaign

add_action('admin_menu', 'mfs_campaign_setting_menu');

function mfs_campaign_setting_menu() {
	add_submenu_page( 'edit.php?post_type=campaign', 'Settings', 'Settings', 'manage_options', 'campaign-settings', 'campaign_settings' ); 
}
function campaign_settings() {
	 if(isset($_POST['campaign_submit'])){
			if(isset($_POST['campaign_enabled']))
				 $enabled = 1;
			else
				 $enabled = 0;
			update_option( "campaign_enabled",$enabled );
	 }
?>
	 <div class="wrap"><div id="icon-options-general" class="icon32"></div>
			<h2>Campaign Settings</h2>
	 </div>
	 <form method="post" action="">
			<table cellpadding="4" cellspacing="4">
				 <tr>
						<td>Enable Campaign</td>
						<td><input type="checkbox" name="campaign_enabled" value="1" <?php checked( get_option('campaign_enabled'),1 ); ?>></td>
				 </tr>
				 <tr>
						<td colspan="2" align="right"><input type="submit" name="campaign_submit" class="button button-primary" value="Save"></td>
				 </tr>
			</table>
	 </form>
<?php
}