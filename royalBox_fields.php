<?php
/**
 * @deprecated @package royalBox.php 
 * @deprecated @version 1.0.0
 * 
 * @method text { placeholder } 
 * @method textarea { rows }  
 * @method select { value(opt) | options=>opt_value } 
 * @method checkbox { value(opt) | options=>opt_value } 
 * @method color { value(opt) } 
 * @method file @see bug if uploaded and delet uploaded agan get problem
 * 
 * @method @todo bulk sections
 * @method @todo time piker
 * @method @todo date piker
 * @method @todo timezone piker
 * @method @todo page / post / custom_post piker|list
 * @method @todo buttons { href } // un a cu still
 * @method @todo section { width }
 * @method @todo imbeded video
 * 
 * @todo grup controls
 * @todo sub-grup inside a grup
 * 
 * 
 */
if (class_exists('RoyalBox') && !is_object(RoyalBox::$create_field)) {
	RoyalBox::$create_field = (object) array();

	// <input type="text">
	RoyalBox::$create_field->text = function ($name, $data) {
		$id = $name.'_id';
		$placeholder = @$data['placeholder'];
		$width = isset($data['width']) ? $data['width'] : '100';
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';
		$label = @$data['label'];
		if (isset($data['label'])) {
			$_label = array(
				'label', 'for' => $id,
						 'style' => 'display: inline-block;
						 			 margin-bottom: 5px;
						 			 margin-left: 5px;',
					array('strong', $data['label'])		
			);
		} else $_label = '';

		return array( 
			array('div',
				'style' =>	'width: calc('. $width . '% - 20px); 
							display:inline-block; 
							vertical-align: top;
							margin: 0 10px;',
				$_label,
				array('input', 
					'style' => 'width:100%;margin-bottom: 20px;',
						'type' => 'text', 
						'id' => $id, 
						'name' => $name, 
						'value' => $opt_valueue, 
						'placeholder' => $placeholder
				)
			)
		);

	};

	// <textarea> 
	RoyalBox::$create_field->textarea = function ($name, $data) {
		$id = $name.'_id';
		$width = isset($data['width']) ? $data['width'] : '100';
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';
		$label = @$data['label'];
		if (isset($data['label'])) {
			$_label = array(
				'label',
				'style' => 'display: inline-block;
							 margin-bottom: 5px;
							 margin-left: 5px;',
					array('strong', $data['label'])
			);
		} else $_label = '';
		if (isset($data['rows'])) $textarea['rows'] = $data['rows'];
		$textarea_arguments = array ( 
			'textarea_rows' => $textarea['rows'],
			'textarea_name' => $name,
		);
		
		ob_start();
		wp_editor($opt_valueue, $name, $textarea_arguments);
		$textarea = ob_get_clean();

		return array(
			array('div',
			'style' =>	'width: calc('. $width . '% - 20px); 
						display:inline-block; 
						vertical-align: top;
						margin: 0 10px;',
			$_label,		
			$textarea
			)
			
		);

	};


	// <select>
	RoyalBox::$create_field->select = function ($name, $data) {
		$id = $name.'_id';
		$width = isset($data['width']) ? $data['width'] : '100';
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';
		$options = array();
		foreach ($data['options'] as $opt_name => $opt_value) {
			$option = array('option', $opt_value);

			// Transform name to value if name not array key for value
			if (is_string($opt_name)) {
				$option['value'] = $opt_name;
			}

			// set selected attribute
			if ((is_string($opt_name) && $opt_name === $opt_valueue) || (!is_string($opt_name) && ($opt_name === $opt_valueue || $opt_value === $opt_valueue))) {
				$option['selected'] = ''; // this will render as selected checkbox in html generator
			}
			
			// add to main option array
			array_push($options, $option);
		}

		$label = @$data['label'];
		if (isset($data['label'])) {
			$_label = array(
				'label', 'for' => $id,
				'style' => 'display: inline-block;
							 margin-bottom: 5px;
							 margin-left: 5px;',
					array('strong', $data['label'])		
			);
		} else $_label = '';

		return array( 
			array('div',
				'style' =>	'width: calc('. $width . '% - 20px); 
							display:inline-block; 
							vertical-align: top;
							margin: 0 10px;',
				$_label,
				array('select', 
					'style' => 'display:block;margin-bottom: 20px;',
						'type' => 'text', 
						'id' => $id, 
						'name' => $name, 
						$options
				)
			)
		);
	};

	// <input type="checkbox">
	RoyalBox::$create_field->checkbox = function ($name, $data) {
		$id = $name.'_id';
		$label = @$data['label'];
		$width = isset($data['width']) ? $data['width'] : '100';

		$opt_valueue = isset($data['value']) ? $data['value'] : array();
		$opt_valueue = explode('---',$opt_valueue[0]);
		
		$options = array();
		$index = 0;
		foreach ($data['options'] as $opt_name => $opt_value) {
		
			// create option field
			$checkbox = array('input', 'type' => 'checkbox', 'name' => $name.'[]', 'id' => $name.$index);

			// create label
			$label    = array('label', 'for' => $name.$index, $opt_value);

			// set value attribute as option key or option value
			$checkbox['value'] = is_string($opt_name) ? $opt_name : $opt_value;

			// conditionally set checked attribute
			if (in_array($opt_name, $opt_valueue, true) || in_array($opt_value, $opt_valueue, true)) {
				$checkbox['checked'] = '';
			}

			// add to options collection
			array_push($options, array('p', $checkbox, $label));
			++$index;
		}

		$label = @$data['label'];
		if (isset($data['label'])) {
			$_label = array(
				'label', 
				'style' => 'display: inline-block;
							 margin-bottom: 5px;
							 margin-left: 5px;',
					array('strong', $data['label'])		
			);
		} else $_label = '';

		$layout = array('div',
						'style' =>	'width: calc('. $width . '% - 20px); 
									display:inline-block; 
									vertical-align: top;
									margin: 0 10px;',
						$_label
				);
		$layout= array( array_merge($layout, $options) );

		return $layout;
	};
	
	// <input type="color">
	RoyalBox::$create_field->color = function ($name, $data) {
		$id = $name.'_id';
		$width = isset($data['width']) ? $data['width'] : '100';
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';
		$label = @$data['label'];
		if (isset($data['label'])) {
			$_label = array(
				'label', 
				'style' => 'display: inline-block;
							 margin-bottom: 5px;
							 margin-left: 5px;',
					array('strong', $data['label'])		
			);
		} else $_label = '';
		
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');

		return array( 
			array('div',
				'style' =>	'width: calc('. $width . '% - 20px); 
							display:inline-block; 
							vertical-align: top;
							margin: 0 10px;',
				$_label,
				array('input', 
						'type' => 'color', 
						'id' => $id, 
						'name' => $name, 
						'value' => $opt_valueue
					),
				array('script', 'jQuery(
										function($){
												$("#'.$id.'").wpColorPicker();
											}
										)'
					)
			)
		);
	};

	// <input type="file">
	RoyalBox::$create_field->file = function ($name, $data) {
		$id = $name.'_id';
		$width = isset($data['width']) ? $data['width'] : '100';
		$label = @$data['label'];
		if (isset($data['label'])) {
			$_label = array(
				'label', 
				'style' => 'display: inline-block;
							 margin-bottom: 5px;
							 margin-left: 5px;',
					array('strong', $data['label'])		
			);
		} else $_label = '';

		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';

		wp_enqueue_style('wp-media-upload');
		wp_enqueue_script('wp-media-upload');

		$layout = array ('input',
				'type'  => 'hidden',
				'name'  => $name,
				'id' 	=> $id,
				'value' => $opt_valueue,
		);

		if ( '' == $opt_valueue ) $layout = array_merge(
			$layout, array( 
				array('input',
					'type'  => 'button',
					'class' => $name.'_btn button',
					'value' => 'Upload',
					'style' => 'display: block;'
				),
				array('img',
						'class' => $name.'_img',
						'src'   => '',
						'style' => 'display: none;'
				),
				array('input',
						'type'  => 'button',
						'class' => $name.'_btn_remove button',
						'value' => 'Remove',
						'style' => 'display: none;'
				)
			)
		); else $layout = array_merge(
			$layout, array(
				array('img',
						'class' => $name.'_img',
						'src'   => $opt_valueue,
				),
				array('input',
						'type'  => 'button',
						'class' => $name.'_btn_remove button',
						'value' => 'Remove',
						'style' => 'display: block;'
				),
				array('input',
					'type'  => 'button',
					'class' => $name.'_btn button',
					'value' => 'Upload',
					'style' => 'display: none;'
				)

			)
		) ;
		



		return array( 
			array('div',
				'style' =>	'width: calc('. $width . '% - 20px); 
							display:inline-block; 
							vertical-align: top;
							margin: 0 10px;',
				$_label,
				$layout,
				array('script', 
				'jQuery(function ($) {

					var meta_image_frame;

					$(".'.$name.'_btn").click(function (e) {
						e.preventDefault();

						// If the frame already exists, re-open it.
						if ( meta_image_frame ) {
							wp.media.editor.open();
							return;
						}

						// Sets up the media library frame
						meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
							title: "test",
							button: { text:  "load" },
							library: { type: "image" }
						});

						// Runs when an image is selected.
						meta_image_frame.on("select", function () {

							// Grabs the attachment selection and creates a JSON representation of the model.
							var media_attachment = meta_image_frame.state().get("selection").first().toJSON();

							// Sends the attachment URL to our custom image input field.
							$("#'.$id.'").val(media_attachment.url);
							$(".'.$name.'_img").attr("src",$("#'.$id.'").val()).show();
							$(".'.$name.'_btn").css("display","none");
							$(".'.$name.'_btn_remove").css("display","block");
						
						});

						// Opens the media library frame.
						meta_image_frame.open();

					}); //end upload


					$(".'.$name.'_btn_remove").click(function (e) {
						e.preventDefault();
						$("#'.$id.'").attr("value","");
						$(".'.$name.'_img").attr("src","").hide();
						$(".'.$name.'_btn").css("display","block");
						$(".'.$name.'_btn_remove").css("display","none");
						
					}); //end remove

				}); '
				)
			)
		);
	};
}	 