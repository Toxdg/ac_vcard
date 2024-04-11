<?php 
/**
* Plugin Name: AC vCard - Generator
* Plugin URI: https://tox.ovh
* Description: Generate vCard
* Version: 1.0
* Author: Tomasz Gołkowski
* Author URI: https://tox.ovh
**/

function ac_vcard_config(){
	$qrCodeApiQRserver = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=';
	$qrCodeApiGoogle = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=';
	$qrCodeApi = $qrCodeApiQRserver;
	$apiUrl = get_home_url().'/api_vcard/';
	$rootDir = plugin_dir_path(__FILE__).'cards/';
	$rootURL = plugin_dir_url(__FILE__).'cards/';

	$returnArray = array(
		"qrCodeApi" => $qrCodeApi,
		"apiUrl" => $apiUrl,
		"rootDir" => $rootDir, 
		"rootUrl" => $rootURL,
	);
	return $returnArray;
}


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

// columns list
add_filter('manage_ac_vcard_cpt_posts_columns', 'ac_vcard_cpt_posts_columns');

function ac_vcard_cpt_posts_columns($columns) {
    $columnsMod = array();
    $index = 0;
    foreach ($columns as $key => $singleColum) {
        $columnsMod[$key] = $singleColum;
		if ($index == 0) {
			// before post title 
		}
        if ($index == 1) {
			// after post title 
			$columnsMod['ac_post_id'] = "Post Data";
			$columnsMod['photo'] = "Photo";
            $columnsMod['qrcode'] = "API url qrcode";
			$columnsMod['qrcode_src'] = "API url src";
			$columnsMod['image_full'] = "Full qrcode";
			$columnsMod['image_full_src'] = "Full qrcode src";
			$columnsMod['image'] = "Image embed";	
        }
		if ($index == 2) {
			//after post date
		}
        $index++;
    }
    return $columnsMod;
}

add_action('manage_ac_vcard_cpt_posts_custom_column', 'custom_ac_vcard_cpt_column', 10, 2);

function custom_ac_vcard_cpt_column($column, $post_id) {
    $meta = get_post_meta($post_id);
    switch ($column) {
		case 'ac_post_id':
			$postData = get_post($post_id);
            echo '<b>id:</b> '.$post_id;
			echo '<br><b>slug:</b> '.$postData->post_name;
			echo '<br><b>api url:</b> '.ac_vcard_config()['apiUrl'].$postData->post_name;
            break;
        case 'qrcode':
			$postData = get_post($post_id);
			$apiUrl = ac_vcard_config()['apiUrl'].$postData->post_name;
			//echo '<img width="100px" height="100px" src="'..'">';
            echo '<img width="180px" height="180px" src="'.ac_vcard_config()['qrCodeApi'].$apiUrl.'">';
			//echo '<br>'.ac_vcard_config()['qrCodeApi'].$apiUrl;
            break;
		case 'qrcode_src':
			$postData = get_post($post_id);
			$apiUrl = ac_vcard_config()['apiUrl'].$postData->post_name;
			echo '<br>'.ac_vcard_config()['qrCodeApi'].$apiUrl;
            break;
		case 'photo':
			$cpt_PostMeta = get_post_meta($post_id);
			if($cpt_PostMeta['ac_vcard__user_photo'][0] != ''){
				echo "<img width='180px' height='180px' src='".wp_get_attachment_url($cpt_PostMeta['ac_vcard__user_photo'][0])."'>";
			}else{
				echo "no image";
			}
            break;
		case 'image_full':
			$cpt_PostMeta = get_post_meta($post_id);
			$fileName = ac_vcard__GetFileNameCore($post_id).'.png';	
			if (file_exists(ac_vcard_config()["rootDir"].$fileName)) {
				echo '<img width="180px" height="180px" src="'.ac_vcard_config()["rootUrl"].$fileName.'">';
				//echo '<br>'.ac_vcard_config()["rootUrl"].$fileName;
			}else{
				echo "no image";
			}
            break;
		case 'image_full_src':
			$cpt_PostMeta = get_post_meta($post_id);
			$fileName = ac_vcard__GetFileNameCore($post_id).'.png';	
			if (file_exists(ac_vcard_config()["rootDir"].$fileName)) {
				echo '<br>'.ac_vcard_config()["rootUrl"].$fileName;
			}else{
				echo "no image";
			}
            break;
		case 'image':
			$postData = get_post($post_id);
			$fileName = ac_vcard__GetFileNameCore($post_id).'.png';
			echo 'Api URL: <br><textarea><img width="100px" height="100px" src="'.ac_vcard_config()['qrCodeApi'].$postData->post_name.'"></textarea>';
			echo '<br><br>Full Data: <br><textarea><img style="max-width:200px; height:auto;" src="'.ac_vcard_config()['rootUrl'].$fileName.'"></textarea>';
            break;
    }
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
require_once __DIR__ . '/vendor/phpqrcode/qrlib.php';
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
    //$template_dir = ac_bup__config()['plugin_path'].'templates/';
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
		
		$cptUserPostID = ac_vcard_get_postid_by_slug($nameUserCard);
		$cpt_PostMeta = get_post_meta($cptUserPostID);
		
		ac_vcard__generateVCF($cptUserPostID);
		
		$fileName = ac_vcard__GetFileNameCore($cptUserPostID);
		
		$fileVCF = $fileName.'.vcf';
		
		header("Location: ".plugin_dir_url(__FILE__).'cards/'.$fileVCF);
		exit();
    }           
    return;
}

