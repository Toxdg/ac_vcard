<?php 

/**
* Plugin Name: AC vCard - Generator
* Plugin URI: https://tox.ovh
* Description: Generate vCard
* Version: 1.0
* Author: Tomasz Gołkowski
* Author URI: https://tox.ovh
**/

add_action( 'template_redirect', 'redirect_post_type_single' );
function redirect_post_type_single(){
    if ( ! is_singular( 'ac_vcard_cpt' ) )
        return;
    wp_redirect( get_home_url(), 301 );
    exit;
}
// Register post type
add_action('init', 'ac_vcard_cpt');
function ac_vcard_cpt() {
    $plugins_url = plugins_url();
    $labels = array(
        'name'               => __( 'AC vCard', 'ac_vcard' ),
        'singular_name'      => __( 'AC vCard', 'ac_vcard' ),
        'menu_name'          => __( 'AC vCard', 'ac_vcard' ),
        'name_admin_bar'     => __( 'AC vCard', 'ac_vcard' ),
        'all_items'          => __( 'AC vCard', 'ac_vcard' ),
    );
    $args = array(
        'labels' => $labels,
    	'public' => true,
		'publicly_queryable' => false,
		'query_var' => true,
    	'show_ui' => true,
        'exclude_from_search' => true,
    	'hierarchical' => false,
		'has_archive' => false, 
    	'supports' => array('title','revisions','thumbnail'),
        'menu_icon' => 'dashicons-editor-justify',
    );
    register_post_type( 'ac_vcard_cpt' , $args );
}

function ac_vcard_cpt_enqueue(){
	$screen = get_current_screen();
	$screen = $screen->post_type;
    if ($screen == "ac_vcard_cpt") {
		wp_register_script('ac_vcard_cpt_media_admin', plugin_dir_url(__FILE__) . 'js/media.js', array(
            'jquery',
		));
		wp_enqueue_script('ac_vcard_cpt_media_admin');
	}
}
add_action('admin_enqueue_scripts', 'ac_vcard_cpt_enqueue');

include 'meta_box.php';
require_once __DIR__ . '/vendor/autoload.php';
use JeroenDesloovere\VCard\VCard;

/*
*
* ===
* Router 
* ===
*
*/

// init router links
add_action( 'init', 'ac_vcard__api_router' );
function ac_vcard__api_router(){
    add_rewrite_rule( '^api_vcard', 'index.php?ac_vcard_api=1', 'top' );
}

// add to query vars
add_filter( 'query_vars', 'ac_vcard__add_api_slug' );
function ac_vcard__add_api_slug( $query_vars ){
    $query_vars[] = 'ac_vcard_api';
    return $query_vars;
}

