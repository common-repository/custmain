<?php
/*
Plugin Name: CustMain
Plugin URI: https://inyh.ru/
Description: Easily maintain customizations of installed themes, plugins and WordPress core.
Author: Inyh - IT & Design Solutions
Developers: Vladislav Sivachuk
Version: 0.4
Author URI: https://inyh.ru/
Text Domain: custmain
Domain Path: /lang
*/
	
if ( ! defined( 'ABSPATH' ) ) {
 exit;
}

$custmain['version'] = "0.4";
$custmain['release'] = "2020-09-21";

require_once(plugin_dir_path( __FILE__ ).'includes/CustMainAjax.php');

register_activation_hook( __FILE__, 'custmain_activate' );

function custmain_activate() {
	global $wpdb;
	$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	$table_name = $wpdb->get_blog_prefix() . 'custmain_files';
	$sql1 = "CREATE TABLE {$table_name} (
		id int(11) unsigned NOT NULL auto_increment,
		path varchar(1024) NOT NULL default '',
		group_id int(11) unsigned NOT NULL default '0',
		active int(11) unsigned NOT NULL default '1',
		PRIMARY KEY (id)
	) {$charset_collate};";
	
	$table_name = $wpdb->get_blog_prefix() . 'custmain_groups';
	$sql2 = "CREATE TABLE {$table_name} (
		id int(11) unsigned NOT NULL auto_increment,
		title varchar(512) NOT NULL default '',
		active int(11) unsigned NOT NULL default '1',
		PRIMARY KEY (id)
	) {$charset_collate};";
	
	$table_name = $wpdb->get_blog_prefix() . 'custmain_changes';
	$sql3 = "CREATE TABLE {$table_name} (
		id int(11) unsigned NOT NULL auto_increment,
		file_id int(11) unsigned NOT NULL default '0',
		title varchar(512) NOT NULL default '',
		original text NOT NULL default '',
		replacement text NOT NULL default '',
		active int(11) unsigned NOT NULL default '1',
		PRIMARY KEY (id)
	) {$charset_collate};";
	
	$table_name = $wpdb->get_blog_prefix() . 'custmain_logs';
	$sql4 = "CREATE TABLE {$table_name} (
		id int(11) unsigned NOT NULL auto_increment,
		calltype int(11) unsigned NOT NULL default '0',
		datetime int(11) unsigned NOT NULL default '0',
		filename varchar(1024) NOT NULL default '',
		changename varchar(512) NOT NULL default '',
		status int(11) unsigned NOT NULL default '0',
		message varchar(512) NOT NULL default '',
		PRIMARY KEY (id)
	) {$charset_collate};";
	
	$table_name = $wpdb->get_blog_prefix() . 'custmain_autorun';
	$sql5 = "CREATE TABLE {$table_name} (
		id int(11) unsigned NOT NULL auto_increment,
		`change_id` int(11) unsigned NOT NULL,
		`type` VARCHAR(8) NOT NULL default '',
		`title` VARCHAR(256) NOT NULL default '',
		`subject` VARCHAR(512) NOT NULL default '',
		PRIMARY KEY (id)
	) {$charset_collate};";
	
	dbDelta($sql1.$sql2.$sql3.$sql4.$sql5);
	
	if(!file_exists(ABSPATH . "cm-backup") || !is_dir(ABSPATH . "cm-backup")){
		if (!mkdir(ABSPATH . "cm-backup", 0755, false)) {
			die(__('Unable to create a backup directory.<br>Please, try again or create a backup directory manually:<br>'.ABSPATH.'cm-backup/ with 0755 rights.', 'custmain'));
		}
	}
}

add_action( 'admin_enqueue_scripts', 'enqueue_custmain_styles' );
function enqueue_custmain_styles() {
	require_once(plugin_dir_path( __FILE__ ).'includes/srp.php');
	$vars = array();
	$vars['lightgreen'] = "#1abc9c";
	$vars['darkgreen'] = "#16a085";
	$vars['CMH'] = "'Ubuntu', Verdana, Arial, sans-serif";
	$vars['CMP'] = "'Ubuntu', Verdana, Arial, sans-serif";

	$style = new SRP(plugin_dir_path( __FILE__ ) . 'styles/style.srp', $vars);
	$style->setFlag("minify", true);
	$style->stylesToFile(true);
	
	wp_enqueue_style( 'custmain', plugins_url( 'styles/style.css', __FILE__ ) );
}

add_action('admin_menu', 'custmain_create_menu');
function custmain_create_menu() {
	global $custmain;
	add_management_page('CustMain', '<span style="color: #1abc9c;">Cust</span>Main', 'administrator', "custmain", 'custmain_page');
}

