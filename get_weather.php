<?php
/**
 * get_weather.php
 * 
 * Script para obtener información meteorológica de un municipio usando WeatherAPI
 * a través de RapidAPI.
 *
 * @package     ElTiempoEnEspana
 * @subpackage  API
 * @author      Tu Nombre <tu.email@ejemplo.com>
 * @version     1.0.0
 * @license     MIT License
 */

// Incluir archivo de configuración
require_once 'config.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

/**
 * Valida y procesa las coordenadas recibidas
 * 
 * @param array $params Parámetros de la petición GET
 * @return array Resultado de la validación [válido, mensaje, coordenadas]
 */
function validarCoordenadas($params) {
    if (!isset($params['coords']) || empty($params['coords'])) {
        return [false, 'Es necesario especificar las coordenadas del municipio', null];
    }
    
    // Obtener y limpiar las coordenadas
    $coords = str_replace(' ', '', $params['coords']);
    
    return [true, '', $coords];
}

/**
 * Realiza la petición a la API de WeatherAPI
 * 
 * @param string $coords Coordenadas geográficas (latitud,longitud)
 * @return array Respuesta de la API o array con error
 */
function consultarAPI($coords) {
    // URL de la API de WeatherAPI
    $url = "https://weatherapi-com.p.rapidapi.com/forecast.json?q=" . urlencode($coords) . "&days=3&lang=es";
    
    // Inicializar cURL
    $ch = curl_init($url);
    
    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-RapidAPI-Key: " . RAPIDAPI_KEY,
        "X-RapidAPI-Host: weatherapi-com.p.rapidapi.com"
    ]);
    
    // Ejecutar la solicitud
    $response = curl_exec($ch);
    
    // Verificar si hay errores en cURL
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'message' => 'Error cURL: ' . $error];
    }
    
    // Obtener código de respuesta HTTP
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Cerrar la sesión cURL
    curl_close($ch);
    
    // Verificar el código de respuesta HTTP
    if ($httpCode !== 200) {
        return [
            'success' => false, 
            'message' => 'Error en la API: Código HTTP ' . $httpCode,
            'response' => substr($response, 0, 300)
        ];
    }
    
    // Decodificar la respuesta JSON
    $data = json_decode($response, true);
    
    // Verificar si se pudo decodificar la respuesta
    if ($data === null) {
        return [
            'success' => false, 
            'message' => 'Error al decodificar la respuesta JSON',
            'response' => substr($response, 0, 300)
        ];
    }
    
    return ['success' => true, 'data' => $data];
}

/**
 * Formatea los datos meteorológicos para la respuesta
 * 
 * @param array $data Datos meteorológicos de la API
 * @return array Datos formateados para la respuesta
 */
function formatearDatosMeteorologicos($data) {
    $resultado = [
        'success' => true,
        'current' => isset($data['current']) ? $data['current'] : null,
        'forecast' => isset($data['forecast']['forecastday']) ? $data['forecast']['forecastday'] : []
    ];
    
    // Verificar que tenemos la información mínima necesaria
    if ($resultado['current'] === null || empty($resultado['forecast'])) {
        return [
            'success' => false,
            'message' => 'La respuesta de la API no tiene los datos esperados',
            'data_keys' => array_keys($data)
        ];
    }
    
    return $resultado;
}

// Validar coordenadas
list($valido, $mensaje, $coords) = validarCoordenadas($_GET);

if (!$valido) {
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

// Consultar la API
$respuesta = consultarAPI($coords);

if (!$respuesta['success']) {
    echo json_encode($respuesta);
    exit;
}

// Formatear y devolver los datos
$resultado = formatearDatosMeteorologicos($respuesta['data']);
echo json_encode($resultado);