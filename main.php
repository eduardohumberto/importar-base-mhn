<?php
include('config.php');
include('functions.php');

$counter = 0;
$max_database = 360;
//21985
$mysqli_tainacan = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//buscando os dados do mhn
$csvFile = file('Aquisicoes2017_ReservaTecnica.csv');

foreach ($csvFile as $line) {
    $item = str_getcsv($line,';');
    if ($item && count($item) === 10) {
        $post_id = $item[0];

        if($post_id === 'NÚMERO DE REGISTRO') 
            continue;

        $patrimonio = $item[0]; // PATRIMONIO    
        $title = ucfirst(strtolower( $item[1])  );
        $description = $item[2];
        $codigo_classe = $item[3]; // CLASSE_NUM
        $Classes = $item[4]; // CLASSE #term
        $processo = $item[5]; // PROCE
        $fonte = $item[6]; // fonte
        $aquisicao = html_entity_decode($item[7]);// AQUI
        $valor = str_replace('.',',', $item[8]); // VALOR

        $post_id = create_post($mysqli_tainacan,$title,$description);
            echo "--- INSERINDO ITEM - " . $counter . '/'. $max_database . PHP_EOL;

        insert_fixed_metadata($mysqli_tainacan,$post_id,'socialdb_object_dc_source',$fonte);
        insert_fixed_metadata($mysqli_tainacan,$post_id,'socialdb_object_collection',COLLECTION_ID);
        insert_term( $mysqli_tainacan, $Classes, $post_id,CLASSE_TERM,CLASSE  );

        insert_regular_metadata($mysqli_tainacan,$post_id,PATRIMONIO,$patrimonio);
        insert_regular_metadata($mysqli_tainacan,$post_id,AQUI,$aquisicao);
        insert_regular_metadata($mysqli_tainacan,$post_id,PROCE,$processo);
        insert_regular_metadata($mysqli_tainacan,$post_id,VALOR,$valor);
        insert_regular_metadata($mysqli_tainacan,$post_id,CLASSE_NUM,$codigo_classe);

        add_post_meta($post_id,'mhn_import', 'v2');
        $counter++;
    }
}