function custmain_page(){
	global $custmain;
	
	require_once(plugin_dir_path( __FILE__ ).'includes/CustMainScripts.php');
	
	custmain_print_group_placeholder();
	custmain_print_file_placeholder();
	custmain_print_change_placeholder();
	
	custmain_print_group();
	custmain_print_file();
	custmain_print_change();
	?>
	<div class="wrap custmain_wrap">
	
		<div id="custmain_fonts_load">
			<span class="custmain_font_4">.</span>
		</div>
		
		<div id="custmain_heading_board">
			<div id="custmain_logo"></div>
			<div id="custmain_version">
				<span>v.<?php echo $custmain['version']; ?></span>
				<span></span>
				<span><?php echo $custmain['release']; ?></span>
			</div>
			<div id="custmain_tabs">
				<div class="custmain_tab custmain_tab_active" onclick="custmain_switch_tab(1, this);"><span class="custmain_icon custmain_icon_custs"></span><?php echo __('Customizations', 'custmain'); ?></div>
				<div class="custmain_tab" onclick="custmain_switch_tab(2, this);"><span class="custmain_icon custmain_icon_logs"></span><?php echo __('Logs', 'custmain'); ?></div>
				<div class="custmain_tab" onclick="custmain_switch_tab(3, this);"><span class="custmain_icon custmain_icon_import"></span><?php echo __('Import', 'custmain'); ?></div>
				<div id="custmain_tabs_line"></div>
			</div>
		</div>
		
		<div id="custmain_common_board">
			<div id="custmain_select_all" onclick="custmain_select_all();"></div>
			<div id="custmain_selected_text"></div>
			<div id="custmain_selected_controls">
				<button class="custmain_control_export" onclick="custmain_export_selected();"><span><?php echo __('Export selected', 'custmain'); ?></span></button>
				<button class="custmain_control_autorun" onclick="custmain_autorun_selected();"><span><?php echo __('Autorun settings', 'custmain'); ?></span></button>
				<button class="custmain_control_run" onclick="custmain_run_selected();"><span><?php echo __('Run selected', 'custmain'); ?></span></button>
				<button class="custmain_control_delete" onclick="custmain_delete_selected();"><span><?php echo __('Delete selected', 'custmain'); ?></span></button>
			</div>
			<div id="custmain_common_controls">
				<button class="custmain_control_new" onclick="custmain_new_group();"><span><?php echo __('Add new group', 'custmain'); ?></span></button>
			</div>
		</div>
		
		<?php
		$groups = custmain_get_groups();
		custmain_print_group(0, __('Uncategorized', 'custmain'));
		foreach($groups as $group){
			custmain_print_group($group['id'], $group['title']);
		}
		?>
		
		<div id="custmain_logs">
			<div id="custmain_logs_panel">
				<span class="custmain_load"></span>
				<div class="custmain_controls">
					<input type="date" id="custmain_logs_start"> - <input type="date" id="custmain_logs_end">
					<button id="custmain_logs_download" onclick="custmain_download_logs();"><span><?php echo __('Download logs', 'custmain'); ?></span></button>
				</div>
			</div>
			<div id="custmain_logs_header">
				<div class="custmain_lr_calltype"><?php echo __('Type', 'custmain'); ?></div>
				<div class="custmain_lr_datetime"><?php echo __('Date & Time', 'custmain'); ?></div>
				<div class="custmain_lr_filename"><?php echo __('File name', 'custmain'); ?></div>
				<div class="custmain_lr_status"><?php echo __('Status', 'custmain'); ?></div>
			</div>
		</div>
		
		<div id="custmain_import">
			<div id="custmain_import_heading"><span class="custmain_load"></span><?php echo __('Paste the code from the CustMain export file', 'custmain'); ?>:</div>
			<textarea id="custmain_import_input"></textarea>
			<button class="custmain_confirm_button" onclick="custmain_import();"><?php echo __('Import', 'custmain'); ?></button>
		</div>
		
		<div id="custmain_modal_bg"></div>
		<div id="custmain_confirm">
			<div id="custmain_confirm_descr"></div>
			<button class="custmain_cancel_button" onclick="custmain_confirm_run(false);"><?php echo __('Cancel', 'custmain'); ?></button>
			<button class="custmain_confirm_button" onclick="custmain_confirm_run(true);"><?php echo __('Run', 'custmain'); ?></button>
		</div>
		<div id="custmain_modal">
			<div id="custmain_progressline_bg"></div>
			<div id="custmain_progressline"></div>
			<div id="custmain_progressline_anim"><div></div></div>
			<div id="custmain_progressvalue">...</div>
			<div id="custmain_progresslog"></div>
			<button class="custmain_confirm_button" onclick="custmain_hide_modal();"><?php echo __('Finish', 'custmain'); ?></button>
		</div>
		<div id="custmain_autorun">
			<div id="custmain_autorun_descr"></div>
			<label for="custmain_autorun_type"><?php echo __('Autorun type', 'custmain'); ?>:</label>
			<select id="custmain_autorun_type" onchange="custmain_load_autorun_subjects();">
				<option value="plugin"><?php echo __('On plugin update', 'custmain'); ?></option>
				<option value="theme"><?php echo __('On theme update', 'custmain'); ?></option>
				<!--<option value="cron"><?php echo __('By task scheduler', 'custmain'); ?></option>-->
			</select>
			<label for="custmain_autorun_subject"><?php echo __('Select desired', 'custmain'); ?>:</label>
			<select id="custmain_autorun_subject">
				
			</select>
			<button class="custmain_cancel_button" onclick="custmain_set_autorun(false);"><?php echo __('Cancel', 'custmain'); ?></button>
			<button class="custmain_confirm_button" onclick="custmain_set_autorun(true);"><?php echo __('Set', 'custmain'); ?></button>
		</div>
		<div id="custmain_alert"></div>
	</div>
<?php
	
}

