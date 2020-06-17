<?php

/**
 * Plugin Name:       LS Gallery Override
 * Plugin URI:        https://ccharacter.com/custom-plugins/sws-wp-tweaks/
 * Description:       Override the default WordPress gallery shortcode with a friendlier version
 * Version:           1.7
 * Requires at least: 5.2
 * Requires PHP:      5.5
 * Author:            Laura Sage
 * Author URI:        https://ccharacter.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ls-gallery
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once plugin_dir_path(__FILE__).'inc/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://raw.githubusercontent.com/ccharacter/ls-gallery/master/plugin.json',
	__FILE__,
	'ls-gallery'
);
function laura_scripts() {
	wp_enqueue_style( 'ls-elastislide', plugin_dir_url(__FILE__). 'css/elastislide.css' );
	wp_enqueue_style( 'ls-elastistyle',  plugin_dir_url(__FILE__). . 'css/elastistyle.css' );
	
	wp_enqueue_script( 'ls-jqueryeasing', '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js', '', '', true );
	wp_enqueue_script( 'ls-jquerytmpl',  plugin_dir_url(__FILE__). .'js/jquery.tmpl.min.js', '', '', true );
	wp_enqueue_script( 'ls-elastislide',  plugin_dir_url(__FILE__). .'js/jquery.elastislide.js', '', '', true );
	wp_enqueue_script( 'ls-gallery',  plugin_dir_url(__FILE__). . 'js/gallery.js', '', '', true );
}
add_action( 'wp_enqueue_scripts', 'laura_scripts' );


//function my_scripts() {
	//wp_enqueue_style('bootstrap4', '//stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
    ////wp_enqueue_script( 'boot1','//code.jquery.com/jquery-3.3.1.slim.min.js', array( 'jquery' ),'',true );
    //wp_enqueue_script( 'boot2','//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ),'',true );
    //wp_enqueue_script( 'boot3','//stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
//}
//add_action( 'wp_enqueue_scripts', 'my_scripts' );

// Replace gallery shortcode with carousel
add_shortcode( 'gallery', 'modified_gallery_shortcode' );
function modified_gallery_shortcode($attr) { ?>
<!--<div class="container mt-5">
	<div class="carousel-container position-relative row">-->
	<?php $post = get_post();
	$gallerycount = 0;
	$divider = 6;
	static $instance = 0;
	$instance++;
	if (!empty($attr['ids'])) {
		if (empty($attr['orderby'])) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}
	$output = apply_filters('post_gallery', '', $attr);
	if ($output != '') {
		return $output;
    }
    if (isset($attr['orderby'])) {
        $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
        if (!$attr['orderby']) {
            unset($attr['orderby']);
        }
    }
    extract(shortcode_atts(array(
        'order' => 'ASC',
        'orderby' => 'menu_order ID',
        'id' => $post->ID,
        'itemtag' => '',
        'icontag' => '',
        'captiontag' => '',
        'columns' => 3,
        'size' => 'full',
        'include' => '',
        'link' => '',
        'exclude' => '',
        //'gallerynum' => 'nothing'
    ), $attr));
    $id = intval($id);
    if ($order === 'RAND') {
        $orderby = 'none';
    }
    if (!empty($include)) {
        $_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
        $attachments = array();
        foreach ($_attachments as $key => $val) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    } elseif (!empty($exclude)) {
        $attachments = get_children(array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
    } else {
        $attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
    }
    if (empty($attachments)) {
        return '';
    }
    if (is_feed()) {
        $output = "\n";
        foreach ($attachments as $att_id => $attachment) {
            $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
        }
        return $output;
    }
    //Carousel Output Begins Here
    //Needs a unique carousel id to work properly. Because I'm only using one gallery per post and showing them on an archive page, this uses the $post->ID to allow for multiple galleries on the same page.
    $output .= '<noscript>
    	<style>
    		.es-carousel ul {
    			display:block;
			}
		</style>
	</noscript>';
	
	$output .= '<script id="img-wrapper-tmpl" type="text/x-jquery-tmpl">
		<div class="rg-image-wrapper">
			{{if itemsCount > 1}}
			<div class="rg-image-nav">
				<a href="#" class="rg-image-nav-prev">Previous Image</a>
				<a href="#" class="rg-image-nav-next">Next Image</a>
			</div>
			{{/if}}
			<div class="rg-image"></div>
			<div class="rg-loading"></div>
			<div class="rg-caption-wrapper">
				<div class="rg-caption" style="display:none;">
					<p></p>
				</div>
			</div>
		</div>
	</script>';
    
    //$randstr = rand(); 
    //$result = hash("sha256", $randstr);
    
    $output .= '<style>
    	.es-nav span {
			background:transparent url(' . get_stylesheet_directory_uri() .'/images/nav_thumbs.png' . ') no-repeat top left;
		}
		
		.rg-image-nav a{
			background:#000 url(' . get_stylesheet_directory_uri() .'/images/nav.png) no-repeat -20% 50%;
		}
		
		.rg-view a{
			background:#464646 url(' . get_stylesheet_directory_uri() .'/images/views.png) no-repeat top left;
		}
		
		.rg-loading{
			background:#000 url(' . get_stylesheet_directory_uri() .'/images/ajax-loader.gif) no-repeat center center;
		}
	</style>';

    $output .= '<div id="rg-gallery" class="rg-gallery">';
    $output .= '<div class="rg-thumbs">';
    $output .= '<!-- Elastislide Carousel Thumbnail Viewer -->';
    $output .= '<div class="es-carousel-wrapper">';
    $output .= '<div class="es-nav">';
    $output .= '<span class="es-nav-prev">Previous</span>';
    $output .= '<span class="es-nav-next">Next</span>';
    $output .= '</div>';
    $output .= '<div class="es-carousel">';
    $output .= '<ul>';

    //$output .= '<div id="carousel-' . $post->ID . '-' . $result . '" class="carousel slide" data-ride="carousel">';
    //$output .= '<div class="carousel-inner">';
    //$output .= '<!-- Wrapper for slides -->';
	//Begin counting slides to set the first one as the active class
	$slidecount = 1;
	foreach ($attachments as $id => $attachment) {
		$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);
		$image_src_url = wp_get_attachment_image_src($id, 'full');
		$image_src_url2 = wp_get_attachment_image_src($id, 'thumbnail');
		
		error_log(print_r($image_src_url,true),0);
		
		list($imagewidth, $imageheight) = getimagesize($image_src_url[0]);
		$imagewidth=$image_src_url[1];
		$imageheight=$image_src_url[2];
		
		//if ($slidecount == 1) {
			$output .= '<li><a href="#"><img src="' . $image_src_url2[0] . '" data-large="' . $image_src_url[0] . '" alt="image' . $slidecount . '" data-description="' . wptexturize($attachment->post_excerpt) . '" data-orientation="';
			if ($imagewidth > $imageheight) {
				$output .= 'cover';
			} else {
				$output .= 'contain';
			}
			$output .= '"/></a></li>';
			//$output .= '<div class="carousel-item active" data-thumb="' . $slidecount . '"style="height: 500px; background-image: url(' . $image_src_url[0] . '); background-position: center center; background-size: ';
			//if ($imagewidth > $imageheight) { 
			//	$output .= 'cover';
			//} else {
			//	$output .= 'contain';
			//};
			//$output .= '; background-repeat: no-repeat; background-color: #f7f7f7;">';
		//} else {
			//$output .= '<div class="carousel-item" data-thumb="' . $slidecount . '" style="height: 500px; background-image: url(' . $image_src_url[0] . '); background-position: center center; background-size: ';
			//if ($imagewidth > $imageheight) { 
			//	$output .= 'cover';
			//} else {
			//	$output .= 'contain';
			//};
			//$output .= '; background-repeat: no-repeat; background-color: #f7f7f7;">';
		//}
		//if (trim($attachment->post_excerpt)) {
		//	$output .= '<div class="carousel-caption">
		//		<p class="caption">' . wptexturize($attachment->post_excerpt) . '</p>
		//	</div>';
		//}
		//$output .= '</div>';
		$slidecount++;
	}
	//$output .= '</div>';
	// main image controls
	//$output .= '<div id="carousel-thumbs" class="carousel slide" data-ride="carousel">';
	//$output .= '<div class="carousel-inner">';

	//$output .= '<a class="carousel-control-prev main" href="#carousel-' . $post->ID . '-' . $result . '" role="button" data-slide="prev">';
	//$output .= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
	//$output .= '<span class="sr-only">Previous</span>';
	//$output .= '</a>';
	//$output .= '<a class="carousel-control-next main" href="#carousel-' . $post->ID . '-' . $result . '" role="button" data-slide="next">';
	//$output .= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
	//$output .= '<span class="sr-only">Next</span>';
	//$output .= '</a>';
	//$output .= '</div>';
	//$output .= '<div class="clearfix"></div>';
	//$output .= '<!-- Controls -->';
	//$output .= '<div id="thumbcarousel-' . $post->ID . '-' . $result . '" class="carousel slide thumbs" data-interval="false"><div class="carousel-inner"><div class="control-wrapper">';
	//$thumbcount = 0;
	//if ($thumbcount == 0) {
	//	$output .= '<div class="carousel-item row active">';
	//} else {
	//	$output .= '<div class="carousel-item row">';
	//}
	//foreach ($attachments as $id => $attachment) {
	//	$link2 = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);
	//	$image_src_url2 = wp_get_attachment_image_src($id, 'thumbnail');
	//	if ($thumbcount == 0) {
	//		$output .= '<div data-target="#carousel-' . $post->ID . '-' . $result . '" data-slide-to="' . $thumbcount . '" class="showing thumby">';
	//	} else {
	//		$output .= '<div data-target="#carousel-' . $post->ID . '-' . $result . '" data-slide-to="' . $thumbcount . '" class="thumby">';
	//	}
	//	$output .= '<img src="' . $image_src_url2[0] . '" style="margin-left: auto; margin-right: auto; width: 100%;">';
	//	$output .= '</div>';
	//	$thumbcount++;
	//	if (/* $image < $image->$total && */ $thumbcount % $divider == 0) {
	//		$output .= '</div><div class="carousel-item row">';
	//	}
	//}
	//if (/* $image < $image->$total && */ $thumbcount % $divider == 0) {
	//	$output .= '</div></div></div>';
	//} else {
	//	$output .= '</div></div></div>';
	//}
	//$output .= '<!-- /carousel-inner -->';
	//$output .= '<a class="carousel-control-prev" href="#thumbcarousel-' . $post->ID . '-' . $result . '" role="button" data-slide="prev">';
	//$output .= '<span class="fa-stack fa-2x">';
	//$output .= '<i class="far fa-circle fa-stack-2x"></i>';
	//$output .= '<i class="fas fa-angle-left fa-stack-1x" aria-hidden="true"></i>';
	//$output .= '</span>';
	//$output .= '</a>';
	//$output .= '<a class="carousel-control-next" href="#thumbcarousel-' . $post->ID . '-' . $result . '" role="button" data-slide="next">';
	//$output .= '<span class="fa-stack fa-2x">';
	//$output .= '<i class="far fa-circle fa-stack-2x"></i>';
	//$output .= '<i class="fas fa-angle-right fa-stack-1x" aria-hidden="true"></i>';
	//$output .= '</span>';
	//$output .= '</div>';
	//$output .= '</a>';
	//$output .= '</div>';
	//$output .= '<!-- /thumbcarousel -->';
	//$output .= '</div>';
	
	$output .= '</ul>';
	$output .= '</div>';
	$output .= '</div>';
	$output .= '<!-- End Elastislide Carousel Thumbnail Viewer -->';
	$output .= '</div><!-- rg-thumbs -->';
	$output .= '</div>';
	
	$gallerycount++;
	return $output; // final html ?>
	</div>
</div>
<?php }