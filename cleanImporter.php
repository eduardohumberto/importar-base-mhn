<?php

include('config.php');

global $wpdb;


$query = "DELETE FROM $wpdb->term_relationships  WHERE object_id IN 
( SELECT post_id FROM $wpdb->postmeta  WHERE  meta_key LIKE 'mhn_import' AND  meta_value LIKE 'v2' );";
$result = $wpdb->get_results($query);

$query = "DELETE FROM $wpdb->posts  WHERE ID IN 
( SELECT post_id FROM $wpdb->postmeta  WHERE  meta_key LIKE 'mhn_import' AND  meta_value LIKE 'v2' );";
$result = $wpdb->get_results($query);

$query = "SELECT post_id FROM $wpdb->postmeta  WHERE  meta_key LIKE 'mhn_import' AND  meta_value LIKE 'v2'";
$result = $wpdb->get_results($query);

if( $result && is_array($result) ){
    $ids = [];
    foreach( $result as $obj){
        $ids[] = $obj->post_id;
    }

    $query = "DELETE FROM $wpdb->postmeta WHERE post_id IN ( " . implode(',', $ids) . " );";
    $result = $wpdb->get_results($query);
}
