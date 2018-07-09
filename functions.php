<?php

function create_post($mysqli_tainacan,$title,$content){
    $author = AUTHOR;
    $category = CATEGORY_ROOT_ID;

    $post = array(
        'post_title' =>  $title ,
        'post_status' => 'publish',
        'post_content' => $content ,
        'post_author' => $author,
        'post_type' => 'socialdb_object',
        'post_parent' => COLLECTION_ID
    );
    $post_id = wp_insert_post($post);

    //// $name = slugify( $title );
    //$insert = $mysqli_tainacan->query("INSERT INTO wp_posts ( post_author, post_name ,post_title, post_content, post_type,post_status,post_date,post_date_gmt,post_modified,post_modified_gmt,post_excerpt,to_ping,pinged,post_content_filtered)
    //VALUES ( $author, '$name' , '$title', '$content', 'socialdb_object', 'publish',NOW(),NOW(),NOW(),NOW(),'','','','');");
    if ($post_id) {
        //$post_id = $mysqli_tainacan->insert_id;
        echo 'inserindo item '. $title . PHP_EOL;
        $relantionship = $mysqli_tainacan->query("INSERT INTO wp_term_relationships (object_id, term_taxonomy_id)
             VALUES ( $post_id, $category );");
        if($relantionship !== TRUE){
            echo "Error: " . $relantionship . "<br>" . $mysqli_tainacan->error;
        }
    } else {
        echo "Error: " . $insert . "<br>" . $mysqli_tainacan->error;
    }
    return $post_id;
}



function insert_fixed_metadata($mysqli_tainacan,$post_id,$key,$value){
    global $wpdb;
    $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key LIKE '$key'");
    $fixed = $mysqli_tainacan->query("INSERT INTO $wpdb->postmeta (post_id, meta_key,meta_value)
        VALUES ( $post_id,  '$key' , '$value' );");
    if($fixed !== TRUE){
        echo "Error: " . $fixed . "<br>" . $mysqli_tainacan->error;
    }
}

function insert_regular_metadata($mysqli_tainacan,$post_id,$key,$value){
    global $wpdb;
    $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key LIKE 'socialdb_property_$key'");
    $meta = $mysqli_tainacan->query("INSERT INTO $wpdb->postmeta (post_id, meta_key,meta_value)
        VALUES ( $post_id, 'socialdb_property_$key', '$value' );");
    if($meta !== TRUE){
        echo "Error: " . $meta . "<br>" . $mysqli_tainacan->error;
    }else{
        $meta_id = $mysqli_tainacan->insert_id;
        $helper = array( array( [
            'type' => 'data', // data, term, object
            'values' => [ $meta_id ] // post_meta_id
        ] ) );

        $key = "socialdb_property_helper_" . $key ;
        $helper_value = serialize(serialize($helper));
        $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key LIKE '$key'");
        $fixed = $mysqli_tainacan->query("INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
           VALUES ( $post_id, '$key', '$helper_value' );");
    }
}


function insert_term( $mysqli_tainacan, $name, $item_id, $parent,$property_id ){
    global $wpdb;
    $category = 0;
    
     if($name === '') return false;
	
    //$term = get_term_by('name', $name, 'socialdb_category_type');
    $term = get_term_by_name_and_parent(utf8_encode(trim($name)),$parent);
    if($term){
        $category = $term->term_id;
    }else if($name === ''){
       // $array = wp_insert_term( 'Sem resposta', 'socialdb_category_type', [ 'parent'=> $parent ] );
       // if(!is_wp_error($array)){
       //      $category = $array['term_id'];
      //      add_term_meta($category,'socialdb_category_owner','1');
      //  }
    }
	
    echo $name.' '.$category.PHP_EOL;
    if( $category === 0 )
        return false;

    $relantionship = $mysqli_tainacan->query("INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id)
             VALUES ( $item_id, $category );");
    
     //if(!$relantionship) return false;

    //inserindo o helper
    $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $item_id AND meta_key LIKE 'socialdb_property_".$property_id."_cat'");
    $meta = $mysqli_tainacan->query("INSERT INTO wp_postmeta (post_id, meta_key,meta_value)
        VALUES ( $item_id, 'socialdb_property_".$property_id."_cat', '$category' );");
    if($meta !== TRUE){
        echo "Error: " . $meta . "<br>" . $mysqli_tainacan->error;
    }else {
        $meta_id = $mysqli_tainacan->insert_id;
        $helper = array(array([
            'type' => 'term', // data, term, object
            'values' => [$meta_id] // post_meta_id
        ]));

        $key = "socialdb_property_helper_" . $property_id ;
        $helper_value = serialize(serialize($helper));
        $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $item_id AND meta_key LIKE '".$key."'");
        $fixed = $mysqli_tainacan->query("INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
           VALUES ( $item_id, '$key', '$helper_value' );");
    }
}

function set_value_term($mysqli_tainacan,$item_id,$property_id, $category){
    //inserindo o helper
    $mysqli_tainacan->query("DELETE FROM wp_postmeta WHERE post_id = $item_id AND meta_key LIKE 'socialdb_property_".$property_id."_cat'");
    $meta = $mysqli_tainacan->query("INSERT INTO wp_postmeta (post_id, meta_key,meta_value)
        VALUES ( $item_id, 'socialdb_property_".$property_id."_cat', '$category' );");
    if($meta !== TRUE){
        echo "Error: " . $meta . "<br>" . $mysqli_tainacan->error;
    }else {
        $meta_id = $mysqli_tainacan->insert_id;
        $helper = array(array([
            'type' => 'term', // data, term, object
            'values' => [$meta_id] // post_meta_id
        ]));

        $key = "socialdb_property_helper_" . $property_id ;
        $helper_value = serialize(serialize($helper));
        $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $item_id AND meta_key LIKE '".$key."'");
        $fixed = $mysqli_tainacan->query("INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
           VALUES ( $item_id, '$key', '$helper_value' );");
        $mysqli_tainacan->query("INSERT INTO wp_term_relationships (object_id, term_taxonomy_id)
             VALUES ( $item_id, $category );");
    }
}

function get_term_by_name_and_parent($name,$parent){
    global $wpdb;
    if($parent === PAIS_TERM) $name = strtoupper($name);
    $query = "SELECT t.* FROM $wpdb->terms t INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id  WHERE tt.parent = $parent AND t.name LIKE '$name'";
    $result = $wpdb->get_results($query);
    if ($result && is_array($result)) {
        return ( isset( $result[0] )) ? $result[0] : $result;
    } elseif ($result) {
        return $result;
    } else {
        return false;
    }

}

function term_update( $mysqli_tainacan, $name, $item_id, $parent,$property_id ){
    global $wpdb;
    $category = 0;

    //$term = get_term_by('name', $name, 'socialdb_category_type');
    $term = get_term_by_name_and_parent($name,$parent);

    if($term){
        return false;
    }else if(trim($name) !== ''){
        $array = wp_insert_term( trim($name), 'socialdb_category_type', [ 'parent'=> $parent ] );
        if(!is_wp_error($array)){
            $category = $array['term_id'];
            add_term_meta($category,'socialdb_category_owner','1');
        }

        if( $category === 0 )
            return false;

        $relantionship = $mysqli_tainacan->query("INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id)
             VALUES ( $item_id, $category );");

        //inserindo o helper
        $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $item_id AND meta_key LIKE 'socialdb_property_".$property_id."_cat'");
        $meta = $mysqli_tainacan->query("INSERT INTO wp_postmeta (post_id, meta_key,meta_value)
        VALUES ( $item_id, 'socialdb_property_".$property_id."_cat', '$category' );");
        if($meta !== TRUE){
            echo "Error: " . $meta . "<br>" . $mysqli_tainacan->error;
        }else {
            $meta_id = $mysqli_tainacan->insert_id;
            $helper = array(array([
                'type' => 'term', // data, term, object
                'values' => [$meta_id] // post_meta_id
            ]));

            $key = "socialdb_property_helper_" . $property_id ;
            $helper_value = serialize(serialize($helper));
            $mysqli_tainacan->query("DELETE FROM $wpdb->postmeta WHERE post_id = $item_id AND meta_key LIKE '".$key."'");
            $fixed = $mysqli_tainacan->query("INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
           VALUES ( $item_id, '$key', '$helper_value' );");
        }
    }
}

function item_is_inserted( $patrimonio ){
    global $wpdb;

    $query = "SELECT * FROM $wpdb->postmeta WHERE meta_value LIKE '$patrimonio' AND meta_key LIKE 'socialdb_property_" . PATRIMONIO."'  ORDER BY meta_id";
    $result = $wpdb->get_results($query);
    if ($result && is_array($result)) {
        return ( isset( $result[0] )) ? $result[0] : $result;
    } elseif ($result && isset($result->ID)) {
        return $result;
    } else {
        return false;
    }
}

