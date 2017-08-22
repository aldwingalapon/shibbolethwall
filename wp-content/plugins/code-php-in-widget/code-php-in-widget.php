<?php
/* 
Plugin Name: Code PHP in Widget
Plugin URI: https://wordpress.org/plugins/code-php-in-widget/
Description: Run your PHP code within a Widget correctly.
Version: 1.5
Author: iLen
Author URI: http://ilentheme.com
*/
if ( !class_exists('code_php_in_widget') ) {

require_once 'assets/ilenframework/assets/lib/utils.php'; // get utils
require_once 'assets/functions/options.php'; // get options plugins

class code_php_in_widget extends code_php_in_widget_make{

	function __construct(){ parent::__construct(); }
 
} // end class
} // end if
global $IF_CONFIG, $CODE_PHP_IN_WIDGET;
unset($IF_CONFIG);
$IF_CONFIG = null;
$IF_CONFIG = $CODE_PHP_IN_WIDGET = new code_php_in_widget;
require_once "assets/ilenframework/core.php";
?>