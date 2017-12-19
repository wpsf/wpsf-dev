<?php
if (! defined ( 'ABSPATH' )) {
	die ();
} // Cannot access pages directly.
/**
 *
 * Framework Class
 *
 * @since 1.0.0
 * @version 1.0.0
 * @todo add admin_init hook from framework.class.php
 *      
 */
class WPSFramework_Settings extends WPSFramework_Abstract {
	
	/**
	 *
	 * option database/data name
	 *
	 * @access public
	 * @var string
	 *
	 */
    public $unique = WPSF_OPTION;
	
	/**
	 *
	 * settings
	 *
	 * @access public
	 * @var array
	 *
	 */
	public $settings = array ();
	
	/**
	 *
	 * options tab
	 *
	 * @access public
	 * @var array
	 *
	 */
	public $options = array ();
	
	/**
	 *
	 * options section
	 *
	 * @access public
	 * @var array
	 *
	 */
	public $sections = array ();
	
	/**
	 *
	 * options store
	 *
	 * @access public
	 * @var array
	 *
	 */
	public $get_option = array ();
	
	/*
	 * page slug store
	 * @access public
	 * @var array
	 */
	public $settings_page = null;
	
	/**
	 *
	 * instance
	 *
	 * @access private
	 * @var class
	 *
	 */
	private static $instance = null;
	
    private function _set_settings_options($settings = array(), $options = array()) {
		if (! empty ( $settings )) {
			$this->settings = apply_filters ( 'wpsf_settings', $settings );
			if (isset ( $this->settings ['options_name'] )) {
				$this->unique = $this->settings ['options_name'];
			}
		}
		
		if (! empty ( $options )) {
			$this->options = apply_filters ( 'wpsf_options', $options );
		}
	}
	
    public function __construct($settings = array(), $options = array()) {
		$this->init ( $settings, $options );
	}
	
    public function init($settings = array(), $options = array()) {
		$this->_set_settings_options ( $settings, $options );
		$this->total_fields = 0;
		if (! empty ( $this->options )) {
			$this->sections = $this->get_sections ();
            $this->get_option = get_option ( $this->unique );
			$this->addAction ( 'admin_menu', 'admin_menu' );
            $this->addAction('admin_init','register_settings');
            $this->addAction('load-options.php','register_settings_fields');
		}
	}
	
    public function admin_menu() {
		$defaults = array (
				'menu_type' => '',
				'menu_parent' => '',
				'menu_title' => '',
				'menu_slug' => '',
				'menu_capability' => 'manage_options',
				'menu_icon' => null,
				'menu_position' => null 
		);
		
		$args = wp_parse_args ( $this->settings, $defaults );
		
		extract ( $args );
		
		if ($menu_type === 'submenu') {
			$ps = add_submenu_page ( $menu_parent, $menu_title, $menu_title, $menu_capability, $menu_slug, array (
					&$this,
					'admin_page' 
			) );
		} else if ($menu_type === 'management') {
			$ps = add_management_page ( $menu_title, $menu_title, $menu_capability, $menu_slug, array (
					&$this,
					'admin_page' 
			), $menu_icon, $menu_position );
		} else if ($menu_type === 'dashboard') {
			$ps = add_dashboard_page ( $menu_title, $menu_title, $menu_capability, $menu_slug, array (
					&$this,
					'admin_page' 
			), $menu_icon, $menu_position );
		} else if ($menu_type === 'options') {
			$ps = add_options_page ( $menu_title, $menu_title, $menu_capability, $menu_slug, array (
					&$this,
					'admin_page' 
			), $menu_icon, $menu_position );
		} else if ($menu_type === 'plugins') {
			$ps = add_plugins_page ( $menu_title, $menu_title, $menu_capability, $menu_slug, array (
					&$this,
					'admin_page' 
			), $menu_icon, $menu_position );
		} else if ($menu_type === 'theme') {
			$ps = add_theme_page ( $menu_title, $menu_title, $menu_capability, $menu_slug, array (
					&$this,
					'admin_page' 
			), $menu_icon, $menu_position );
		} else {
			$ps = add_menu_page ( $menu_title, $menu_title, $menu_capability, $menu_slug, array (
					&$this,
					'admin_page' 
			), $menu_icon, $menu_position );
		}
		
		$this->settings_page = $ps;
	}
	
