<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package activello
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function activello_page_menu_args( $args ) {
  $args['show_home'] = true;
  return $args;
}
add_filter( 'wp_page_menu_args', 'activello_page_menu_args' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function activello_body_classes( $classes ) {
  // Adds a class of group-blog to blogs with more than 1 published author.
  if ( is_multi_author() ) {
    $classes[] = 'group-blog';
  }
	
	if ( get_theme_mod( 'activello_sidebar_position' ) == "pull-right" ) {
		$classes[] = 'has-sidebar-left';
	} else if ( get_theme_mod( 'activello_sidebar_position' ) == "no-sidebar" ) {
		$classes[] = 'has-no-sidebar';
	} else if ( get_theme_mod( 'activello_sidebar_position' ) == "full-width" ) {
		$classes[] = 'has-full-width';
	} else {
		$classes[] = 'has-sidebar-right';
	}

  return $classes;
}
add_filter( 'body_class', 'activello_body_classes' );


if ( version_compare( $GLOBALS['wp_version'], '4.1', '<' ) ) :
  /**
   * Filters wp_title to print a neat <title> tag based on what is being viewed.
   *
   * @param string $title Default title text for current view.
   * @param string $sep Optional separator.
   * @return string The filtered title.
   */
  function activello_wp_title( $title, $sep ) {
    if ( is_feed() ) {
      return $title;
    }
    global $page, $paged;
    // Add the blog name
    $title .= get_bloginfo( 'name', 'display' );
    // Add the blog description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) ) {
      $title .= " $sep $site_description";
    }
    // Add a page number if necessary:
    if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
      $title .= " $sep " . sprintf( esc_html__( 'Page %s', 'activello' ), max( $paged, $page ) );
    }
    return $title;
  }
  add_filter( 'wp_title', 'activello_wp_title', 10, 2 );
  /**
   * Title shim for sites older than WordPress 4.1.
   *
   * @link https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
   * @todo Remove this function when WordPress 4.3 is released.
   */
  function activello_render_title() {
    ?>
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <?php
  }
  add_action( 'wp_head', 'activello_render_title' );
endif;


// Mark Posts/Pages as Untiled when no title is used
add_filter( 'the_title', 'activello_title' );

function activello_title( $title ) {
  if ( $title == '' ) {
    return 'Untitled';
  } else {
    return $title;
  }
}

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function activello_setup_author() {
  global $wp_query;

  if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
    $GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
  }
}
add_action( 'wp', 'activello_setup_author' );


/**
 * Password protected post form using Boostrap classes
 */
add_filter( 'the_password_form', 'custom_password_form' );

function custom_password_form() {
  global $post;
  $label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
  $o = '<form class="protected-post-form" action="' . get_option('siteurl') . '/wp-login.php?action=postpass" method="post">
  <div class="row">
    <div class="col-lg-10">
        ' . esc_html__( "<p>This post is password protected. To view it please enter your password below:</p>" ,'activello') . '
        <label for="' . $label . '">' . esc_html__( "Password:" ,'activello') . ' </label>
      <div class="input-group">
        <input class="form-control" value="' . get_search_query() . '" name="post_password" id="' . $label . '" type="password">
        <span class="input-group-btn"><button type="submit" class="btn btn-default" name="submit" id="searchsubmit" value="' . esc_attr__( "Submit",'activello' ) . '">' . esc_html__( "Submit" ,'activello') . '</button>
        </span>
      </div>
    </div>
  </div>
</form>';
  return $o;
}

// Add Bootstrap classes for table
add_filter( 'the_content', 'activello_add_custom_table_class' );
function activello_add_custom_table_class( $content ) {
    return str_replace( '<table>', '<table class="table table-hover">', $content );
}

if ( ! function_exists( 'activello_header_menu' ) ) :
/**
 * Header menu (should you choose to use one)
 */
function activello_header_menu() {
  // display the WordPress Custom Menu if available
  wp_nav_menu(array(
    'menu'              => 'primary',
    'theme_location'    => 'primary',
    'depth'             => 2,
    'container'         => 'div',
    'container_class'   => 'collapse navbar-collapse navbar-ex1-collapse',
    'menu_class'        => 'nav navbar-nav',
    'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
    'walker'            => new wp_bootstrap_navwalker()
  ));
} /* end header menu */
endif;

if ( ! function_exists( 'activello_footer_links' ) ) :
/**
 * Footer menu (should you choose to use one)
 */
