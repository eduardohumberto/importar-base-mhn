<?php

include('config.php');
include('functions.php');

$counter = 0;

if( isset( $argv ) ){
    $init = ( $argv[1] - 1 ) * 1000 ;
    $max_itens_search = $argv[1] * 1000;
    $counter = $init + 1;
}else{
    $max_itens_search = 1000;
}


$max_database = 21982;


//buscando os dados do mhn

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'SERET');
if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//while ($init < $max_database) {
    $items = $mysqli->query("SELECT inv.PATRI, 
    inv.OBJ,
    autor.AUTO , 
    titulo.TITULO, 
    proc.FONTE,
    ( medida.ALT) AS medida_altura,
    (medida.LAR ) AS medida_largura,
    (medida.COMP) AS medida_comprimento,
    materiais.MATERIAIS,
    dest.ORIGE,
    obs.OBS,
    termos.TERMOS,
    (SELECT DESCCODIGO FROM SERETTABELAS WHERE CODIGO = autor.PAIS limit 1  ) AS PAIS,
    (SELECT DESCCODIGO FROM SERETTABELAS WHERE CODIGO = inv.CAQUI limit 1  ) AS AQUI ,
    medida.PESO,
    materiais.TECNICAS,
    dest.DEST,
     (SELECT DESCCODIGO FROM SERETTABELAS WHERE CODIGO = inv.CLASSE limit 1  ) AS CLASSE,
     inv.CLASSE AS CLASSE_NUM,
    autor.DATAPRO,
    proc.PROCE,
    (SELECT DESCCODIGO FROM SERETTABELAS WHERE CODIGO = medida.ECON limit 1  ) AS ECON,
    lbw.VALOR

    FROM `INVSERET` as inv 
    LEFT JOIN INVSERETAUTOR autor ON inv.PATRI = autor.PATRI 
    LEFT JOIN INVSERETTITU titulo ON inv.PATRI = titulo.PATRI
    LEFT JOIN INVSERETMED medida ON inv.PATRI = medida.PATRI
    LEFT JOIN INVSERETMATTEC materiais ON inv.PATRI = materiais.PATRI
    LEFT JOIN INVSERETDEST dest ON inv.PATRI = dest.PATRI
    LEFT JOIN INVSERETOBS obs ON inv.PATRI = obs.PATRI
    LEFT JOIN INVSERETTERMOS termos ON inv.PATRI = termos.PATRI
    LEFT JOIN INVSERETPROC proc ON inv.PATRI = proc.PATRI
    LEFT JOIN INVLBW lbw ON inv.PATRI = lbw.PATRI 
    limit $init,1000 ");

    while ( $result = $items->fetch_assoc() ) {
        $mysqli_tainacan = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if (mysqli_connect_errno()){
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }else{
            if( item_is_inserted( $result['PATRI'] ) ){
                echo "---  ITEM INSERIDO - " . $counter . '/'. $max_database . PHP_EOL;
                $counter++;
                continue;
            }


            $post_id = create_post($mysqli_tainacan,$result['OBJ'],$result['OBS']);
            echo "--- INSERINDO ITEM - " . $counter . '/'. $max_database . PHP_EOL;

            insert_fixed_metadata($mysqli_tainacan,$post_id,'socialdb_object_dc_source',$result['FONTE']);
            insert_fixed_metadata($mysqli_tainacan,$post_id,'socialdb_object_collection',COLLECTION_ID);
            insert_regular_metadata($mysqli_tainacan,$post_id,TITULO,$result['TITULO']);
            insert_regular_metadata($mysqli_tainacan,$post_id,AUTOR,$result['AUTO']);
            insert_regular_metadata($mysqli_tainacan,$post_id,PATRIMONIO,$result['PATRI']);
            insert_regular_metadata($mysqli_tainacan,$post_id,ALTURA,$result['medida_altura']);
            insert_regular_metadata($mysqli_tainacan,$post_id,LARGURA,$result['medida_largura']);
            insert_regular_metadata($mysqli_tainacan,$post_id,COMPRIMENTO,$result['medida_comprimento']);
            insert_regular_metadata($mysqli_tainacan,$post_id,ORIGEM,$result['ORIGE']);//ok
            insert_regular_metadata($mysqli_tainacan,$post_id,TERMOS,$result['TERMOS']);//ok
            //insert_regular_metadata($mysqli_tainacan,$post_id,PAIS,$result['PAIS']);//ok
            insert_regular_metadata($mysqli_tainacan,$post_id,AQUI,$result['AQUI']);
            insert_regular_metadata($mysqli_tainacan,$post_id,PESO,$result['PESO']);
            insert_regular_metadata($mysqli_tainacan,$post_id,TECNICAS,$result['TECNICAS']);
            insert_regular_metadata($mysqli_tainacan,$post_id,DATAPRO,$result['DATAPRO']);
            insert_regular_metadata($mysqli_tainacan,$post_id,PROCE,$result['PROCE']);
            insert_regular_metadata($mysqli_tainacan,$post_id,ECON,$result['ECON']);
            insert_regular_metadata($mysqli_tainacan,$post_id,VALOR,$result['VALOR']);
            insert_regular_metadata($mysqli_tainacan,$post_id,MATERIAIS,$result['MATERIAIS']);
            insert_regular_metadata($mysqli_tainacan,$post_id,DEST,$result['DEST']);
            insert_regular_metadata($mysqli_tainacan,$post_id,CLASSE_NUM,$result['CLASSE_NUM']);

            insert_term( $mysqli_tainacan, $result['CLASSE'], $post_id,CLASSE_TERM,CLASSE  );
            insert_term( $mysqli_tainacan, $result['PAIS'], $post_id , PAIS_TERM,PAIS );

            set_value_term($mysqli_tainacan,$post_id,REVISION, REVISION_VALUE );
        }
        $counter++;
    }

 
