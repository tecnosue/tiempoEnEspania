<?php
/**
 * get_municipios.php
 * 
 * Obtiene los municipios de una provincia específica desde la API PublicOpenDataSoft.
 * Maneja la paginación para provincias con más de 100 municipios.
 *
 * @package ElTiempoEnEspana
 * @author  Susana Paracuellos
 * @version 1.0.0
 */

// Incluir archivo de configuración
require_once 'config.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Verificar que se ha recibido el código de la provincia
if (!isset($_GET['provincia_code']) || empty($_GET['provincia_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Es necesario especificar el código de la provincia'
    ]);
    exit;
}

// Obtener el código de la provincia
$codigo_provincia = $_GET['provincia_code'];

/**
 * Obtiene municipios de una provincia con soporte para paginación
 * 
 * @param string $codigo_provincia Código de la provincia
 * @param int $offset Número de resultados a saltar (para paginación)
 * @return array Datos de la respuesta de la API o array con error
 */
function obtenerMunicipios($codigo_provincia, $offset = 0) {
    // URL de la API para obtener municipios de una provincia
    $url = URL_MUNICIPIOS_BASE . $codigo_provincia . "%27&order_by=mun_name&limit=100&offset=" . $offset;
    
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
        curl_close($ch);
        return [
            'success' => false,
            'message' => 'Error cURL: ' . curl_error($ch)
        ];
    }
    
    // Cerrar la sesión cURL
    curl_close($ch);
    
    // Decodificar la respuesta JSON
    $data = json_decode($response, true);
    
    // Verificar si se pudo decodificar la respuesta
    if ($data === null) {
        return [
            'success' => false,
            'message' => 'Error al decodificar la respuesta JSON'
        ];
    }
    
    return $data;
}

/**
 * Formatea los datos de un municipio para la respuesta
 * 
 * @param array $municipio Datos del municipio desde la API
 * @return array Datos formateados con nombre y coordenadas
 */
function formatearMunicipio($municipio) {
    return [
        'name' => $municipio['mun_name'],
        'geo_point' => $municipio['geo_point_2d']['lat'] . ',' . $municipio['geo_point_2d']['lon']
    ];
}

// Array para almacenar todos los municipios
$municipios = [];

// Obtener la primera página de municipios
$data = obtenerMunicipios($codigo_provincia, 0);

// Verificar si hay error en la primera petición
if (!isset($data['success']) && isset($data['results'])) {
    // Procesar los resultados de la primera página
    foreach ($data['results'] as $municipio) {
        $municipios[] = formatearMunicipio($municipio);
    }
    
    // Si hay 100 resultados, puede haber más páginas
    if (count($data['results']) == 100) {
        $offset = 100;
        $continuarPaginacion = true;
        
        // Obtener páginas adicionales hasta que ya no haya resultados o haya un error
        while ($continuarPaginacion) {
            $nextData = obtenerMunicipios($codigo_provincia, $offset);
            
            // Verificar si la petición fue exitosa y hay resultados
            if (!isset($nextData['success']) && isset($nextData['results']) && count($nextData['results']) > 0) {
                foreach ($nextData['results'] as $municipio) {
                    $municipios[] = formatearMunicipio($municipio);
                }
                
                // Si hay menos de 100 resultados, hemos llegado al final
                if (count($nextData['results']) < 100) {
                    $continuarPaginacion = false;
                } else {
                    $offset += 100;
                }
            } else {
                // Error o no hay más resultados
                $continuarPaginacion = false;
            }
        }
    }
    
    // Devolver todos los municipios recopilados
    echo json_encode([
        'success' => true,
        'municipios' => $municipios
    ]);
} else {
    // Error en la primera petición
    echo json_encode([
        'success' => false,
        'message' => isset($data['message']) ? $data['message'] : 'No se encontraron municipios para esta provincia'
    ]);
}