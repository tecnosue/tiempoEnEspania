/**
 * app.js
 * 
 * Aplicación Web Híbrida - Tarea UD8
 * 
 * Este archivo contiene la lógica principal de la aplicación que permite:
 * - Cargar comunidades autónomas, provincias y municipios de España
 * - Consultar información meteorológica para un municipio seleccionado
 * 
 * @package     ElTiempoEnEspana
 * @subpackage  Frontend
 * @author      Tu Nombre <tu.email@ejemplo.com>
 * @version     1.0.0
 * @license     MIT License
 * @copyright   2025 Tu Nombre
 * 
 * Usa AJAX con jQuery para comunicarse con el backend PHP
 */

/**
 * Inicialización de la aplicación cuando el DOM está listo
 * Configura los eventos para los selectores y carga los datos iniciales
 */
$(document).ready(function() {
    // Cargar las comunidades autónomas al iniciar la aplicación
    cargarComunidades();

    /**
     * Evento para el cambio de comunidad autónoma
     * Carga las provincias correspondientes a la comunidad seleccionada
     */
    $('#comunidad').on('change', function() {
        const codigoComunidad = $(this).val();
        if (codigoComunidad) {
            $('#provincia').prop('disabled', true).html('<option value="">Cargando provincias...</option>');
            cargarProvincias(codigoComunidad);
        } else {
            $('#provincia').prop('disabled', true).html('<option value="">Selecciona una provincia...</option>');
            $('#municipio').prop('disabled', true).html('<option value="">Selecciona un municipio...</option>');
            $('#consultarTiempo').prop('disabled', true);
        }
    });

    /**
     * Evento para el cambio de provincia
     * Carga los municipios correspondientes a la provincia seleccionada
     */
    $('#provincia').on('change', function() {
        const codigoProvincia = $(this).val();
        if (codigoProvincia) {
            $('#municipio').prop('disabled', true).html('<option value="">Cargando municipios...</option>');
            cargarMunicipios(codigoProvincia);
        } else {
            $('#municipio').prop('disabled', true).html('<option value="">Selecciona un municipio...</option>');
            $('#consultarTiempo').prop('disabled', true);
        }
    });

    /**
     * Evento para el cambio de municipio
     * Consulta automáticamente el tiempo para el municipio seleccionado
     */
    $('#municipio').on('change', function() {
        const municipioId = $(this).val();
        const municipioNombre = $('#municipio option:selected').text();
        if (municipioId) {
            consultarTiempo(municipioId, municipioNombre);
        }
    });
});

/**
 * Función para cargar las comunidades autónomas desde la API
 * 
 * Realiza una petición AJAX para obtener las comunidades autónomas
 * y actualiza el selector correspondiente con las opciones recibidas
 * 
 * @returns {void}
 */
function cargarComunidades() {
    $.ajax({
        url: 'get_comunidades.php',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#comunidad').prop('disabled', true).html('<option value="">Cargando comunidades...</option>');
        },
        success: function(data) {
            let options = '<option value="">Selecciona una comunidad...</option>';
            
            if (data.success && data.comunidades.length > 0) {
                data.comunidades.forEach(function(comunidad) {
                    options += `<option value="${comunidad.code}">${comunidad.name}</option>`;
                });
            } else {
                mostrarError('Error al cargar las comunidades autónomas');
            }
            
            $('#comunidad').html(options).prop('disabled', false);
        },
        error: function(xhr, status, error) {
            mostrarError('Error en la conexión: ' + error);
            $('#comunidad').html('<option value="">Error al cargar</option>').prop('disabled', false);
        }
    });
}

/**
 * Función para cargar las provincias de una comunidad autónoma
 * 
 * Realiza una petición AJAX para obtener las provincias de una comunidad
 * y actualiza el selector correspondiente con las opciones recibidas
 * 
 * @param {string} codigoComunidad - Código de la comunidad autónoma seleccionada
 * @returns {void}
 */
