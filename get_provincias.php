<?php
/**
 * get_provincias.php
 * 
 * Obtiene las provincias de una comunidad autónoma específica desde la API PublicOpenDataSoft.
 *
 * @package ElTiempoEnEspana
 * @author  Susana Paracuellos
 * @version 1.0.0
 */

// Incluir archivo de configuración
require_once 'config.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Verificar que se ha recibido el código de la comunidad autónoma
if (!isset($_GET['comunidad_code']) || empty($_GET['comunidad_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Es necesario especificar el código de la comunidad autónoma'
    ]);
    exit;
}

// Obtener el código de la comunidad autónoma
$codigo_comunidad = $_GET['comunidad_code'];

// URL de la API para obtener provincias de una comunidad autónoma
$url = URL_PROVINCIAS_BASE . $codigo_comunidad . "%27&limit=100";

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
        'message' => 'No se encontraron provincias para esta comunidad autónoma'
    ]);
    exit;
}

/**
 * Procesa los resultados de la API y formatea las provincias
 * 
 * @param array $results Resultados de la API
 * @return array Array de provincias con código y nombre
 */
function formatearProvincias($results) {
    $provincias = [];
    foreach ($results as $provincia) {
        $provincias[] = [
            'code' => $provincia['prov_code'],
            'name' => $provincia['prov_name']
        ];
    }
    return $provincias;
}

// Procesar y devolver la respuesta formateada
$provincias = formatearProvincias($data['results']);
echo json_encode([
    'success' => true,
    'provincias' => $provincias
]);