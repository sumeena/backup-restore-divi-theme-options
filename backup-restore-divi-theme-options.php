<?php
/**
 * Plugin Name: Backup/Restore Divi Theme Options
 * Description: Backup & Restore your Divi Theme Options.
 * Theme URI: https://github.com/SiteSpace/backup-restore-divi-theme-options
 * Author: Divi Space
 * Author URI: http://divispace.com
 * Version: 1.0.2
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tags: divi, theme options, theme settings, divi theme options, divi options, divi theme settings, divi settings
 * Text Domain: backup-restore-divi-theme-options
 */


class backup_restore_divi_theme_options {

	function backup_restore_divi_theme_options() {
		add_action('admin_menu', array(&$this, 'admin_menu')); //shows Backup/Restore Theme Options in admin under tools and divi
	}
	function admin_menu() {

		$page = add_submenu_page('tools.php', 'Backup/Restore Theme Options', 'Backup/Restore Theme Options', 'manage_options', 'backup-restore-divi-theme-options', array(&$this, 'options_page'));  //adds menu under tool menu in admin

		add_action("load-{$page}", array(&$this, 'import_export'));

		add_submenu_page( 'et_divi_options',__( 'Backup/Restore Theme Options', 'Divi' ), __( 'Backup/Restore Theme Options', 'Divi' ), 'manage_options', 'tools.php?page=backup-restore-divi-theme-options', 'backup-restore-divi-theme-options' ); //adds menu under Divi menu in admin

	}
	function import_export() {                             //function to import and export theme data
		if (isset($_GET['action']) && ($_GET['action'] == 'download')) {
			header("Cache-Control: public, must-revalidate");
			header("Pragma: hack");
			header("Content-Type: text/plain");
			header('Content-Disposition: attachment; filename="divi-theme-options-'.date("dMy").'.dat"');  //downloads file with name(divi-theme-options)includeing date month year
			echo serialize($this->_get_options());
			die();
		}
		if (isset($_POST['upload']) && check_admin_referer('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions')) {   //uploads file in admin 
			if ($_FILES["file"]["error"] > 0) {
				// error if file is not correct
			} else {
				$options = unserialize(file_get_contents($_FILES["file"]["tmp_name"]));  
				if ($options) {
					foreach ($options as $option) {
						update_option($option->option_name, unserialize($option->option_value));  //replaces old data with new
					}
				}
			}
			wp_redirect(admin_url('tools.php?page=backup-restore-divi-theme-options'));
			exit;
		}
	}
	function options_page() { ?>

		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Backup/Restore Theme Options</h2>
			<form action="" method="POST" enctype="multipart/form-data">
				<style>#backup-restore-divi-theme-options td { display: block; margin-bottom: 20px; }</style>
				<table id="backup-restore-divi-theme-options">
					<tr>
						<td>
							<h3>Backup/Export</h3>
							<p>Here are the stored settings for the current theme:</p>
							<p><textarea disabled class="widefat code" rows="20" cols="100" onclick="this.select()"><?php echo serialize($this->_get_options()); ?></textarea></p>  <!--Displays Theme data in textarea--->
							<p><a href="?page=backup-restore-divi-theme-options&action=download" class="button-secondary">Download as file</a></p><!--Downloads Theme data--->
						</td>
						<td>
							<h3>Restore/Import</h3>
							<p><label class="description" for="upload">Restore a previous backup</label></p>
							<p><input type="file" name="file" /> <input type="submit" name="upload" id="upload" class="button-primary" value="Upload file" /></p>
							<?php if (function_exists('wp_nonce_field')) wp_nonce_field('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions'); ?>
						</td>
					</tr>
				</table>
			</form>
		</div>

	<?php }
	function _display_options() {
		$options = unserialize($this->_get_options());            // takes a single serialized variable and converts it back into a PHP value
	}
	
	function _get_options() {
		global $wpdb;
		return $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name = 'et_divi'"); // edit 'shapeSpace_options' to match theme options
	}
}
new backup_restore_divi_theme_options();
?>