    public function get_sections() {
		$sections = array ();
		foreach ( $this->options as $key => $value ) {
			if (isset ( $value ['sections'] )) {
				foreach ( $value ['sections'] as $section ) {
					if (isset ( $section ['fields'] )) {
						$sections [] = $section;
                        $this->total_fields += count($section['fields']);
					}
				}
			} else {
				if (isset ( $value ['fields'] )) {
					$sections [] = $value;
                    $this->total_fields += count($value['fields']);
				}
			}
		}
        
        return $sections;
	}
	
    public function register_settings(){
        register_setting ( $this->unique, $this->unique, array (&$this,'validate_save'));
        if(count($this->get_option) < $this->total_fields){
            $this->register_settings_fields();
        }
	}
    
    public function register_settings_fields(){
        $defaults = array ();
		
		foreach ( $this->sections as $section ) {
			if (isset ( $section ['fields'] )) {
				foreach ( $section ['fields'] as $field_key => $field ) {
					if (isset ( $field ['default'] )) {
						$defaults [$field ['id']] = $field ['default'];
						if (! empty ( $this->get_option ) && ! isset ( $this->get_option [$field ['id']] )) {
							$this->get_option [$field ['id']] = $field ['default'];
						}
					}
				}
			}
		}
		
		if (empty ( $this->get_option ) && ! empty ( $defaults )) {
			update_option ( $this->unique, $defaults );
			$this->get_option = $defaults;
		}
    }
	
	public function validate_save($request) {
		$add_errors = array ();
		$section_id = wpsf_get_var ( 'wpsf_section_id' );
		$parent_sectionid = wpsf_get_var('wpsf-parent-section');
        $posted_vals = $request;
        $isp = $this->is_single_page();
        
        if($isp === false){
            $old_options = is_array($this->get_option) ? $this->get_option : array();
            $request = array_merge($old_options,$request);
        }
        
		// ignore nonce requests
		if (isset ( $request ['_nonce'] )) {
			unset ( $request ['_nonce'] );
		}
		
		// import
		if (isset ( $request ['import'] ) && ! empty ( $request ['import'] )) {
			$decode_string = wpsf_decode_string ( $request ['import'] );
			if (is_array ( $decode_string )) {
				return $decode_string;
			}
			$add_errors [] = $this->add_settings_error ( esc_html__ ( 'Success. Imported backup options.', 'wpsf-framework' ), 'updated' );
		}
		
		// reset all options
		if (isset ( $request ['resetall'] )) {
			$add_errors [] = $this->add_settings_error ( esc_html__ ( 'Default options restored.', 'wpsf-framework' ), 'updated' );
			return;
		}
		
		// reset only section
		if (isset ( $request ['reset'] ) && ! empty ( $section_id )) {
			foreach ( $this->sections as $value ) {
				if ($value ['name'] == $section_id) {
					foreach ( $value ['fields'] as $field ) {
						if (isset ( $field ['id'] )) {
							if (isset ( $field ['default'] )) {
								$request [$field ['id']] = $field ['default'];
							} else {
								unset ( $request [$field ['id']] );
							}
						}
					}
				}
			}
			$add_errors [] = $this->add_settings_error ( esc_html__ ( 'Default options restored for only this section.', 'wpsf-framework' ), 'updated' );
		}
        
		// option sanitize and validate
		foreach ( $this->sections as $section ) {
            if($isp === false && $section_id != $section['name']){
                continue;
            }

                
			if (isset ( $section ['fields'] )) {
				foreach ( $section ['fields'] as $field ) {
					
					// ignore santize and validate if element multilangual
					if (isset ( $field ['type'] ) && ! isset ( $field ['multilang'] ) && isset ( $field ['id'] )) {
						
						// sanitize options
						$request_value = isset ( $request [$field ['id']] ) ? $request [$field ['id']] : '';
						$sanitize_type = $field ['type'];
						
						if (isset ( $field ['sanitize'] )) {
							$sanitize_type = ($field ['sanitize'] !== false) ? $field ['sanitize'] : false;
						}
						
						if ($sanitize_type !== false && has_filter ( 'wpsf_sanitize_' . $sanitize_type )) {
							$request [$field ['id']] = apply_filters ( 'wpsf_sanitize_' . $sanitize_type, $request_value, $field, $section ['fields'] );
						}
						
						// validate options
						if (isset ( $field ['validate'] ) && has_filter ( 'wpsf_validate_' . $field ['validate'] )) {
							$validate = apply_filters ( 'wpsf_validate_' . $field ['validate'], $request_value, $field, $section ['fields'] );
							if (! empty ( $validate )) {
								$add_errors [] = $this->add_settings_error ( $validate, 'error', $field ['id'] );
								$request [$field ['id']] = (isset ( $this->get_option [$field ['id']] )) ? $this->get_option [$field ['id']] : '';
							}
						}
                        
                        if($isp === false){
                            if(!isset($posted_vals[$field['id']])){
                                $request[$field['id']] = '';
                            }
                        }
					}
                    
                    
					if (! isset ( $field ['id'] ) || empty ( $request [$field ['id']] )) {
						continue;
					}
				}
			}
		}
		
		$request = apply_filters ( 'wpsf_validate_save', $request );
		
		do_action ( 'wpsf_validate_save_after', $request );
		
		// set transient
		$transient_time = (wpsf_language_defaults () !== false) ? 30 : 10;
		set_transient ( '_wpsf_' . $this->get_cache_key(), array (
				'errors' => $add_errors,
				'section_id' => $section_id 
		), $transient_time );
		
		return $request;
	}
    
