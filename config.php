<?php
/**
 * config.php
 * 
 * Archivo de configuración para la aplicación
 * Contiene las claves de API y otras configuraciones
 */

// Configuración de RapidAPI para WeatherAPI
define('RAPIDAPI_KEY', '4ec1566cffmsh6e45f24333596f6p1065bfjsn24c85a6c3785');

// URLs de las APIs de PublicOpenDataSoft
define('URL_COMUNIDADES', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/georef-spain-comunidad-autonoma/records?select=acom_code%2C%20acom_name&order_by=acom_name&limit=20');
define('URL_PROVINCIAS_BASE', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/georef-spain-provincia/records?select=prov_code%2C%20prov_name&where=acom_code%20%3D%20%27');
define('URL_MUNICIPIOS_BASE', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/georef-spain-municipio/records?select=mun_name%2C%20geo_point_2d&where=prov_code%20%3D%20%27');

// URL de WeatherAPI a través de RapidAPI (la definimos directamente en get_weather.php para mayor claridad)
define('URL_WEATHER_BASE', 'https://weatherapi-com.p.rapidapi.com/forecast.json?days=3&lang=es&q=');

// Configuración de la aplicación
define('APP_NAME', 'España Clima');
define('APP_VERSION', '1.0.0');

// Configuración de tiempo de espera para las peticiones cURL (en segundos)
define('CURL_TIMEOUT', 30);