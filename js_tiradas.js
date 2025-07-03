$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaTiradas').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        },
        "ajax": {
            "url": "backend.php",
            "type": "POST",
            "data": {
                action: 'listar_tiradas'
            }
        },
        "columns": [{
                "data": "id_tirada"
            },
            {
                "data": "fecha_inicio"
            },
            {
                "data": "fecha_final"
            },
            {
                "data": "responsable"
            },
            {
                "data": "maquina"
            },
            {
                "data": "operadores"
            },
            {
                "data": "estatus_tirada"
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return '<button class="btn btn-sm btn-success" onclick="capturarProduccion(' +
                        row.id_tirada +
                        ')"><i class="fas fa-clipboard-check"></i> Capturar Producción</button>';
                }
            }
        ]
    });

    // Cargar datos iniciales
    cargarResponsables();
    cargarMaquinas();
    cargarOperadores();

    // Configurar fechas por defecto
    const ahora = new Date();
    const fechaInicio = new Date(ahora.getTime() - ahora.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    const fechaFinal = new Date(ahora.getTime() + 8 * 60 * 60 * 1000 - ahora.getTimezoneOffset() * 60000)
        .toISOString().slice(0, 16);

    $('#fecha_inicio').val(fechaInicio);
    $('#fecha_final').val(fechaFinal);

    // Configurar drag and drop desde el inicio
    setupDragAndDrop();

    // Manejar envío del formulario
    $('#formTirada').on('submit', function(e) {
        e.preventDefault();

        const operadoresSeleccionados = [];
        $('#operadores-seleccionados .operador-item').each(function() {
            operadoresSeleccionados.push($(this).data('id'));
        });

        if (operadoresSeleccionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Operadores requeridos',
                text: 'Debe seleccionar al menos un operador para la tirada.'
            });
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'registrar_tirada');
        formData.append('operadores', JSON.stringify(operadoresSeleccionados));

        $.ajax({
            url: 'backend.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Limpiar formulario
                    $('#formTirada')[0].reset();
                    $('#operadores-seleccionados').html(
                        '<p class="text-muted text-center">Arrastra operadores aquí o haz doble clic</p>'
                    );
                    cargarOperadores();

                    // Actualizar fechas
                    $('#fecha_inicio').val(fechaInicio);
                    $('#fecha_final').val(fechaFinal);

                    // Recargar tabla si está en la pestaña de listado
                    if ($('#listado-tab').hasClass('active')) {
                        $('#tablaTiradas').DataTable().ajax.reload();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud'
                });
            }
        });
    });
});

function cargarResponsables() {
    $.post('backend.php', {
        action: 'cargar_responsables'
    }, function(response) {
        const responsables = JSON.parse(response);
        let options = '<option value="">Seleccionar responsable...</option>';
        responsables.forEach(function(responsable) {
            options +=
                `<option value="${responsable.id}">${responsable.nombre} ${responsable.appaterno} ${responsable.apmaterno}</option>`;
        });
        $('#responsable').html(options);
    });
}

function cargarMaquinas() {
    $.post('backend.php', {
        action: 'cargar_maquinas'
    }, function(response) {
        const maquinas = JSON.parse(response);
        let options = '<option value="">Seleccionar máquina...</option>';
        maquinas.forEach(function(maquina) {
            options +=
                `<option value="${maquina.id_maquina}">${maquina.nombre_maquina} - ${maquina.modelo}</option>`;
        });
        $('#maquina').html(options);
    });
}

function cargarOperadores() {
    $.post('backend.php', {
        action: 'cargar_operadores'
    }, function(response) {
        const operadores = JSON.parse(response);
        let html = '';
        operadores.forEach(function(operador) {
            html += `<div class="operador-item" data-id="${operador.id}" draggable="true">
                    <i class="fas fa-user"></i> ${operador.nombre} ${operador.appaterno} ${operador.apmaterno}
                </div>`;
        });
        $('#operadores-disponibles').html(html);
        
        // Actualizar placeholders después de cargar
        updatePlaceholders();
    });
}

