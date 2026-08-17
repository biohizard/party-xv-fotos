<?php

// If this is a preflight (OPTIONS) request, exit early
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}


header('Content-Type: application/json; charset=utf-8');

$directorio = __DIR__ . '/thumbnails';

// Validar parámetro
if (!isset($_GET['grupo']) || $_GET['grupo'] == '') {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Debe indicar el grupo.'
    ], JSON_PRETTY_PRINT);

    exit;
}

$grupo = preg_replace('/[^0-9]/', '', $_GET['grupo']);

$imagenes = [];

// Buscar imágenes
foreach (glob($directorio . '/*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE) as $archivo) {

    $nombre = basename($archivo);

    if (preg_match('/^[^-]+-' . preg_quote($grupo, '/') . '--/i', $nombre)) {
        $imagenes[] = $nombre;
    }
}

// Respuesta
echo json_encode([
    'success'   => true,
    'grupo'     => $grupo,
    'total'     => count($imagenes),
    'imagenes'  => $imagenes
], JSON_PRETTY_PRINT);