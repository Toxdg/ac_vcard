<?php 

add_action('edit_form_after_title', function() {
    global $post, $wp_meta_boxes;
    do_meta_boxes(get_current_screen(), 'advanced', $post);
    unset($wp_meta_boxes[get_post_type($post)]['advanced']);
});

function ac_vcard_register_meta_boxes() {
	add_meta_box( 
		'ac_vcard_cpt-3457896349', 
		__( 'AC vCard Meta Box', 'ac_vcard' ), 
		'ac_vcard_cpt_display_callback', 
		'ac_vcard_cpt',
		'advanced', 
		'high' // priority
		);
}
add_action( 'add_meta_boxes', 'ac_vcard_register_meta_boxes' );

function ac_vcard_cpt_display_callback( $post ) {
	// Display code/markup goes here. Don't forget to include nonces!'
	$ac_postID = $post->ID;
	$cpt_PostMeta = get_post_meta($ac_postID);
	//var_dump($cpt_PostMeta);
	
	$apiUrl = get_home_url().'/api_vcard/'.$post->post_name;
	?>
	<input type="hidden" name="ac_vcard" value="ac_vcard_save" />
	<input type="hidden" name="ac_vcard__title" id="ac_vcard__title" value="">
	
	<style>
		.ac-vcard-form input,.ac-vcard-form textarea{
			width:100%;
			max-width:320px;
		}
		#person_imagebox img{
			max-width:150px;
			height:auto;
		}
	</style>
	<?php?>
	<table class="form-table ac-vcard-form">
		<tr>
			<th><label for="ac_vcard__title_show"><?php _e("Api url", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__title_show" value="<?php echo $apiUrl; ?>">
			</td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__qrcode_embed"><?php _e("Embed QR code", 'ac_vcard'); ?></label></th>
			<td>
			<img src="https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=<?php echo $apiUrl; ?>"><br>
			<textarea name="ac_vcard__qrcode_embed" ><img src="https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=<?php echo $apiUrl; ?>"></textarea>
			</td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__qrcode_embed"><?php _e("Image", 'ac_vcard'); ?></label></th>
			<td>
				<div id="person_imagebox"><?php 
				if($cpt_PostMeta['ac_vcard__user_photo'][0] != ''){
					echo "<img src='".wp_get_attachment_url($cpt_PostMeta['ac_vcard__user_photo'][0])."'>";
				}
				?></div>
				<label for="ac_vcard__qrcode_embed"><?php _e("image id:", 'ac_vcard'); ?></label><br>
				<input type="text" name="ac_vcard__user_photo" id="icon_input" value="<?php echo $cpt_PostMeta['ac_vcard__user_photo'][0]; ?>" /><br><br>
				<a href="#" id="set-ico-button" class="button upload_image_button">Set Image</a> <a href="#" id="reset_ico" class="button">Reset</a>
			</td>            
        </tr>
		<tr data-name="cpt_acvcard__name">
			<th><label for="ac_vcard__user_fname"><?php _e("First name", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_fname" value="<?php echo $cpt_PostMeta['ac_vcard__user_fname'][0]; ?>"></td>            
        </tr>
		<tr data-name="cpt_acvcard__name">
			<th><label for="ac_vcard__user_lname"><?php _e("Last name", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_lname" value="<?php echo $cpt_PostMeta['ac_vcard__user_lname'][0]; ?>"></td>            
        </tr>		
		<tr>
			<th><label for="ac_vcard__user_titleposition"><?php _e("Title/position (optional)", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_titleposition" value="<?php echo $cpt_PostMeta['ac_vcard__user_titleposition'][0]; ?>"></td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__user_cpompanyname"><?php _e("Company name (optional)", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_cpompanyname" value="<?php echo $cpt_PostMeta['ac_vcard__user_cpompanyname'][0]; ?>"></td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__user_fulladress"><?php _e("Full address (optional)", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_fulladress" value="<?php echo $cpt_PostMeta['ac_vcard__user_fulladress'][0]; ?>"></td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__user_phonenumber"><?php _e("Phone number (optional)", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_phonenumber" value="<?php echo $cpt_PostMeta['ac_vcard__user_phonenumber'][0]; ?>"></td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__user_mobilenumber"><?php _e("Mobile phone (optional)", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_mobilenumber" value="<?php echo $cpt_PostMeta['ac_vcard__user_mobilenumber'][0]; ?>"></td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__user_emailadress"><?php _e("Email address (optional)", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_emailadress" value="<?php echo $cpt_PostMeta['ac_vcard__user_emailadress'][0]; ?>"></td>            
        </tr>
		<tr>
			<th><label for="ac_vcard__user_webside"><?php _e("Website (optional)", 'ac_vcard'); ?></label></th>
			<td><input name="ac_vcard__user_webside" value="<?php echo $cpt_PostMeta['ac_vcard__user_webside'][0]; ?>"></td>            
        </tr>
    </table>
	<?php 
	
	
	?>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Ustawienie pola o id "title" na disabled
                    var titleInput = document.getElementById("ac_vcard__title");
                    //titleInput.disabled = true;
                    titleInput.style.pointerEvents = "none";

                    function updateTitle() {
                        titleInput.value = ''; // Wyczyść aktualną wartość pola "title"
                        var divs = document.querySelectorAll('tr[data-name="cpt_acvcard__name"]');
                        divs.forEach(function(div) {
                            var input = div.querySelector('input');
                            if (input) {
                                titleInput.value += input.value + '_';
                            }
                        });
                    }
                    // Połącz onchange zdarzenie do każdego pola input
                    var inputs = document.querySelectorAll('tr[data-name="cpt_acvcard__name"] input');
                    inputs.forEach(function(input) {
                        input.addEventListener('input', updateTitle); // Dodajemy obsługę zdarzenia input
                    });

                    // Wywołaj funkcję raz na starcie, aby początkowa wartość pola "title" została ustawiona
                    updateTitle();
                    
                });
            </script>  
	<?php
}

function ac_vcard_cpt_save_meta_box( $post_id ) {
	// Save logic goes here. Don't forget to include nonce checks!
	$ac_postID = $post_id;
	if($_POST['ac_vcard'] == 'ac_vcard_save'){
		$fieldArray = array(
			'ac_vcard__user_photo',
			'ac_vcard__title',
			'ac_vcard__user_fname', 
			'ac_vcard__user_lname',
			'ac_vcard__user_titleposition',
			'ac_vcard__user_cpompanyname', 
			'ac_vcard__user_fulladress',
			'ac_vcard__user_phonenumber',
			'ac_vcard__user_mobilenumber',
			'ac_vcard__user_emailadress',
			'ac_vcard__user_webside',
		);
		foreach ($fieldArray as $fieldName) {
			update_post_meta( $ac_postID, $fieldName, $_POST[$fieldName]);
		}
	}
	//update_post_meta( $post_id, "add_article_main", array( "adf", "bfdf" ) )
}
add_action( 'save_post', 'ac_vcard_cpt_save_meta_box' );



function ac_vcard_cpt_checkpost($value){
	$args = [
		'post_type'      => 'ac_vcard_cpt',
		'posts_per_page' => 1,
		'post_name__in'  => ['post-slug'],
	];
	$q = get_posts( $args );
	return $q;
}

function ac_vcard_get_postid_by_slug( $page_slug = '', $posttype = 'ac_vcard_cpt' ) {
	if ( $page_slug ) {
	$postobject = get_page_by_path( $page_slug, OBJECT, $posttype );
		if ( $postobject ) {
			return $postobject->ID;
		}
	}
	return 0;
}