<?php

/*

Plugin Name: PressRoom Quick Toolbar
Plugin URI:
Description: Adds a dropdown 'PressRoom' to the WordPress toolbar as a quick access to PressRoom settings, developer functions and recently edited Issues ( published, drafts, scheduled )
Version: 1.0.0
Author: thePrintLabs

Release Notes:
1.0.0 - First release.

*/

if ( !function_exists('add_action') )
	die();


if (!isset($no_issue_drafts_to_show)) { $no_issue_drafts_to_show = 3; }
if (!isset($no_issue_future_to_show)) { $no_issue_future_to_show = 3; }
if (!isset($no_issue_edits_to_show )) { $no_issue_edits_to_show  = 3; }

add_action( 'admin_bar_menu', 'pr_issue_admin_bar_function', 998 );

/*
   PR EDITIONS
   ========================================================================== */

function pr_issue_admin_bar_function( $wp_admin_bar ) {

    $args = array(
        'id' => 'issue_list',
        'title' => 'PressRoom',
        'href' => get_bloginfo('wpurl').'/wp-admin/edit.php?post_type=page'
    );
    $wp_admin_bar->add_node( $args );

    $args = array(
        'id' => 'flush_theme_cache',
        'title' => '<a href="#" class="button button-primary right" id="pr-flush-themes-cache">Flush Theme Cache</a>',
        'parent' => 'issue_list'
    );
    $wp_admin_bar->add_node( $args );

    // top item in list is add new page
    $args = array(
        'id' => 'issue_item_a',
        'title' => 'Add New Issue',
        'parent' => 'issue_list',
        'href' => get_bloginfo('wpurl').'/wp-admin/post-new.php?post_type=page'
    );
    $wp_admin_bar->add_node( $args );

/*
    DRAFTS
    ========================================================================== */

    $issue_drafts_found = 'N';
    $issue_drafts = pr_recently_edited_issue_drafts();

    if (!empty($issue_drafts)) {
        // separator from new to drafts
        $args = array(
            'id' => 'issue_item_b',
            'title' => '--- draft ---',
            'parent' => 'issue_list',
            'href' => ''
        );
        $wp_admin_bar->add_node( $args );
    }

    // loop through the most recently modified page drafts
    foreach( $issue_drafts as $issue_draft ) {
        $issue_drafts_found = 'Y';

        // fixing "Warning: Creating default object from empty value in errors":
        if (!is_object($issue_draft)) {
            $issue_draft = new stdClass;
            $issue_draft->post_title = new stdClass;
        }

        $issue_draft_title = return_short_title($issue_draft->post_title,'[EMPTY DRAFT TITLE]');

        // add child nodes (issue_draft recently edited)
        $args = array(
            'id' => 'issue_item_' . $issue_draft->ID,
            'title' => ''.$issue_draft_title,
            'parent' => 'issue_list',
            'href' => get_bloginfo('wpurl').'/wp-admin/post.php?post=' . $issue_draft->ID . '&action=edit'
        );
        $wp_admin_bar->add_node( $args );
    }



/*
    FUTURE
    ========================================================================== */

    $issue_future_found = 'N';
    $issue_future = pr_recently_edited_issue_future();

    if (!empty($issue_future)) {
        // separator from page_future to published
        $args = array(
            'id' => 'issue_item_d',
            'title' => '--- future ---',
            'parent' => 'issue_list',
            'href' => ''
        );
        $wp_admin_bar->add_node( $args );
    }

    // loop through the most recently future pages
    foreach( $issue_future as $future_issue ) {
        $issue_future_found = 'Y';

        // fixing "Warning: Creating default object from empty value in errors":
        if (!is_object($future_issue)) {
            $future_issue = new stdClass;
            $future_issue->post_title = new stdClass;
        }

        $future_issue_title = return_short_title($future_issue->post_title,'[EMPTY DRAFT TITLE]');

        // add child nodes (future_issue recently edited)
        $args = array(
            'id' => 'issue_item_' . $future_issue->ID,
            'title' => ''.$future_issue_title,
            'parent' => 'issue_list',
            'href' => get_bloginfo('wpurl').'/wp-admin/post.php?post=' . $future_issue->ID . '&action=edit'
        );
        $wp_admin_bar->add_node( $args );
    }


//////////////

    // get list of pages
    $issues = pr_recently_edited_issues();

    if (!empty($issues)) {
        // separator from issue_drafts to published
        $args = array(
            'id' => 'issue_item_c',
            'title' => '--- published ---',
            'parent' => 'issue_list',
            'href' => ''
        );
        $wp_admin_bar->add_node( $args );
    }

    // loop through the most recently modified pages
    foreach( $issues as $thisissue ) {

        // fixing "Warning: Creating default object from empty value in errors":
        if (!is_object($thisissue)) {
            $thisissue = new stdClass;
            $thisissue->post_title = new stdClass;
        }

        $thisissue_title = return_short_title($thisissue->post_title,'[EMPTY PAGE TITLE]');

        // add child nodes (pages to edit)
        $args = array(
            'id' => 'issue_item_' . $thisissue->ID,
            'title' => $thisissue_title,
            'parent' => 'issue_list',
            'href' => get_bloginfo('wpurl').'/wp-admin/post.php?post=' . $thisissue->ID . '&action=edit'
        );
        $wp_admin_bar->add_node( $args );
    }

    if (!empty($issues)) {
        // separator from issue_drafts to published
        $args = array(
            'id' => 'issue_item_c',
            'title' => '--- published ---',
            'parent' => 'issue_list',
            'href' => ''
        );
        $wp_admin_bar->add_node( $args );
    }
}