    public function get_cache_key(){
        return isset ( $this->settings ['uid'] ) ? $this->settings ['uid'] : sanitize_title ( $this->settings ['menu_title'] );
    }

    public function field_callback($field) {
		$value = (isset ( $field ['id'] ) && isset ( $this->get_option [$field ['id']] )) ? $this->get_option [$field ['id']] : '';
		return wpsf_add_element ( $field, $value, $this->unique );
	}
	
    public function add_settings_error($message, $type = 'error', $id = 'global') {
		return array (
				'setting' => 'wpsf-errors',
				'code' => $id,
				'message' => $message,
				'type' => $type 
		);
	}
	
    public function theme() {
		return isset ( $this->settings ['style'] ) ? $this->settings ['style'] : 'modern';
	}
	
    public function get_settings_buttons() {
		$this->catch_output ('start');
		
		submit_button ( esc_html__ ( 'Save', 'wpsf-wp' ), 'primary wpsf-save', 'save', false, array ('data-save' => esc_html__ ( 'Saving...', 'wpsf-wp' )));
		submit_button ( esc_html__ ( 'Restore', 'wpsf-wp' ), 'secondary wpsf-restore wpsf-reset-confirm', $this->unique . '[reset]', false );
		
		if ($this->settings ['show_reset_all']) {
			submit_button ( esc_html__ ( 'Reset All Options', 'wpsf-wp' ), 'secondary wpsf-restore wpsf-warning-primary wpsf-reset-confirm', $this->unique . '[resetall]', false );
		}
		
		return $this->catch_output ( false );
	}
	
    public function get_settings_fields() {
		$this->catch_output ();
		settings_fields ( $this->unique );
        echo '<input type="hidden" name="wpsf-parent-section" value="'.wpsf_get_var("wpsf-parent-section").'" /> ';
		return $this->catch_output ( false );
	}
	
    public function has_nav() {
		return (count ( $this->options ) <= 1) ? 'wpsf-show-all' : "";
	}
	
    public function is_single_page(){
        return isset($this->settings['is_single_page']) ? $this->settings['is_single_page'] : true;
    }
    
    public function is_sticky_header(){
        if(isset($this->settings['is_sticky_header'])){
            if($this->settings['is_stick_header'] === true){
                return 'wpsf-sticky-header';
            }
        }
        
        return '';
    }
    
    public function validate_sections(){
        $this->parentsection = wpsf_get_var('wpsf-parent-section',false);
        if($this->parentsection !== false){
            foreach($this->options as $option){
                if($option['name'] == $this->parentsection){
                    if(isset($option['sections'])){
                        $this->sections = $option['sections'];
                    } else if(isset($option['fields'])){
                        $this->sections = $option['fields'];
                    }
                    break;
                }
            }
        }
    }
    