// template loader
add_action( 'parse_request', 'ac_vcard__api_main_function' );
function ac_vcard__api_main_function( &$wp ){
    
    // template directory
    $template_dir = ac_bup__config()['plugin_path'].'templates/';
    // form url 
    $form_link_serv = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $form_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$form_link_serv;
    

    // API
    if ( array_key_exists( 'ac_vcard_api', $wp->query_vars ) ) {
		$next_link_part = ac_vcard__url_return_error($_SERVER['REQUEST_URI']);;
		// if next after user is get and after slash
		if($next_link_part === true){
			ac_vcard__page_404();
		}
		$nameUserCard = ac_vcard__urlParseToTemplate();
		$nameUserCard = str_replace('/api_vcard/', "", $nameUserCard['core_name']);
		$nameUserCard = explode("/", $nameUserCard)[0];

		$cpt_PostMeta = get_post_meta(ac_vcard_get_postid_by_slug($nameUserCard));
		//var_dump($cpt_PostMeta);
		
		// generate vcard
		$vcard = new VCard();
		// define variables
		$lastname = $cpt_PostMeta['ac_vcard__user_fname'][0];
		$firstname = $cpt_PostMeta['ac_vcard__user_lname'][0];
		$additional = '';
		$prefix = '';
		$suffix = '';

		// add personal data
		$vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);

		// add work data
		if($cpt_PostMeta['ac_vcard__user_cpompanyname'][0] != ''){
			$vcard->addCompany($cpt_PostMeta['ac_vcard__user_cpompanyname'][0]);
		}
		
		if($cpt_PostMeta['ac_vcard__user_titleposition'][0] != ''){
			$vcard->addJobtitle($cpt_PostMeta['ac_vcard__user_titleposition'][0]);
		}
		
		if($cpt_PostMeta['ac_vcard__user_emailadress'][0] != ''){
			$vcard->addEmail($cpt_PostMeta['ac_vcard__user_emailadress'][0]);
		}
		
		if($cpt_PostMeta['ac_vcard__user_emailadress'][0] != ''){
			$vcard->addEmail($cpt_PostMeta['ac_vcard__user_emailadress'][0]);
		}
		
		if($cpt_PostMeta['ac_vcard__user_webside'][0] != ''){
			$vcard->addURL($cpt_PostMeta['ac_vcard__user_webside'][0]);
		}
		
		if($cpt_PostMeta['ac_vcard__user_mobilenumber'][0] != ''){
			$vcard->addPhoneNumber($cpt_PostMeta['ac_vcard__user_mobilenumber'][0], 'PREF;WORK');
		}
		
		if($cpt_PostMeta['ac_vcard__user_phonenumber'][0] != ''){
			$vcard->addPhoneNumber($cpt_PostMeta['ac_vcard__user_phonenumber'][0], 'WORK');
		}
		if($cpt_PostMeta['ac_vcard__user_fulladress'][0] != ''){
			$vcard->addAddress(null, null, $cpt_PostMeta['ac_vcard__user_fulladress'][0], null, null, null, null);
		}
		if($cpt_PostMeta['ac_vcard__user_photo'][0] != ''){
			$vcard->addPhoto(wp_get_original_image_path($cpt_PostMeta['ac_vcard__user_photo'][0]));
		}
		
		//$vcard->addRole('Data Protection Officer');
		//$vcard->addEmail('info@jeroendesloovere.be');
		//$vcard->addPhoneNumber(1234121212, 'PREF;WORK');
		//$vcard->addPhoneNumber(123456789, 'WORK');
		//$vcard->addAddress(null, null, 'street', 'worktown', null, 'workpostcode', 'Belgium');
		//$vcard->addLabel('street, worktown, workpostcode Belgium');
		//$vcard->addURL('http://www.jeroendesloovere.be');

		//$vcard->addPhoto(__DIR__ . '/landscape.jpeg');

		// return vcard as a string
		//return $vcard->getOutput();

		// return vcard as a download
		//return $vcard->download();
		
		$rootDir = plugin_dir_path(__FILE__).'cards/';
		if (!file_exists($rootDir)) {
			mkdir($rootDir, 0777, true);
		}
		$file = $firstname.'-'.$lastname.'.vcf';
		file_put_contents($rootDir.$file, $vcard->getOutput());
		header("Location: ".plugin_dir_url(__FILE__).'cards/'.$file);
		exit();
    }           
    return;
}

/* ROUTER END */

/* ROUTER FUNCTION */
// Parse url to template file name
function ac_vcard__urlParseToTemplate(){
	$site = str_replace("http://", "", get_site_url());
	$site = str_replace("https://", "", $site);
	$requestURI = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$returnURL = str_replace($site, "", $requestURI);
	$coreName = $returnURL;
	
	$returnURL = explode("?", $returnURL);
	
	$returnURL = explode("/", $returnURL[0]);
	$template_name = '';
	$index = 0;
	foreach ($returnURL as $key => $link) {
		if($link != ''){
			if($index == 0){
				$template_name = $link;
			}else{
				$template_name .= '_'.$link;
			}
			$index++;
		}
	}
	return array(
		'core_name' => $coreName,
		'tamplate' => $template_name.'.php', 
		'url' => get_site_url(),
		'requestURI' => $_SERVER['REQUEST_URI'],
	);
	
}

// page 404 template
function ac_vcard__page_404(){
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}
// validate url
function ac_vcard__url_return_error($url){
	$url_array = explode("/", $url);
	$index = 0;
	foreach ($url_array as $value) {
		// last should be get parameter
		if(strpos($value, '?') !== false) {
			$index = 1;
		}else{
			// if before last section url is get
			if($index == 1){
				return true;
			}
		}
	}
	return false;
}