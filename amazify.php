<?php

/*
Plugin Name: Amazify
Plugin URI: https://bridon.fr
Description: Add automatically your tag to all Amazon links in your posts. Replace it if the tag is already existing. Also add nofollow and target="_blank".
Version: 1.0
Author: Maxime Bridon
Author URI: https://bridon.fr
License: GPLv2 or later
*/

defined('ABSPATH') or die();

function AddAdminMenu(){
	add_menu_page( 'Amazify', 'Amazify', 'activate_plugins', 'Amazify', 'Admin_amazify' );
}

function Admin_amazify() { 
	global $wpdb;
	
	if(isset($_POST['save_options_amazify'])){
		update_option( 'amazify_tag', $_POST['the_tag_amazify'] );
		update_option( 'amazify_nofollow', $_POST['alsoaddnofollow'] );
		update_option( 'amazify_blank', $_POST['alsoaddblank'] );
	}
	
	$AmazifyTag = get_option('amazify_tag');
	$AmazifyNofollow = get_option('amazify_nofollow');
	$AmazifyBlank = get_option('amazify_blank');
	?>
		<div class="wrap">
			<h2>Options Amazify</h2>
			<p>
				This plugin will automatically edit any amazon link to add or replace any existing tag with yours. In addition, you can tell him also to add a nofollow attribute or a target="_blank" one.
			</p>
			<form name="options_amazify" action="options.php?page=Amazify" method="post">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="the_tag_amazify">Your Amazon tag</label>
						</th>
						<td>
							<input name="the_tag_amazify" type="text" value="<?php echo $AmazifyTag; ?>" id="the_tag_amazify" class="regular-text code">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="MoreOptions">Also add :</label>
						</th>
						<td>
							Nofollow <input type="checkbox" name="alsoaddnofollow"<?php if(!empty($AmazifyNofollow) && $AmazifyNofollow == "on" ) echo ' checked="checked"';?> /> 
							Target blank <input type="checkbox" name="alsoaddblank"<?php if(!empty($AmazifyBlank) && $AmazifyBlank == "on" ) echo ' checked="checked"';?> />
						</td>
					</tr>

					</tbody>
				</table>
				<input type="submit" value="Save changes" class="button button-primary" name="save_options_amazify" />
			</form>
		</div>
	<?php
	}
	?>

<?php
add_action('admin_menu', 'AddAdminMenu');

function AddTag($content){

	if(get_option('amazify_tag') != ""){
		$TagAmazify = get_option('amazify_tag');
	}else{
		//If it can't find any tag (add it in "Amazify options page") it will use this one, the author's one. Just add // before $TagAmazify to disable this.
		$TagAmazify = 'wpamazify-21';
	}
	
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    if(preg_match_all("/$regexp/siU", $content, $link_matches, PREG_SET_ORDER)) {
		foreach($link_matches as $match) {
			if(preg_match('/(.*)amazon\.(com|co.uk|de|fr|co.jp|ca)+/i', $match[2])){
				$parsed = parse_url($match[2]);
				
				if(preg_match('#tag#i',$parsed['query'])){

					$query_string = html_entity_decode($parsed['query']);
					parse_str($query_string, $variables);
				
					$variables["tag"] = 'supamii-21';
					$new_query = http_build_query($variables, '', '&amp;');
					$newlink = $parsed['scheme'].'://'.$parsed['host'].$parsed['path'].'?'.$new_query;
				}else{
					$newlink = $match[2].'&tag='.$TagAmazify.'"';
					if(get_option('amazify_nofollow') == 'on'){
						$newlink .= ' rel="nofollow"';
					}
					if(get_option('amazify_blank') == 'on'){
						$newlink .= ' target="_blank"';
					}
				}
				$content = str_replace($match[2],$newlink,$content);	
			}
		}	
	}
	return $content;
}

add_filter( 'the_content', 'AddTag');

?>