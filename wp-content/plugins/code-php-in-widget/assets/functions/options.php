<?php
/**
 * Options Plugin
 * Make configutarion
*/
if ( !class_exists('code_php_in_widget_make') ) {
class code_php_in_widget_make extends WP_Widget{

	// always variables
	var $default          = array();
	var $default_validate = array();
	var $widget_ops       = array();

	var $parameter        = null;
	var $ME               = null;


	function print_script_footer_widget(){ 
		global $IF; 
		$IF->ilen_print_script_footer_widget( array('color') , $this->widget_ops['classname'], (int)$this->number  ); 
	}



	function __construct(){

		// always variables
		global $IF; 
		$this->parameter  = isset($IF->parameter)?(array)$IF->parameter:null;
		$this->ME = $IF;


		// Script need for widgets
		add_action( 'admin_footer-widgets.php', array( $this, 'print_script_footer_widget' ), 9999 );
		/* NO REMOVE **********************************************************/

		if( is_admin() )
			self::configuration_plugin();
		else
			self::parameters();

		// Widget Builder.
		// Widget Builder.
		$this->widget_ops = array(  'classname'     => 'widget_code_php_in_widget',
									'description'   => 'Run your PHP code within a Widget correctly.' );
		//$this->WP_Widget('widget_code_php_in_widget', "Code PHP in Widget", $widget_ops);
		parent::__construct('widget_code_php_in_widget', "Code PHP in Widget", $this->widget_ops);


		// default inputs
		$this->default 					 = array( 'code'                     => '<?php // my code php ?>',
											      'title_top'					 => '',
											      'add_paragraphs'			 => '' );

		// validate inputs
		$this->default_validate =array( 'code'                      => 's',
										'title_top'						=> 's',
										'add_paragraphs'			=> 's');



	}

	function form($instance){
 
		if( ! $instance ){ // only validate once (when widget is added)
			$instance = wp_parse_args( (array) $instance, $this->default ); 
		}
 
		// variable's widget
		extract( $instance );
		

		// set header widget
		$widget_conf = array(
							'id'          => 'code_php_in_widget',
							'description' => 'Widget is created by <a href="http://ilentheme.com" target="_blank">iLen</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd =_s-xclick&hosted_button_id=49DZQBQ96UAPU" target="_blank">Donate</a> | <a href="http://support.ilentheme.com/" target="_blank">Support</a>',
							'color'       => '#E2B096', // #96E2B2
							'width'       => '230',
							);
	  
		// validate if new widget or not
		$widget_conf['new'] = !(int)$this->number ? 'new_widget_'.rand(11,5559) : 'no_new_widget '.$widget_conf['id'].'-'.$this->number;
		$status_widget      = $widget_conf['ref'] = (  (int)$this->number > 0 ) ? $widget_conf['id'].'-'.$this->number : $widget_conf['new'];

		// set body widget
		$widget_fields = array('a'=>array(          'title'      => __('Options',$this->parameter['name_option']), 
													'title_large'=> __('',$this->parameter['name_option']), 
													'description'=> '', 
													'icon'       => '',

													'options'    => array(


																		array(  'title' =>__('Title',$this->parameter['name_option']),
																				'help'  =>__('Title of widget',$this->parameter['name_option']),
																				'type'  =>'text',
																				'value' =>$title_top,
																				'id'    =>$this->get_field_id('title_top'),
																				'name'  =>$this->get_field_name('title_top'),
																				'class' =>'',
																				'row'   =>array('a','b')),
																		 
																		array(  'title' =>__('Code',$this->parameter['name_option']),
																				'help'  =>__('Enter your PHP code',$this->parameter['name_option']),
																				'type'  =>'textarea',
																				'value' =>$code,
																				'id'    =>$this->get_field_id('code'),
																				'name'  =>$this->get_field_name('code'),
																				'class' =>'widget_input_code_php_in_widget_code',																		  
																				'row'   =>array('a','b')),

																		array(  'title' =>__('Add paragraphs automatically',$this->parameter['name_option']),
																				'help'  =>__('Changes double line-breaks in the text into HTML paragraphs <code>< p ></code>',$this->parameter['name_option']),
																				'type'  =>'checkbox', //type input configuration
																				'value' =>( isset($add_paragraphs) && $add_paragraphs)?$add_paragraphs:0,
																				'id'    =>$this->get_field_id('add_paragraphs'),
																				'name'  =>$this->get_field_name('add_paragraphs'),
																				'class' =>'', //class
																				'row'   =>array('a','b')),


																	)
										  )
							);

		$this->ME->create_ilenWidget( $widget_conf , $widget_fields );
		echo '<style>
	[id*=_widget_code_php_in_widget-] .widget-title h3{ padding-left:32px; }
	.widget_input_code_php_in_widget_code textarea{ height:300px!important; }
	div.widget[id*=_widget_code_php_in_widget-] .widget-title::before {
		content: "\f121";
		font-family: "fontawesome";
	}	
	</style>';


	}


	function update($new_instance, $old_instance){

		$update = array();

		//var_dump( $new_instance );
		
		//In the examples of what fields checkbox come without that key, then you can not parse the defects since it back on and it makes you save them wrong.
		foreach ($new_instance as $key => $value)  $update[$key] = $this->ME->ilenwidget_validate_inputs_ext( ($value),$this->default_validate[$key] ); 
		//var_dump(  $update );
		//exit;
		
		return $update;
	  
	}

	function widget($args,$instance){

		global $if_utils,$option_widget,$CODE_PHP_IN_WIDGET;
		// get Option widget
		$option_widget = json_decode (json_encode ($instance), FALSE);
		//var_dump($option_widget->code);
		$option_widget->code = htmlspecialchars_decode( $option_widget->code, ENT_QUOTES );
		//var_dump($option_widget->code);
		$option_widget->code = apply_filters( 'widget_code_php_in_widget', $option_widget->code , $instance );
 		?>

		 <div id="<?php echo $args['widget_id'] ?>" class="widget code_php_in_widget" data-version="<?php echo $CODE_PHP_IN_WIDGET->parameter["version"]; ?>">
		 	<h3 class="widget-title widget_title">
		 		<span><?php echo $if_utils->IF_setHtml($option_widget->title_top); ?></span>
		 	</h3>
			<div>
				<?php
					ob_start();
					eval( '?>' .  $option_widget->code );
					$option_widget->code = ob_get_contents();
					ob_end_clean();
				?>
				<div class="execphpwidget"><?php echo isset($instance['add_paragraphs']) && $instance['add_paragraphs'] ? wpautop($option_widget->code) : $option_widget->code; ?></div>
			</div>
		</div> <?php
	}




	function getHeaderPlugin(){
		//code 

		return array( 'id'            =>'code_php_in_widget_id',
					 'id_menu'        =>'',
					 'name'           =>'code_php_in_widget',
					 'name_long'      =>'',
					 'name_option'    =>'code_php_in_widget',
					 'name_plugin_url'=>'code-php-in-widget',
					 'descripcion'    =>'',
					 'version'        =>'1.4',
					 'url'            =>'',
					 'logo'           =>'',
					  // or image .jpg,png | use class 'text-long' in case of name long
					 'logo_text'      =>'', // alt of image
					 'slogan'         =>'', // powered by <a href="">iLenTheme</a>
					 'url_framework'  =>plugins_url()."/code-php-in-widget/assets/ilenframework",
					 'theme_imagen'   =>plugins_url()."/code-php-in-widget/assets/images",
					 'twitter'        =>'',
					 'wp_review'      =>'',
					 'link_donate'    =>'',
					 'wp_support'     =>'',
					 'type'           =>'plugin',
					 'method'         =>'free',
					 'themeadmin'     =>'fresh',
					 'scripts_admin'  =>array( 'widgets'     => array(), ),
					 'no_menu'        => 1);

	}

	function getOptionsPlugin(){  
	}









	function parameters(){

		$this->parameter = self::getHeaderPlugin();

	}



	function myoptions_build(){

		$this->options = self::getOptionsPlugin();

		return $this->options;
		
	}

	function use_components(){
		//code 
		$this->components = array();

	}

	function configuration_plugin(){
		// set parameter 
		self::parameters();

		// my configuration 
		self::myoptions_build();

		// my component to use
		self::use_components();
	}



}
}
add_action( 'widgets_init', create_function('', 'return register_widget("code_php_in_widget");') ); 
global $widget_code_php_in_widget;
$widget_code_php_in_widget = new code_php_in_widget_make;
?>