function custmain_print_group($id = -1, $title = ""){
	?>
	<div <?php if($id < 0) echo 'id="custmain_group_template"'; ?>class="custmain_group<?php if(isset($id) && $id == 0) echo " custmain_default"; ?>" data-id="<?php echo $id; ?>">
		<div class="custmain_select" onclick="custmain_select_group(this);"></div>
		<div class="custmain_group_title" onclick="if(event.target == this) custmain_toggle_group(this);"><input type="text" class="custmain_title_text" data-saved="<?php echo $title; ?>" value="<?php echo $title; ?>" title="<?php echo $title; ?>" size="<?php echo (mb_strlen($title)>50?50:(mb_strlen($title)==0?10:mb_strlen($title))); ?>" <?php if(isset($id) && $id == 0) echo "readonly"; else { ?>oninput="custmain_manage_input(this);" onchange="custmain_update_group_title(this);" onfocus="custmain_focus(true);" onfocusout="custmain_focus(false);"<?php } ?> placeholder="<?php echo __('Group', 'custmain'); ?>"><span class="custmain_load"></span><?php custmain_print_group_controls(); ?></div>
		<div class="custmain_container"></div>
	</div>
	<?php
}

function custmain_print_file($id = -1, $title = ""){
	?>
	<div <?php if($id < 0) echo 'id="custmain_file_template"'; ?>class="custmain_file" data-id="<?php echo $id; ?>">
		<div class="custmain_select" onclick="custmain_select_file(this);"></div>
		<div class="custmain_file_title" onclick="if(event.target == this) custmain_toggle_file(this);"><input type="text" class="custmain_title_text" data-saved="<?php echo $title; ?>" value="<?php echo $title; ?>" title="<?php echo $title; ?>" size="<?php echo (mb_strlen($title)>50?50:(mb_strlen($title)==0?10:mb_strlen($title))); ?>" oninput="custmain_manage_input(this); custmain_file_tip(this);" onchange="custmain_update_file_path(this);" onfocus="custmain_focus(true); custmain_file_tip(this); custmain_check_file(this);" onfocusout="custmain_focus(false); custmain_close_tip(this);" placeholder="<?php echo __('File', 'custmain'); ?>"><span class="custmain_load"></span><?php custmain_print_file_controls($id); ?><div class="custmain_file_tip"></div></div>
		<div class="custmain_container"></div>
	</div>
	<?php
}