function activello_footer_links() {
  // display the WordPress Custom Menu if available
  wp_nav_menu(array(
    'container'       => '',                              // remove nav container
    'container_class' => 'footer-links clearfix',   // class of container (should you choose to use it)
    'menu'            => esc_html__( 'Footer Links', 'activello' ),   // nav name
    'menu_class'      => 'nav footer-nav clearfix',      // adding custom nav class
    'theme_location'  => 'footer-links',             // where it's located in the theme
    'before'          => '',                                 // before the menu
    'after'           => '',                                  // after the menu
    'link_before'     => '',                            // before each link
    'link_after'      => '',                             // after each link
    'depth'           => 0,                                   // limit the depth of the nav
    'fallback_cb'     => 'activello_footer_links_fallback'  // fallback function
  ));
} /* end activello footer link */
endif;


if ( ! function_exists( 'activello_featured_slider' ) ) :
/**
 * Featured image slider, displayed on front page for static page and blog
 */
function activello_featured_slider() {
  if ( is_front_page() && get_theme_mod( 'activello_featured_hide' ) == 1 ) {
		
		wp_enqueue_style( 'flexslider-css' );
		wp_enqueue_script( 'flexslider-js' );
		wp_enqueue_script( 'flexslider-customization' );
		
    echo '<div class="flexslider">';
      echo '<ul class="slides">';

        $count = 4;
        $slidecat = get_theme_mod( 'activello_featured_cat' );

        $query = new WP_Query( array( 'cat' => $slidecat,'posts_per_page' => $count ) );
        if ($query->have_posts()) :
          while ($query->have_posts()) : $query->the_post();

          echo '<li>';
            if ( (function_exists( 'has_post_thumbnail' )) && ( has_post_thumbnail() ) ) :
              echo get_the_post_thumbnail( get_the_ID(), 'activello-slider' );
            endif;

              echo '<div class="flex-caption">';
									echo get_the_category_list();
                  if ( get_the_title() != '' ) echo '<a href="' . get_permalink() . '"><h2 class="entry-title">'. get_the_title().'</h2></a>';
                  echo '<div class="read-more"><a href="' . get_permalink() . '">' . __( 'Read More', 'activello' ) .'</a></div>';
              echo '</div>';

              endwhile; wp_reset_query();
            endif;

          echo '</li>';
      echo '</ul>';
    echo ' </div>';
  }
}
endif;

/**
 * function to show the footer info, copyright information
 */
function activello_footer_info() {
global $activello_footer_info;
  printf( esc_html__( 'Theme by %1$s Powered by %2$s', 'activello' ) , '<a href="http://colorlib.com/" target="_blank">Colorlib</a>', '<a href="http://wordpress.org/" target="_blank">WordPress</a>');
}


/**
 * Add Bootstrap thumbnail styling to images with captions
 * Use <figure> and <figcaption>
 *
 * @link http://justintadlock.com/archives/2011/07/01/captions-in-wordpress
 */
function activello_caption($output, $attr, $content) {
  if (is_feed()) {
    return $output;
  }

  $defaults = array(
    'id'      => '',
    'align'   => 'alignnone',
    'width'   => '',
    'caption' => ''
  );

  $attr = shortcode_atts($defaults, $attr);

  // If the width is less than 1 or there is no caption, return the content wrapped between the [caption] tags
  if ($attr['width'] < 1 || empty($attr['caption'])) {
    return $content;
  }

  // Set up the attributes for the caption <figure>
  $attributes  = (!empty($attr['id']) ? ' id="' . esc_attr($attr['id']) . '"' : '' );
  $attributes .= ' class="thumbnail wp-caption ' . esc_attr($attr['align']) . '"';
  $attributes .= ' style="width: ' . (esc_attr($attr['width']) + 10) . 'px"';

  $output  = '<figure' . $attributes .'>';
  $output .= do_shortcode($content);
  $output .= '<figcaption class="caption wp-caption-text">' . $attr['caption'] . '</figcaption>';
  $output .= '</figure>';

  return $output;
}
add_filter('img_caption_shortcode', 'activello_caption', 10, 3);

/**
 * Skype URI support for social media icons
 */
function activello_allow_skype_protocol( $protocols ){
    $protocols[] = 'skype';
    return $protocols;
}
add_filter( 'kses_allowed_protocols' , 'activello_allow_skype_protocol' );

/**
 * Add custom favicon displayed in WordPress dashboard and frontend
 */
function activello_add_favicon() {
	echo '<link rel="shortcut icon" type="image/x-icon" href="' . get_template_directory_uri() . '/favicon.png" />'. "\n";
}
add_action( 'wp_head', 'activello_add_favicon', 0 );
add_action( 'admin_head', 'activello_add_favicon', 0 );

/*
 * This one shows/hides the an option when a checkbox is clicked.
 */
add_action( 'optionsframework_custom_scripts', 'optionsframework_custom_scripts' );

