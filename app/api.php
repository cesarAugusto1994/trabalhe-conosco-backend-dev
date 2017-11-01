<?php

$api = $app['controllers_factory'];

function getDataFiles(string $file)
{
    $usuarios = fopen(__DIR__ . '/../res/' . $file, 'r');

    while (($line = fgets($usuarios)) !== false) {

        $item = explode(',', $line);

        yield $item[0] = [
            'id' => $item[0],
            'nome' => $item[1],
            'username' => $item[2],
        ];
    }

    fclose($usuarios);
}

function getListaRelevancia(string $listaRelevancia)
{
    $resultado = [];
    $arquivo1 = fopen(__DIR__ . '/../res/' . $listaRelevancia, 'r');

    while (($line = fgets($arquivo1)) !== false) {
        array_push($resultado, $line);
    }

    fclose($arquivo1);

    return $resultado;
}

function encontrarOcorrecias(array $linha, string $q)
{
    foreach ($linha as $data) {
        if (stristr($data, $q)) {
            return true;
        }
    }

    return false;
}

function existeNaListaRelevancia(string $id, $prioriade = 1)
{
    switch ($prioriade) {
        case 1 :
            $listaRelevancia = getListaRelevancia('lista_relevancia_1.txt');
            break;
        case 2:
            $listaRelevancia = getListaRelevancia('lista_relevancia_2.txt');
            break;
        default:
            throw new Exception("Informe uma lista de Prioridade.");
    }

    foreach ($listaRelevancia as $item) {
        if (strstr($item, $id)) {
            return true;
        }
    }

    return false;
}

function tratarOcorrencias(string $arquivo, string $q)
{
    $lista1 = $lista2 = $lista = [];

    foreach (getDataFiles($arquivo) as $dataFile) {

        if (!encontrarOcorrecias($dataFile, $q)) {
            continue;
        }

        if (existeNaListaRelevancia($dataFile['id'])) {
            $lista1[] = $dataFile;
        } else if (existeNaListaRelevancia($dataFile['id'], 2)) {
            $lista2[] = $dataFile;
        } else {
            $lista[] = $dataFile;
        };

    }

    return array_merge($lista1, $lista2, $lista);
}

$api->get('{tokenId}/users/{q}', function ($tokenId, $q) use ($app) {

    if ($tokenId != API_TOKEN) {
        return $app->json([
            'classe' => 'Erro',
            'mensagem' => 'Voce nao tem permissao para acessar este recurso.',
        ], \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
    }

    $resultado = tratarOcorrencias('users.csv', $q);

    if (empty($resultado)) {
        return $app->json([
            'classe' => 'sucesso',
            'mensagem' => 'Nenhum Usuario Encontrado.',
            'data' => []
        ], \Symfony\Component\HttpFoundation\Response::HTTP_ACCEPTED);
    }

    return $app->json([
        'classe' => 'sucesso',
        'mensagem' => 'Usuario(s) Encontrado(s).',
        'data' => $resultado
    ], \Symfony\Component\HttpFoundation\Response::HTTP_ACCEPTED);

});

return $api;