function custmain_print_change($id = -1, $title = "", $original = "", $replacement = ""){
	?>
	<div <?php if($id < 0) echo 'id="custmain_change_template"'; ?>class="custmain_change" data-id="<?php echo $id; ?>">
		<div class="custmain_select" onclick="custmain_select_change(this);"></div>
		<div class="custmain_change_title"><input type="text" class="custmain_title_text" data-saved="<?php echo $title; ?>" value="<?php echo $title; ?>" title="<?php echo $title; ?>" size="<?php echo (mb_strlen($title)>50?50:(mb_strlen($title)==0?10:mb_strlen($title))); ?>" oninput="custmain_manage_input(this);" onchange="custmain_update_change_title(this);" onfocus="custmain_focus(true);" onfocusout="custmain_focus(false);" placeholder="<?php echo __('Change', 'custmain'); ?>"><span class="custmain_load"></span><?php custmain_print_change_controls(); ?></div>
		<div class="custmain_change_original"><?php echo __('Original', 'custmain'); ?>:<br><textarea class="custmain_original_lines" readonly><?php echo "1\n2\n3\n4"; ?></textarea><textarea class="custmain_original_input" oninput="custmain_manage_input(this);" onchange="custmain_update_original(this);" onfocus="custmain_focus(true, this);" onfocusout="custmain_focus(false, this);"><?php echo $original; ?></textarea><span class="custmain_load custmain_load_next"></div>
		<div class="custmain_change_replacement"><?php echo __('Replacement', 'custmain'); ?>:<br><textarea class="custmain_replacement_lines" readonly><?php echo "1\n2\n3\n4"; ?></textarea><textarea class="custmain_replacement_input" oninput="custmain_manage_input(this);" onchange="custmain_update_replacement(this);" onfocus="custmain_focus(true, this);" onfocusout="custmain_focus(false, this);"><?php echo $replacement; ?></textarea><span class="custmain_load custmain_load_next"></div>
	</div>
	<?php
}

function custmain_print_group_controls(){
	?>
	<div class="custmain_controls">
		<button class="custmain_control_new" onclick="custmain_new_file(this);"><span><?php echo __('Add new file', 'custmain'); ?></span></button>
		<button class="custmain_control_export" onclick="custmain_export_group(this);"><span><?php echo __('Export this group', 'custmain'); ?></span></button>
		<button class="custmain_control_autorun" onclick="custmain_autorun_group(this);"><span><?php echo __('Autorun settings', 'custmain'); ?></span></button>
		<button class="custmain_control_run" onclick="custmain_run_group(this);"><span><?php echo __('Run this group', 'custmain'); ?></span></button>
		<button class="custmain_control_delete" onclick="custmain_delete_group(this);"><span><?php echo __('Delete this group', 'custmain'); ?></span></button>
	</div>
	<?php
}

function custmain_print_group_placeholder(){
	?>
	<div id="custmain_group_placeholder_template" class="custmain_placeholder">
		<?php echo __('Group has been deleted', 'custmain'); ?>. <b onclick="custmain_restore_group(this);"><?php echo __('Restore back', 'custmain'); ?>?</b> <span class="custmain_load"></span>
	</div>
	<?php
}

function custmain_print_file_controls($file_id){
	?>
	<div class="custmain_controls">
		<button class="custmain_control_new" onclick="custmain_new_change(this);"><span><?php echo __('Add new change', 'custmain'); ?></span></button>
		<button class="custmain_control_revert" onclick="custmain_revert_file(this);" <?php if(!custmain_check_backup($file_id)) echo 'style="display: none;"';?>><span><?php echo __('Revert this file', 'custmain'); ?></span></button>
		<button class="custmain_control_export" onclick="custmain_export_file(this);"><span><?php echo __('Export this file', 'custmain'); ?></span></button>
		<button class="custmain_control_autorun" onclick="custmain_autorun_file(this);"><span><?php echo __('Autorun settings', 'custmain'); ?></span></button>
		<button class="custmain_control_run" onclick="custmain_run_file(this);"><span><?php echo __('Run this file', 'custmain'); ?></span></button>
		<button class="custmain_control_delete" onclick="custmain_delete_file(this);"><span><?php echo __('Delete this file', 'custmain'); ?></span></button>
	</div>
	<?php
}

function custmain_print_file_placeholder(){
	?>
	<div id="custmain_file_placeholder_template" class="custmain_placeholder">
		<?php echo __('File has been deleted', 'custmain'); ?>. <b onclick="custmain_restore_file(this);"><?php echo __('Restore back', 'custmain'); ?>?</b> <span class="custmain_load"></span>
	</div>
	<?php
}

function custmain_print_change_controls(){
	?>
	<div class="custmain_controls">
		<button class="custmain_control_export" onclick="custmain_export_change(this);"><span><?php echo __('Export this change', 'custmain'); ?></span></button>
		<button class="custmain_control_autorun" onclick="custmain_autorun_change(this);"><span><?php echo __('Autorun settings', 'custmain'); ?></span></button>
		<button class="custmain_control_run" onclick="custmain_run_change(this);"><span><?php echo __('Run this change', 'custmain'); ?></span></button>
		<button class="custmain_control_delete" onclick="custmain_delete_change(this);"><span><?php echo __('Delete this change', 'custmain'); ?></span></button>
	</div>
	<?php
}

