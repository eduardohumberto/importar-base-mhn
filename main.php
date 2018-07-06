<?php
include('config.php');
include('functions.php');

$counter = 0;
$max_database = 361;
$mysqli_tainacan = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//buscando os dados do mhn
$csvFile = file('Aquisicoes2017_ReservaTecnica.csv');
foreach ($csvFile as $line) {
    $item = str_getcsv($line,';');
    if ($item && count($item) === 34) {
        $post_id = $item[0];
        if($post_id === 'ID') continue;

        $title = ucfirst(strtolower( utf8_encode($item[1]) ) );
        $description = utf8_encode($item[2]);
        $Revisado = utf8_encode($item[9]); // REVISION #term
        $Classes = utf8_encode($item[10]); // CLASSE #term
        $Pais_origem = utf8_encode($item[11]); // PAIS #term
        $Autorizacao_uso = utf8_encode($item[12]); // PODESERPUBLICADO #term
        $situacao_imagem = utf8_encode($item[13]);
        $fonte_revisada = utf8_encode($item[14]); // PODESERPUBLICADO #term
        $situacao = utf8_encode($item[15]); //SITUACAO #term
        $patrimonio = $item[16]; // PATRIMONIO
        $titulo = $item[17]; // TITULO
        $altura = str_replace('.',',', $item[18]); // ALTURA
        $largura = str_replace('.',',', $item[19]); // LARGURA
        $comprimento = str_replace('.',',', $item[20]); // COMPRIMENTO
        $termos = $item[21]; // TERMOS
        $aquisicao = $item[22];// AQUI
        $peso = $item[23]; // PESO
        $tecnicas = $item[24]; // TECNICAS
        $data = $item[25]; // DATAPRO
        $processo = $item[26]; // PROCE
        $conservacao = $item[27]; // ECON
        $valor = str_replace('.',',', $item[28]); // VALOR
        $materiais = $item[29]; // MATERIAIS
        $localizacao_atual = $item[30]; // DEST
        $codigo_classe = $item[31]; // CLASSE_NUM
        $autor = $item[32]; // AUTOR

        //executando o script
        update_post($mysqli_tainacan,$post_id, $title, $description);
        echo "--- ITEM - " . $counter . '/'. $max_database . PHP_EOL;

        insert_term( $mysqli_tainacan, $Revisado, $post_id,REVISION_TERM,REVISION  );
        insert_term( $mysqli_tainacan, $Classes, $post_id,CLASSE_TERM,CLASSE  );
        insert_term( $mysqli_tainacan, $Pais_origem, $post_id , PAIS_TERM,PAIS );
        insert_term( $mysqli_tainacan, $Autorizacao_uso, $post_id , PODESERPUBLICADO_TERM,PODESERPUBLICADO );
        insert_term( $mysqli_tainacan, $fonte_revisada, $post_id , FONTEREVISADA_TERM, FONTEREVISADA );
        insert_term( $mysqli_tainacan, $situacao, $post_id , SITUACAO_TERM, SITUACAO );
        insert_term( $mysqli_tainacan, $situacao_imagem, $post_id , SITUACAOIMAGEM_TERM, SITUACAOIMAGEM );

        insert_regular_metadata($mysqli_tainacan,$post_id,PATRIMONIO,$patrimonio);
        insert_regular_metadata($mysqli_tainacan,$post_id,TITULO,$titulo);

        insert_regular_metadata($mysqli_tainacan,$post_id,ALTURA,$altura);
        insert_regular_metadata($mysqli_tainacan,$post_id,LARGURA,$largura);
        insert_regular_metadata($mysqli_tainacan,$post_id,COMPRIMENTO,$comprimento);
        insert_regular_metadata($mysqli_tainacan,$post_id,TERMOS,$termos);//ok
        insert_regular_metadata($mysqli_tainacan,$post_id,AQUI,$aquisicao);
        insert_regular_metadata($mysqli_tainacan,$post_id,PESO,$peso);
        insert_regular_metadata($mysqli_tainacan,$post_id,TECNICAS,$tecnicas);
        insert_regular_metadata($mysqli_tainacan,$post_id,DATAPRO,$data);
        insert_regular_metadata($mysqli_tainacan,$post_id,PROCE,$processo);
        insert_regular_metadata($mysqli_tainacan,$post_id,ECON,$conservacao);
        insert_regular_metadata($mysqli_tainacan,$post_id,VALOR,$valor);
        insert_regular_metadata($mysqli_tainacan,$post_id,MATERIAIS,$materiais);
        insert_regular_metadata($mysqli_tainacan,$post_id,DEST,$localizacao_atual);
        insert_regular_metadata($mysqli_tainacan,$post_id,CLASSE_NUM,$codigo_classe);
        insert_regular_metadata($mysqli_tainacan,$post_id,AUTOR,$autor);
        $counter++;
    }
}


