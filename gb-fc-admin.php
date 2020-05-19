<?php
class GbFcAdmin {

	public static function menus(){
		$page = add_options_page('GB FullCalendar', 'GB FullCalendar', 'manage_options', 'gb-fullcalendar', array('GbFcAdmin','admin_options'));
		wp_enqueue_style('gb-fullcalendar', plugins_url('includes/css/admin.css',__FILE__));
	}


	public static function admin_options(){
		if( !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'gbfc_options_save')){
			foreach($_REQUEST as $option_name => $option_value){
				if(substr($option_name, 0, 5) == 'gbfc_'){
				    if( $option_name == 'gbfc_scripts_limit' ){ $option_value = str_replace(' ', '', $option_value); } //clean up comma seperated emails, no spaces needed
					update_option($option_name, $option_value);
				}
			}
			if( empty($_REQUEST['gbfc_post_taxonomies']) ){ update_option('gbfc_post_taxonomies', ''); }
			echo '<div class="updated notice"><p>'.__('Settings saved.').'</p></div>';
		}
		?>
		<div class="wrap">
			<h2>GB FullCalendar</h2>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle" style="color:green;">** Support this plugin! **</h3>
						<div class="inside">
							<p>This plugin was developed by <a href="http://msyk.es/">Marcus Sykes</a> and is now provided free of charge thanks to proceeds from the <a href="http://wp-events-plugin.com/">Events Manager</a> Pro plugin.</p>
							<p>We're not asking for donations, but we'd appreciate a 5* rating and/or a link to our plugin page!</p>
							<ul>
								<li><a href="http://wordpress.org/extend/plugins/gb-fullcalendar/" >Give us 5 Stars on WordPress.org</a></li>
								<li><a href="http://wordpress.org/extend/plugins/gb-fullcalendar/" >Link to our plugin page.</a></li>
							</ul>
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">About FullCalendar</h3>
						<div class="inside">
							<p><a href="http://arshaw.com/fullcalendar/">FullCalendar</a> is a jQuery plugin developed by Adam Shaw, which adds a beautiful AJAX-enabled calendar which can communicate with your blog.</p>
							<p>If you find this calendar particularly useful and can spare a few bucks, please <a href="http://arshaw.com/fullcalendar/">donate something to his project</a>, most of the hard work here was done by him and he gives this out freely for everyone to use!</p>
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Getting Help</h3>
						<div class="inside">
							<p>Before asking for help, check the readme files or the plugin pages for answers to common issues.</p>
							<p>If you're still stuck, try the <a href="http://wordpress.org/support/plugin/gb-fullcalendar/">community forums</a>.</p>
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Translating</h3>
						<div class="inside">
							<p>If you'd like to translate this plugin, the language files are in the langs folder.</p>
							<p>Please email any translations to wp.plugins@netweblogic.com and we'll incorporate it into the plugin.</p>
						</div>
					</div>
				</div>
				<div id="post-body">
					<div id="post-body-content">
						<p>
							<?php echo sprintf(__('To use this plugin, simply use the %s shortcode in one of your posts or pages.','gb-fullcalendar'),'<code>[fullcalendar]</code>'); ?>
							<?php echo sprintf(__('You can also do this with PHP and this snippet : %s.','gb-fullcalendar'),'<code>echo WP_FullCalendar::calendar($args);</code>'); ?>
						</p>
						<form action="" class="wpfc-options" method="post">
							<?php do_action('gbfc_admin_before_options'); ?>
							<h2 style="margin-top:0px;"><?php _e('Post Types','gb-fullcalendar'); ?></h2>
							<p><?php echo sprintf(__('By default, your calendar will show the types of posts based on settings below.','gb-fullcalendar'),''); ?></p>
							<p>
								<?php echo sprintf(__('You can override these settings by choosing your post type in your shortode like this %s.','gb-fullcalendar'),'<code>[fullcalendar type="post"]</code>'); ?>
								<?php echo sprintf(__('You can override taxonomy search settings as well like this %s.','gb-fullcalendar'),'<code>[fullcalendar type="post_tag,category"]</code>'); ?>
								<?php _e('In both cases, the values you should use are in (parenteses) below.','gb-fullcalendar');?>
							</p>
							<p>
								<ul class="wpfc-post-types">
									<?php
									$selected_taxonomies = get_option('gbfc_post_taxonomies');
									foreach( get_post_types( apply_filters('gbfc_get_post_types_args', array('public'=>true )), 'names') as $post_type ){
		 								$checked = get_option('gbfc_default_type') == $post_type ? 'checked':'';
		 								$post_data = get_post_type_object($post_type);
										echo "<li><label><input type='radio' class='wpfc-post-type' name='gbfc_default_type' value='$post_type' $checked />&nbsp;&nbsp;{$post_data->labels->name} (<em>$post_type</em>)</label>";
										do_action('gbfc_admin_options_post_type_'.$post_type);
										$post_type_taxonomies = get_object_taxonomies($post_type);
										if( count($post_type_taxonomies) > 0 ){
											$display = empty($checked) ? 'style="display:none;"':'';
											echo "<div $display>";
											echo "<p>".__('Choose which taxonomies you want to see listed as search options on the calendar.','gb-fullcalendar')."</p>";
											echo "<ul>";
											foreach( $post_type_taxonomies as $taxonomy_name ){
												$taxonomy = get_taxonomy($taxonomy_name);
												$tax_checked = !empty($selected_taxonomies[$post_type][$taxonomy_name]) ? 'checked':'';
												echo "<li><label><input type='checkbox' name='gbfc_post_taxonomies[$post_type][$taxonomy_name]' value='1' $tax_checked />&nbsp;&nbsp;{$taxonomy->labels->name} (<em>$taxonomy_name</em>)</label></li>";
											}
											echo "</ul>";
											echo "</div>";
										}
										echo "</li>";
									}
									?>
								</ul>
							</p>
				            <script type="text/javascript">
				            	jQuery(document).ready(function($){
					            	$('input.wpfc-post-type').change(function(){
						            	$('ul.wpfc-post-types div').hide();
					            		$('input[name=gbfc_default_type]:checked').parent().parent().find('div').show();
					            	});
					            });
				            </script>
							<?php do_action('gbfc_admin_after_cpt_options'); ?>
						    <h2><?php _e('Calendar Options','gb-fullcalendar'); ?></h2>
							<table class='form-table'>
                                <?php
                                //                                dayGridMonth', 'timeGridWeek', 'timeGridDay', 'list'
                                $available_views = apply_filters('gbfc_available_views', array(
                                    'dayGridMonth' => 'Month (DayGrid)',
                                    'dayGridWeek' => 'Week (DayGrid)',
                                    'timeGridWeek' => 'Week (TimeGrid)',
                                    'timeGridDay' => 'Day (TimeGrid)',
                                    'listMonth' => 'Month (List)',
                                    'listWeek' => 'Week (List)',
                                    'listDay' => 'Day (List)',
                                ));
                                ?>
								<tr>
									<th scope="row"><?php _e('Available Views','gb-fullcalendar'); ?></th>
									<td>
										<?php $gbfc_available_views = get_option('gbfc_available_views', array('month','basicWeek','basicDay')); ?>
										<?php foreach( $available_views as $view_key => $view_value ): ?>
										<input type="checkbox" name="gbfc_available_views[]" value="<?php echo $view_key ?>" <?php if( in_array($view_key, $gbfc_available_views) ){ echo 'checked="checked"'; } ?>/> <?php echo $view_value; ?><br />
										<?php endforeach; ?>
										<em><?php _e('Users will be able to select from these views when viewing the calendar.'); ?></em>
									</td>
								</tr>
								<?php
								gbfc_options_select( __('Default View','gb-fullcalendar'), 'gbfc_defaultView', $available_views, __('Choose the default view to be displayed when the calendar is first shown.','gb-fullcalendar') );
								gbfc_options_input_text ( __( 'Time Format', 'gb-fullcalendar'), 'gbfc_timeFormat', sprintf(__('Set the format used for showing the times on the calendar, <a href="%s">see possible combinations</a>. Leave blank for no time display.','gb-fullcalendar'),'http://momentjs.com/docs/#/displaying/format/'), 'h(:mm)a' );
								gbfc_options_input_text ( __( 'Events limit', 'gb-fullcalendar'), 'gbfc_limit', __('Enter the maximum number of events to show per day, which will then be preceded by a link to the calendar day page.','gb-fullcalendar') );
								gbfc_options_input_text ( __( 'View events link', 'gb-fullcalendar'), 'gbfc_limit_txt', __('When the limit of events is shown for one day, this text will be used for the link to the calendar day page.','gb-fullcalendar') );
								?>
							</table>
							<?php do_action('gbfc_admin_after_calendar_options'); ?>
						    <h2><?php _e('jQuery UI Themeroller','gb-fullcalendar'); ?></h2>
						    <p><?php echo sprintf(__( 'You can select from a set of pre-made CSS themes, which are taken from the <a href="%s">jQuery Theme Roller</a> gallery. If you roll your own theme, upload the CSS file and images folder to <code>wp-content/yourtheme/plugins/gb-fullcalendar/</code> and refresh this page, it should appear an option in the pull down menu below.','gb-fullcalendar'),'http://jqueryui.com/themeroller/'); ?></p>
							<table class='form-table'>
								<?php
								//jQuery UI ships with pre-made themes, so here they are. This was coded for packaged CSS Themes 1.10.4 and 1.11.4
								$jquery_themes = array('black-tie','blitzer','cupertino','dark-hive','dot-luv','eggplant','excite-bike','flick','hot-sneaks','humanity','le-frog','mint-choc','overcast','pepper-grinder','redmond','smoothness','south-street','start','sunny','swanky-purse','trontastic','ui-darkness','ui-lightness','vader');
								$jquery_themes = apply_filters('gbfc_jquery_themes', $jquery_themes);
								//get custom theme CSS files
								$plugin_path = get_stylesheet_directory()."/plugins/gb-fullcalendar/";
								foreach( glob( $plugin_path.'*.css') as $css_file ){
									$css_file = str_replace($plugin_path,'',$css_file);
									$css_custom_files[] = $css_file;
								}
								?>
							    <tr class="form-field">
							        <th scope="row" valign="top"><label for="product_package_unit_price"><?php _e( 'jQuery CSS Theme?', 'gb-fullcalendar'); ?></label></th>
							        <td>
							            <select name="gbfc_theme_css">
							            	<option value="0"><?php _e( 'No Theme','gb-fullcalendar'); ?></option>
							            	<optgroup label="<?php _e('Built-In','gb-fullcalendar'); ?>">
								            	<?php foreach( $jquery_themes as $jquery_theme ): ?>
								            	<option <?php if(get_option('gbfc_theme_css') == $jquery_theme) echo 'selected="selected"'; ?>><?php echo esc_html($jquery_theme); ?></option>
								            	<?php endforeach; ?>
							            	</optgroup>
							            	<?php if( !empty($css_custom_files) ): ?>
							            	<optgroup label="<?php _e('Custom','gb-fullcalendar'); ?>">
							            		<?php foreach( $css_custom_files as $css_custom_file ): ?>
							            			<option <?php if(get_option('gbfc_theme_css') == $css_custom_file) echo 'selected="selected"'; ?>><?php echo esc_html($css_custom_file); ?></option>
							            		<?php endforeach; ?>
							            	</optgroup>
							            	<?php endif; ?>
							            </select>
							            <i><?php _e( 'You can use the jQuery UI CSS framework to style the calendar, and choose from a set of themes below.','gb-fullcalendar'); ?></i>
							        </td>
							    </tr>
							</table>
							<?php do_action('gbfc_admin_after_themeroller_options'); ?>
						    <h2><?php _e('Tooltips','gb-fullcalendar'); ?></h2>
						    <p><?php _e( 'You can use <a href="http://craigsworks.com/projects/qtip2/">jQuery qTips</a> to show excerpts of your events within a tooltip when hovering over a specific event on the calendar. You can control the content shown, positioning and style of the tool tips below.','gb-fullcalendar'); ?></p>