function pr_recently_edited_drafts() {
	global $no_post_drafts_to_show;
	$args = array(
		'posts_per_page' => $no_post_drafts_to_show,
		'sort_column' => 'post_modified',
		'orderby' => 'post_date',
		'post_status' => 'draft',
		'order' => 'DESC'
	);
	$drafts = get_posts( $args );
	return $drafts;
}
function pr_recently_edited_posts() {
	global $no_post_edits_to_show;
	$args = array(
		'posts_per_page' => $no_post_edits_to_show,
		'sort_column' => 'post_modified',
		'orderby' => 'post_date',
		'order' => 'DESC'
	);
	$posts = get_posts( $args );
	return $posts;
}
function pr_recently_edited_posts_future() {
	global $no_post_future_to_show;
	$args = array(
		'posts_per_page' => $no_post_future_to_show,
		'sort_column' => 'post_modified',
		'orderby' => 'post_date',
		'post_status' => 'future',
		'order' => 'DESC'
	);
	$posts = get_posts( $args );
	return $posts;
}
function pr_recently_edited_pages() {
	global $no_page_edits_to_show;
	$args = array(
		'number' => $no_page_edits_to_show,
		'post_type' => 'page',
		'post_status' => 'publish',
		'sort_column' => 'post_modified',
		'hierarchical' => 0,
		'sort_order' => 'DESC'
	);
	$pages = get_pages( $args );
	return $pages;
}
function pr_recently_edited_page_drafts() {
	global $no_page_drafts_to_show;
	$args = array(
		'number' => $no_page_drafts_to_show,
		'post_type' => 'page',
		'post_status' => 'draft',
		'sort_column' => 'post_modified',
		'hierarchical' => 0,
		'sort_order' => 'DESC'
	);
	$pagedraft = get_pages( $args );
	return $pagedraft;
}
function pr_recently_edited_page_future() {
	global $no_page_future_to_show;
	$args = array(
		'number' => $no_page_future_to_show,
		'post_type' => 'page',
		'post_status' => 'future',
		'sort_column' => 'post_modified',
		'hierarchical' => 0,
		'sort_order' => 'DESC'
	);
	$pagefuture = get_pages( $args );
	return $pagefuture;
}

function pr_recently_edited_issues() {
	global $no_page_edits_to_show;
	$args = array(
		'number' => $no_page_edits_to_show,
		'post_type' => 'pr_edition',
		'post_status' => 'publish',
		'sort_column' => 'post_modified',
		'hierarchical' => 0,
		'sort_order' => 'DESC'
	);
	$issues = get_posts( $args );
	return $issues;
}
function pr_recently_edited_issue_drafts() {
	global $no_issue_drafts_to_show;
	$args = array(
		'number' => $no_issue_drafts_to_show,
		'post_type' => 'pr_edition',
		'post_status' => 'draft',
		'sort_column' => 'post_modified',
		'hierarchical' => 0,
		'sort_order' => 'DESC'
	);
	$issuedraft = get_posts( $args );
	return $issuedraft;
}
function pr_recently_edited_issue_future() {
	global $no_issue_future_to_show;
	$args = array(
		'number' => $no_issue_future_to_show,
		'post_type' => 'pr_edition',
		'post_status' => 'future',
		'sort_column' => 'post_modified',
		'hierarchical' => 0,
		'sort_order' => 'DESC'
	);
	$issuefuture = get_posts( $args );
	return $issuefuture;
}
function return_short_title( $title_to_shorten, $if_empty ) {
	// the variables passed
//	$the_title = $title_to_shorten;
	$the_title = apply_filters('the_title', $title_to_shorten );
	$return_if_empty = $if_empty;
	$return_value = $the_title;
	if (trim($the_title)== FALSE) {
		$the_title='';
		$title_len=0;
	} else {
		$title_len=strlen($the_title);
	}
	if ($title_len < 40){
		if ($title_len == 0) {
			$return_value = $return_if_empty;
		} else {
			$return_value = $the_title;
		}
	} else {
		$return_value = substr($the_title, 0, 36).' [...]';
	}
	return $return_value;
}
// This code adds the links in the settings section of the plugin
if ( ! function_exists( 'pr_edit_toolbar_plugin_meta' ) ) :
        function pr_edit_toolbar_plugin_meta( $links, $file ) { // add 'Plugin page' and 'Donate' links to plugin meta row
                if ( strpos( $file, 'post-edit-toolbar.php' ) !== false ) {
//                        $links = array_merge( $links, array( '<a href="http://www.webyourbusiness.com/post-edit-toolbar/#donate" title="Support the development">Donate</a>' ) );
                        $links = array_merge( $links, array( '<a href="http://wordpress.org/support/view/plugin-reviews/post-edit-toolbar#postform" title="Review-Post-Edit-Toolbar">Please Review Post-Edit-Toolar</a>' ) );
                        $links = array_merge( $links, array( '<a href="http://wordpress.org/support/plugin/post-edit-toolbar" title="Support-for-Post-Edit-Toolbar">Support</a>' ) );
                }
                return $links;
        }
        add_filter( 'plugin_row_meta', 'pr_edit_toolbar_plugin_meta', 10, 2 );
endif; // end of pr_edit_toolbar_plugin_meta()
?>