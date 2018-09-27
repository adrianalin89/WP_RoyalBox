# WP_RoyalBox
WordPress custom metabox factory

#1. Add in function.php 
```
require_once get_template_directory().'royalBox.php';
```

#2. Create a new RoyalBox obj

##2.1 via JSON

Create a json file like this:
 ```
 {
  "screens" : "page",
  "template" : "homepage-template.php",
	"title"   : "Custom Royal Box Section -Testing-",
	"context" : "normal",
	"fields"  : {
		"text_testing" : {
			"label" : "The text field",
			"type" : "text",
			"placeholder" : "This is a placeholder text",
			"width" : 50
        },
		"textarea_testing" : {
			"label" : "The textarea field",
			"type" : "textarea",
			"rows" : 20,
			"width" : 50
        }
    }
  }
```

Create a obj bay adding this in function.php

  ```
  $rb = RoyalBox::load('test_json.json');
  ```

##2.2 via PHP

You can pass all as one big array
```
$rb = new RoyalBox(array(
	'screens' => 'page',
	'template' => 'homepage-template.php', // optional
	'title'   => 'Custom Royal Box Section -Testing-',
	'context' => 'normal',
	'fields'  => array(
		'text_testing' => array(
			'label' => 'The text field',
			'type' => 'text',
			'placeholder' => 'This is a placeholder text'
		),
		'textarea_testing' => array(
			'label' => 'The textarea field',
			'type' => 'textarea',
			'placeholder' => 'asd',
			'rows' => 20,
			'cols' => 10,
			'max'  => 144,
		),
		
	)
));
```

or

```
 $rb = new RoyalBox();
 $rb->add_screen('post');
 $rb->set_title('Title');
 $rb->set_context('side');
 $rb->set_priority();
 $rb->add_field('string', array( ));
 ```
#3. You can make custom field in function using 
```
RoyalBox::$create_field->custom_field_name = function ($name, $data) { 
//layot logic see fields file for more structure information
}
$rb->add_field('custom_field_name', array( ));
```
