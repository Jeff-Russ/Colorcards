<?php
namespace Jr;
/**
 * WpSettingsHelper helps maintain a plugin options page as well as access options 
 * values in your WordPress Plugin. It makes heavy use of the PersistArgs class 
 * which it extends. 
 * 
 * @package     JeffPack
 * @subpackage  WordPress Libraries
 * @access      public
 * @author      Jeff Russ
 * @copyright   2016 Jeff Russ
 * @license     GPL-2.0
 */
// TODO use add_submenu_page with parent slug = null

if ( ! class_exists('WpSettingsHelper')) {
	/**
	 * WpSettingsHelper class is a collection of methods to help maintain a plugin 
	 * options page as well as access options values in your WordPress Plugin. 
	 * It makes heavy use of the PersistArgs class which it extends. 
	 * 
	 * Most options are chainable, using  the variable data from the previous call 
	 * in the next. For example, you call
	 * 
	 * $plug->addSettingsPage()->addSettingsSection()->addSetting("Your Name");
	 * 
	 * The section is added to the page you just made, not some previously made one, 
	 * and the setting is added to the section you just made. Even if you don't chain
	 * the above would work since it's in logical order but if it's not the last 
	 * argument is passed by reference and written to for future use in what would 
	 * be the next logical method call. For example: 
	 * 
	 * $plug->addSettingsPage('menu' "on sidebar", null, $menu_def_section);
	 * $plug->addSettingsPage('options' "in tools", null, $tools_def_section);
	 * 
	 * $menu_my_section = $menu_def_section;
	 * 
	 * $plug->addSettingsSection('my section', $menu_my_section);
	 * 
	 * $plug->addSetting('setting1', '', '', $menu_def_section );
	 * $plug->addSetting('setting2', '', '', $menu_my_section );
	 * $plug->addSetting('setting3', '', '', $tools_def_section);
	 * 
	 */
	class WpSettingsHelper extends WpInfoHash { # extends PersistArgs extends HelperModule

		/**
		* Adds a page-less menu entry directly on the admin sidebar. This method
		* should only be called once. The first call to addAdminPage() (which must be 
		* any time after calling addAdminMenu) will defines what the first submenu will be
		* and where clicking the root menu item takes you.  
		* 
		* There is no need to capture the results of the call with an $args array 
		* (the args array is not passed by reference). 
		* 
		*
		* @param  string $menu_title optional, can be in second arg instead or not at all
		* @param  array $args  also optional, containing more properties you can set for the 
		* menu entry. If not found anywhere, 'menu_title'/$menu_title will be the plugin_slug
		* beside 'menu_title', other keys are: 'capability', 'menu_slug', 'css_classes', 
		* 'css_id', and 'dashicon'
		* 
		* @return $this  returns reference to object to enable method call chaining
		* @access public
		*/
		public function addAdminMenu ($menu_title=null, $args=[]) {
			if (! is_null($menu_title)) $args['menu_title'] = $menu_title;
			$args = array_merge(
				[ 'menu_title'  => $this->info['plugin']['name'],
				  'capability'  => 'manage_options',
				  'menu_slug'   => $this->info['plugin']['prefix'].'_menu',
				  'css_classes' => 'menu-top menu-icon-generic',
				  'css_id'      => $this->info['plugin']['prefix'].'_menu',
				  'dashicon'    => 'dashicons-menu',
				  'submenus'    => array(),
				], $args
			);
			$this->info['admin_menu'] = $args;

			add_action('admin_menu', function() use ($args) {
				extract($args);
				global $menu;
				$menu[] = [ $menu_title, $capability, $menu_slug, '', $css_classes, $css_id, $dashicon ];
			});
			return $this;
		}

		/**
		* Adds a page to the page-less menu entry added directly to the admin sidebar
		* by addAdminMenu. The first call to addAdminPage() (which must be any time after 
		* calling addAdminMenu) will defines what the first submenu will be and where 
		* clicking the root menu item takes you.  
		* 
		* After calling this method you call addSettingsSection followed by repeated calls to
		* addSetting or go straight to calling addSetting (which would place them in the 
		* default section)
		*
		* @param  string $page_title optional, if not provided will be plugin slug
		* @param  string $menu_title  also optional, if not provided will match $page_title
		* 
		* @return $this  returns reference to object to enable method call chaining
		* @access public
		*/
		public function addAdminPage ($page_title=null, $menu_title=null) {
			$menu_slug = $this->info['admin_menu']['menu_slug'];
			if ( empty($this->info['admin_menu']['submenus']) ):
				$this->info['admin_menu']['submenus'][] = $menu_slug;
				$this->addSettingsPage($menu_slug, $page_title, $menu_title, $args, $menu_slug);
			else:
				$this->addSettingsPage($menu_slug, $page_title, $menu_title, $args);
				$this->info['admin_menu']['submenus'][] = $args['page_slug'];
			endif;
			return $this;
			// return $menu_slug;
		}
		/**
		* Calls add_menu_page(), add_options_page(), or add_submenu_page() with a built in
		* Closure that calls settings_fields(), do_settings_sections() submit_button()
		*
		* @param  string $menu_location "menu" calls add_menu_page, 
		* "options" calls add_options_page anything else calls add_submenu_page 
		* and is passed as first arg
		* @param  string $page_title first arg sent to 
		* add_options_page/add_submenu_page or second arg send to add_submenu_page
		* @param  string $menu_title second arg sent to 
		* add_options_page/add_submenu_page or third arg send to add_submenu_page
		* @param  array  $args can have 'icon_url' or 'position' elements added 
		* and after call will have page and default section info added. 
		* @return $this  returns reference to object to enable method call chaining
		* @access public
		*/
		public function addSettingsPage (
			$menu_location="menu", $page_title=null, $menu_title=null, &$args=null, $page_slug=null
		) {
			if ($page_title === null) $page_title = $this->info['plugin']['name'];
			if ($menu_title === null) $menu_title = $page_title;
			if ($args === null) $args = array();
			$icon_url = $this->getPassedArg($args, 'icon_url');
			$position = $this->getPassedArg($args, 'position');

			switch ($menu_location) {
				case "menu":
					$page_slug = $this->info['plugin']['prefix'].'_menu'; break;
				case "options":
					$page_slug = $this->info['plugin']['prefix'].'_settings'; break;
				default:
					if (is_null($page_slug)) {
						$snake_loc = to_snake($menu_location);
						$snake_title = to_snake($menu_title);
						$page_slug = $this->info['plugin']['prefix']
							. "_${snake_loc}_${snake_title}";
					}
			}
			$this->info['settings_pages'][$page_slug] = array(
				'menu_location' => $menu_location,
				'page_title' => $page_title,
				'menu_title' => $menu_title,
				'page_slug' => $page_slug,
				'icon_url' => $icon_url,
				'position' => $position,
				'option_group' => $page_slug.'_option_group',
				'option_name' => $page_slug.'_option_name',
				'sections' => array( $page_slug.'_default_section' )
			);
			$this->info['settings_sections'][$page_slug.'_default_section'] = array(
				'page_slug' => $page_slug,
				'section_id' => $page_slug.'_default_section',
				'section_title' => '', # no title!
				'option_group' => $page_slug.'_option_group',
				'option_name' => $page_slug.'_option_name',
				'indented' => false,
				'settings' => array(),
			);
			$args = $this->mergeArgs(
				$this->info['settings_pages'][$page_slug],
				$this->info['settings_sections'][$page_slug.'_default_section']
			);

			add_action("admin_menu", function() use ($args) { extract($args);
				
				if ($menu_location === 'menu'):
					add_menu_page( # add_menu_page adds page directly on the sidebar:
					$page_title, $menu_title, "manage_options", $page_slug, function() use ($args) {
						extract($args);
						?><div class="wrap">
							<h1><?php echo $page_title; ?></h1>
							<hr color='#333' size='4'>
							<form action="options.php" method="post"> <?php
								settings_fields( $option_group );
								do_settings_sections( $page_slug );
								submit_button();
							?><form>
						</div><?php
					}, $icon_url, $position);

				elseif ($menu_location === 'options'):
					add_options_page( # add menu under "Settings"
					$page_title, $menu_title, "manage_options", $page_slug, function() use ($args) {
						extract($args);
						?><div class="wrap">
							<h1><?php echo $page_title; ?></h1>
							<hr color='#333' size='4'>
							<form action="options.php" method="post"> <?php
								settings_fields( $option_group );
								do_settings_sections( $page_slug );
								submit_button();
							?><form>
						</div><?php
					}, $icon_url, $position);

				else:
					add_submenu_page( $menu_location, # add submenu under $menu_location
					$page_title, $menu_title, "manage_options", $page_slug, function() use ($args) {
						extract($args);
						?><div class="wrap">
							<h1><?php echo $page_title; ?></h1>
							<hr color='#333' size='4'>
							<form action="options.php" method="post"> <?php
								settings_fields( $option_group );
								do_settings_sections( $page_slug );
								submit_button();
							?><form>
						</div><?php
					}, $icon_url, $position);
				endif;
			});

			add_action( "admin_init", function() use ($args){
				extract($args);
				register_setting( $option_group, $option_name ); 
				add_settings_section( $section_id, "", function(){}, $page_slug );
			});

			return $this;
		}

		/**
		* Calls add_action("admin_init",function(){add_settings_section(...function(){...});
		*
		* @param  string $section_title (optional, defaulting to '') 
		* @param  array  $args if provided should have 'page_slug'. after call it will have 
		* section info added. 
		* @return $this  returns reference to object to enable method call chaining
		* @access public
		*/
		public function addSettingsSection($section_title='', &$args=null)
		{
			if ($args === null) $args = array();
			$page_slug = $this->getArg($args, 'page_slug');
			$section_id = $page_slug . '_' . to_snake($section_title);

			array_push( $this->info['settings_pages'][$page_slug]['sections'], $section_id );

			$this->info['settings_sections'][$section_id] = array (
				'section_title' => $section_title,
				'page_slug' => $page_slug,
				'section_id' => $section_id,
				'option_group' => $this->getArg($args, 'option_group'),
				'option_name' => $this->getArg($args, 'option_name'),
				'indented' => $this->getPassedArg($args, 'indented', true),
				'settings'=> array(),
			);

			$args = $this->mergeArgs($this->info['settings_sections'][$section_id] );

			if ($use['indented']):
				add_action( "admin_init", function() use ($args) { extract($args);
					add_settings_section( $section_id, $section_title,
					function(){echo'<div style="margin-left:8%">';}, $page_slug );

					add_settings_section('/'.$section_id,'', # dummy section to close div
					function(){echo"</div>\r\n";}, $page_slug ); 
				});
			else:
				add_action( "admin_init", function() use ($args) { extract($args);
					add_settings_section( $section_id, $section_title, function(){
					/* $section_title WILL be displayed! */}, $page_slug );
				});
			endif;

			$this->args = array_merge($this->args, $args);
			return $this;
		}

		/**
		* Calls add_action("admin_init",function(){add_settings_section(...function(){...});
		*
		* @param  string $setting_label
		* @param  mixed $default is the default value the setting should have
		* @param  mixed $source if provided should be a string of html or a callback.
		* If not provided, a text field will be created. 
		* If you provide a string use single quotes and embed $name and $value.
		* If you use a callback, do function($args) { extract($args); ...}
		* @param  array  $args if provided should have 'page_slug' and section info. 
		* after call it will have setting info added. 
		* @return $this  returns reference to object to enable method call chaining
		* @access public
		*/
		public function addSetting($setting_label, $default='', $source='', &$args=null)
		{
			if ($args === null) $args = array();
			$section_id = $this->getArg($args, 'section_id');
			$setting_id = "${section_id}_" . to_snake($setting_label);

			$section_info = $this->info['settings_sections'][$section_id];
			array_push( $this->info['settings_sections'][$section_id]['settings'], $setting_id );

			if ( empty($source) ) $source = '<input type="text" name="$name" value="$value">';

			$this->info['settings'][$setting_id] = array (
				'setting_id' => $setting_id,
				'setting_label' => $setting_label,
				'name' => $section_info['option_name']."[${setting_id}]",
				'source' => $source,
				'default' => $default,
				'page_slug' => $section_info['page_slug'],
				'section_id' => $section_info['section_id'],
				'section_title' => $section_info['section_title'],
				'option_group' => $section_info['option_group'],
				'option_name' => $section_info['option_name'],
				'indented' => $section_info['indented'],
			);
			# getSetting below cant be called until the array above is set.
			$this->info['settings'][$setting_id]['value'] = $this->getSetting($setting_id);

			$args = $this->mergeArgs($args, $this->info['settings'][$setting_id]);

			if ( is_callable($source) ):
				add_action( "admin_init", function() use($args) { extract($args);
					add_settings_field(
						$setting_id, $setting_label, $source, $page_slug, $section_id, $args);
				});
			else:
				add_action( "admin_init", function() use($args) { extract($args);
					add_settings_field( $setting_id, $setting_label, function() use($args) {
						extract($args);
						$source = str_replace('$name', $name, $source);
						$source = str_replace('$value', $value, $source);
						echo $source;
					}, $page_slug, $section_id );
				});
			endif;
			return $this;
		}
		/**
		* public function getSetting($setting_id)
		* {
		* 	extract( $this->info['settings'][$setting_id] );
		* 	$options = wp_parse_args(get_option($option_name), [$setting_id => $default] );
		* 	return $options[$setting_id];
		* }
		*
		* @param  string $setting_id 
		* @return mixed  returns value of setting
		* @access public
		*/
		public function getSetting($setting_id)
		{
			extract( $this->info['settings'][$setting_id] );
			$options = wp_parse_args(get_option($option_name), [$setting_id => $default] );
			return $options[$setting_id];
		}

		#==========================================================================

		// private function getSettingsInfo($arg, $key='page_slug', $this_info='')
		// {
		// 	if ( empty($this_info) ) $this_info = $this->info['settings_pages'];

		// 	if (is_string($arg)):
		// 		return $arg;
		// 	elseif ( is_array($arg) ):
		// 		return $arg[$key];
		// 	elseif (empty($arg) ):
		// 		# if they don't specify we'll assume last created
		// 		return end( $this_info )[$key];
		// 	endif;
		// }
	}
}
