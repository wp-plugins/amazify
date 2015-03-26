<?php

/*
Plugin Name: Amazify
Plugin URI: https://bridon.fr
Description: Add automatically your tag to all Amazon links in your posts. Replace it if the tag is already existing. Also add nofollow and target="_blank".
Version: 1.1.0.1
Author: Maxime Bridon
Author URI: https://bridon.fr
License: GPLv2 or later
*/

defined('ABSPATH') or die();


add_action( 'admin_menu', 'amazify_add_admin_menu' );
add_action( 'admin_init', 'amazify_settings_init' );


function amazify_add_admin_menu(){ 
	add_options_page( 'Amazify', 'Amazify', 'manage_options', 'amazify', 'amazify_options_page' );
}

function amazify_settings_init(){ 
	register_setting( 'Amazify', 'amazify_settings' );

	add_settings_section(
		'amazify_Amazify_section', 
		__( 'Amazify options', 'amazify' ), 
		'amazify_settings_section_callback', 
		'Amazify'
	);

	add_settings_field( 
		'amazify_tag', 
		__( 'Your Amazon Tag', 'amazify' ), 
		'amazify_tag_render', 
		'Amazify', 
		'amazify_Amazify_section' 
	);

	add_settings_field( 
		'amazify_nofollow', 
		__( 'Add nofollow?', 'amazify' ), 
		'amazify_nofollow_render', 
		'Amazify', 
		'amazify_Amazify_section' 
	);

	add_settings_field( 
		'amazify_target', 
		__( 'Add target="_blank"?', 'amazify' ), 
		'amazify_target_render', 
		'Amazify', 
		'amazify_Amazify_section' 
	);
}

function amazify_tag_render(){ 
	$options = get_option( 'amazify_settings' );
	?>
	<input type='text' name='amazify_settings[amazify_tag]' value='<?php echo esc_attr($options['amazify_tag']); ?>'>
	<?php
}


function amazify_nofollow_render(){ 
	$options = get_option( 'amazify_settings' );
	?>
	<input type='checkbox' name='amazify_settings[amazify_nofollow]' <?php checked( $options['amazify_nofollow'], 1 ); ?> value='1'>
	<?php
}


function amazify_target_render(){ 
	$options = get_option( 'amazify_settings' );
	?>
	<input type='checkbox' name='amazify_settings[amazify_target]' <?php checked( $options['amazify_target'], 1 ); ?> value='1'>
	<?php
}


function amazify_settings_section_callback(){ 
	echo __( 'Amazify will automatically add or edit the "tag" variable in each Amazon links in your posts. Here you can tell the plugin which tag he will add and if the links should be in nofollow and/or opened in a new tab.', 'wordpress' );
}

function amazify_options_page(){ 
	?>
	<form action='options.php' method='post'>		
		<?php
		settings_fields( 'Amazify' );
		do_settings_sections( 'Amazify' );
		submit_button();
		?>
	</form>
	<?php
}

function AddTag($content){
	$options_amazify = get_option('amazify_settings');
	
	if($options_amazify['amazify_tag'] != ""){
		$TagAmazify = $options_amazify['amazify_tag'];
	}else{
		//If it can't find any tag (add it in "Amazify options page") it will use this one, the author's one. Just add // before $TagAmazify to disable this.
		$TagAmazify = 'wpamazify-21';
	}
	
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    if(preg_match_all("/$regexp/siU", $content, $link_matches, PREG_SET_ORDER)) {
		foreach($link_matches as $match) {

			if(preg_match('/(.*)amazon\.(com|co.uk|de|fr|co.jp|ca)+/i', $match[2])){
				$thelink = str_replace('&#038;',"&amp;",$match[2]);
				$parsed = parse_url($thelink);
				$options_attr = "";
				if(preg_match('#tag#i',$parsed['query'])){
					$query_string = html_entity_decode($parsed['query']);
					parse_str($query_string, $variables);
					
					$variables["tag"] = $TagAmazify;
					$new_query = http_build_query($variables, '', '&amp;');
					
					if($options_amazify['amazify_nofollow'] == '1'){
						$options_attr .= ' rel="nofollow"';
					}
					if($options_amazify['amazify_target'] == '1'){
						$options_attr .= ' target="_blank"';
					}
					$newlink = '<a href="'.$parsed['scheme'].'://'.$parsed['host'].$parsed['path'].'?'.$new_query.'"'.$options_attr.'>'.$match[3].'</a>';
				}else{
					if($options_amazify['amazify_nofollow'] == '1'){
						$options_attr .= ' rel="nofollow"';
					}
					if($options_amazify['amazify_target'] == '1'){
						$options_attr .= ' target="_blank"';
					}
					$newlink = '<a href="'.$thelink.'&amp;tag='.$TagAmazify.'"'.$options_attr.'>'.$match[3].'</a>';
				}
				
				
				$content = str_replace($match[0], $newlink, $content);	
			}
		}	
	}
	return $content;
}

add_filter( 'the_content', 'AddTag');

?>