function setupDragAndDrop() {
    // Drag and drop functionality - usar delegación de eventos
    $(document).on('dragstart', '.operador-item', function(e) {
        $(this).addClass('dragging');
        e.originalEvent.dataTransfer.setData('text/plain', '');
        e.originalEvent.dataTransfer.effectAllowed = 'move';
    });

    $(document).on('dragend', '.operador-item', function(e) {
        $(this).removeClass('dragging');
    });

    // Eventos para contenedores
    $(document).on('dragover', '.operadores-disponibles, .operadores-seleccionados', function(e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        $(this).addClass('drag-over');
    });

    $(document).on('dragleave', '.operadores-disponibles, .operadores-seleccionados', function(e) {
        $(this).removeClass('drag-over');
    });

    $(document).on('drop', '.operadores-disponibles, .operadores-seleccionados', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');

        const draggedElement = $('.operador-item.dragging');
        if (draggedElement.length) {
            // Remover placeholder si existe
            $(this).find('p.text-muted').remove();
            
            // Mover elemento
            $(this).append(draggedElement);
            draggedElement.removeClass('dragging');
            
            // Actualizar placeholders
            updatePlaceholders();
        }
    });

    // Double click functionality - CORREGIDO para usar delegación de eventos
    $(document).on('dblclick', '.operador-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $operador = $(this);
        const $parent = $operador.parent();
        
        // Determinar el contenedor destino
        let $destino;
        if ($parent.hasClass('operadores-disponibles') || $parent.attr('id') === 'operadores-disponibles') {
            $destino = $('#operadores-seleccionados');
        } else if ($parent.hasClass('operadores-seleccionados') || $parent.attr('id') === 'operadores-seleccionados') {
            $destino = $('#operadores-disponibles');
        }
        
        if ($destino && $destino.length) {
            // Remover placeholders
            $destino.find('p.text-muted').remove();
            
            // Mover operador
            $destino.append($operador);
            
            // Actualizar placeholders
            updatePlaceholders();
        }
    });
}

function updatePlaceholders() {
    // Actualizar placeholder de operadores seleccionados
    const $seleccionados = $('#operadores-seleccionados');
    const $disponibles = $('#operadores-disponibles');
    
    if ($seleccionados.find('.operador-item').length === 0) {
        if ($seleccionados.find('p.text-muted').length === 0) {
            $seleccionados.html('<p class="text-muted text-center">Arrastra operadores aquí o haz doble clic</p>');
        }
    } else {
        $seleccionados.find('p.text-muted').remove();
    }
    
    // Actualizar placeholder de operadores disponibles si está vacío
    if ($disponibles.find('.operador-item').length === 0) {
        if ($disponibles.find('p.text-muted').length === 0) {
            $disponibles.html('<p class="text-muted text-center">No hay operadores disponibles</p>');
        }
    } else {
        $disponibles.find('p.text-muted').remove();
    }
}