/* ROUTER END */

/* ROUTER FUNCTION */
// sanitize_filename
function ac_vcard__sanitizeFilename($fileName){
	$fileName = str_replace(' ', '', $fileName);
	$fileName = ac_vcard__sanitizeString($fileName);
	return $fileName;
}

function ac_vcard__sanitizeString($string){
	$input = array('ą', 'Ą', 'ć', 'Ć', 'ę', 'Ę', 'ł', 'Ł', 'ń', 'Ń', 'ó', 'Ó', 'ś', 'Ś', 'ź', 'Ź', 'ż', 'Ż');
	$output = array('a', 'A', 'c', 'C', 'e', 'E', 'l', 'L', 'n', 'N', 'o', 'O', 's', 'S', 'z', 'Z', 'z', 'Z');
	$stringReturn = str_replace($input, $output, $string);
	return $stringReturn;
}

// get core filename
function ac_vcard__GetFileNameCore($acPostId){
	$cpt_PostMeta = get_post_meta($acPostId);
	$lastname = $cpt_PostMeta['ac_vcard__user_fname'][0];
	$firstname = $cpt_PostMeta['ac_vcard__user_lname'][0];
	$fileName = strtolower($firstname.'-'.$lastname);
	$fileName = ac_vcard__sanitizeFilename($fileName);
	return $fileName;
}