function custmain_print_change_placeholder(){
	?>
	<div id="custmain_change_placeholder_template" class="custmain_placeholder">
		<?php echo __('Change has been deleted', 'custmain'); ?>. <b onclick="custmain_restore_change(this);"><?php echo __('Restore back', 'custmain'); ?>?</b> <span class="custmain_load"></span>
	</div>
	<?php
}


function custmain_get_groups(){
	global $wpdb;
	$data = $wpdb->get_results("SELECT * FROM `".$wpdb->get_blog_prefix()."custmain_groups` WHERE `active`=1", 'ARRAY_A');
	return $data;
}

function custmain_get_files($group_id){
	global $wpdb;
	$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".$wpdb->get_blog_prefix()."custmain_files` WHERE `group_id`=%d AND `active`=1", $group_id), 'ARRAY_A');
	return $data;
}

function custmain_get_changes($file_id){
	global $wpdb;
	$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".$wpdb->get_blog_prefix()."custmain_changes` WHERE `file_id`=%d AND `active`=1", $file_id), 'ARRAY_A');
	foreach($data as $key => $value){
		$data[$key]['original'] = stripslashes($value['original']);
		$data[$key]['replacement'] = stripslashes($value['replacement']);
	}
	return $data;
}

function custmain_check_backup($file_id){
	global $wpdb;
	$data = $wpdb->get_row($wpdb->prepare("SELECT path FROM `".$wpdb->get_blog_prefix()."custmain_files` WHERE `id`=%d", $file_id), ARRAY_A);
	$path = $data['path'];
	if(file_exists(ABSPATH . "cm-backup/" . $path. ".cmcopy")) return true;
	return false;
}

function custmain_on_update( $upgrader_object, $options ) {
	if($options['action'] != 'update') return;
	
	global $wpdb, $_custmain_ajax;
	
	if($options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		$data = $wpdb->get_results($wpdb->prepare("SELECT `".$wpdb->get_blog_prefix()."custmain_autorun`.*, `".$wpdb->get_blog_prefix()."custmain_changes`.file_id FROM `".$wpdb->get_blog_prefix()."custmain_autorun` INNER JOIN `".$wpdb->get_blog_prefix()."custmain_changes` ON `".$wpdb->get_blog_prefix()."custmain_autorun`.change_id=`".$wpdb->get_blog_prefix()."custmain_changes`.id WHERE `type`='plugin'"), ARRAY_A);
		if(!count($data)) {
			return;
		}
		$plugins = array();
		foreach($data as $autorun){
			$plugins[] = $autorun['subject'];
		}
		
		foreach($options['plugins'] as $plugin) {
			if(in_array($plugin, $plugins)) {
				$files = array();
				foreach($data as $autorun){
					if($autorun['subject'] == $plugin) {
						$files[$autorun['file_id']] .= $autorun['change_id'].',';
					}
				}
				foreach($files as $file_id => $change_ids){
					$_POST['file_id'] = $file_id;
					$_POST['change_ids'] = substr($change_ids,0,strlen($change_ids)-1);
					$_custmain_ajax->process_file(1);
				}
			}
		}
	} else
	if($options['type'] == 'theme' && isset( $options['themes'] ) ) {
		$data = $wpdb->get_results($wpdb->prepare("SELECT `".$wpdb->get_blog_prefix()."custmain_autorun`.*, `".$wpdb->get_blog_prefix()."custmain_changes`.file_id FROM `".$wpdb->get_blog_prefix()."custmain_autorun` INNER JOIN `".$wpdb->get_blog_prefix()."custmain_changes` ON `".$wpdb->get_blog_prefix()."custmain_autorun`.change_id=`".$wpdb->get_blog_prefix()."custmain_changes`.id WHERE `type`='theme'"), ARRAY_A);
		if(!count($data)) {
			return;
		}
		$themes = array();
		foreach($data as $autorun){
			$themes[] = $autorun['subject'];
		}
		
		foreach($options['themes'] as $theme) {
			if(in_array($theme, $themes)) {
				$files = array();
				foreach($data as $autorun){
					if($autorun['subject'] == $theme) {
						$files[$autorun['file_id']] .= $autorun['change_id'].',';
					}
				}
				foreach($files as $file_id => $change_ids){
					$_POST['file_id'] = $file_id;
					$_POST['change_ids'] = substr($change_ids,0,strlen($change_ids)-1);
					$_custmain_ajax->process_file(1);
				}
			}
		}
	}
	
}
add_action( 'upgrader_process_complete', 'custmain_on_update', 1000, 2 );


?>