							<h2><?php _e ( 'JS and CSS Files (Optimization)', 'gb-fullcalendar'); ?></h2>
				            <table class="form-table">
								<?php
								gbfc_options_input_text( __( 'Load JS and CSS files on', 'dbem' ), 'gbfc_scripts_limit', __('Write the page IDs where you will display the FullCalendar on so CSS and JS files are only included on these pages. For multiple pages, use comma-seperated values e.g. 1,2,3. Leaving this blank will load our CSS and JS files on EVERY page, enter -1 for the home page.','gb-fullcalendar') );
								?>
							</table>
							<?php do_action('gbfc_admin_after_optimizations'); ?>

							<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('gbfc_options_save'); ?>" />
							<p class="submit"><input type="submit" value="<?php _e('Submit Changes','gb-fullcalendar'); ?>" class="button-primary"></p>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

$str = file_get_contents(__DIR__ . '/package.json');
$package = json_decode($str, true);

//check for updates
if( version_compare($package['version'], get_option('gbfc_version',0)) > 0 && current_user_can('activate_plugins') ){
	//include('wpfc-install.php');
}
//add admin action hook
add_action ( 'admin_menu', array('GbFcAdmin', 'menus') );


/*
 * Admin UI Helpers
*/
function gbfc_options_input_text($title, $name, $description, $default='') {
	?>
	<tr valign="top" id='<?php echo esc_attr($name);?>_row'>
		<th scope="row"><?php echo esc_html($title); ?></th>
	    <td>
			<input name="<?php echo esc_attr($name) ?>" type="text" id="<?php echo esc_attr($title) ?>" style="width: 95%" value="<?php echo esc_attr(get_option($name, $default), ENT_QUOTES); ?>" size="45" /><br />
			<em><?php echo $description; ?></em>
		</td>
	</tr>
	<?php
}
function gbfc_options_input_password($title, $name, $description) {
	?>
	<tr valign="top" id='<?php echo esc_attr($name);?>_row'>
		<th scope="row"><?php echo esc_html($title); ?></th>
	    <td>
			<input name="<?php echo esc_attr($name) ?>" type="password" id="<?php echo esc_attr($title) ?>" style="width: 95%" value="<?php echo esc_attr(get_option($name)); ?>" size="45" /><br />
			<em><?php echo $description; ?></em>
		</td>
	</tr>
	<?php
}

function gbfc_options_textarea($title, $name, $description) {
	?>
	<tr valign="top" id='<?php echo esc_attr($name);?>_row'>
		<th scope="row"><?php echo esc_html($title); ?></th>
			<td>
				<textarea name="<?php echo esc_attr($name) ?>" id="<?php echo esc_attr($name) ?>" rows="6" cols="60"><?php echo esc_attr(get_option($name), ENT_QUOTES);?></textarea><br/>
				<em><?php echo $description; ?></em>
			</td>
		</tr>
	<?php
}

function gbfc_options_radio($name, $options, $title='') {
		$option = get_option($name);
		?>
	   	<tr valign="top" id='<?php echo esc_attr($name);?>_row'>
	   		<?php if( !empty($title) ): ?>
	   		<th scope="row"><?php  echo esc_html($title); ?></th>
	   		<td>
	   		<?php else: ?>
	   		<td colspan="2">
	   		<?php endif; ?>
	   			<table>
	   			<?php foreach($options as $value => $text): ?>
	   				<tr>
	   					<td><input id="<?php echo esc_attr($name) ?>_<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($name) ?>" type="radio" value="<?php echo esc_attr($value); ?>" <?php if($option == $value) echo "checked='checked'"; ?> /></td>
	   					<td><?php echo $text ?></td>
	   				</tr>
				<?php endforeach; ?>
				</table>
			</td>
	   	</tr>
<?php
}

function gbfc_options_radio_binary($title, $name, $description, $option_names = '') {
	if( empty($option_names) ) $option_names = array(0 => __('No','dbem'), 1 => __('Yes','dbem'));
	if( substr($name, 0, 7) == 'dbem_ms' ){
		$list_events_page = get_site_option($name);
	}else{
		$list_events_page = get_option($name);
	}
	?>
   	<tr valign="top" id='<?php echo $name;?>_row'>
   		<th scope="row"><?php echo esc_html($title); ?></th>
   		<td>
   			<?php echo $option_names[1]; ?> <input id="<?php echo esc_attr($name) ?>_yes" name="<?php echo esc_attr($name) ?>" type="radio" value="1" <?php if($list_events_page) echo "checked='checked'"; ?> />&nbsp;&nbsp;&nbsp;
			<?php echo $option_names[0]; ?> <input  id="<?php echo esc_attr($name) ?>_no" name="<?php echo esc_attr($name) ?>" type="radio" value="0" <?php if(!$list_events_page) echo "checked='checked'"; ?> />
			<br/><em><?php echo $description; ?></em>
		</td>
   	</tr>
	<?php
}

function gbfc_options_select($title, $name, $list, $description, $default='') {
	$option_value = get_option($name, $default);
	if( $name == 'dbem_events_page' && !is_object(get_page($option_value)) ){
		$option_value = 0; //Special value
	}
	?>
   	<tr valign="top" id='<?php echo esc_attr($name);?>_row'>
   		<th scope="row"><?php echo esc_html($title); ?></th>
   		<td>
			<select name="<?php echo esc_attr($name); ?>" >
				<?php foreach($list as $key => $value) : ?>
 				<option value='<?php echo esc_attr($key) ?>' <?php echo ("$key" == $option_value) ? "selected='selected' " : ''; ?>>
 					<?php echo esc_html($value); ?>
 				</option>
				<?php endforeach; ?>
			</select> <br/>
			<em><?php echo $description; ?></em>
		</td>
   	</tr>
	<?php
}
