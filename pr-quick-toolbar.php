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
    DRAFTS ISSUES
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
    SCHEDULED ISSUES
    ========================================================================== */

    $issue_future_found = 'N';
    $issue_future = pr_recently_edited_issue_future();

    if (!empty($issue_future)) {
        // separator
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


/*
   PUBLISHED ISSUES
   ========================================================================== */

    // get list of Issues
    $issues = pr_recently_edited_issues();

    if (!empty($issues)) {
        // separator
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
        // separator
        $args = array(
            'id' => 'issue_item_c',
            'title' => '--- published ---',
            'parent' => 'issue_list',
            'href' => ''
        );
        $wp_admin_bar->add_node( $args );
    }
}

function pr_recently_edited_issues() {
	global $no_page_edits_to_show;
	$args = array(
		'posts_per_page' => $no_page_edits_to_show,
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
		'posts_per_page' => $no_issue_drafts_to_show,
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
		'posts_per_page' => $no_issue_future_to_show,
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

?>