// generate VCF
function ac_vcard__generateVCF($cptUserPostID){
	
	$rootDir = plugin_dir_path(__FILE__).'cards/';
	$rootDirIndex = $rootDir.'index.php';
	if (!file_exists($rootDir)) {
		mkdir($rootDir, 0777, true);

	}
	if (!file_exists($rootDirIndex)) {
		file_put_contents($rootDirIndex, '<?php // silence ?>');
	}
	
	$cpt_PostMeta = get_post_meta($cptUserPostID);
	// generate vcard
	$vcard = new VCard();
	// define variables
	$lastname = $cpt_PostMeta['ac_vcard__user_fname'][0];
	$firstname = $cpt_PostMeta['ac_vcard__user_lname'][0];
	$additional = '';
	$prefix = '';
	$suffix = '';

	$tmpName = $lastname.' '.$firstname;
	// add personal data
	$vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);
	//$vcard->addName($tmpName, '('.$cpt_PostMeta['ac_vcard__user_cpompanyname'][0].')', $additional, $prefix, $suffix);
	
	// add work data
	if($cpt_PostMeta['ac_vcard__user_cpompanyname'][0] != ''){
		$vcard->addCompany($cpt_PostMeta['ac_vcard__user_cpompanyname'][0]);
	}
	
	if($cpt_PostMeta['ac_vcard__user_titleposition'][0] != ''){
		$vcard->addRole($cpt_PostMeta['ac_vcard__user_titleposition'][0]);
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
		$addressPart = $cpt_PostMeta['ac_vcard__user_fulladress'][0];
		$addressPart = ac_vcard__sanitizeString($addressPart);
		$addressPart = explode(",", $addressPart);
		$vcard->addAddress(null, null, $addressPart[0], $addressPart[2], null, $addressPart[1], $addressPart[3]);
		
		//$vcard->addAddress(null, null, $cpt_PostMeta['ac_vcard__user_fulladress'][0], null, null, null, null);
	}
	
	if($cpt_PostMeta['ac_vcard__user_photo'][0] != ''){
		//$vcard->addPhoto(wp_get_original_image_path($cpt_PostMeta['ac_vcard__user_photo'][0]));
	}
	
	//$vcard->addJobtitle($cpt_PostMeta['ac_vcard__user_titleposition'][0]);
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
	
	
	$fileName = ac_vcard__GetFileNameCore($cptUserPostID);
	$fileVCF = $fileName.'.vcf';
	
	if (file_exists($rootDir.$fileVCF)) {
		if(date ("YmdHis", filemtime($rootDir.$fileVCF)) <=  $cpt_PostMeta['ac_vcard__date_update'][0]){
			file_put_contents($rootDir.$fileVCF, $vcard->getOutput());
		}
	}else{
		file_put_contents($rootDir.$fileVCF, $vcard->getOutput());
	}
	
	$imageFile = $fileName.'.png';
	// https://codedamn.com/news/web-development/php-qr-code-generator-with-examples
	QRcode::png($vcard->getOutput(), $rootDir.$imageFile);
	
	
	$postData = get_post($cptUserPostID);
	$apiUrl = ac_vcard_config()['apiUrl'].$postData->post_name;
	$imageFile = $fileName.'-url.png';
	QRcode::png($apiUrl, $rootDir.$imageFile);
}

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

/* CRON FUNCTION */
// init cron function
add_action( 'init', 'ac_vcard__removeOldFiles', 0 );
function ac_vcard__removeOldFiles(){
	if (isset($_GET["ac_vcard_cron"]) && $_GET["ac_vcard_cron"] === 'true') {
        ac_vcard__removeOldFilesCleaner();
    }
    $cron_day = intval(get_option('ac_vcard_cron_day'));
    $currentDate = date("Ymd");
    if($currentDate > $cron_day){
        ac_vcard__removeOldFilesCleaner();
        update_option( 'ac_vcard_cron_day', $currentDate );
    }
}

// main cleaner
function ac_vcard__removeOldFilesCleaner(){
	$args = array(
        'post_type' => 'ac_vcard_cpt',
        'posts_per_page' => -1,
    );
	$fileArray = array();
	$fileArray[] = 'index.php';
    $ac_vcard_cpt_list = new WP_Query( $args );
	if ($ac_vcard_cpt_list->have_posts()):
        while ( $ac_vcard_cpt_list->have_posts() ) : $ac_vcard_cpt_list->the_post();
			$acPostId = get_the_ID();
			
			$fileName = ac_vcard__GetFileNameCore($acPostId);
			$fileArray[] = $fileName.'.vcf';
			$fileArray[] = $fileName.'.png';
			$fileArray[] = $fileName.'-url.png';
			
		endwhile;                
    endif;
	wp_reset_query();
	$filesDir = scandir(ac_vcard_config()['rootDir']);
	$filesDir = array_diff($filesDir, array('.', '..'));
	
	foreach($filesDir as $key => $TMPfile) {
		if(!in_array($TMPfile, $fileArray)) {
			unlink(ac_vcard_config()['rootDir'].$TMPfile);
		}
	}
}


