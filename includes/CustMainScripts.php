<?php

if ( ! defined( 'ABSPATH' ) ) {
 exit;
}

class CustMainScripts{
	
	function __construct(){
		?>
		<script>
		$ = jQuery;
		
		function custmain_alert(type, message){
			var alert_class;
			var alert_timeout;
			if(type) {
				alert_class = "custmain_alert_success";
				alert_timeout = 2000;
			} else {
				alert_class = "custmain_alert_error";
				alert_timeout = 6000;
			}
			$('#custmain_alert').css('bottom', '-100px');
			setTimeout(function(){
				$('#custmain_alert').removeClass("custmain_alert_success");
				$('#custmain_alert').removeClass("custmain_alert_error");
				$('#custmain_alert').addClass(alert_class);
				$('#custmain_alert').html(message);
				$('#custmain_alert').css('bottom', '0px');
				setTimeout(function(){
					$('#custmain_alert').css('bottom', '-100px');
				}, alert_timeout);
			}, 500);
		}
		
		function custmain_load(element, state){
			if(state) element.find('.custmain_load').first().fadeIn(300);
			else element.find('.custmain_load').first().fadeOut(300);
		}
		
		function custmain_ajax(action, data, onsuccess, onfailure){
			data['action'] = 'custmain_' + action;
			$.post(ajaxurl, data, function(response) {
				onsuccess(response);
			});
		}
		
		$(window).load(function(){
			$('.custmain_wrap').css('opacity','1');
		});
		
		document.onkeydown = custmain_keypress_handle;
		
		function custmain_toggle_group(elem){
			elem = $(elem);
			if(elem.find('.custmain_title_text').is(":focus") || custmain_infocus()) return;
			var group_id = elem.parent().attr('data-id');
			var data = {
				group_id: group_id
			};
			
			if(elem.parent().find('.custmain_container').first().html() == '') {
				custmain_load(elem, true);
				custmain_ajax('load_files', data, function(response){
					$('.custmain_group[data-id="'+group_id+'"] .custmain_container').html(response);
					custmain_load(elem, false);
					elem.parent().find('.custmain_container').first().slideToggle(500);
				});
			} else elem.parent().find('.custmain_container').first().slideToggle(500);
		}
		
		function custmain_toggle_file(elem){
			elem = $(elem);
			if(elem.find('.custmain_title_text').is(":focus") || custmain_infocus()) return;
			var file_id = elem.parent().attr('data-id');
			var data = {
				file_id: file_id
			};
			if(elem.parent().find('.custmain_container').first().html() == '') {
				custmain_load(elem, true);
				custmain_ajax('load_changes', data, function(response){
					$('.custmain_file[data-id="'+file_id+'"] .custmain_container').html(response);
					custmain_load(elem, false);
					elem.parent().find('.custmain_container').first().slideToggle(500);
				});
			} else elem.parent().find('.custmain_container').first().slideToggle(500);
		}
		
		function custmain_list_group(group, callback){
			var group_id = group.attr('data-id');
			var data = {
				group_id: group_id
			};
			if(group.find('.custmain_container').first().html() == '') {
				custmain_load(group.find('.custmain_group_title'), true);
				custmain_ajax('load_files', data, function(response){
					$('.custmain_group[data-id="'+group_id+'"] .custmain_container').html(response);
					custmain_load(group.find('.custmain_group_title'), false);
					group.find('.custmain_container').first().slideDown(500);
					if(typeof callback !== 'undefined') callback(group);
				});
			} else {
				group.find('.custmain_container').first().slideDown(500);
				if(typeof callback !== 'undefined') callback(group);
			}
		}
		
		function custmain_list_file(file, callback){
			var file_id = file.attr('data-id');
			var data = {
				file_id: file_id
			};
			if(file.find('.custmain_container').first().html() == '') {
				custmain_load(file.find('.custmain_file_title'), true);
				custmain_ajax('load_changes', data, function(response){
					$('.custmain_file[data-id="'+file_id+'"] .custmain_container').html(response);
					custmain_load(file.find('.custmain_file_title'), false);
					file.find('.custmain_container').first().slideDown(500);
					if(typeof callback !== 'undefined') callback(file);
				});
			} else {
				file.find('.custmain_container').first().slideDown(500);
				if(typeof callback !== 'undefined') callback(file);
			}
		}
		
		function custmain_delete_selected(){
			$('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').find('.custmain_control_delete').each(function(){
				custmain_delete_change(this);
			});
			$('.custmain_wrap .custmain_file[data-selected="1"]').not('.custmain_removed').find('.custmain_control_delete').each(function(){
				custmain_delete_file(this);
			});
			$('.custmain_wrap .custmain_group[data-selected="1"]').not('.custmain_removed').find('.custmain_control_delete').each(function(){
				custmain_delete_group(this);
			});
		}
		
		function custmain_delete_group(elem){
			elem = $(elem);
			var group_id = elem.closest('.custmain_group').attr('data-id');
			var data = {
				group_id: group_id
			};
			custmain_load(elem.parent().parent(), true);
			custmain_ajax('delete_group', data, function(response){
				custmain_load(elem.parent().parent(), false);
				if(response == "true"){
					var ph = $('#custmain_group_placeholder_template').clone();
					ph.attr('data-id', group_id);
					ph.removeAttr('id');
					ph.css('display','none');
					elem.parent().parent().parent().after(ph);
					elem.parent().parent().parent().slideUp(500);
					elem.parent().parent().parent().addClass('custmain_removed');
					ph.slideDown(500);
					custmain_alert(true, "<?php echo __('Group has been deleted!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_restore_group(elem){
			elem = $(elem);
			var group_id = elem.parent().attr('data-id');
			var data = {
				group_id: group_id
			};
			custmain_load(elem.parent(), true);
			custmain_ajax('restore_group', data, function(response){
				custmain_load(elem.parent(), false);
				if(response == "true"){
					$('.custmain_group[data-id="'+group_id+'"]').slideDown(500);
					$('.custmain_group[data-id="'+group_id+'"]').removeClass('custmain_removed');
					elem.parent().slideUp(500);
					setTimeout(function(){elem.parent().remove();}, 600);
					custmain_alert(true, "<?php echo __('Group has been restored!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_delete_file(elem){
			elem = $(elem);
			var file_id = elem.closest('.custmain_file').attr('data-id');
			var data = {
				file_id: file_id
			};
			custmain_load(elem.parent().parent(), true);
			custmain_ajax('delete_file', data, function(response){
				custmain_load(elem.parent().parent(), false);
				if(response == "true"){
					var ph = $('#custmain_file_placeholder_template').clone();
					ph.attr('data-id', file_id);
					ph.removeAttr('id');
					ph.css('display','none');
					elem.parent().parent().parent().after(ph);
					elem.parent().parent().parent().slideUp(500);
					elem.parent().parent().parent().addClass('custmain_removed');
					ph.slideDown(500);
					custmain_alert(true, "<?php echo __('File has been deleted!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_restore_file(elem){
			elem = $(elem);
			var file_id = elem.parent().attr('data-id');
			var data = {
				file_id: file_id
			};
			custmain_load(elem.parent(), true);
			custmain_ajax('restore_file', data, function(response){
				custmain_load(elem.parent(), false);
				if(response == "true"){
					$('.custmain_file[data-id="'+file_id+'"]').slideDown(500);
					$('.custmain_file[data-id="'+file_id+'"]').removeClass('custmain_removed');
					elem.parent().slideUp(500);
					setTimeout(function(){elem.parent().remove();}, 600);
					custmain_alert(true, "<?php echo __('File has been restored!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_delete_change(elem){
			elem = $(elem);
			var change_id = elem.closest('.custmain_change').attr('data-id');
			var data = {
				change_id: change_id
			};
			custmain_load(elem.parent().parent(), true);
			custmain_ajax('delete_change', data, function(response){
				custmain_load(elem.parent().parent(), false);
				if(response == "true"){
					var ph = $('#custmain_change_placeholder_template').clone();
					ph.attr('data-id', change_id);
					ph.removeAttr('id');
					ph.css('display','none');
					elem.parent().parent().parent().after(ph);
					elem.parent().parent().parent().slideUp(500);
					elem.parent().parent().parent().addClass('custmain_removed');
					ph.slideDown(500);
					custmain_alert(true, "<?php echo __('Change has been deleted!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_restore_change(elem){
			elem = $(elem);
			var change_id = elem.parent().attr('data-id');
			var data = {
				change_id: change_id
			};
			custmain_load(elem.parent(), true);
			custmain_ajax('restore_change', data, function(response){
				custmain_load(elem.parent(), false);
				if(response == "true"){
					$('.custmain_change[data-id="'+change_id+'"]').slideDown(500);
					$('.custmain_change[data-id="'+change_id+'"]').removeClass('custmain_removed');
					elem.parent().slideUp(500);
					setTimeout(function(){elem.parent().remove();}, 600);
					custmain_alert(true, "<?php echo __('Change has been restored!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_update_original(elem){
			var elem = $(elem);
			var original = elem.val();
			var change_id = elem.parent().parent().attr('data-id');
			var data = {
				original: original,
				change_id: change_id
			};
			custmain_load(elem.parent().parent(), true);
			elem.attr('disabled','true');
			custmain_ajax('update_original', data, function(response){
				custmain_load(elem.parent().parent(), false);
				elem.removeAttr('disabled');
				if(response == "true") {
					custmain_alert(true, "<?php echo __('Original has been saved!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_update_replacement(elem){
			var elem = $(elem);
			var replacement = elem.val();
			var change_id = elem.parent().parent().attr('data-id');
			var data = {
				replacement: replacement,
				change_id: change_id
			};
			custmain_load(elem.parent().parent(), true);
			elem.attr('disabled','true');
			custmain_ajax('update_replacement', data, function(response){
				custmain_load(elem.parent().parent(), false);
				elem.removeAttr('disabled');
				if(response == "true") {
					custmain_alert(true, "<?php echo __('Replacement has been saved!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_manage_input(elem){
			if($(elem).hasClass('custmain_title_text')){
				var occ = $(elem).val().length;
				if(occ > 50) occ = 50;
				if(occ < 10) occ = 10;
				$(elem).attr('size', occ);
			} else
			if($(elem).hasClass('custmain_original_input') || $(elem).hasClass('custmain_replacement_input')){
				var occ = ($(elem).val().match(/\n/g) || []).length + 1;
				if(occ < 2) occ = 2;
				$(elem).attr('rows', occ);
				var lines = '';
				for(var r=1; r<=occ+1; r++) lines += r + '\n';
				$(elem).prev().val(lines);
				$(elem).prev().attr('rows', occ);
			}
		}
		
		function custmain_update_group_title(elem){
			var elem = $(elem);
			var group_id = elem.parent().parent().attr('data-id');
			var title = elem.val();
			elem.attr('title', title);
			if(title == elem.attr('data-saved')) return;
			var data = {
				title: title,
				group_id: group_id
			};
			custmain_load(elem.parent(), true);
			elem.attr('disabled','true');
			custmain_ajax('update_group_title', data, function(response){
				custmain_load(elem.parent(), false);
				elem.removeAttr('disabled');
				if(response == "true") {
					custmain_alert(true, "<?php echo __('Title has been saved!', 'custmain'); ?>");
					elem.attr('data-saved', title);
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_update_file_path(elem){
			var elem = $(elem);
			var file_id = elem.parent().parent().attr('data-id');
			var path = elem.val();
			elem.attr('title', path);
			if(path.slice(-1) == "/") return;
			if(path == elem.attr('data-saved')) return;
			var data = {
				path: path,
				file_id: file_id
			};
			custmain_load(elem.parent(), true);
			elem.attr('disabled','true');
			custmain_ajax('update_file_path', data, function(response){
				custmain_load(elem.parent(), false);
				elem.removeAttr('disabled');
				if(response == "true") {
					custmain_alert(true, "<?php echo __('File has been saved!', 'custmain'); ?>");
					elem.attr('data-saved', path);
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_update_change_title(elem){
			var elem = $(elem);
			var change_id = elem.parent().parent().attr('data-id');
			var title = elem.val();
			elem.attr('title', title);
			if(title == elem.attr('data-saved')) return;
			var data = {
				title: title,
				change_id: change_id
			};
			custmain_load(elem.parent(), true);
			elem.attr('disabled','true');
			custmain_ajax('update_change_title', data, function(response){
				custmain_load(elem.parent(), false);
				elem.removeAttr('disabled');
				if(response == "true") {
					custmain_alert(true, "<?php echo __('Title has been saved!', 'custmain'); ?>");
					elem.attr('data-saved', title);
				} else {
					custmain_alert(false, response);
				}
			});
		}

		function custmain_focus(state, elem){
			if(state){
				$('.custmain_wrap').addClass('custmain_infocus');
				if(typeof elem !== 'undefined'){
					if($(elem).hasClass('custmain_original_input') || $(elem).hasClass('custmain_replacement_input')){
						var occ = ($(elem).val().match(/\n/g) || []).length + 1;
						if(occ < 2) occ = 2;
						$(elem).attr('rows', occ);
						var lines = '';
						for(var r=1; r<=occ+1; r++) lines += r + '\n';
						$(elem).prev().val(lines);
						$(elem).prev().attr('rows', occ);
					}
				}
			} else {
				setTimeout(function(){
					$('.custmain_wrap').removeClass('custmain_infocus');
				}, 500);
				if(typeof elem !== 'undefined'){
					if($(elem).hasClass('custmain_original_input') || $(elem).hasClass('custmain_replacement_input')){
						var occ = ($(elem).val().match(/\n/g) || []).length + 1;
						if(occ > 10) occ = 10;
						$(elem).attr('rows', occ);
						$(elem).prev().val('1\n2\n3\n4\n5\n6\n7\n8\n9\n10\n11');
						$(elem).prev().attr('rows', occ);
					}
				}
			}
		}
		
		function custmain_infocus(){
			return $('.custmain_wrap').hasClass('custmain_infocus');
		}
		
		function custmain_check_file(elem){
			var elem = $(elem);
			var path = $(elem).val();
			var data = {
				path: path
			};
			custmain_ajax('check_file', data, function(response){
				if(response == 'true') $(elem).css('background','');
				else if(response == 'false') $(elem).css('background','rgba(255,0,0,0.1)');
				if($(elem).is(':focus')) setTimeout(function(){
					custmain_check_file(elem[0]);
				},500);
			});
		}
		
		function custmain_revert_file(elem){
			elem = $(elem);
			var file_id = elem.closest('.custmain_file').attr('data-id');
			var data = {
				file_id: file_id
			};
			custmain_load(elem.parent().parent(), true);
			custmain_ajax('revert_file', data, function(response){
				custmain_load(elem.parent().parent(), false);
				if(response == "true"){
					elem.fadeOut(500);
					custmain_alert(true, "<?php echo __('File has been reverted!', 'custmain'); ?>");
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_file_tip(elem){
			var elem = $(elem);
			var tip = elem.parent().find('.custmain_file_tip');
			var path = $(elem).val();
			var data = {
				path: path
			};
			custmain_ajax('file_tip', data, function(response){
				tip.fadeIn(300);
				tip.addClass('custmain_active_tip');
				tip.html(response);
			});
		}
		
		function custmain_close_tip(elem){
			var elem = $(elem);
			var tip = elem.parent().find('.custmain_file_tip');
			tip.fadeOut(300);
		}
		
		function custmain_run_change(elem){
			custmain_clear_selected();
			custmain_add_change(elem, custmain_run_selected);
		}
		
		function custmain_run_file(elem){
			custmain_clear_selected();
			custmain_add_file(elem, custmain_run_selected);
		}
		
		function custmain_run_group(elem){
			custmain_clear_selected();
			custmain_add_group(elem, custmain_run_selected);
		}
		
		function custmain_select_change(elem){
			elem = $(elem);
			if(elem.closest('.custmain_change').attr('data-selected') != "1") custmain_add_change(elem[0], custmain_selection_changed);
			else {
				elem.closest('.custmain_change').removeAttr('data-selected');
				if(elem.closest('.custmain_file').find('.custmain_change[data-selected="1"]').not('.custmain_removed').length == 0) elem.closest('.custmain_file').removeAttr('data-selected');
				if(elem.closest('.custmain_group').find('.custmain_file[data-selected="1"]').not('.custmain_removed').length == 0) elem.closest('.custmain_group').removeAttr('data-selected');
				custmain_selection_changed();
			}
		}
		
		function custmain_select_file(elem){
			elem = $(elem);
			if(elem.closest('.custmain_file').attr('data-selected') != "1") custmain_add_file(elem[0], custmain_selection_changed);
			else {
				elem.closest('.custmain_file').removeAttr('data-selected');
				elem.closest('.custmain_file').find('.custmain_change[data-selected="1"]').removeAttr('data-selected');
				if(elem.closest('.custmain_group').find('.custmain_file[data-selected="1"]').not('.custmain_removed').length == 0) elem.closest('.custmain_group').removeAttr('data-selected');
				custmain_selection_changed();
			}
		}
		
		function custmain_select_group(elem){
			elem = $(elem);
			if(elem.closest('.custmain_group').attr('data-selected') != "1") custmain_add_group(elem[0], custmain_selection_changed);
			else {
				elem.closest('.custmain_group').removeAttr('data-selected');
				elem.closest('.custmain_group').find('.custmain_file[data-selected="1"], .custmain_change[data-selected="1"]').removeAttr('data-selected');
				custmain_selection_changed();
			}
		}
		
		function custmain_select_all(){
			var elem = $('#custmain_select_all');
			if(elem.attr('data-selected') != "1") {
				$('.custmain_wrap .custmain_group').not('.custmain_removed').each(function(){
					custmain_add_group(this, custmain_all_selected);
				});
			}
			else {
				$('.custmain_wrap .custmain_group').not('.custmain_removed').each(function(){
					$(this).removeAttr('data-selected');
					$(this).find('.custmain_file[data-selected="1"], .custmain_change[data-selected="1"]').removeAttr('data-selected');
				});
				elem.removeAttr('data-selected');
				custmain_selection_changed();
			}
		}
		
		function custmain_all_selected(){
			if($('.custmain_wrap .custmain_group').not('.custmain_removed').not('[data-selected="1"]').length == 0){
				var elem = $('#custmain_select_all');
				elem.attr('data-selected', '1');
				custmain_selection_changed();
			}
		}
		
		function custmain_selection_changed(){
			if($('.custmain_wrap .custmain_group').not('.custmain_removed').not('[data-selected="1"]').length == 0){
				var elem = $('#custmain_select_all');
				elem.attr('data-selected', '1');
			} else {
				var elem = $('#custmain_select_all');
				elem.removeAttr('data-selected');
			}
			
			if($('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').length > 0){
				$('#custmain_selected_text').html('<?php echo __('Changes selected', 'custmain'); ?>: '+$('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').length).fadeIn(500).css("display","inline-block");
				$('#custmain_selected_controls').fadeIn(500).css("display","inline-block");
			} else {
				$('#custmain_selected_text').fadeOut(500);
				$('#custmain_selected_controls').fadeOut(500);
			}
		}
		
		function custmain_add_change(elem, callback){
			elem = $(elem);
			elem.closest('.custmain_change').attr('data-selected', '1');
			elem.closest('.custmain_file').attr('data-selected', '1');
			elem.closest('.custmain_group').attr('data-selected', '1');
			if(typeof callback !== 'undefined') callback();
		}
		
		function custmain_add_file(elem, callback){
			elem = $(elem);
			custmain_list_file(elem.closest('.custmain_file'), function(){
				elem.closest('.custmain_file').attr('data-selected', '1');
				elem.closest('.custmain_group').attr('data-selected', '1');
				elem.closest('.custmain_file').find('.custmain_change').not('.custmain_removed').attr('data-selected', '1');
				if(typeof callback !== 'undefined') callback();
			});
		}
		
		function custmain_add_group(elem, callback){
			elem = $(elem);
			custmain_list_group(elem.closest('.custmain_group'), function(){
				elem.closest('.custmain_group').attr('data-selected', '1');
				if(elem.closest('.custmain_group').find('.custmain_file').not('.custmain_removed').not('[data-selected="1"]').length == 0) if(typeof callback !== 'undefined') custmain_finish_add_group(elem.closest('.custmain_group'), callback);
				elem.closest('.custmain_group').find('.custmain_file').not('.custmain_removed').each(function(){
					custmain_add_file($(this), function(){
						if(typeof callback !== 'undefined') custmain_finish_add_group(elem.closest('.custmain_group'), callback);
					});
				});
			});
		}
		
		function custmain_finish_add_group(elem, callback){
			if(elem.find('.custmain_file').not('.custmain_removed').not('[data-selected="1"]').length == 0) if(typeof callback !== 'undefined') callback();
		}
		
		function custmain_clear_selected(){
			$('.custmain_change, .custmain_file, .custmain_group').removeAttr('data-selected');
		}
		
		function custmain_show_modal(){
			$('#custmain_modal_bg').fadeIn(500);
			$('#custmain_modal button').hide();
			$('#custmain_progresslog').html('');
			$('#custmain_progressvalue').html('...');
			$('#custmain_progressline').css('width','0%');
			setTimeout(function(){
				$('#custmain_modal').fadeIn(500);
			},500);
		}
		
		function custmain_hide_modal(){
			$('#custmain_modal').fadeOut(500);
			setTimeout(function(){
				$('#custmain_modal_bg').fadeOut(500);
			},500);
		}
		
		function custmain_confirm_run(status){
			if(typeof status === 'undefined'){
				if($('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').length == 0) return custmain_alert(false, "<?php echo __('No changes selected for execution!', 'custmain'); ?>");
				$('#custmain_modal_bg').fadeIn(500);
				$('#custmain_confirm_descr').html('<?php echo __('You are going to execute', 'custmain'); ?><br>'+ $('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').length + ' <?php echo __('changes', 'custmain'); ?>');
				setTimeout(function(){
					$('#custmain_confirm').fadeIn(500);
				},500);
			} else {
				$('#custmain_confirm').fadeOut(500);
				if(status == false) {
					setTimeout(function(){
						$('#custmain_modal_bg').fadeOut(500);
					},500);
				}
				if(status == true){
					custmain_run_selected(true);
				}
			}
		}
		
		var custmain_total;
		var custmain_done;
		
		function custmain_run_selected(confirm){
			if(typeof confirm === 'undefined') return custmain_confirm_run();
			$('#custmain_progressline_anim div').css('display','');
			custmain_show_modal();
			custmain_total = 0;
			custmain_done = 0;
			setTimeout(function(){
				var progresslog = $('#custmain_progresslog');
				progresslog.html('');
				$('.custmain_group[data-selected="1"]').each(function(){
					if($(this).find('.custmain_file').length == 0) return;
					var title = $(this).find('.custmain_group_title .custmain_title_text').val();
					var groupid = $(this).attr('data-id');
					$('<div>', {class: 'custmain_progresslog_note custmain_pn_group custmain_pn_waiting', 'data-id': groupid, html: '<b>'+title+'</b>'}).appendTo(progresslog);
					
					$(this).find('.custmain_file[data-selected="1"]').each(function(){
						if($(this).find('.custmain_change').length == 0) return;
						var title = $(this).find('.custmain_file_title .custmain_title_text').val();
						var fileid = $(this).attr('data-id');
						$('<div>', {class: 'custmain_progresslog_note custmain_pn_file custmain_pn_waiting', 'data-id': fileid, 'data-pid': groupid, html: '<b>'+title+'</b>'}).appendTo(progresslog);
						
						$(this).find('.custmain_change[data-selected="1"]').each(function(){
							var title = $(this).find('.custmain_change_title .custmain_title_text').val();
							var changeid = $(this).attr('data-id');
							$('<div>', {class: 'custmain_progresslog_note custmain_pn_change custmain_pn_waiting', 'data-id': changeid, 'data-pid': fileid, html: '<b>'+title+'</b>'}).appendTo(progresslog);
							custmain_total++;
						});
					});
				});
				setTimeout(function(){
					custmain_refresh_progress(0);
					custmain_process_group();
				}, 1000);
			}, 1000);
		}
		
		function custmain_process_group(){
			var group = $('.custmain_pn_group.custmain_pn_waiting').first();
			if(group.length == 0) {
				$('#custmain_progressline_anim div').fadeOut(500);
				$('#custmain_modal').css('height','440px');
				$('#custmain_progresslog').css('bottom','60px');
				$('#custmain_modal button').css('width','100%');
				setTimeout(function(){
					$('#custmain_modal button').fadeIn(500);
				}, 500);
				return;
			}
			var groupid = group.attr('data-id');
			group.removeClass('custmain_pn_waiting');
			group.addClass('custmain_pn_process');
			custmain_process_file(groupid);
		}
		
		function custmain_process_file(groupid){
			var file = $('.custmain_pn_file.custmain_pn_waiting[data-pid="'+groupid+'"]').first();
			if(file.length == 0) {
				$('.custmain_pn_group[data-id="'+groupid+'"]').removeClass('custmain_pn_process');
				if($('.custmain_pn_file.custmain_pn_failure[data-pid="'+groupid+'"]').length > 0) {
					$('.custmain_pn_group[data-id="'+groupid+'"]').addClass('custmain_pn_failure');
				} else if($('.custmain_pn_file.custmain_pn_warning[data-pid="'+groupid+'"]').length > 0) {
					$('.custmain_pn_group[data-id="'+groupid+'"]').addClass('custmain_pn_warning');
				} else {
					$('.custmain_pn_group[data-id="'+groupid+'"]').addClass('custmain_pn_success');
				}
				setTimeout(function(){
					custmain_process_group();
				}, 500);
				return;
			}
			var fileid = file.attr('data-id');
			file.removeClass('custmain_pn_waiting');
			file.addClass('custmain_pn_process');
			
			var changeslist = '';
			
			$('.custmain_pn_change.custmain_pn_waiting[data-pid="'+fileid+'"]').each(function(){
				$(this).removeClass('custmain_pn_waiting');
				$(this).addClass('custmain_pn_process');
				changeslist += $(this).attr('data-id') + ",";
			});
			
			var data = {
				file_id: fileid,
				change_ids: changeslist.substr(0, changeslist.length-1)
			};
			
			custmain_ajax('process_file', data, function(response){
				var resp = $.parseJSON(response);
				var filestatus = 'success';
				var filechanged = false;
				$.each(resp, function(index, value) {
					$('.custmain_pn_change[data-id="'+value[0]+'"]').removeClass('custmain_pn_process');
					if(value[1] == 0) {
						$('.custmain_pn_change[data-id="'+value[0]+'"]').addClass('custmain_pn_success');
						filechanged = true;
					}
					if(value[1] == 1) {
						$('.custmain_pn_change[data-id="'+value[0]+'"]').addClass('custmain_pn_warning');
						if(filestatus != 'failure') filestatus = 'warning';
					}
					if(value[1] == 2) {
						$('.custmain_pn_change[data-id="'+value[0]+'"]').addClass('custmain_pn_failure');
						filestatus = 'failure';
					}
					if(value[1] > 0) $('.custmain_pn_change[data-id="'+value[0]+'"]').after('<p>'+value[2]+'</p>');
					custmain_done++;
					custmain_refresh_progress(Math.round((custmain_done/custmain_total)*100));
				});
				
				file.removeClass('custmain_pn_process');
				file.addClass('custmain_pn_'+filestatus);
				
				if(filechanged){
					$('.custmain_file[data-id='+fileid+'] .custmain_control_revert').fadeIn(500);
				}
				
				setTimeout(function(){
					custmain_process_file(groupid);
				}, 500);
			});
		}
		
		function custmain_refresh_progress(value){
			$('#custmain_progressline').css('width',value+'%');
			$('#custmain_progressvalue').html(value+'%');
		}
		
		function custmain_new_change(elem){
			elem = $(elem);
			var file = elem.closest('.custmain_file');
			var file_id = file.attr('data-id');
			custmain_list_file(file, function(){
				var data = {
					file_id: file_id
				};
				custmain_ajax('new_change', data, function(response){
					response = Number(response);
					if(response > 0){
						var newchange = $('#custmain_change_template').clone();
						newchange.attr('data-id', response);
						newchange.removeAttr('id');
						newchange.css('display','none');
						elem.closest('.custmain_file').find('.custmain_container').first().append(newchange);
						newchange.slideDown(500);
					}
				});
			});
		}
		
		function custmain_new_file(elem){
			elem = $(elem);
			var group = elem.closest('.custmain_group');
			var group_id = group.attr('data-id');
			custmain_list_group(group, function(){
				var data = {
					group_id: group_id
				};
				custmain_ajax('new_file', data, function(response){
					response = Number(response);
					if(response > 0){
						var newfile = $('#custmain_file_template').clone();
						newfile.attr('data-id', response);
						newfile.removeAttr('id');
						newfile.css('display','none');
						elem.closest('.custmain_group').find('.custmain_container').first().append(newfile);
						newfile.slideDown(500);
					}
				});
			});
		}
		
		function custmain_new_group(elem){
			elem = $(elem);
			var data = {};
			custmain_ajax('new_group', data, function(response){
				response = Number(response);
				if(response > 0){
					var newgroup = $('#custmain_group_template').clone();
					newgroup.attr('data-id', response);
					newgroup.removeAttr('id');
					newgroup.css('display','none');
					$('.custmain_group').last().after(newgroup);
					newgroup.slideDown(500);
				}
			});
		}
		
		function custmain_download(filename, content) {
			var elem = document.createElement('a');
			elem.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
			elem.setAttribute('download', filename);
			elem.style.display = 'none';
			document.body.appendChild(elem);
			elem.click();
			document.body.removeChild(elem);
		}

		function custmain_export_selected(){
			var changeslist = '';
			$('.custmain_wrap .custmain_change[data-selected="1"]').each(function(){
				changeslist += $(this).attr('data-id') + ",";
			});
			if(changeslist == '') return custmain_alert(false, "<?php echo __('No changes selected for export', 'custmain'); ?>");
			var data = {
				change_ids: changeslist.substr(0, changeslist.length-1)
			};
			custmain_load($('.custmain_wrap .custmain_change[data-selected="1"] .custmain_change_title'), true);
			custmain_ajax('export', data, function(response){
				custmain_load($('.custmain_wrap .custmain_change[data-selected="1"] .custmain_change_title'), false);
				if(response != "false"){
					custmain_download("custmain_export.txt", response);
					custmain_alert(true, "<?php echo __('Export has been finished!', 'custmain'); ?>");
				} else {
					custmain_alert(false, "<?php echo __('Unable to export selected items', 'custmain'); ?>");
				}
			});
		}
		
		function custmain_export_change(elem){
			custmain_clear_selected();
			custmain_add_change(elem, custmain_export_selected);
		}
		
		function custmain_export_file(elem){
			custmain_clear_selected();
			custmain_add_file(elem, custmain_export_selected);
		}
		
		function custmain_export_group(elem){
			custmain_clear_selected();
			custmain_add_group(elem, custmain_export_selected);
		}
		
		function custmain_switch_tab(num, elem){
			if($(elem).hasClass('custmain_tab_active')) return;
			$('.custmain_tab_active').removeClass('custmain_tab_active');
			$('.custmain_wrap .custmain_group').fadeOut(200);
			$('#custmain_common_board').fadeOut(200);
			$('#custmain_logs').fadeOut(200);
			$('#custmain_import').fadeOut(200);
			setTimeout(function(){
				if(num == 1){
					$('.custmain_tab:eq(0)').addClass('custmain_tab_active');
					$('.custmain_wrap .custmain_group').fadeIn(200);
					$('#custmain_common_board').fadeIn(200);
				}
				
				if(num == 2){
					$('.custmain_tab:eq(1)').addClass('custmain_tab_active');
					custmain_get_logs();
					$('#custmain_logs').fadeIn(200);
				}
				
				if(num == 3){
					$('.custmain_tab:eq(2)').addClass('custmain_tab_active');
					$('#custmain_import').fadeIn(200);
				}
			}, 200);
		}
		
		function custmain_toggle_lr(elem){
			elem = $(elem).next();
			elem.fadeToggle(500);
		}
		
		function custmain_get_logs(){
			var data = {};
			custmain_load($('#custmain_logs_panel'), true);
			custmain_ajax('get_logs', data, function(response){
				custmain_load($('#custmain_logs_panel'), false);
				$('.custmain_log_record').remove();
				$('.custmain_log_record_details').remove();
				$('#custmain_logs_header').after(response);
			});
		}
		
		function custmain_get_customizations(){
			var data = {};
			custmain_load($('#custmain_common_board'), true);
			custmain_ajax('get_customizations', data, function(response){
				custmain_load($('#custmain_common_panel'), false);
				$('.custmain_group').remove();
				$('#custmain_common_board').after(response);
			});
		}
		
		function custmain_download_logs(){
			var start = $('#custmain_logs_start').val();
			var end = $('#custmain_logs_end').val();
			var data = {
				start: start,
				end: end
			};
			custmain_load($('#custmain_logs_panel'), true);
			custmain_ajax('download_logs', data, function(response){
				custmain_load($('#custmain_logs_panel'), false);
				if(response != "false"){
					if(start == '') start = "beginning";
					if(end == '') end = "end";
					custmain_download("custmain_log_"+start+"_"+end+".txt", response);
					custmain_alert(true, "<?php echo __('Logs have been prepared!', 'custmain'); ?>");
				} else {
					custmain_alert(false, "<?php echo __('No log records found for stated period', 'custmain'); ?>");
				}
			});
		}
		
		function custmain_import(){
			var importcode = $('#custmain_import_input').val();
			var data = {
				importcode: importcode
			};
			custmain_load($('#custmain_import_heading'), true);
			custmain_ajax('import', data, function(response){
				custmain_load($('#custmain_import_heading'), false);
				if(response == "true"){
					custmain_alert(true, "<?php echo __('Customizations imported successfully!', 'custmain'); ?>");
					$('#custmain_import_input').val('');
					custmain_get_customizations();
					custmain_switch_tab(1, $('#custmain_tabs .custmain_tab:nth-child(1)'))
				} else {
					custmain_alert(false, response);
				}
			});
		}
		
		function custmain_keypress_handle(e){

			e = e || window.event;

			if($(".custmain_file_tip").is(":visible")){
				var tip = $(".custmain_file_tip.custmain_active_tip");
				if(e.keyCode == '38'){
					e.preventDefault();
					if(!tip.find('.custmain_tip_selected').length){
						custmain_select_tip(tip.find('.custmain_tip_item').last()[0]);
					} else {
						custmain_select_tip($('.custmain_tip_selected').prev()[0]);
					}
				}
				else if(e.keyCode == '40'){
					e.preventDefault();
					if(!tip.find('.custmain_tip_selected').length){
						custmain_select_tip(tip.find('.custmain_tip_item').first()[0]);
					} else {
						custmain_select_tip($('.custmain_tip_selected').next()[0]);
					}
				}
				else if(e.keyCode == '13'){
					custmain_apply_tip($('.custmain_tip_selected')[0])
				}
			}

		}
		
		function custmain_select_tip(elem){
			var elem = $(elem);
			$('.custmain_tip_item').removeClass('custmain_tip_selected');
			elem.addClass('custmain_tip_selected');
		}
		
		function custmain_apply_tip(elem){
			var elem = $(elem);
			var title = elem.closest('.custmain_file_title').find('.custmain_title_text');
			if(elem.text().slice(-1) == "/") {
				title.val(elem.text());
				custmain_file_tip(title[0]);
				custmain_manage_input(title[0]);
				title.css('background','rgba(255,0,0,0.1)');
			} else {
				title.val(elem.text());
				custmain_manage_input(title[0]);
				custmain_update_file_path(title[0]);
				title.css('background','');
				custmain_close_tip(elem.closest('.custmain_file_title').find('.custmain_title_text')[0]);
			}
		}
		
		function custmain_autorun_change(elem){
			custmain_clear_selected();
			custmain_add_change(elem, custmain_autorun_selected);
		}
		
		function custmain_autorun_file(elem){
			custmain_clear_selected();
			custmain_add_file(elem, custmain_autorun_selected);
		}
		
		function custmain_autorun_group(elem){
			custmain_clear_selected();
			custmain_add_group(elem, custmain_autorun_selected);
		}
		
		function custmain_autorun_selected(confirm){
			if($('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').length == 0) return custmain_alert(false, "<?php echo __('No changes selected for autorun!', 'custmain'); ?>");
			$('#custmain_modal_bg').fadeIn(500);
			$('#custmain_autorun_descr').html('<?php echo __('You are going to set autorun for', 'custmain'); ?><br>'+ $('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').length + ' <?php echo __('changes', 'custmain'); ?>');
			$('#custmain_autorun_type').val('plugin');
			custmain_load_autorun_subjects();
			setTimeout(function(){
				$('#custmain_autorun').fadeIn(500);
			},500);
		}
		
		function custmain_load_autorun_subjects(){
			$('#custmain_autorun_subject option').remove();
			$('#custmain_autorun_subject').attr('disabled','disabled');
			var autorun_type = $('#custmain_autorun_type').val();
			var data = {
				autorun_type: autorun_type
			};
			custmain_ajax('get_autorun_subjects', data, function(response){
				var resp = $.parseJSON(response);
				$.each(resp, function(index, value) {
					$('#custmain_autorun_subject').append('<option value="'+index+'">'+value+'</option>');
				});
				$('#custmain_autorun_subject').removeAttr('disabled');
			});
		}
		
		function custmain_set_autorun(status){
			if(status == false) {
				$('#custmain_autorun').fadeOut(500);
				setTimeout(function(){
					$('#custmain_modal_bg').fadeOut(500);
				},500);
			}
			if(status == true){
				if($('.custmain_wrap .custmain_change[data-selected="1"]').not('.custmain_removed').length == 0) {
					$('#custmain_autorun').fadeOut(500);
					setTimeout(function(){
						$('#custmain_modal_bg').fadeOut(500);
					},500);
					return custmain_alert(false, "<?php echo __('No changes selected for autorun!', 'custmain'); ?>");
				}
				var autorun_type = $('#custmain_autorun_type').val();
				var autorun_subject = $('#custmain_autorun_subject').val();
				var autorun_title = $('#custmain_autorun_subject option[value="'+autorun_subject+'"]').text();
				var autorun_changeids = [];
				$('.custmain_change[data-selected="1"]').each(function(){
					var changeid = $(this).attr('data-id');
					autorun_changeids.push(changeid);
				});
				var data = {
					autorun_type: autorun_type,
					autorun_subject: autorun_subject,
					autorun_title: autorun_title,
					changeids: autorun_changeids
				};
				custmain_ajax('set_autorun', data, function(response){
					if(response != "false"){
						$('#custmain_autorun').fadeOut(500);
						custmain_alert(true, "<?php echo __('Autorun settings applied successfully', 'custmain'); ?>");
						setTimeout(function(){
							$('#custmain_modal_bg').fadeOut(500);
						},500);
					} else {
						custmain_alert(false, "<?php echo __('An error occured while saving autorun settings', 'custmain'); ?>");
					}
				});
			}
		}
		
		</script>
		<?php
	}
}

$_custmain_scripts = new CustMainScripts();