function capturarProduccion(id_tirada) {
    // Cargar datos de la tirada
    $.post('backend.php', {
        action: 'obtener_tirada',
        id_tirada: id_tirada
    }, function(response) {
        const tirada = JSON.parse(response);

        Swal.fire({
            title: 'Capturar Producción',
            html: `
                    <div class="text-start">
                        <h6>Tirada #${tirada.id_tirada}</h6>
                        <p><strong>Responsable:</strong> ${tirada.responsable}</p>
                        <p><strong>Operadores:</strong> ${tirada.operadores}</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de Captura:</label>
                            <select id="tipo_captura" class="form-select">
                                <option value="general">Captura General</option>
                                <option value="individual">Captura Individual por Operador</option>
                            </select>
                        </div>
                        
                        <div id="captura_general" class="mb-3">
                            <label class="form-label">Producto:</label>
                            <select id="producto_general" class="form-select mb-2">
                                <option value="">Seleccionar producto...</option>
                            </select>
                            <label class="form-label">Cantidad:</label>
                            <input type="number" id="cantidad_general" class="form-control" min="1">
                        </div>
                        
                        <div id="captura_individual" style="display:none;" class="mb-3">
                            <label class="form-label">Operador:</label>
                            <select id="operador_individual" class="form-select mb-2">
                                <option value="">Seleccionar operador...</option>
                            </select>
                            <label class="form-label">Producto:</label>
                            <select id="producto_individual" class="form-select mb-2">
                                <option value="">Seleccionar producto...</option>
                            </select>
                            <label class="form-label">Cantidad:</label>
                            <input type="number" id="cantidad_individual" class="form-control" min="1">
                            <button type="button" id="agregar_individual" class="btn btn-sm btn-primary mt-2">Agregar</button>
                        </div>
                        
                        <div id="lista_individual" style="display:none;">
                            <h6>Producciones Registradas:</h6>
                            <ul id="producciones_list" class="list-group mb-3"></ul>
                        </div>
                    </div>
                `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Finalizar Tirada',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                // Cargar productos
                cargarProductos();

                // Cargar operadores de la tirada
                cargarOperadoresTirada(tirada.operadores_ids);

                // Manejar cambio de tipo de captura
                $('#tipo_captura').on('change', function() {
                    if ($(this).val() === 'individual') {
                        $('#captura_general').hide();
                        $('#captura_individual, #lista_individual').show();
                    } else {
                        $('#captura_general').show();
                        $('#captura_individual, #lista_individual').hide();
                    }
                });

                // Manejar agregar producción individual
                $('#agregar_individual').on('click', function() {
                    const operador = $('#operador_individual option:selected').text();
                    const operador_id = $('#operador_individual').val();
                    const producto = $('#producto_individual option:selected').text();
                    const producto_id = $('#producto_individual').val();
                    const cantidad = $('#cantidad_individual').val();

                    if (operador_id && producto_id && cantidad) {
                        const item = `<li class="list-group-item d-flex justify-content-between align-items-center" 
                                        data-operador="${operador_id}" data-producto="${producto_id}" data-cantidad="${cantidad}">
                                ${operador} - ${producto}: ${cantidad} unidades
                                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).parent().remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </li>`;
                        $('#producciones_list').append(item);

                        // Limpiar campos
                        $('#operador_individual, #producto_individual').val('');
                        $('#cantidad_individual').val('');
                    } else {
                        Swal.showValidationMessage('Complete todos los campos');
                    }
                });
            },
            preConfirm: () => {
                const tipo = $('#tipo_captura').val();
                let producciones = [];

                if (tipo === 'general') {
                    const producto = $('#producto_general').val();
                    const cantidad = $('#cantidad_general').val();

                    if (!producto || !cantidad) {
                        Swal.showValidationMessage('Complete todos los campos');
                        return false;
                    }

                    producciones.push({
                        tipo: 'general',
                        producto: producto,
                        cantidad: cantidad
                    });
                } else {
                    $('#producciones_list li').each(function() {
                        producciones.push({
                            tipo: 'individual',
                            operador: $(this).data('operador'),
                            producto: $(this).data('producto'),
                            cantidad: $(this).data('cantidad')
                        });
                    });

                    if (producciones.length === 0) {
                        Swal.showValidationMessage('Agregue al menos una producción');
                        return false;
                    }
                }

                return {
                    producciones: producciones
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Procesar la finalización de la tirada
                $.post('backend.php', {
                    action: 'finalizar_tirada',
                    id_tirada: id_tirada,
                    producciones: JSON.stringify(result.value.producciones)
                }, function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Tirada Finalizada!',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#tablaTiradas').DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message
                        });
                    }
                });
            }
        });
    });
}

function cargarProductos() {
    $.post('backend.php', {
        action: 'cargar_productos'
    }, function(response) {
        const productos = JSON.parse(response);
        let options = '<option value="">Seleccionar producto...</option>';
        productos.forEach(function(producto) {
            options +=
                `<option value="${producto.id_producto}">${producto.nombre_producto} - ${producto.tipo_producto}</option>`;
        });
        $('#producto_general, #producto_individual').html(options);
    });
}

function cargarOperadoresTirada(operadores_ids) {
    const ids = operadores_ids.split(',');
    let options = '<option value="">Seleccionar operador...</option>';

    $.post('backend.php', {
        action: 'cargar_operadores_especificos',
        ids: JSON.stringify(ids)
    }, function(response) {
        const operadores = JSON.parse(response);
        operadores.forEach(function(operador) {
            options +=
                `<option value="${operador.id}">${operador.nombre} ${operador.appaterno} ${operador.apmaterno}</option>`;
        });
        $('#operador_individual').html(options);
    });
}

// Recargar tabla cuando se cambie a la pestaña de listado
$('#listado-tab').on('shown.bs.tab', function() {
    $('#tablaTiradas').DataTable().ajax.reload();
});