function optionsframework_custom_scripts() { ?>

<script type="text/javascript">
jQuery(document).ready(function() {

  jQuery('#activello_slider_checkbox').click(function() {
      jQuery('#section-activello_slide_categories').fadeToggle(400);
  });

  if (jQuery('#activello_slider_checkbox:checked').val() !== undefined) {
    jQuery('#section-activello_slide_categories').show();
  }

  jQuery('#activello_slider_checkbox').click(function() {
      jQuery('#section-activello_slide_number').fadeToggle(400);
  });

  if (jQuery('#activello_slider_checkbox:checked').val() !== undefined) {
    jQuery('#section-activello_slide_number').show();
  }

});
</script>

<?php
}

/*
 * This display logo from wp customizer setting.
 */
function activello_logo() {
	$logo = wp_get_attachment_image_src( get_theme_mod( 'activello_logo' ), 'medium' );
	return $logo[0];
}

/*
 * This display blog description from wp customizer setting.
 */
function activello_cats() {
	$cats = array();
	$cats[0] = "All";
	
	foreach ( get_categories() as $categories => $category ) {
		$cats[$category->term_id] = $category->name;
	}
	
	return $cats;
}

/**
 * Custom comment template
 */
function activello_cb_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);

	if ( 'div' == $args['style'] ) {
		$tag = 'div';
		$add_below = 'comment';
	} else {
		$tag = 'li';
		$add_below = 'div-comment';
	}
?>
	<<?php echo $tag ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ) ?> id="comment-<?php comment_ID() ?>">
	<?php if ( 'div' != $args['style'] ) : ?>
	 <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
	<?php endif; ?>

	<div class="comment-author vcard">
  	<?php if ( $args['avatar_size'] != 0 ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
  	<?php printf( __( '<cite class="fn">%s</cite> <span class="says">says:</span>', 'activello' ), get_comment_author_link() ); ?>
  	<?php comment_reply_link( array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>

    <div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>">
      <?php
        /* translators: 1: date, 2: time */
        printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( 'Edit' ), '  ', '' );
      ?>
    </div>

  </div>

	<?php if ( $comment->comment_approved == '0' ) : ?>
		<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'activello' ); ?></em>
		<br />
	<?php endif; ?>

	<?php comment_text(); ?>

	<?php if ( 'div' != $args['style'] ) : ?>
	</div>
	<?php endif; ?>
<?php
}

/**
 * Get custom CSS from Theme setting panel and output in header
 */
if (!function_exists('get_activello_theme_setting'))  {
  function get_activello_theme_setting(){

    echo '<style type="text/css">';

    if ( get_theme_mod('accent_color')) {
      echo 'a:hover, a:focus,article.post .post-categories a:hover,
          .entry-title a:hover, .entry-meta a:hover, .entry-footer a:hover,
          .read-more a:hover, .social-icons a:hover,
          .flex-caption .post-categories a:hover, .flex-caption .read-more a:hover,
          .flex-caption h2:hover, .comment-meta.commentmetadata a:hover,
          .post-inner-content .cat-item a:hover,.navbar-default .navbar-nav > .active > a,
          .navbar-default .navbar-nav > .active > a:hover,
          .navbar-default .navbar-nav > .active > a:focus,
          .navbar-default .navbar-nav > li > a:hover,
          .navbar-default .navbar-nav > li > a:focus, .navbar-default .navbar-nav > .open > a,
          .navbar-default .navbar-nav > .open > a:hover,
          .navbar-default .navbar-nav > .open > a:focus, .cat-title a {color:' . get_theme_mod('accent_color') . '}';
      
      echo 'article.post .post-categories:after, .post-inner-content .cat-item:after, #secondary .widget-title:after {background:' . get_theme_mod('accent_color') . '}';
    
      echo '.btn-default:hover, .label-default[href]:hover,
          .label-default[href]:focus, .btn-default:hover,
          .btn-default:focus, .btn-default:active,
          .btn-default.active, #image-navigation .nav-previous a:hover,
          #image-navigation .nav-next a:hover, .woocommerce #respond input#submit:hover,
          .woocommerce a.button:hover, .woocommerce button.button:hover,
          .woocommerce input.button:hover, .woocommerce #respond input#submit.alt:hover,
          .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover,
          .woocommerce input.button.alt:hover, .input-group-btn:last-child>.btn:hover,
          button, html input[type=button]:hover, input[type=reset]:hover,
          input[type=submit]:hover, .comment-form #submit:hover, .tagcloud a:hover{background-color:' . get_theme_mod('accent_color') . '; }';
    }
    if ( get_theme_mod('social_color')) {
      echo '#social a { color:' . get_theme_mod('social_color') .'}';
    }
    if ( get_theme_mod('social_hover_color')) {
      echo '#social a:hover { color:' . get_theme_mod('social_hover_color') .'}';
    }
    
    if ( get_theme_mod('custom_css')) {
      echo html_entity_decode( get_theme_mod( 'custom_css', 'no entry' ) );
    }
    
    echo '</style>';
  }
}
add_action('wp_head','get_activello_theme_setting',10);