<?php
/**
 * @deprecated @package royalBox.php 
 * @deprecated @version 1.0.0
 * 
 * @method text { placeholder }  @todo make a new option for email text / pass text
 * @method textarea { rows }   @todo add more textarea_arguments and fallbacks to default if null
 * 
 * need more love to this:
 * - set div+css wrapper
 * - add width option (+fallback)
 * - rebuild return for new generator version (+label)
 * @method select { value(opt) | options=>opt_value } 
 * @method checkbox { value(opt) | options=>opt_value } // bug not savings all checks
 * @method color { value(opt) }  // bug not showing color piker
 * @method file (need more js logic) // bug not showing brows button
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
					array('strong', $data['label'])		
			);
		} else $_label = '';

		return array( 
			array('div',
				'style' =>	'width: calc('. $width . '% - 20px); 
							float:left; 
							vertical-align: top;
							margin: 0 10px;',
				$_label,
				array('input', 
					'style' => 'width:100%',
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
				'label', 'for' => 'wp-'.$name.'-wrap',
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
						float:left; 
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
		$label = @$data['label'];
		
		// selected
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : ''; //@todo daca nu are optiuni declarate....
		$options = array();

		foreach ($data['options'] as $opt_name => $opt_value) {
			
			$option = array('option', $opt_value);

			// value attribute
			if (is_string($opt_name)) {
				$option['value'] = $opt_name;
			}

			// set selected attribute
			if ((is_string($opt_name) && $opt_name === $opt_valueue) || (!is_string($opt_name) && ($opt_name === $opt_valueue || $opt_value === $opt_valueue))) {
				$option['selected'] = '';
			}

			// add to main option array
			array_push($options, $option);
		}

		// wrapped options
		$layout = array(
			array('p',
				array_merge(
					array(
						'select', 
						'id' => $id, 
						'name' => $name
						), 
					$options
				)
			)
		);

		// label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}

		return $layout;
	};

	// <input type="checkbox">
	RoyalBox::$create_field->checkbox = function ($name, $data) {
		// cache id, label
		$id = $name.'_id';
		$label = @$data['label'];
		// assign first value (even if blank) or blank
		$opt_valueue = isset($data['value']) ? $data['value'] : array();
		// initialize options collection, index
		$options = array();
		$index = 0;
		// for each option
		foreach ($data['options'] as $opt_name => $opt_value) {
			// create option field
			$checkbox = array('input', 'type' => 'checkbox', 'name' => $name, 'id' => $name.$index);
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
			// advance index
			++$index;
		}
		// create wrapped options collection
		$layout = array(
			array_merge(array('p'), $options)
		);

		// label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('strong', $data['label'])
			));
		}

		return $layout;
	};

	// <input type="color">
	RoyalBox::$create_field->color = function ($name, $data) {
		// cache id, label
		$id = $name.'_id';
		$label = @$data['label'];
		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';
		// create wrapped text field
		$layout = array(
			array('p',
				array('input', 'type' => 'color', 'id' => $id, 'name' => $name, 'value' => $opt_valueue),
				array('script', 'jQuery(function($){$("#'.$id.'").wpColorPicker()})')
			)
		);

		// label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		return $layout;
	};

	// <input type="file">
	RoyalBox::$create_field->file = function ($name, $data) {
		// cache id, label
		$id = $name.'_id';
		$label = @$data['label'];
		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';
		// create wrapped text field
		$layout = array(
			array('p',
				array('input', 'type' => 'text', 'id' => $id, 'name' => $name, 'value' => $opt_valueue),
				array('script', 'jQuery(function ($) {
					var meta_image_frame;
					$("#'.$id.'").click(function (e) {
						e.preventDefault();
						// If the frame already exists, re-open it.
						if ( meta_image_frame ) {
							wp.media.editor.open();
							return;
						}
						// Sets up the media library frame
						meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
							title: meta_image.title,
							button: { text:  meta_image.button },
							library: { type: "image" }
						});
						// Runs when an image is selected.
						meta_image_frame.on("select", function () {
							// Grabs the attachment selection and creates a JSON representation of the model.
							var media_attachment = meta_image_frame.state().get("selection").first().toJSON();
							// Sends the attachment URL to our custom image input field.
							$("#meta-image").val(media_attachment.url);
						});
						// Opens the media library frame.
						wp.media.editor.open();
					});
				});')
			)
		);

		// label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}
		wp_enqueue_style('wp-media-upload');
		wp_enqueue_script('wp-media-upload');

		return $layout;
	};
}