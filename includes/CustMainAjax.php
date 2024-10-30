<?php

if ( ! defined( 'ABSPATH' ) ) {
 exit;
}

class CustMainAjax{
	
	private $ajax_actions = array(
		"get_customizations",
		"check_file",
		"file_tip",
		"load_files",
		"load_changes",
		"update_original",
		"update_replacement",
		"delete_group",
		"restore_group",
		"delete_file",
		"restore_file",
		"delete_change",
		"restore_change",
		"update_group_title",
		"update_file_path",
		"update_change_title",
		"process_file",
		"new_change",
		"new_file",
		"new_group",
		"export",
		"import",
		"get_logs",
		"download_logs",
		"revert_file",
		"get_autorun_subjects",
		"set_autorun"
	);

	
	function __construct(){
		foreach($this->ajax_actions as $ajax_action){
			add_action('wp_ajax_custmain_'.$ajax_action, array($this, $ajax_action));
		}
	}
	
	
	function get_customizations(){
		$groups = custmain_get_groups();
		custmain_print_group(0, __('Uncategorized', 'custmain'));
		foreach($groups as $group){
			custmain_print_group($group['id'], $group['title']);
		}
		wp_die();
	}

	function check_file() {
		$path = $_POST['path'];
		$path = str_replace("\\", "/", $path);
		if($path[0] == "/") $path = substr($path,1);
		if(file_exists(ABSPATH.$path) && !is_dir(ABSPATH.$path)) wp_die('true');
		wp_die('false');
	}
	
	function file_tip() {
		$path = $_POST['path'];
		$path = str_replace("\\", "/", $path);
		if($path[0] == "/") $path = substr($path,1);
		$dir = ABSPATH.substr($path, 0, strrpos($path, "/"));
		$name = substr($path, strrpos($path, "/")+1);
		if(strrpos($path, "/") === false) $name = $path;
		$listing = scandir($dir);
		$items1 = array();
		$items2 = array();
		$items3 = array();
		$items4 = array();
		foreach($listing as $listitem){
			if($listitem == "." || $listitem == "..") continue;
			$pos = strpos($listitem, $name);
			if($pos === false && $name != "") continue;
			if($pos == 0) {
				if(is_dir($dir."/".$listitem)) {
					$listitem .= "/";
					$listitem = "<b>".$name."</b>".substr($listitem, strlen($name));
					$items1[] = $listitem;
				} else {
					$listitem = "<b>".$name."</b>".substr($listitem, strlen($name));
					$items3[] = $listitem;
				}
			} else {
				if(is_dir($dir."/".$listitem)) {
					$listitem .= "/";
					$listitem = substr($listitem, 0, $pos)."<b>".$name."</b>".substr($listitem, $pos+strlen($name));
					$items2[] = $listitem;
				} else {
					$listitem = substr($listitem, 0, $pos)."<b>".$name."</b>".substr($listitem, $pos+strlen($name));
					$items4[] = $listitem;
				}
			}
		}
		$listing = array_merge($items1, $items2, $items3, $items4);
		foreach($listing as $listitem){
			$pre = substr($path, 0, strrpos($path, "/"));
			if($pre != "") $pre = "<b>".$pre."/"."</b>";
			if($listitem[strlen($listitem)-1] == '/') $class = 'custmain_tip_folder';
			else $class = 'custmain_tip_file';
			echo '<div class="custmain_tip_item '.$class.'" onmouseenter="custmain_select_tip(this);" onclick="custmain_apply_tip(this);">'.$pre.$listitem.'</div>';
		}
		if(!count($listing)) echo '<div class="custmain_tip_item">'.__('No matches found', 'custmain').'</div>';
		wp_die();
	}

	function load_files() {
		$group_id = $_POST['group_id'];
		$files = custmain_get_files($group_id);
		foreach($files as $file){
			custmain_print_file($file['id'], $file['path']);
		}
		wp_die();
	}

	function load_changes() {
		$file_id = $_POST['file_id'];
		$changes = custmain_get_changes($file_id);
		foreach($changes as $change){
			custmain_print_change($change['id'], $change['title'], $change['original'], $change['replacement']);
		}
		wp_die();
	}

