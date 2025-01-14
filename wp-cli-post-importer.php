<?php
/**
 * Plugin Name:     WP CLI Post Importer
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Anam Hossain
 * Author URI:      https://anam.rocks
 * Text Domain:     wp-cli-post-importer
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Cli_Import_Posts_From_Api
 */

// Your code starts here.

require plugin_dir_path( __FILE__ ) . 'cli/class-wp-cli-post-importer-manage-post.php';
require plugin_dir_path( __FILE__ ) . 'cli/class-wp-cli-post-importer-manage-posts.php';
new WP_CLI_Post_Importer_Manage_Post();
new WP_CLI_Post_Importer_Manage_Posts();
