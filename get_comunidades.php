<?php
/**
 * get_comunidades.php
 * 
 * Obtiene el listado de comunidades autónomas de España desde la API PublicOpenDataSoft.
 *
 * @package ElTiempoEnEspana
 * @author  Susana Paracuellos
 * @version 1.0.0
 */

// Incluir archivo de configuración
require_once 'config.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// URL de la API para obtener comunidades autónomas
$url = URL_COMUNIDADES; 

// Inicializar cURL
$ch = curl_init($url);

// Configurar opciones de cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);

// Ejecutar la solicitud
$response = curl_exec($ch);

// Verificar si hay errores
if (curl_errno($ch)) {
    echo json_encode([
        'success' => false,
        'message' => 'Error cURL: ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

// Cerrar la sesión cURL
curl_close($ch);

// Decodificar la respuesta JSON
$data = json_decode($response, true);

// Verificar si se pudo decodificar la respuesta
if ($data === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al decodificar la respuesta JSON'
    ]);
    exit;
}

// Verificar si hay resultados
if (!isset($data['results']) || empty($data['results'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No se encontraron comunidades autónomas'
    ]);
    exit;
}

/**
 * Procesa los resultados de la API y formatea las comunidades autónomas
 * 
 * @param array $results Resultados de la API
 * @return array Array de comunidades con código y nombre
 */
function procesarComunidades($results) {
    $comunidades = [];
    foreach ($results as $comunidad) {
        $comunidades[] = [
            'code' => $comunidad['acom_code'],
            'name' => $comunidad['acom_name']
        ];
    }
    return $comunidades;
}

// Procesar y devolver la respuesta formateada
$comunidades = procesarComunidades($data['results']);
echo json_encode([
    'success' => true,
    'comunidades' => $comunidades
]);