function cargarProvincias(codigoComunidad) {
    $.ajax({
        url: 'get_provincias.php',
        type: 'GET',
        dataType: 'json',
        data: {
            comunidad_code: codigoComunidad
        },
        success: function(data) {
            let options = '<option value="">Selecciona una provincia...</option>';
            
            if (data.success && data.provincias.length > 0) {
                data.provincias.forEach(function(provincia) {
                    options += `<option value="${provincia.code}">${provincia.name}</option>`;
                });
                $('#provincia').html(options).prop('disabled', false);
            } else {
                mostrarError('Error al cargar las provincias');
                $('#provincia').html('<option value="">Error al cargar</option>').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error en la conexión: ' + error);
            $('#provincia').html('<option value="">Error al cargar</option>').prop('disabled', false);
        }
    });
}

/**
 * Función para cargar los municipios de una provincia
 * 
 * Realiza una petición AJAX para obtener los municipios de una provincia
 * y actualiza el selector correspondiente con las opciones recibidas
 * 
 * @param {string} codigoProvincia - Código de la provincia seleccionada
 * @returns {void}
 */
function cargarMunicipios(codigoProvincia) {
    $.ajax({
        url: 'get_municipios.php',
        type: 'GET',
        dataType: 'json',
        data: {
            provincia_code: codigoProvincia
        },
        success: function(data) {
            let options = '<option value="">Selecciona un municipio...</option>';
            
            if (data.success && data.municipios.length > 0) {
                data.municipios.forEach(function(municipio) {
                    // El valor contiene las coordenadas geográficas
                    options += `<option value="${municipio.geo_point}">${municipio.name}</option>`;
                });
                $('#municipio').html(options).prop('disabled', false);
            } else {
                mostrarError('Error al cargar los municipios');
                $('#municipio').html('<option value="">Error al cargar</option>').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error en la conexión: ' + error);
            $('#municipio').html('<option value="">Error al cargar</option>').prop('disabled', false);
        }
    });
}

/**
 * Función para consultar el tiempo en el municipio seleccionado
 * 
 * Realiza una petición AJAX para obtener la información meteorológica
 * de un municipio y muestra los datos recibidos
 * 
 * @param {string} coordenadas - Coordenadas geográficas del municipio (latitud,longitud)
 * @param {string} nombreMunicipio - Nombre del municipio para mostrar
 * @returns {void}
 */
function consultarTiempo(coordenadas, nombreMunicipio) {
    // Ocultar resultados anteriores y mostrar spinner
    $('#tiempoActual, #prediccion').addClass('d-none');
    $('#loading').removeClass('d-none');
    
    $.ajax({
        url: 'get_weather.php', 
        type: 'GET',
        dataType: 'json',
        data: {
            coords: coordenadas
        },
        success: function(data) {
            $('#loading').addClass('d-none');
            
            if (data.success) {
                // Mostrar el tiempo actual
                mostrarTiempoActual(data.current, nombreMunicipio);
                
                // Mostrar la predicción
                mostrarPrediccion(data.forecast);
            } else {
                mostrarError('Error al consultar el tiempo: ' + (data.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            $('#loading').addClass('d-none');
            mostrarError('Error en la conexión: ' + error);
        }
    });
}

/**
 * Función para mostrar el tiempo actual
 * 
 * Actualiza el DOM con la información meteorológica actual
 * 
 * @param {Object} datos - Datos del tiempo actual
 * @param {string} nombreMunicipio - Nombre del municipio
 * @returns {void}
 */
function mostrarTiempoActual(datos, nombreMunicipio) {
    $('#nombreMunicipio').text(nombreMunicipio);
    $('#temperatura').text(datos.temp_c + ' °C');
    $('#condicion').text(datos.condition.text);
    $('#iconoTiempo').attr('src', datos.condition.icon);
    $('#viento').text(datos.wind_kph + ' km/h');
    $('#probLluvia').text(datos.precip_mm + ' mm');
    $('#humedad').text(datos.humidity + '%');
    
    $('#tiempoActual').removeClass('d-none');
}

/**
 * Función para mostrar la predicción a 3 días
 * 
 * Genera y actualiza el DOM con la información de predicción
 * meteorológica para los próximos días
 * 
 * @param {Array} datos - Datos de la predicción
 * @returns {void}
 */
function mostrarPrediccion(datos) {
    // Generar las pestañas para cada día
    let tabs = '';
    let tabContent = '';
    
    datos.forEach(function(dia, index) {
        const fechaObj = new Date(dia.date);
        const nombreDia = fechaObj.toLocaleDateString('es-ES', { weekday: 'long' });
        const fechaFormateada = fechaObj.toLocaleDateString('es-ES');
        const active = index === 0 ? 'active' : '';
        
        // Crear la pestaña
        tabs += `
            <li class="nav-item" role="presentation">
                <button class="nav-link ${active}" id="dia${index}-tab" data-bs-toggle="tab" 
                        data-bs-target="#dia${index}" type="button" role="tab" 
                        aria-controls="dia${index}" aria-selected="${index === 0}">
                    ${nombreDia} ${fechaFormateada}
                </button>
            </li>
        `;
        
        // Crear el contenido de la pestaña con la tabla de horas
        let horasHTML = '<div class="table-responsive"><table class="table table-striped table-forecast">';
        horasHTML += '<thead><tr><th>Hora</th><th>Temp.</th><th>Condición</th><th>Viento</th><th>Prob. Lluvia</th></tr></thead><tbody>';
        
        dia.hour.forEach(function(hora) {
            const horaFormateada = hora.time.split(' ')[1];
            horasHTML += `
                <tr>
                    <td>${horaFormateada}</td>
                    <td>${hora.temp_c} °C</td>
                    <td>
                        <img src="${hora.condition.icon}" alt="${hora.condition.text}" class="weather-icon-small">
                        <span class="d-none d-md-inline"> ${hora.condition.text}</span>
                    </td>
                    <td>${hora.wind_kph} km/h</td>
                    <td>${hora.chance_of_rain}%</td>
                </tr>
            `;
        });
        
        horasHTML += '</tbody></table></div>';
        
        tabContent += `
            <div class="tab-pane fade show ${active}" id="dia${index}" role="tabpanel" aria-labelledby="dia${index}-tab">
                <h5 class="mb-3">Previsión para ${nombreDia} ${fechaFormateada}</h5>
                ${horasHTML}
            </div>
        `;
    });
    
    // Actualizar el DOM
    $('#prediccionTabs').html(tabs);
    $('#prediccionTabContent').html(tabContent);
    $('#prediccion').removeClass('d-none');
}

/**
 * Función para mostrar mensajes de error
 * 
 * Muestra un mensaje de error al usuario mediante una alerta
 * 
 * @param {string} mensaje - Mensaje de error a mostrar
 * @returns {void}
 */
function mostrarError(mensaje) {
    alert(mensaje);
}