    public function admin_page() {
		$slug = $this->get_cache_key();
		$cache = get_transient ( '_wpsf_' . $slug );
		$section_id = (! empty ( $cache ['section_id'] )) ? $cache ['section_id'] : $this->sections [0] ['name'];
		$this->csectionid = wpsf_get_var ( 'wpsf-section', $section_id );
        $this->validate_sections();
		$errors_html = '';
		
		if ($this->settings ['ajax_save'] !== true && ! empty ( $cache ['errors'] )) {
			global $wpsf_errors;
			$wpsf_errors = $cache ['errors'];
			
			if (! empty ( $wpsf_errors )) {
				foreach ( $wpsf_errors as $error ) {
					if (in_array ( $error ['setting'], array ('general','wpsf-errors'))) {
						$errors_html .= '<div class="wpsf-settings-error ' . $error ['type'] . '">';
						$errors_html .= '<p><strong>' . $error ['message'] . '</strong></p>';
						$errors_html .= '</div>';
					}
				}
			}
		}
		
		wpsf_template ( 'settings/render.php', array (
				'class' => &$this,
				'errors' => $errors_html,
				'current_section_id' => $this->csectionid 
		) );
	}
    
    public function get_tab_url($section = '',$parent = ''){
        if($this->is_single_page() !== true){
            $data = array();
            if(!empty($section)){
                $data['wpsf-section'] = $section;
            }
            if(!empty($parent)){
                $data['wpsf-parent-section'] = $parent;
            }
            $url = remove_query_arg(array('wpsf-section','wpsf-parent-section'));
            return add_query_arg($data,$url);
            
        }
        return '#';
    }
	
    public function html_nav_bar() {
		$r = '';
        $isp = $this->is_single_page();
        
		if ($this->theme () === 'modern') {
			$r = '<div class="wpsf-nav"> <ul>';
			foreach ( $this->options as $key => $tab ) {
				if ((isset ( $tab ['sections'] ))) {
					$tab_active = wpsf_array_search ( $tab ['sections'], 'name', $this->csectionid );
					$active_style = (! empty ( $tab_active )) ? ' style="display: block;"' : '';
					$active_list = (! empty ( $tab_active )) ? ' wpsf-tab-active' : '';
					$tab_icon = (! empty ( $tab ['icon'] )) ? '<i class="wpsf-icon ' . $tab ['icon'] . '"></i>' : '';
					
					$r .= '<li class="wpsf-sub' . $active_list . '">';
					$r .= '<a href="#" class="wpsf-arrow">' . $tab_icon . $tab ['title'] . '</a>';
					$r .= '<ul' . $active_style . '>';
					
					foreach ( $tab ['sections'] as $tab_section ) {
						$active_tab = ($this->csectionid == $tab_section ['name']) ? ' class="wpsf-section-active"' : '';
						$icon = (! empty ( $tab_section ['icon'] )) ? '<i class="wpsf-icon ' . $tab_section ['icon'] . '"></i>' : '';
						$r .= '<li><a href="'.$this->get_tab_url($tab_section['name'],$tab['name']).'"' . $active_tab . ' data-section="' . $tab_section ['name'] . '">' . $icon . $tab_section ['title'] . '</a></li>';
					}
					$r .= '</ul>';
					$r .= '</li>';
				} else {
					$icon = (! empty ( $tab ['icon'] )) ? '<i class="wpsf-icon ' . $tab ['icon'] . '"></i>' : '';
					if (isset ( $tab ['fields'] )) {
						$active_list = ($this->csectionid == $tab ['name']) ? ' class="wpsf-section-active"' : '';
                        $r .= '<li><a href="'.$this->get_tab_url($tab['name']).'"' . $active_list . ' data-section="' . $tab ['name'] . '">' . $icon . $tab ['title'] . '</a></li>';
					} else {
						$r .= '<li><div class="wpsf-seperator">' . $icon . $tab ['title'] . '</div></li>';
					}
				}
			}
			$r .= '</ul></div>';
		} else {
            
            foreach($this->options as $key => $tab){
                if(!isset($tab['fields']) && ! isset($tab['sections'])){
                    continue;
                }
                $icon = (! empty($tab['icon'])) ? '<i class="wpsf-icon '.$tab['icon'].'"></i>': '';
                $tab_active = '';
                if(isset($tab ['sections'])){
                    $tab_active = wpsf_array_search ( $tab ['sections'], 'name', $this->csectionid );
                }
                
                if(!empty($tab_active)){
                    $is_active = ' nav-tab-active ';
                } else {
                    $is_active = ($this->csectionid == $tab['name']) ? ' nav-tab-active ' : '';
                }
                
                $r .= '<a href="'.$this->get_tab_url($tab['name']).'" data-section="'.$tab['name'].'" class="nav-tab '.$is_active.'">'.$icon.' '.$tab['title'].'</a>';
            }
		}
		
		return $r;
	}
	
