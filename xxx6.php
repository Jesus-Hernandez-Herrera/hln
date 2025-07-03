<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Tiradas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css"
        rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.12/sweetalert2.min.css"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
    .operadores-disponibles,
    .operadores-seleccionados {
        min-height: 200px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 15px;
    }

    .operador-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 8px 12px;
        margin: 5px 0;
        cursor: grab;
        transition: all 0.3s ease;
    }

    .operador-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .operador-item.dragging {
        opacity: 0.5;
        transform: rotate(5deg);
    }

    .drag-over {
        background-color: #e3f2fd;
        border-color: #2196f3;
    }

    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .btn-gradient {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        color: white;
    }

    .btn-gradient:hover {
        background: linear-gradient(45deg, #0056b3, #004085);
        color: white;
    }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-industry"></i> Sistema de Gestión de Tiradas de Producción
                </h1>
            </div>
        </div>

        <!-- Pestañas -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="registro-tab" data-bs-toggle="tab" data-bs-target="#registro"
                    type="button" role="tab">
                    <i class="fas fa-plus"></i> Registrar Tirada
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado" type="button"
                    role="tab">
                    <i class="fas fa-list"></i> Tiradas en Proceso
                </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Tab Registro de Tiradas -->
            <div class="tab-pane fade show active" id="registro" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-clipboard-list"></i> Registrar Nueva Tirada</h5>
                            </div>
                            <div class="card-body">
                                <form id="formTirada">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                                <input type="datetime-local" class="form-control" id="fecha_inicio"
                                                    name="fecha_inicio" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha_final" class="form-label">Fecha Final</label>
                                                <input type="datetime-local" class="form-control" id="fecha_final"
                                                    name="fecha_final" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="responsable" class="form-label">Responsable</label>
                                                <select class="form-select" id="responsable" name="responsable"
                                                    required>
                                                    <option value="">Seleccionar responsable...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="maquina" class="form-label">Máquina</label>
                                                <select class="form-select" id="maquina" name="maquina" required>
                                                    <option value="">Seleccionar máquina...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Operadores Disponibles</h6>
                                            <div class="operadores-disponibles" id="operadores-disponibles">
                                                <!-- Los operadores se cargarán aquí -->
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Operadores Seleccionados</h6>
                                            <div class="operadores-seleccionados" id="operadores-seleccionados">
                                                <p class="text-muted text-center">Arrastra operadores aquí o haz doble
                                                    clic</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-gradient btn-lg">
                                            <i class="fas fa-save"></i> Registrar Tirada
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-info-circle"></i> Instrucciones</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-drag-area text-primary"></i> Arrastra operadores entre las
                                        columnas</li>
                                    <li><i class="fas fa-mouse-pointer text-success"></i> Haz doble clic para mover
                                        operadores</li>
                                    <li><i class="fas fa-check text-info"></i> Selecciona responsable y máquina</li>
                                    <li><i class="fas fa-calendar text-warning"></i> Define fechas de inicio y fin</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Listado de Tiradas -->
            <div class="tab-pane fade" id="listado" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cogs"></i> Tiradas en Proceso</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaTiradas" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Final</th>
                                        <th>Responsable</th>
                                        <th>Máquina</th>
                                        <th>Operadores</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.12/sweetalert2.min.js"></script>

    <script>
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
        const fechaInicio = new Date(ahora.getTime() - ahora.getTimezoneOffset() * 60000).toISOString().slice(0,
            16);
        const fechaFinal = new Date(ahora.getTime() + 8 * 60 * 60 * 1000 - ahora.getTimezoneOffset() * 60000)
            .toISOString().slice(0, 16);

        $('#fecha_inicio').val(fechaInicio);
        $('#fecha_final').val(fechaFinal);

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

        // Funcionalidad drag and drop
        setupDragAndDrop();
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
            setupDragAndDrop();
        });
    }

    function setupDragAndDrop() {
        // Drag and drop functionality
        $(document).on('dragstart', '.operador-item', function(e) {
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.setData('text/plain', '');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        });

        $(document).on('dragend', '.operador-item', function(e) {
            $(this).removeClass('dragging');
        });

        $('.operadores-disponibles, .operadores-seleccionados').on('dragover', function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            $(this).addClass('drag-over');
        });

        $('.operadores-disponibles, .operadores-seleccionados').on('dragleave', function(e) {
            $(this).removeClass('drag-over');
        });

        $('.operadores-disponibles, .operadores-seleccionados').on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');

            const draggedElement = $('.operador-item.dragging');
            if (draggedElement.length) {
                $(this).append(draggedElement);
                draggedElement.removeClass('dragging');
                updatePlaceholders();
            }
        });

        // Double click functionality
        $(document).on('dblclick', '.operador-item', function() {
            const parent = $(this).parent();
            if (parent.hasClass('operadores-disponibles')) {
                $('#operadores-seleccionados').append($(this));
            } else {
                $('#operadores-disponibles').append($(this));
            }
            updatePlaceholders();
        });
    }

    function updatePlaceholders() {
        if ($('#operadores-seleccionados .operador-item').length === 0) {
            $('#operadores-seleccionados').html(
                '<p class="text-muted text-center">Arrastra operadores aquí o haz doble clic</p>');
        } else {
            $('#operadores-seleccionados p.text-muted').remove();
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
    </script>
</body>

</html>