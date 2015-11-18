<?php
/*
Plugin Name: mypace Custom Meta Robots
Plugin URI: 
Description: This plugin allows you to edit meta robots tag at every singular post(posts, pages, custom post types). This is a very simple plugin.
Version: 1.0
Author: Kei Nomura (mypacecreator)
Author URI: http://mypacecreator.net/
Text Domain: mypace-custom-meta-robots
Domain Path: /languages
*/

if ( !class_exists( 'Mypace_Custom_Meta_Robots' ) ){

	class Mypace_Custom_Meta_Robots{

		public function __construct() {
			//Actions and Filters	
			add_action( 'admin_menu',                      array( $this, 'add_meta_box' ) );
			add_action( 'save_post',                       array( $this, 'save_metadata' ) );
			add_filter( 'wp_head',                         array( $this, 'custom_meta_robots' ) );
			add_action( 'admin_print_styles-post.php',     array( $this, 'robots_meta_box_styles' ) );
			add_action( 'admin_print_styles-post-new.php', array( $this, 'robots_meta_box_styles' ) );
			load_plugin_textdomain( 'mypace-custom-meta-robots', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}

		//make a meta box
		public function add_meta_box(){
			$post_types = wp_list_filter(
					get_post_types(array('public' => true)),
					array('attachment'),
					'NOT'
			);
			foreach ( $post_types as $post_type ){
				add_meta_box(
					'mypace-meta-robots',
					esc_html__( 'meta robots difinition', 'mypace-custom-meta-robots' ),
					array( $this, 'robots_meta_box' ),
					$post_type,
					'advanced'
				);
			}
		}

		public function robots_meta_box(){
			//input form
			wp_nonce_field( plugin_basename(__FILE__), 'mypace_robots_meta_noncename' );
			$field_name = 'mypace_robots_meta';
			$field_value = get_post_meta( get_the_ID(), $field_name, true );
		?>

		<div id="mypace_robots_meta-box">
			<label><input type="radio" name="mypace_robots_meta" value="index, follow"<?php if ( 'index, follow' == $field_value ) echo ' checked="checked"'; ?> />index, follow</label>
			<label><input type="radio" name="mypace_robots_meta" value="noindex, follow"<?php if ( 'noindex, follow' == $field_value ) echo ' checked="checked"'; ?> />noindex, follow</label>
			<label><input type="radio" name="mypace_robots_meta" value="index, nofollow"<?php if ( 'index, nofollow' == $field_value ) echo ' checked="checked"'; ?> />index, nofollow</label>
			<label><input type="radio" name="mypace_robots_meta" value="noindex, nofollow"<?php if ( 'noindex, nofollow' == $field_value ) echo ' checked="checked"'; ?> />noindex, nofollow</label>
			<label><input type="radio" name="mypace_robots_meta" value=""<?php if ( empty($field_value) ) echo ' checked="checked"'; ?> /><?php esc_attr_e( "None (Do not output meta robots definition.)", 'mypace-custom-meta-robots' ); ?> </label>
		</div>

<?php
	}

		public function robots_meta_box_styles() {
		?>
		<style type="text/css" charset="utf-8">
			#mypace_robots_meta-box label {
				display: inline-block;
				cursor: pointer;
				width: 48%;
			}
		</style>
		<?php
		}

		public function save_metadata($post_id){

			//permission check and save data
			if ( !isset($_POST['mypace_robots_meta_noncename']) ){
				return $post_id;
			}
			if ( !wp_verify_nonce( $_POST['mypace_robots_meta_noncename'], plugin_basename(__FILE__) ) ){
				return $post_id;
			}
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
				return $post_id;
			}

			$post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';
			$post_types = wp_list_filter(
					get_post_types(array('public' => true)),
					array('attachment'),
					'NOT'
			);
			if ( in_array($post_type, $post_types) ){
				if ( !current_user_can( 'edit_' . $post_type, $post_id ) ){
					return $post_id;
				}
			} else {
				return $post_id;
			}

			$mydata = isset($_POST['mypace_robots_meta']) ? $_POST['mypace_robots_meta'] : '';
			if ( !empty($mydata) ){
				update_post_meta( $post_id, 'mypace_robots_meta', $mydata );
			} else {
				delete_post_meta( $post_id, 'mypace_robots_meta' );
			}
			return $mydata;
		}

		//output meta robots tag
		public function custom_meta_robots(){
			if( is_singular() ){
				$post_id = get_the_ID();
				$robots_value = get_post_meta( $post_id, 'mypace_robots_meta', true );
				if( $robots_value ){
					$output = '<meta name="robots" content="' . esc_attr($robots_value) . '" />';
				echo $output . "\n";
				}
			}
		}

	}
	new Mypace_Custom_Meta_Robots();

}