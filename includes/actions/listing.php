<?php

/**
 *
 */


function ldl_action__listing( $listing ) {
    global $post;

    $terms = wp_get_post_terms($listing->ID, LDDLITE_TAX_CAT);
    if ( isset( $terms[0] ) ) {
        $term_link = add_query_arg(array(
            'show' => 'category',
            't'    => $terms[0]->slug,
        ) );
        $term_name = $terms[0]->name;
    }

    $tpl = ldl::tpl();

    $post_id = $listing->ID;
    $title = $listing->post_title;
    $meta = ldl_get_listing_meta( $post_id );
        $address = $meta['address'];
        $website = $meta['website'];
        $email   = $meta['email'];
        $phone   = $meta['phone'];
    $social = ldl_get_social( $post_id );

    if ( has_post_thumbnail( $post_id ) )
        $thumbnail = get_the_post_thumbnail( $post_id, 'directory-listing', array( 'class' => 'img-rounded' ) );
    else
        $thumbnail = '<img src="' . LDDLITE_URL . '/public/images/noimage.png" class="img-rounded">';

    $geocode = false;

    if ( !empty( $meta['geocode'] ) ) {

        $get_address = 'http://maps.google.com/maps/api/geocode/json?address=' . $meta['geocode'] . '&sensor=false';
        $geocode = wp_remote_get( $get_address );

        $output = json_decode( $geocode['body'] );

        $geocode['lat'] = $output->results[0]->geometry->location->lat;
        $geocode['lng'] = $output->results[0]->geometry->location->lng;

    }



    $tpl->assign( 'header',     ldl_get_header( 'category' ) );

    $tpl->assign( 'home',       remove_query_arg( array(
        'show',
        't',
    ) ) );


    $tpl->assign( 'id',         $post_id );
    $tpl->assign( 'title',      $title );

    $tpl->assign( 'term_link', $term_link );
    $tpl->assign( 'term_name', $term_name );

    $tpl->assign( 'thumbnail',  $thumbnail );

    $tpl->assign( 'address',    $address );
    $tpl->assign( 'website',    $website );
    $tpl->assign( 'phone',      $phone );

    $tpl->assign( 'social',     $social );

    $google_maps = ( ldl_use_google_maps() && $geocode ) ? true : false;
    $tpl->assign( 'google_maps',  $google_maps );
    $tpl->assign( 'geo', $geocode );
    $tpl->assign( 'description', wpautop( $listing->post_content ) );


    wp_enqueue_script( 'lddlite-happy' );
    add_action( 'wp_footer', '_f_draw_modal' );
    function _f_draw_modal() {

        $listing = ldl::pull();

        $to = ldl_get_listing_email( $listing->ID );
        if ( !$to )
            return;

        $modal = ldl::tpl();
        $modal->assign( 'to', $to );
        $modal->assign( 'ajaxurl', admin_url( 'admin-ajax.php' ) );
        $modal->assign( 'nonce', wp_create_nonce( 'contact-form-nonce' ) );
        $modal->assign( 'post_id', $listing->ID );

        $modal->draw( 'modal-contact' );
    }


    return $tpl->draw( 'listing', 1 );
}
