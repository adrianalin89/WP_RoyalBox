<?php
/**
 * @deprecated @package royalBox.php 
 * @deprecated @version 1.0.0
 * 
 * @method text { placeholder } 
 * @method textarea { rows }  
 * @method select { value(opt) | options=>opt_value } 
 * @method checkbox { value(opt) | options=>opt_value } 
 * @method color piker { value(opt) } 
 * @method file
 * @method image gallery
 * @method radio
 * @method time piker
 * @method date piker
 * @method page / post / custom_post piker|list
 * @method imbeded video
 * @method @todo bulk sections @todo grup controls @todo sub-grup inside a grup
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
						'style' => 'display: none; width: 50%;'
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
						'style' => 'display: block; width: 50%;'
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

					$("body").on("click", ".'.$name.'_btn", function(e) { 
					
						e.preventDefault();
						// If the frame already exists, re-open it.
						if ( meta_image_frame ) {
							meta_image_frame.open();
							return;
						}

						// Sets up the media library frame
						meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
							title: "Media",
							button: { text:  "load" },
							library: { type: "image" }
						});
						
						// Runs when an image is selected.
						meta_image_frame.on("select", function () {

							// Grabs the attachment selection and creates a JSON representation of the model.
							var media_attachment = meta_image_frame.state().get("selection").first().toJSON();

							// Sends the attachment URL to our custom image input field.
							$("#'.$id.'").val(media_attachment.url);
							$(".'.$name.'_img").attr("src",$("#'.$id.'").val()).css("display", "block");
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

	// <input type="gallery">
	RoyalBox::$create_field->gallery = function ($name, $data) {
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

		$add_img_btn = array ('input',
				'type'  => 'button',
				'class' => $name.'_btn button',
				'value' => 'Upload',
				'style' => 'display: block;'
		);

		$meta_gallery_container = array('span',
				'id' => $name.'_gallery_container'
		);

		if ($opt_valueue) {
			$meta_html = array('ul',
								 'class' => $name.'_gallery_list');
			$meta_array = explode(',', $opt_valueue);
			foreach ($meta_array as $meta_gall_item) {
					$meta_html =  array_merge($meta_html, array(
															array('li', 
																array('img',
																		'id'  => esc_attr($meta_gall_item),
																		'src' => wp_get_attachment_thumb_url($meta_gall_item)
																),
																array('input',
																		'type'  => 'button',
																		'class' => $name.'_btn_remove button',
																		'id'    => 'remove_'.esc_attr($meta_gall_item),
																		'value' => 'Remove'
																)
															)
														)
												);
			}
		}

		if ( '' == $opt_valueue ) $layout = array_merge($layout, array(	$add_img_btn, $meta_gallery_container)); 
		else $layout = array_merge(
			$layout, array(
				$add_img_btn,
				$meta_html,
				$meta_gallery_container
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

					$("body").on("click", ".'.$name.'_btn", function(e) { 
					
						e.preventDefault();

						// If the frame already exists, re-open it.
						if ( meta_image_frame ) {
							meta_image_frame.open();
							return;
						}

						// Sets up the media library frame
						meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
							title: "gallery",
							button: { text:  "load" },
							library: { type: "image" },
                        	multiple: true
						});

						// Create Featured Gallery state. This is essentially the Gallery state, but selection behavior is altered.
						meta_image_frame.states.add([
							new wp.media.controller.Library({
									id:         "'.$name.'-portfolio-gallery",
									title:      "Select Images for Gallery",
									priority:   20,
									toolbar:    "main-gallery",
									filterable: "uploaded",
									library:    wp.media.query( meta_image_frame.options.library ),
									multiple:   meta_image_frame.options.multiple ? "reset" : false,
									editable:   true,
									allowLocalEdits: true,
									displaySettings: true,
									displayUserSettings: true
							}),
						]);
				
						// Runs when an image is selected.
						meta_image_frame.on("open", function () {
							var selection = meta_image_frame.state().get("selection");
							var library = meta_image_frame.state("gallery_edit").get("library");
							var ids = $("#'.$id.'").val();
							if( ids ) {
								idsArray = ids.split(",");
								idsArray.forEach(function(id){
									attachment = wp.media.attachment(id);
									attachment.fetch();
									selection.add( attachment ? [ attachment ] : [] );
								});
							}
						});

						meta_image_frame.on("select", function () {
							var imageIDArray = [];
							var imageHTML = "";
							var metadataString = "";

							images =  meta_image_frame.state().get("selection");
							imageHTML += "<ul>";
							images.each(function(attachment){
								imageIDArray.push(attachment.attributes.id);
								imageHTML += "<li><img id="+attachment.attributes.id+" src="+attachment.attributes.sizes.thumbnail.url+"></li>";
							});
							imageHTML += "</ul>";
							metadataString = imageIDArray.join(",");
							if( metadataString ) {
								$("#'.$id.'").val( metadataString );
								$(".'.$name.'_gallery_list").remove();
								$("#'.$name.'_gallery_container").html(imageHTML);
							}
						
						});

						// Opens the media library frame.
						meta_image_frame.open();
						
					}); //end upload


					$(".'.$name.'_btn_remove").click(function (e) {
						e.preventDefault();
	
						var removedImage = $(this).attr("id").replace("remove_","");
						var oldGallery = $("#'.$id.'").val();
						var newGallery = oldGallery.replace(","+removedImage,"").replace(removedImage+",","").replace(removedImage,"");

						// got to remove the id of the image from the hided input
                        $("#'.$id.'").val(newGallery);
						// go to remove the button
						$(this).remove();
						// go to remove the image
						$("#"+removedImage).remove();
						
					}); //end remove

				}); '
				)
			)
		);
	};

	// <input type="date">
	RoyalBox::$create_field->date = function ($name, $data) {
		$id = $name.'_id';
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
		wp_enqueue_script('jquery-ui-datepicker');
		return array( 
			array('div',
				'style' =>	'width: calc('. $width . '% - 20px); 
							display:inline-block; 
							vertical-align: top;
							margin: 0 10px;',
				$_label,
				array('input', 
					'style' => 'margin-bottom: 20px;margin-left:10px;',
						'type' => 'text', 
						'id' => $id, 
						'name' => $name, 
						'value' => $opt_valueue,
						'size' => "30"
				),
				array('script', 
				'jQuery(function ($) {
					$("#'.$id.'").datepicker();
				})'
				),
				array('style',
				'#ui-datepicker-div{
					background-color: #fff;
					padding: 20px;
					border: solid 2px #f5f5f5;
				}
				.ui-datepicker-next span{ float: right; }'
				)
			)
		);
	};

	// <input type="time">
	RoyalBox::$create_field->time = function ($name, $data) {
		$id = $name.'_id';
		$width = isset($data['width']) ? $data['width'] : '100';
		$min = isset($data['min']) ? $data['min'] : '00:00';
		$max = isset($data['max']) ? $data['max'] : '23:59';
		
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
		wp_enqueue_script('jquery-ui-datepicker');
		return array( 
			array('div',
				'style' =>	'width: calc('. $width . '% - 20px); 
							display:inline-block; 
							vertical-align: top;
							margin: 0 10px;',
				$_label,
				array('input', 
					'style' => 'margin-bottom: 20px;margin-left:10px;',
						'type' => 'time', 
						'id' => $id, 
						'name' => $name, 
						'value' => $opt_valueue,
						'min' => $min,
						'max' => $max
				)
			)
		);
	};

	// <input type="radio">
	RoyalBox::$create_field->radio = function ($name, $data) {
		$id = $name.'_id';
		$label = @$data['label'];
		$width = isset($data['width']) ? $data['width'] : '100';

		$opt_valueue = isset($data['value']) ? $data['value'] : array();
		
		$options = array();
		$index = 0;
		foreach ($data['options'] as $opt_name => $opt_value) {
		
			// create option field
			$radio = array('input', 'type' => 'radio', 'name' => $name, 'id' => $name.$index);

			// create label
			$label    = array('label', 'for' => $name.$index, $opt_value);

			// set value attribute as option key or option value
			$radio['value'] = is_string($opt_name) ? $opt_name : $opt_value;

			// conditionally set checked attribute
			if (in_array($opt_name, $opt_valueue, true) || in_array($opt_value, $opt_valueue, true)) {
				$radio['checked'] = '';
			}

			// add to options collection
			array_push($options, array('p', $radio, $label));
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

	// <select post>
	RoyalBox::$create_field->selectpost = function ($name, $data) {
		$id = $name.'_id';
		$width = isset($data['width']) ? $data['width'] : '100';
		$post_type = isset($data['post_type']) ? $data['post_type'] : 'post';
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';
		$options = array();
		
		$posts_option = get_posts( array (
			'post_type'	=> $post_type,
			'posts_per_page' => -1
		));

		foreach ($posts_option as $opt_name) {
			$option = array('option', 
							'value' => $opt_name->ID,
							$opt_name->post_type.': '.$opt_name->post_title
			);

			if ($opt_valueue == $opt_name->ID) {
				$option['selected'] = '';
			}

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

	// <input type="oEmbed">
	RoyalBox::$create_field->embed = function ($name, $data) {
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
		$embed_code = wp_oembed_get($opt_valueue, array('width'=>400)); 
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
				),
				$embed_code
			)
		);

	};
}	 