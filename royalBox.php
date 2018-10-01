<?php
/**
 * @version 1.0.0
 * 
 * @method add_screen(string)  page|post|custom-post-type
 * @method set_title(string)
 * @method set_context(string)  normal|advanced|side
 * @method add_field(string, array) or just array
 * @method set_priority(string) high|core|default|low
 * @method set_template(string) template-name1.php,template_name2.php
 * @method load(file.json)
 * @method $create_field can create new fields and use them. 
 * 
 * @todo Fix revision fields and limit save revision to 5
 * **/

if (!class_exists('RoyalBox')) {
	class RoyalBox {

		function __construct($opts = array()) {
			// INITIALIZATION
			$opts = (array) $opts; 	// MAIN ARRAY 

				/** add_meta_box( 
				 * 		1		$id:string, 								// ID attribute of metabox
				 * 		2		$title:string, 								// Title of metabox visible to user
				 * 		3		$callback:callable, 						// Function that prints box in wp-admin
				 * 		4		$screen:string|array|WP_Screen|null, 		// Show box for posts, pages, custom, etc.
				 * 		5		$context:string, 							// Where on the page to show the box
				 * 		6		$priority:string, 							// Priority of box in display order
				 * )            		
				 */

			// #1  ID optional (in json) daca el nu este scris v-om luat titlul
			// #2  Titlul este oblicatoriu si daca nu e scris ii dam un default
			$this->title = isset($opts['title']) ? $opts['title'] : 'Custom section'; // ne trebuie un titlu indiferent asa ca daca nu e puntem unul default
			$this->id = isset($opts['id']) ? $opts['id'] : preg_replace('/[^a-z0-9]/', '', strtolower($this->title));
			
			// #4  Screen-ul este obligatoriu chiar daca folosim un custom template
			$this->screens = array();
			if (isset($opts['screens'])) {
				if (is_string($opts['screens'])) { $opts['screens'] = preg_split('/\s*,\s*/', $opts['screens']); }
				if (is_array($opts['screens'])) {  call_user_func_array(array($this, 'add_screen'), $opts['screens']); }
			}

			// #5  Context
			// #6  Priority	
			$this->set_context(@$opts['context']);
			$this->set_priority(@$opts['priority']);

			// END  add_meta_box  REQUIREMENTS



			// TEMPLATE - Optional (pt un sau mai multe templat-uri)
			$this->template = array();
			if (isset($opts['template'])) {
				if (is_string($opts['template'])) { $opts['template'] = preg_split('/\s*,\s*/', $opts['template']); }
				if (is_array($opts['template'])) {	call_user_func_array(array($this, 'add_template'), $opts['template']); }
			}

			//--> Group
			$this->group  = array();
			if (isset($opts['group']) && is_array($opts['group'])) {
			/* 	print_r( $opts['group'] ); die; */
				$index = 1;
				foreach ($opts['group'] as  $fields_set ) { 
					$this->add_group_field($fields_set,$index); 
					$index++;
				}
			}

			 //--> FILDURILE
			$this->fields  = array();
			if (isset($opts['fields']) && is_array($opts['fields'])) {
				foreach ($opts['fields'] as $name => $field) {
					$this->add_field($name, $field); 
				}
			}

		} //END - Constructor


		// #2  TITLE FUNCTION
		public function set_title($title = 'Custom section') {
			$this->title = $title;
		}

		// #4  SCREEN FUNCTION
		public function add_screen() {
			foreach (func_get_args() as $screen) {
				if (!$this->has_screen($screen)) {
					array_push($this->screens, $screen);
					$this->activate_on('load-post.php', 'load-post-new.php'); 
				}
			}
			return $this;
		}
		public function has_screen($screen = null) {
			return in_array($screen, $this->screens, true);
		}
		public function activate() { //Wordpres hook in
			add_action('add_meta_boxes',  array($this, 'rbf_add_meta_box'));
			/* add_filter( 'wp_revisions_to_keep', 5, 10, 2 ); */ 
			add_action('save_post', array($this, 'rbf_post_revision'), 10, 2);
			add_filter( '_wp_post_revision_fields', array($this, 'rbf_post_revision_fields'), 10, 2  );
			add_action( 'wp_restore_post_revision',  array($this,'rbf_restore_revision'), 10, 2 );
			add_action('pre_post_update', array($this, 'rbf_pre_post_update'), 10, 2);
			return $this;
		}
		public function activate_on() {
			foreach (func_get_args() as $name) {
				if (!isset($this->actions[$name])) {
					add_action($name, array($this, 'activate'));
					$this->actions[$name] = true;
				}
			}
			return $this;
		}

		// #5  Context FUNCTION	
		public function set_context($context) {
			$this->context = preg_match('/^(normal|advanced|side)$/', $context) ? $context : 'normal';
			return $this;
		}
		// #6  Priority	FUNCTION
		public function set_priority($priority) {
			$this->priority = preg_match('/^(high|core|default|low)$/', $priority) ? $priority : 'default';
			return $this;
		}
	
		// Template FUNCTION
		public function add_template() {
			foreach (func_get_args() as $template) {
				if (!$this->has_template($template)) {
					array_push($this->template, $template);
				}
			}
			return $this;
		}
		public function has_template($template = null) {
			return in_array($template, $this->template, true);
		}

		// GROUP FUNCTION
		public function add_group_field($fields_data = array(), $index) {
			
			if (is_array($fields_data)) {
						$this->group['field_set'.$index] = $fields_data;  
			} 
			
			
			elseif (is_string($fields)) { 
				$this->group[$fields] = $fields_data;
			}
			

			return $this;
		}

		// FIELDS FUNCTION
		public function add_field($nameOrFields = 'sample', $data = array()) {
			if (is_array($nameOrFields)) {
				foreach ($nameOrFields as $name => $field) {
					$this->fields[$name] = $field;
				}
			} elseif (is_string($nameOrFields)) { 
				$this->fields[$nameOrFields] = $data;
			}
			return $this;
		}

		function rbf_add_meta_box() {
			if(!empty($this->template)){
				global $post;
				foreach($this->template as $template){
					if ( $template == get_post_meta( $post->ID, '_wp_page_template', true ) ) { 
						foreach ($this->screens as $screen) {
							add_meta_box(
									$this->id,          // $id:string 	(ID attribute of metabox)
									$this->title,       // $title:string (Title of metabox visible to user)
									array($this, 'rbf_render_royal_box'), // $callback:callable (Function that prints box in wp-admin)
									$screen,            // $screen:string|array|WP_Screen|null (Show box for posts, pages, custom, etc.)
									$this->context,     // $context:string (Where on the page to show the box)
									$this->priority     // $priority:string (Priority of box in display order)
								);
						}
					}
				}
			} else { 
				foreach ($this->screens as $screen) {
					add_meta_box(
							$this->id,       
							$this->title,    
							array($this, 'rbf_render_royal_box'), 
							$screen,           
							$this->context,   
							$this->priority     
						);
				}
			}
			
        }
        
		function rbf_render_royal_box($post) {
			wp_nonce_field('rbf_meta_box', 'rbf_meta_box_nonce');
			$buffer = ''; // html buffer
			
			if($this->group) { // arrayul unui singur fields set in care regasit alt array cu field id si in care gasit data pt fiecare metabox.

				/**
				 * @todo GROUP SECTION
				 */

				 $buffer .=  '<h2><span>GROUP title </span> <input type="button" value="add +"></h2>';
				 $buffer .=  '<div class="group_rb_container">';

				 //add a group header
				 //add a add buton in haddder
				 // make a loop using javascrip
 				foreach ($this->group as $field_id ){
					foreach ($field_id as $name => $data) { 
					/* 	print_r($this->group);die; */
						$func = isset($data['type']) ? @self::$create_field->{$data['type']} : self::$create_field->text; // daca exista in array type atunci generam arrayul ce corespunde acelui type
						
						if ($func) {  // daca avem un obiect
							if (count(get_post_meta($post->ID, $name))) { //verificam bd
								$data['value'] = get_post_meta($post->ID, $name); //incarcam din bd pt a completa valorile salvate in fild-uri
							} else if (!isset($data['value'])) { //verificam daca exista json
								$data['value'] = array();  // sanitizam 
							} else { // incaracam din json in functie de tip
								$data['value'] = is_array($data['value']) ? $data['value'] : array($data['value']);
							}
								
							$layout = $func($name, $data);  
							$buffer .= is_string($layout) ? $layout : forward_static_call_array(array('RoyalBox', 'create_element'), $layout); // trimite cate un metabox spre generare k si array de mai multe taguri, atribute si text
						} 
					}
				}
				$buffer .=  '</div>';

			}

			foreach ($this->fields as $name => $data) { 

				$func = isset($data['type']) ? @self::$create_field->{$data['type']} : self::$create_field->text; // daca exista in array type atunci generam arrayul ce corespunde acelui type
				
				if ($func) {  // daca avem un obiect
					if (count(get_post_meta($post->ID, $name))) { //verificam bd
						$data['value'] = get_post_meta($post->ID, $name); //incarcam din bd pt a completa valorile salvate in fild-uri
					} else if (!isset($data['value'])) { //verificam daca exista json
						$data['value'] = array();  // sanitizam 
					} else { // incaracam din json in functie de tip
						$data['value'] = is_array($data['value']) ? $data['value'] : array($data['value']);
					}
						
					$layout = $func($name, $data);  
					$buffer .= is_string($layout) ? $layout : forward_static_call_array(array('RoyalBox', 'create_element'), $layout); // trimite cate un metabox spre generare k si array de mai multe taguri, atribute si text
				} 
			}//end foreache tag
			print($buffer); // Acum in buffer se afla toate tagurile generate deja html
        }

		// GENERATE LAYOUT FROM ARRAY
		static function create_element() {
			$buffer = '';
			/* 	[1] => Array (
				[0] => p                          
				[style] => width:100%                 
				[1] => Array(						
					[0] => input			 
					[type] => text					
					[id] => text_testing_id				
					[name] => text_testing				
					[value] => 									
					[placeholder] => This is a placeholder text  	
			<p 
				style="width:100%"
			>
			<input 
				id="text_testing_id" 
				name="text_testing" 
				value="" 
				placeholder="This is a placeholder text" 
				type="text"
			>
			</p>	*/

			foreach (func_get_args() as $opts) { 
				//asteapta generarea recusiva a unui text din interiorul unui tag
				if (is_string($opts)) {
					$buffer .= $opts;
					continue;
				}
				
				// fie ca e recursiv sa nu in pozitia 0 va sta mereu tipul de TAG-urile
				$buffer .= '<'.$opts[0];
				
				// verificam daca tagul are atribute ( for, width, type, id, name, value, placeholder etc )
				foreach ($opts as $name => $value) {
					if (is_string($name)) {
						$buffer .= ' '.$name.'="'.$value.'"';
					}
				}
				
				// verifica  daca TAG-urile au sub taguri sau atribute 
				if (isset($opts[1])) { 
					$buffer .= '>'; // inchide  '<'.$opts[0];

					//Generam recursiv array-ul
					for ($i = 1; isset($opts[$i]); ++$i) {
						$buffer .=  self::create_element($opts[$i]);
					} 

					// Daca $opts[$i] is_string atunci primesc continu
					$buffer .= '</'.$opts[0].'>';  // inchide  '<'. $opts[0] . '>';
				} else { // daca $opts[$i] !is_string atunci tagul are atribute
					$buffer .= '/>';  // inchide $buffer .= '<'.$opts[0];
				}
			}

			return $buffer;
		} // END GENERATE
		
		// REVISION FUNCTIONS
		function rbf_post_revision($post_id){

				foreach ($this->fields as $name => $data) {
					if (isset($_POST[$name])) { 
						add_post_meta($post_id, $name, get_post_meta( $post_id, $name, true ) );
					}
				}

		}

		function rbf_post_revision_fields($fields){
			foreach ($this->fields as $name => $data) {
				if (isset($_POST[$name])) { 
					$fields[$name] = $data['label'];
					add_filter('_wp_post_revision_field_'.$fields[$name] , 'rbf_post_revision_field', 10, 4);
				}
			}
			return $fields;
		}

		function rbf_post_revision_field( $value, $field , $post = null, $direction = false) {
			$post_id = $post->ID;
			return get_metadata( 'post', $post_id, $field, true );
		}

		function rbf_restore_revision( $post_id, $revision_id ) {
			$revision = get_post( $revision_id );
			$keyArray= array();
			foreach( $this->fields as $key => $val ) $keyArray = array_push($keyArray,$key);
			
			foreach ($keyArray as $name => $data) {
				$rest_elem_data  = get_metadata( 'post', $revision->ID, $name, true );
				if ( !$rest_elem_data )
					delete_post_meta( $post_id, $rest_elem_name );
				else
					update_post_meta( $post_id, $rest_elem_name, $rest_elem_data );
			}
		}
		// END REVISION FUNCTION SECTION

		// UPDATE > DB
		function rbf_pre_post_update($post_id) {
			
			//NONCE VALIDATION
			if (!isset($_POST['rbf_meta_box_nonce']) || !wp_verify_nonce($_POST['rbf_meta_box_nonce'], 'rbf_meta_box')) {
				return;
			}

			// AUTOSAVE ON = STOP
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			// USER PERMISSION
			if (!current_user_can(isset($_POST['post_type']) && $_POST['post_type'] === 'page' ? 'edit_page' : 'edit_post', $post_id)) {
				return;
			}

			// DB UPDATE
			foreach ($this->fields as $name => $data) {
				if (isset($_POST[$name])) { 
					if(is_array($_POST[$name])) {
						$_POST[$name] = implode('---', $_POST[$name]);
					}
					/* echo '<pre>'; print_r( $_POST ); wp_die(); */
					update_post_meta($post_id, $name, sanitize_text_field($_POST[$name]));
				}
			}

		}// END UPDATE

		//JSON FILE LOADER
		static function load($filename = null) {
				$path = dirname(__FILE__).'/'.$filename;
			return new self(json_decode(file_get_contents($path), true));
		}

		static $create_field;
		
	} // END CLASS

	// THE TYPE LAYOUT FILE
	require_once('royalBox_fields.php'); 

} // END OBJ