    public function html_modern_theme() {
		$r = '';
        
        foreach( $this->sections as $section ) {
            if($this->is_single_page() === false){
                if($this->csectionid != $section['name']){
                    continue;
                }
            }
            if( isset( $section['fields'] ) ) {
                $active_content = ( $this->csectionid == $section['name'] ) ? ' style="display: block;"' : '';
                $r .= '<div id="wpsf-tab-'. $section['name'] .'" class="wpsf-section"'. $active_content .'>';
                $r .= ( isset( $section['title'] ) && empty( $this->has_nav() ) ) ? '<div class="wpsf-section-title"><h3>'. $section['title'] .'</h3></div>' : '';
                foreach( $section['fields'] as $field ) {
                    $r .= $this->field_callback( $field );
                }
                $r .= '</div>';
            }
        }
        return $r;
	}

    public function html_simple_theme(){
        $final_html = '';
        foreach($this->options as $options){
            
            if(!isset($options['fields']) && !isset($options['sections'])){continue;}
            $smenu = '';
            $l1_html = '';
            $main_active = false;
            
            if($this->is_single_page() === false){
                if($this->csectionid != $options['name']){
                    continue;
                }                
            }
            
            if(isset($options['sections'])){
                $smenu = array();
                $is_noFs = false;
                $first_section = $options['sections'][0]['name'];
                foreach($options['sections'] as $section){
                    $fields = '';
                    if($main_active !== true){
                        $main_active = ($this->csectionid == $section['name']) ? true : false;
                    }


                    if($this->csectionid == $section['name']){
                        $is_csmenu = 'current';
                        $is_html_active = ' style="display:block;" ';
                    } else {
                        $is_csmenu = $is_html_active = '';
                    }
                    
                    foreach($section['fields'] as $field){
                        $fields .= $this->field_callback($field);
                    }
                    
                    $submenu_icon = (!empty($section['icon'])) ? '<i class="wpsf-icon '.$section['icon'].'" ></i>' : '';
                    $smenu[] = '<li><a href="#" data-parent-section="'.esc_attr($options['name']).'" data-section="'.esc_attr($section['name']).'" class="'.$is_csmenu.'">'.$submenu_icon.' '.$section['title'].'</a>';
                    $l1_html .= '<div id="wpsf-tab-'.$section['name'].'" class="wpsf-section" '.$is_html_active.'>';
                    $l1_html .= (isset($section['title']) && empty($this->has_nav())) ? '<div class="wpsf-section-title"><h3>'.$section['title'].'</h3></div>' : '';
                    $l1_html .= $fields.'</div>';
                }
                
                if($main_active === false){
                    $smenu[0] = str_replace('class=""','class="current"',$smenu[0]);
                    $l1_html = str_replace('id="wpsf-tab-'.$first_section,' id="wpsf-tab-'.$first_section.'" style="display:block;" ',$l1_html);
                }
                
                $smenu = implode(' | </li>',$smenu);
                $smenu = '<ul class="wpsf-submenus subsubsub" id="wpsf-tab-'.$options['name'].'">'.$smenu.'</ul>';
                $main_active = ($main_active === false) ? ' style="display:none" ' : '';

            } else if(isset($options['fields'])){
                $is_active = ($this->csectionid == $options['name']) ? '' : ' style="display:none;" ';
                $smenu = '<span class="wpsf-submenus" id="wpsf-tab-'.$options['name'].'" data-section="'.esc_attr($options['name']).'">'.$options['title'].'</span>';
                $main_active = $is_active;
                $fields ='';
                foreach($options['fields'] as $field){
                    $fields .= $this->field_callback($field);
                }
                
                $l1_html .= (isset($section['title']) && empty($this->has_nav())) ? '<div class="wpsf-section-title"><h3>'.$section['title'].'</h3></div>' : '';
                $l1_html .= $fields;
            }
            
            if($this->is_single_page() === false && $this->csectionid == $options['name']){
                $main_active = ' style="display:block" ';
            }
            
            
            $final_html .= '<div id="wpsf-tab-'.$options['name'].'" '.$main_active.'>';
            $final_html .= '<div class="postbox"><h2 class="wpsf-subnav-container hndle">'.$smenu.'</h2><div class="inside">'.$l1_html.'</div></div>';
            $final_html .=  '</div>';
            $smenu = '';
            $main_active = false;
        }
        
        return $final_html;
        
    }
}