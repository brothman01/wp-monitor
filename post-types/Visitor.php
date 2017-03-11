<?php
// Register Custom Post Type
function custom_post_type() {

	$labels = array(
		'name'                  => _x( 'Visitors', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Visitor', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Visitors', 'text_domain' ),
		'name_admin_bar'        => __( 'Visitor', 'text_domain' ),
		'archives'              => __( 'Visitor Archives', 'text_domain' ),
		'attributes'            => __( 'Visitor Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Visitor:', 'text_domain' ),
		'all_items'             => __( 'All Visitors', 'text_domain' ),
		'add_new_item'          => __( 'Add New Visitor', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Visitor', 'text_domain' ),
		'edit_item'             => __( 'Edit Visitor', 'text_domain' ),
		'update_item'           => __( 'Update Visitor', 'text_domain' ),
		'view_item'             => __( 'View Visitor', 'text_domain' ),
		'view_items'            => __( 'View Visitors', 'text_domain' ),
		'search_items'          => __( 'Search Visitors', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Visitor', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Visitor', 'text_domain' ),
		'items_list'            => __( 'Visitors list', 'text_domain' ),
		'items_list_navigation' => __( 'Visitors list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Visitors list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Visitor', 'text_domain' ),
		'description'           => __( 'visitor to the site', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'visitor', $args );

}
add_action( 'init', 'custom_post_type', 0 );
