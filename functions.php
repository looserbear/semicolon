<?php
/**
 * kovkov functions and definitions
 *
 * @package kovkov
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 940; /* pixels */
}

if ( ! function_exists( 'kovkov_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function kovkov_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on kovkov, use a find and replace
	 * to change 'kovkov' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'kovkov', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 420, 240, true );
	// add_image_size( 'kovkov-featured', 420, 230, true );
	add_image_size( 'kovkov-mini', 60, 60, true );
	add_image_size( 'kovkov-gallery', 300, 300, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'kovkov' ),
		'social' => __( 'Social Menu', 'kovkov' ),
	) );

	// Enable support for Post Formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );

	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form' ) );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'kovkov_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );
}
endif; // kovkov_setup
add_action( 'after_setup_theme', 'kovkov_setup' );

/**
 * Register widgetized area and update sidebar with default widgets.
 */
function kovkov_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Primary', 'kovkov' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );

	register_sidebar( array(
		'name'          => __( 'Secondary', 'kovkov' ),
		'id'            => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );

	register_sidebar( array(
		'name'          => __( 'Tertiary', 'kovkov' ),
		'id'            => 'sidebar-3',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'kovkov_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function kovkov_scripts() {
	wp_enqueue_style( 'kovkov-style', get_stylesheet_uri(), array(), '20140115' );

	wp_enqueue_style( 'kovkov-genericons', get_template_directory_uri() . '/css/genericons.css', array(), '20131222' );

	wp_enqueue_script( 'kovkov-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'kovkov-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	wp_enqueue_script( 'kovkov-grid', get_template_directory_uri() . '/js/grid.js', array( 'jquery' ), '20131223', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'kovkov_scripts' );

/**
 * Implement the Custom Header feature.
 */
//require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

add_action( 'kovkov_header_after', function() {
	if ( ! is_front_page() || is_paged() )
		return;
	?>
	<header class="page-header page-description">
		<h1 class="page-title">WP Magazine — это онлайн журнал посвящённый системе управления контентом WordPress. Здесь вы найдёте много полезной информации, как для начинающих, так и для опытных разработчиков.</h1>
	</header>
	<?php
});

class Kovkov {
	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( null === $instance ) {
			$instance = new Kovkov;
		}

		return $instance;
	}

	function init() {
		// @todo: sticky to featured

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_filter( 'posts_results', array( $this, 'posts_results' ), 10, 2 );
		add_filter( 'found_posts', array( $this, 'found_posts' ), 10, 2 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );

		add_filter( 'shortcode_atts_gallery', array( $this, 'shortcode_atts_gallery' ), 10, 3 );
		add_filter( 'use_default_gallery_style', '__return_false' );
	}

	function shortcode_atts_gallery( $out, $pairs, $atts ) {
		if ( empty( $atts['size'] ) && $out['columns'] >= 2 )
			$out['size'] = 'kovkov-gallery';

		return $out;
	}

	function body_class( $classes ) {
		if ( ! is_singular() )
			$classes[] = 'grid';

		$classes[] = 'no-js';

		return $classes;
	}

	function post_class( $classes, $class, $post_id ) {
		if ( self::is_featured( $post_id ) )
			$classes[] = 'kovkov-featured';

		return $classes;
	}

	function get_featured_posts() {
		$featured_posts = array();

		$jetpack_featured_posts = apply_filters( 'kovkov_get_featured_posts', false );
		if ( ! empty( $jetpack_featured_posts ) )
			$featured_posts = array_map( 'absint', wp_list_pluck( $jetpack_featured_posts, 'ID' ) );
		else
			$featured_posts = (array) get_option( 'sticky_posts' );

		if ( empty( $featured_posts ) )
			return new WP_Query;

		return new WP_Query( array(
			'post__in' => $featured_posts,
			'posts_per_page' => 2,
			'ignore_sticky_posts' => true,
		) );
	}

	public static function is_featured( $post_id = null ) {
		$post = get_post( $post_id );
		$featured = false;

		if ( class_exists( 'Featured_Content' ) && method_exists( 'Featured_Content', 'get_setting' ) ) {
			$tag_id = Featured_Content::get_setting( 'tag-id' );
			$post_tags = wp_get_object_terms( $post->ID, 'post_tag' );

			if ( in_array( absint( $tag_id ), wp_list_pluck( $post_tags, 'term_id' ) ) )
				$featured = true;
		} else {
			$sticky_posts = (array) get_option( 'sticky_posts' );
			$featured = in_array( $post->ID, $sticky_posts );
		}

		return $featured;
	}

	function pre_get_posts( $query ) {
		if ( ! $query->is_main_query() || is_admin() )
			return;

		if ( is_front_page() ) {
			$query->set( 'ignore_sticky_posts', 1 );

			// We're going to stick two posts only, on the home page
			// But not on other pages, see posts_results.
			$featured = $this->get_featured_posts();

			if ( $featured->have_posts() ) {
				$posts_per_page = $query->get( 'posts_per_page' );
				if ( ! $posts_per_page )
					$posts_per_page = get_option( 'posts_per_page', 10 );

				$query->set( 'post__not_in', wp_list_pluck( $featured->posts, 'ID' ) );

				if ( ! is_paged() ) {
					// $query->set( 'posts_per_page', $posts_per_page - $featured->post_count );
				} else {
					$query->set( 'offset', ( $query->get( 'paged' ) - 1 ) * $posts_per_page - $featured->post_count );
					// $query->set( 'offset', get_query_var( 'paged' ) );
				}
			}
		}
	}

	function posts_results( $posts, $query ) {
		if ( ! $query->is_main_query() || is_admin() )
			return $posts;

		if ( is_front_page() ) {

			// Stick only on the home page.
			if ( ! is_paged() ) {
				$featured = $this->get_featured_posts();

				if ( $featured->have_posts() ) {

					// Since we're going to unshif these, we'll need them in reverse order.
					$featured->posts = array_reverse( $featured->posts );

					foreach ( $featured->posts as $post ) {
						array_unshift( $posts, $post );
					}

					// Remove any extras on top of ppp.
					while ( count( $posts ) > $query->get( 'posts_per_page' ) ) {
						array_pop( $posts );
					}
				}
			}
		}

		return $posts;
	}

	function found_posts( $found_posts, $query ) {
		if ( ! $query->is_main_query() || is_admin() )
			return $found_posts;

		if ( is_front_page() ) {
			$featured = $this->get_featured_posts();

			if ( $featured->have_posts() ) {
				$found_posts += $featured->post_count;
			}
		}

		return $found_posts;
	}
};

Kovkov::get_instance()->init();

if ( ! function_exists( 'kovkov_get_related_posts' ) ) :
/**
 * Returns a new WP_Query with related posts.
 */
function kovkov_get_related_posts() {
	$post = get_post();

	// Support for the Yet Another Related Posts Plugin
	if ( function_exists( 'yarpp_get_related' ) ) {
		$related = yarpp_get_related( array( 'limit' => 4 ), $post->ID );
		return new WP_Query( array(
			'post__in' => wp_list_pluck( $related, 'ID' ),
			'posts_per_page' => 3,
			'ignore_sticky_posts' => true,
			'post__not_in' => array( $post->ID ),
		) );
	}

	$args = array(
		'posts_per_page' => 4,
		'ignore_sticky_posts' => true,
		'post__not_in' => array( $post->ID ),
	);

	// Get posts from the same category.
	$categories = get_the_category();
	if ( ! empty( $categories ) ) {
		$category = array_shift( $categories );
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'category',
				'field' => 'id',
				'terms' => $category->term_id,
			),
		);
	}

	return new WP_Query( $args );
}
endif;

add_action( 'kovkov_navigation_after', function() {
	?>
	<ul class="kovkov-social social">
		<li class="twitter"><a href="http://twitter.com/wpmagru" target="_blank" title="Twitter"><span class="genericon genericon-twitter"></span></a></li>
		<li class="facebook"><a href="http://facebook.com/wpmagru" target="_blank" title="Facebook"><span class="genericon genericon-facebook"></span></a></li>
		<li class="google-plus"><a href="https://plus.google.com/108553372817411783434?rel=author" target="_blank" title="Google+"><span class="genericon genericon-googleplus"></span></a></li>
		<li class="feed"><a href="http://wpmag.ru/feed/" target="_blank" title="Feed"><span class="genericon genericon-feed"></span></a></li>
	</ul>
	<?php
});

// Maybe next time?
/*add_filter( 'the_title', function( $title ) {
	$pos = strrpos( $title, ' ' );
	if ( $pos !== false )
		$title = substr_replace( $title, '&nbsp;', $pos, 1 );

	return $title;
});*/