	function new_group(){	
		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_groups` (active) VALUES (1)"));
		if($data == 1) wp_die($wpdb->insert_id);
		wp_die(0);
	}

	function new_file(){
		$group_id = $_POST['group_id'];
		
		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_files` (group_id, active) VALUES (%d, 1)", $group_id));
		if($data == 1) wp_die($wpdb->insert_id);
		wp_die(0);
	}

	function new_change(){
		$file_id = $_POST['file_id'];
		
		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_changes` (file_id, active) VALUES (%d, 1)", $file_id));
		if($data == 1) wp_die($wpdb->insert_id);
		wp_die(0);
	}

	function update_original() {
		$change_id = $_POST['change_id'];
		$original = $_POST['original'];
		
		global $wpdb;
		
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_changes` SET `original`='%s' WHERE `id`=%d", $original, $change_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Original is not saved', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Original is not saved', 'custmain').': '.__('Cannot find the change you are editing in the database', 'custmain').'.');
		wp_die(__('Original is not saved', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function update_replacement() {
		$change_id = $_POST['change_id'];
		$replacement = $_POST['replacement'];
		
		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_changes` SET `replacement`='%s' WHERE `id`=%d", $replacement, $change_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Replacement is not saved', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Replacement is not saved', 'custmain').': '.__('Cannot find the change you are editing in the database', 'custmain').'.');
		wp_die(__('Replacement is not saved', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function delete_group() {
		$group_id = $_POST['group_id'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_groups` SET `active`=0 WHERE `id`=%d", $group_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Group is not deleted', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Group is not deleted', 'custmain').': '.__('Cannot find the group you are trying to delete in the database', 'custmain').'.');
		wp_die(__('Group is not deleted', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function restore_group() {
		$group_id = $_POST['group_id'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_groups` SET `active`=1 WHERE `id`=%d", $group_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Group is not restored', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Group is not restored', 'custmain').': '.__('Cannot find the group you are trying to restore in the database', 'custmain').'.');
		wp_die(__('Group is not restored', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function delete_file() {
		$file_id = $_POST['file_id'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_files` SET `active`=0 WHERE `id`=%d", $file_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('File is not deleted', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('File is not deleted', 'custmain').': '.__('Cannot find the file you are trying to delete in the database', 'custmain').'.');
		wp_die(__('File is not deleted', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function restore_file() {
		$file_id = $_POST['file_id'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_files` SET `active`=1 WHERE `id`=%d", $file_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('File is not restored', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('File is not restored', 'custmain').': '.__('Cannot find the file you are trying to restore in the database', 'custmain').'.');
		wp_die(__('File is not restored', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function delete_change() {
		$change_id = $_POST['change_id'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_changes` SET `active`=0 WHERE `id`=%d", $change_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Change is not deleted', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Change is not deleted', 'custmain').': '.__('Cannot find the change you are trying to delete in the database', 'custmain').'.');
		wp_die(__('Change is not deleted', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function restore_change() {
		$change_id = $_POST['change_id'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_changes` SET `active`=1 WHERE `id`=%d", $change_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Change is not restored', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Change is not restored', 'custmain').': '.__('Cannot find the change you are trying to restore in the database', 'custmain').'.');
		wp_die(__('Change is not restored', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function update_group_title() {
		$group_id = $_POST['group_id'];
		$title = $_POST['title'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_groups` SET `title`=%s WHERE `id`=%d", $title, $group_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Title is not changed', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Title is not changed', 'custmain').': '.__('Cannot find the group you are trying to rename in the database', 'custmain').'.');
		wp_die(__('Title is not changed', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function update_file_path() {
		$file_id = $_POST['file_id'];
		$path = $_POST['path'];
		
		$path = str_replace("\\", "/", $path);
		if($path[0] == "/") $path = substr($path,1);
		if(!file_exists(ABSPATH.$path)) wp_die(__('File is not saved', 'custmain').': '.__('Cannot find the file in the filesystem', 'custmain').'.');

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_files` SET `path`=%s WHERE `id`=%d", $path, $file_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('File is not saved', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('File is not saved', 'custmain').': '.__('Cannot find the file you are trying to edit in the database', 'custmain').'.');
		wp_die(__('File is not saved', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function update_change_title() {
		$change_id = $_POST['change_id'];
		$title = $_POST['title'];

		global $wpdb;
		$data = $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->get_blog_prefix()."custmain_changes` SET `title`=%s WHERE `id`=%d", $title, $change_id));
		if($data == 1) wp_die('true');
		if($data == '') wp_die(__('Title is not changed', 'custmain').': '.__('Database error', 'custmain').'.');
		if($data == 0) wp_die(__('Title is not changed', 'custmain').': '.__('Cannot find the change you are trying to rename in the database', 'custmain').'.');
		wp_die(__('Title is not changed', 'custmain').': '.__('Unknown error', 'custmain').'.');
	}

	function import(){
		$importcode = $_POST['importcode'];
		$importcode = base64_decode($importcode);
		if($importcode === false) wp_die(__('Unable to import', 'custmain').': '.__('Export code is corrupted', 'custmain').'.');
		$groups = json_decode($importcode, true);
		if(is_null($groups)) wp_die(__('Unable to import', 'custmain').': '.__('Export code is corrupted', 'custmain').'.');

		global $wpdb;
		foreach($groups as $group){
			$data = $wpdb->query($wpdb->prepare("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_groups` (title) VALUES (%s)", $group['title']));
			$group_id = $wpdb->insert_id;
			$files = $group['files'];
			foreach($files as $file){
				$data = $wpdb->query($wpdb->prepare("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_files` (path, group_id) VALUES (%s, %d)", $file['path'], $group_id));
				$file_id = $wpdb->insert_id;
				$changes = $file['changes'];
				foreach($changes as $change){
					$data = $wpdb->query($wpdb->prepare("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_changes` (title, original, replacement, file_id) VALUES (%s, %s, %s, %d)", $change['title'], $change['original'], $change['replacement'], $file_id));
				}
			}
		}
		wp_die('true');
	}

	function export(){
		$change_ids = $_POST['change_ids'];
		$change_ids = explode(",",$change_ids);
		
		global $wpdb;
		$file_ids = $wpdb->get_col("SELECT file_id FROM `".$wpdb->get_blog_prefix()."custmain_changes` WHERE `id` IN (".implode(", ",array_map('intval', $change_ids)).") GROUP BY file_id");
		if(!count($file_ids)) {
			wp_die('false');
		}
		
		$group_ids = $wpdb->get_col("SELECT group_id FROM `".$wpdb->get_blog_prefix()."custmain_files` WHERE `id` IN (".implode(", ",array_map('intval', $file_ids)).") GROUP BY group_id");
		if(!count($group_ids)) {
			wp_die('false');
		}
		
		$result = array();
		foreach($group_ids as $group_id){
			if($group_id == 0) $group_title = __('Uncategorized', 'custmain');
			else $group_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM `".$wpdb->get_blog_prefix()."custmain_groups` WHERE `id`=%d", $group_id));
			
			$group = array();
			$group['title'] = $group_title;
			
			$group_files = $wpdb->get_results($wpdb->prepare("SELECT id, path FROM `".$wpdb->get_blog_prefix()."custmain_files` WHERE `id` IN (".implode(", ",array_map('intval', $file_ids)).") AND `group_id`=%d", $group_id), ARRAY_A);
			foreach($group_files as $group_file){
				$file = array();
				$file['path'] = $group_file['path'];
				
				$file_changes = $wpdb->get_results($wpdb->prepare("SELECT original, replacement, title FROM `".$wpdb->get_blog_prefix()."custmain_changes` WHERE `id` IN (".implode(", ",array_map('intval', $change_ids)).") AND `file_id`=%d", $group_file['id']), ARRAY_A);
				foreach($file_changes as $file_change){
					$file['changes'][] = $file_change;
				}
				$group['files'][] = $file;
			}
			$result[] = $group;
		}
		wp_die(base64_encode(json_encode($result)));
	}

	function process_file($calltype = 0) {
		$file_id = $_POST['file_id'];
		$change_ids = $_POST['change_ids'];

		$datetime = time();
		$return = array();
		global $wpdb;
		$data = $wpdb->get_row($wpdb->prepare("SELECT path FROM `".$wpdb->get_blog_prefix()."custmain_files` WHERE `id`=%d", $file_id), ARRAY_A);
		if(is_null($data) || !count($data)) {
			$this->log_change($calltype, $datetime, __('Unknown', 'custmain').": ID ".$file_id, "", 2, __('File not found in the database', 'custmain'));
			if(!$calltype) wp_die($this->error_replies($change_ids, __('File not found in the database', 'custmain')));
			else return;
		}
		$path = $data['path'];
		
		if($path == "") {
			$this->log_change($calltype, $datetime, __('Unknown', 'custmain').": ID ".$file_id, "", 2, __('File name not provided', 'custmain'));
			if(!$calltype) wp_die($this->error_replies($change_ids, __('File name not provided', 'custmain')));
			else return;
		}
		
		$path = str_replace("\\", "/", $path);
		if($path[0] == "/") $path = substr($path,1);
		if(!file_exists(ABSPATH.$path)) {
			$this->log_change($calltype, $datetime, $path, "", 2, __('File not found in the filesystem', 'custmain'));
			if(!$calltype) wp_die($this->error_replies($change_ids, __('File not found in the filesystem', 'custmain')));
			else return;
		}

		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".$wpdb->get_blog_prefix()."custmain_changes` WHERE `file_id`=%d AND `id` IN (".implode(", ",array_map('intval', explode(",",$change_ids))).")", $file_id), ARRAY_A);
		if(!count($data)) {
			$this->log_change($calltype, $datetime, $path, __('Unknown', 'custmain').": IDs ".$change_ids, 2, __('Change not found in the database', 'custmain'));
			if(!$calltype) wp_die($this->error_replies($change_ids, __('Change not found in the database', 'custmain')));
			else return;
		}
		foreach($data as $key => $value){
			$data[$key]['original'] = stripslashes($value['original']);
			$data[$key]['replacement'] = stripslashes($value['replacement']);
		}
		
		$all_ids = explode(",", $change_ids);
		foreach($all_ids as $cid){
			foreach($data as $change){
				if($change['id'] == $cid) continue 2;
			}
			$return[] = array($cid, 2, __('Change not found in the database', 'custmain'));
			$this->log_change($calltype, $datetime, $path, __('Unknown', 'custmain').": ID ".$cid, 2, __('Change not found in the database', 'custmain'));
		}
		
		$firstchange = true;
		$code = file_get_contents(ABSPATH.$path);
		$code = str_replace("\r",'',$code);

		foreach($data as $change){
			$isfound = strpos($code, $change['replacement']);
			if($isfound !== false) {
				$return[] = array($change['id'], 1, __('The replacement code is already found in the file', 'custmain'));
				$this->log_change($calltype, $datetime, $path, ($change['title'] == "" ? __('Unknown', 'custmain') : $change['title']).": ID ".$change['id'], 1, __('The replacement code is already found in the file', 'custmain'));
				continue;
			}
			$isfound = strpos($code, $change['original']);
			if($isfound === false) {
				$return[] = array($change['id'], 2, __('The original code is not found in the file', 'custmain'));
				$this->log_change($calltype, $datetime, $path, ($change['title'] == "" ? __('Unknown', 'custmain') : $change['title']).": ID ".$change['id'], 2, __('The original code is not found in the file', 'custmain'));
				continue;
			}
			
			if($firstchange){
				if(strpos($path, "/") === false) $dirpath = "";
				else $dirpath = substr($path, 0, strrpos($path, "/"));
				if(!file_exists(ABSPATH . "cm-backup/" . $dirpath) || !is_dir(ABSPATH . "cm-backup/" . $dirpath)){
					if (!mkdir(ABSPATH . "cm-backup/" . $dirpath, 0755, true)) {
						$this->log_change($calltype, $datetime, $path, __('Unknown', 'custmain').": IDs ".$change_ids, 2, __('Unable to create a file backup', 'custmain'));
						if(!$calltype) wp_die($this->error_replies($change_ids, __('Unable to create a file backup', 'custmain')));
						else return;
					}
				}
				if(!file_exists(ABSPATH . "cm-backup/" . $path. ".cmcopy")) if(!file_put_contents(ABSPATH . "cm-backup/" . $path. ".cmcopy", $code)) {
					$this->log_change($calltype, $datetime, $path, __('Unknown', 'custmain').": IDs ".$change_ids, 2, __('Unable to create a file backup', 'custmain'));
					if(!$calltype) wp_die($this->error_replies($change_ids, __('Unable to create a file backup', 'custmain')));
					else return;
				}
				$firstchange = false;
			}
			
			$code = str_replace($change['original'], $change['replacement'], $code);
			$return[] = array($change['id'], 0, "");
			$this->log_change($calltype, $datetime, $path, ($change['title'] == "" ? __('Unknown', 'custmain') : $change['title']).": ID ".$change['id'], 0, "");
		}
		
		if(!file_put_contents(ABSPATH.$path, $code)) {
			$this->log_change($calltype, $datetime, $path, __('Unknown', 'custmain').": IDs ".$change_ids, 2, __('Unable to write to the file', 'custmain'));
			if(!$calltype) wp_die($this->error_replies($change_ids, __('Unable to write to the file', 'custmain')));
			else return;
		}
		
		if(!$calltype) wp_die(json_encode($return));
		else return;
	}

	function revert_file(){
		$file_id = $_POST['file_id'];
		
		$calltype = 0;
		
		global $wpdb;
		$data = $wpdb->get_row($wpdb->prepare("SELECT path FROM `".$wpdb->get_blog_prefix()."custmain_files` WHERE `id`=%d", $file_id), ARRAY_A);
		if(!count($data)) {
			$this->log_change($calltype, $datetime, __('Unknown', 'custmain').": ID ".$file_id, "", 2, __('File not found in the database', 'custmain'));
			wp_die(__('File is not reverted', 'custmain').': '.__('File not found in the database', 'custmain'));
		}
		$path = $data['path'];
		
		if($path == "") {
			$this->log_change($calltype, $datetime, __('Unknown', 'custmain').": ID ".$file_id, "", 2, __('File name not provided', 'custmain'));
			wp_die(__('File is not reverted', 'custmain').': '.__('File name not provided', 'custmain'));
		}
		
		$path = str_replace("\\", "/", $path);
		if($path[0] == "/") $path = substr($path,1);
		if(!file_exists(ABSPATH.$path)) {
			$this->log_change($calltype, $datetime, $path, "", 2, __('File not found in the filesystem', 'custmain'));
			wp_die(__('File is not reverted', 'custmain').': '.__('File not found in the filesystem', 'custmain'));
		}

		if(!file_exists(ABSPATH . "cm-backup/" . $path. ".cmcopy")){
			$this->log_change($calltype, $datetime, $path, "", 2, __('File backup not found!', 'custmain'));
			wp_die(__('File is not reverted', 'custmain').': '.__('File backup not found', 'custmain'));
		}
		

		$code = file_get_contents(ABSPATH . "cm-backup/" . $path. ".cmcopy");
		
		if(!file_put_contents(ABSPATH.$path, $code)) {
			$this->log_change($calltype, $datetime, $path, "", 2, __('Unable to write to the file', 'custmain'));
			wp_die($this->error_replies($change_ids, __('Unable to write to the file', 'custmain')));
		}
		
		unlink(ABSPATH . "cm-backup/" . $path. ".cmcopy");
		
		wp_die('true');
	}
	
	function log_change($calltype, $datetime, $filename, $changename, $status, $message){
		global $wpdb;
		
		$data = $wpdb->query($wpdb->prepare("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_logs` (`calltype`,`datetime`,`filename`,`changename`,`status`,`message`) VALUES ('%d', '%d', '%s', '%s', '%d', '%s')",$calltype, $datetime, $filename, $changename, $status, $message));
	}

	function error_replies($change_ids, $reply){
		$change_ids = explode(",", $change_ids);
		$return = array();
		foreach($change_ids as $key => $change_id){
			$return[] = array($change_id, 0, $reply);
		}
		return json_encode($return);
	}

	function get_logs(){
		global $wpdb;
		$logs = $wpdb->get_results("SELECT * FROM `".$wpdb->get_blog_prefix()."custmain_logs` ORDER BY `datetime` DESC", ARRAY_A);
		if(!count($logs)) {
			wp_die('<div class="custmain_log_record"><center>'.__('No log records found', 'custmain').'</center></div>');
		}

		$logs_merged = array();
		$logs_status = array();
		foreach($logs as $log){
			$key = md5($log['datetime'].$log['filename']);
			$logs_merged[$key][] = $log;
			if(!isset($logs_status[$key])) $logs_status[$key] = $log['status'];
			elseif($log['status'] > $logs_status[$key]) $logs_status[$key] = $log['status'];
		}
		
		foreach($logs_merged as $key => $log){
			?>
			<div class="custmain_log_record" onclick="custmain_toggle_lr(this);">
				<div class="custmain_lr_calltype custmain_lr_calltype_<?php echo $log[0]['calltype']; ?>" title="<?php echo (!$log[0]['calltype'] ? __('Manual run', 'custmain') : __('Autorun', 'custmain')); ?>"></div>
				<div class="custmain_lr_datetime"><?php echo date('Y/d/m H:i:s', $log[0]['datetime']); ?></div>
				<div class="custmain_lr_filename"><?php echo $log[0]['filename']; ?></div>
				<div class="custmain_lr_status custmain_lr_status_<?php echo $logs_status[$key]; ?>" title="<?php echo (!$logs_status[$key] ? __('Successful', 'custmain') : ($logs_status[$key] == 1 ? __('No replacements', 'custmain') : __('Failed', 'custmain'))); ?>"></div>
			</div>
			<div class="custmain_log_record_details">
				<?php foreach($log as $lr){ ?>
				<div class="custmain_lr_row">
					<div class="custmain_lr_changename"><?php echo $lr['changename']; ?></div>
					<div class="custmain_lr_message"><?php echo $lr['message']; ?></div>
					<div class="custmain_lr_status custmain_lr_status_<?php echo $lr['status']; ?>" title="<?php echo (!$lr['status'] ? __('Successful', 'custmain') : ($lr['status'] == 1 ? __('No replacements', 'custmain') : __('Failed', 'custmain'))); ?>"></div>
				</div>
				<?php } ?>
			</div>
			<?php
		}
		wp_die();
	}

	function download_logs(){
		$start = $_POST['start'];
		$end = $_POST['end'];
		
		if($start != ""){
			$start = strtotime($start);
		}
		if($end != ""){
			$end = strtotime($end);
			$end += 86399;
		}
		
		global $wpdb;
		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".$wpdb->get_blog_prefix()."custmain_logs` WHERE ".($start != ""?"`datetime` >= ".$start." AND ":"").($end != ""?"`datetime` <= ".$end." AND ":"")."1 ORDER BY `datetime` ASC"), ARRAY_A);
		if(!count($data)) {
			wp_die("false");
		}
		foreach($data as $log){
			echo "[".date('Y.d.m',$log['datetime'])."] "; // Date
			echo "[".date('H:i:s',$log['datetime'])."] "; // Time
			echo "[".($log['calltype'] == 0?"Manual":"Auto")."] "; // Calltype
			echo "[".($log['status'] == 0?"SUCCESS":($log['status'] == 1?"WARNING":"ERROR"))."] "; // Status
			echo "[".$log['filename']."] "; // Filename
			echo "[".$log['changename']."] "; // Changename
			if($log['message'] != "") echo "[".$log['message']."] "; // Message
			echo "\r\n";
		}
		wp_die();
	}
	
	function get_autorun_subjects() {
		$type = $_POST['autorun_type'];
		
		$return = array();
		if($type == 'plugin'){
			$data = get_plugins();
			foreach($data as $key => $elem){
				$return[$key] = $elem['Name'];
			}
		} else
		if($type == 'theme'){
			$data = wp_get_themes();
			foreach($data as $key => $elem){
				$return[$key] = $elem['Name'];
			}
		} else
		if($type == 'cron'){
			$return = array('hourly' => 'H', 'daily' => 'D', 'weekly' => 'W', 'monthly' => 'M');
		}
		wp_die(json_encode($return));
	}
	
	function set_autorun() {
		$change_ids = $_POST['changeids'];
		$type = $_POST['autorun_type'];
		$subject = $_POST['autorun_subject'];
		$title = $_POST['autorun_title']; 

		global $wpdb;
		
		$changeids = implode(',', $change_ids);
		$wpdb->query("DELETE FROM `".$wpdb->get_blog_prefix()."custmain_autorun` WHERE `change_id` IN (".$changeids.")");
		
		$values = "";
		foreach($change_ids as $change_id){
			$values .= $wpdb->prepare("('%d','%s','%s','%s'),", $change_id, $type, $title, $subject);
		}
		$values = substr($values,0,strlen($values)-1);
		
		$data = $wpdb->query("INSERT INTO `".$wpdb->get_blog_prefix()."custmain_autorun` (`change_id`,`type`,`title`,`subject`) VALUES ".$values);
		
		if($data >= 1) wp_die('true');
		wp_die('false');
	}
}

$_custmain_ajax = new CustMainAjax();