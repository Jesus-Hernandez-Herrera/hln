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

        .tirada-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }

        .tirada-card.en-curso {
            border-left-color: #28a745;
        }

        .tirada-card.finalizada {
            border-left-color: #6c757d;
        }

        .badge-status {
            font-size: 0.8em;
        }

        .tirada-info {
            font-size: 0.9em;
        }

        .produccion-item {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 8px;
            margin: 5px 0;
        }

        .scroll-container {
            max-height: 400px;
            overflow-y: auto;
        }

        .section-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Título Principal -->
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-industry"></i> Sistema de Gestión de Tiradas de Producción
                </h1>
            </div>
        </div>

        <!-- Sección Superior: Registro y Tiradas en Curso -->
        <div class="row mb-4">
            <!-- Registro de Tiradas -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Registrar Nueva Tirada</h5>
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
                                        <select class="form-select" id="responsable" name="responsable" required>
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
                                        <p class="text-muted text-center">Arrastra operadores aquí o haz doble clic</p>
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

            <!-- Tiradas en Curso -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Tiradas en Curso</h5>
                    </div>
                    <div class="card-body">
                        <div class="scroll-container" id="tiradas-en-curso">
                            <!-- Las tiradas en curso se cargarán aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección Inferior: Tiradas Finalizadas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> Tiradas Finalizadas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaTiradasFinalizadas" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Final</th>
                                        <th>Responsable</th>
                                        <th>Máquina</th>
                                        <th>Operadores</th>
                                        <th>Producción Total</th>
                                        <th>Tipos de Productos</th>
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

    <!-- Modal para detalles de producción -->
    <div class="modal fade" id="modalDetalleProduccion" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contenidoDetalleProduccion">
                    <!-- Contenido dinámico -->
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
        $(document).ready(function () {
            // Inicializar tabla de tiradas finalizadas
            $('#tablaTiradasFinalizadas').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                },
                "ajax": {
                    "url": "backend.php",
                    "type": "POST",
                    "data": {
                        action: 'listar_tiradas_finalizadas'
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
                    "data": "produccion_total",
                    "render": function (data, type, row) {
                        return '<span class="badge bg-success fs-6">' + data +
                            ' unidades</span>';
                    }
                },
                {
                    "data": "productos",
                    "render": function (data, type, row) {
                        return '<span class="badge bg-primary fs-6">' + data + ' tipos</span>';
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return '<button class="btn btn-sm btn-info" onclick="verDetalleProduccion(' +
                            row.id_tirada +
                            ')"><i class="fas fa-eye"></i> Ver Detalle</button>';
                    }
                }
                ]
            });

            // Cargar datos iniciales
            cargarResponsables();
            cargarMaquinas();
            cargarOperadores();
            cargarTiradasEnCurso();

            // Configurar fechas por defecto
            const ahora = new Date();
            const fechaInicio = new Date(ahora.getTime() - ahora.getTimezoneOffset() * 60000).toISOString().slice(0,
                16);
            const fechaFinal = new Date(ahora.getTime() + 8 * 60 * 60 * 1000 - ahora.getTimezoneOffset() * 60000)
                .toISOString().slice(0, 16);

            $('#fecha_inicio').val(fechaInicio);
            $('#fecha_final').val(fechaFinal);

            // Actualizar tiradas en curso cada 30 segundos
            setInterval(cargarTiradasEnCurso, 30000);

            // Manejar envío del formulario
            $('#formTirada').on('submit', function (e) {
                e.preventDefault();

                const operadoresSeleccionados = [];
                $('#operadores-seleccionados .operador-item').each(function () {
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
                    success: function (response) {
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
                            cargarTiradasEnCurso();

                            // Actualizar fechas
                            $('#fecha_inicio').val(fechaInicio);
                            $('#fecha_final').val(fechaFinal);

                            // Recargar tabla de tiradas finalizadas
                            $('#tablaTiradasFinalizadas').DataTable().ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message
                            });
                        }
                    },
                    error: function () {
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

        function cargarTiradasEnCurso() {
            $.post('backend.php', {
                action: 'listar_tiradas_en_curso'
            }, function (response) {
                const tiradas = JSON.parse(response);
                let html = '';

                if (tiradas.length === 0) {
                    html =
                        '<div class="text-center text-muted"><i class="fas fa-inbox fa-3x mb-3"></i><p>No hay tiradas en curso</p></div>';
                } else {
                    tiradas.forEach(function (tirada) {
                        const fechaInicio = new Date(tirada.fecha_inicio).toLocaleString();
                        const fechaFinal = new Date(tirada.fecha_final).toLocaleString();

                        html += `
                        <div class="card tirada-card en-curso">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">Tirada #${tirada.id_tirada}</h6>
                                    <span class="badge bg-success badge-status">En Curso</span>
                                </div>
                                <div class="tirada-info">
                                    <p class="mb-1"><i class="fas fa-cog"></i> <strong>Máquina:</strong> ${tirada.maquina}</p>
                                    <p class="mb-1"><i class="fas fa-user-tie"></i> <strong>Responsable:</strong> ${tirada.responsable}</p>
                                    <p class="mb-1"><i class="fas fa-users"></i> <strong>Operadores:</strong> ${tirada.operadores}</p>
                                    <p class="mb-1"><i class="fas fa-play"></i> <strong>Inicio:</strong> ${fechaInicio}</p>
                                    <p class="mb-2"><i class="fas fa-stop"></i> <strong>Fin:</strong> ${fechaFinal}</p>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-success w-100" onclick="capturarProduccion(${tirada.id_tirada})">
                                        <i class="fas fa-clipboard-check"></i> Capturar Producción
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                }

                $('#tiradas-en-curso').html(html);
            });
        }

        function verDetalleProduccion(id_tirada) {
            $.post('backend.php', {
                action: 'obtener_detalle_produccion',
                id_tirada: id_tirada
            }, function (response) {
                const data = JSON.parse(response);

                let html = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información de la Tirada</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><td><strong>ID:</strong></td><td>${data.tirada.id_tirada}</td></tr>
                                    <tr><td><strong>Fecha Inicio:</strong></td><td>${data.tirada.fecha_inicio}</td></tr>
                                    <tr><td><strong>Fecha Final:</strong></td><td>${data.tirada.fecha_final}</td></tr>
                                    <tr><td><strong>Responsable:</strong></td><td>${data.tirada.responsable}</td></tr>
                                    <tr><td><strong>Máquina:</strong></td><td>${data.tirada.maquina}</td></tr>
                                    <tr><td><strong>Operadores:</strong></td><td>${data.tirada.operadores}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen de Producción</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border rounded p-3">
                                            <h3 class="text-success mb-0">${data.resumen.total_producido}</h3>
                                            <small class="text-muted">Total Producido</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border rounded p-3">
                                            <h3 class="text-primary mb-0">${data.resumen.productos_diferentes}</h3>
                                            <small class="text-muted">Tipos de Productos</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border rounded p-3">
                                            <h3 class="text-warning mb-0">${data.resumen.operadores_activos}</h3>
                                            <small class="text-muted">Operadores Activos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-boxes"></i> Producción por Producto</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Tipo</th>
                                                <th>Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                `;

                data.detalle_productos.forEach(function (producto) {
                    html += `
                        <tr>
                            <td>${producto.nombre_producto}</td>
                            <td><span class="badge bg-info">${producto.tipo_producto}</span></td>
                            <td><strong>${producto.cantidad_total}</strong></td>
                        </tr>
                    `;
                });

                html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-user-cog"></i> Producción por Operador</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Operador</th>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                `;

                data.detalle_operadores.forEach(function (item) {
                    html += `
                        <tr>
                            <td>${item.operador}</td>
                            <td>${item.producto}</td>
                            <td><strong>${item.cantidad}</strong></td>
                            <td><small>${item.fecha_creacion}</small></td>
                        </tr>
                    `;
                });

                html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;

                $('#contenidoDetalleProduccion').html(html);
                $('#modalDetalleProduccion').modal('show');
            });
        }

        function cargarResponsables() {
            $.post('backend.php', {
                action: 'cargar_responsables'
            }, function (response) {
                const responsables = JSON.parse(response);
                let options = '<option value="">Seleccionar responsable...</option>';
                responsables.forEach(function (responsable) {
                    options +=
                        `<option value="${responsable.id}">${responsable.nombre} ${responsable.appaterno} ${responsable.apmaterno}</option>`;
                });
                $('#responsable').html(options);
            });
        }

        function cargarMaquinas() {
            $.post('backend.php', {
                action: 'cargar_maquinas'
            }, function (response) {
                const maquinas = JSON.parse(response);
                let options = '<option value="">Seleccionar máquina...</option>';
                maquinas.forEach(function (maquina) {
                    options +=
                        `<option value="${maquina.id_maquina}">${maquina.nombre_maquina} - ${maquina.modelo}</option>`;
                });
                $('#maquina').html(options);
            });
        }

        function cargarOperadores() {
            $.post('backend.php', {
                action: 'cargar_operadores'
            }, function (response) {
                const operadores = JSON.parse(response);
                let html = '';
                operadores.forEach(function (operador) {
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
            $(document).on('dragstart', '.operador-item', function (e) {
                $(this).addClass('dragging');
                e.originalEvent.dataTransfer.setData('text/plain', '');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
            });

            $(document).on('dragend', '.operador-item', function (e) {
                $(this).removeClass('dragging');
            });

            $('.operadores-disponibles, .operadores-seleccionados').on('dragover', function (e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                $(this).addClass('drag-over');
            });

            $('.operadores-disponibles, .operadores-seleccionados').on('dragleave', function (e) {
                $(this).removeClass('drag-over');
            });

            $('.operadores-disponibles, .operadores-seleccionados').on('drop', function (e) {
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
            $(document).on('dblclick', '.operador-item', function () {
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
            }, function (response) {
                const tirada = JSON.parse(response);

                Swal.fire({
                    title: 'Capturar Producción',
                    html: `
                        <div class="text-start">
                            <div class="alert alert-info">
                                <h6>Tirada #${tirada.id_tirada}</h6>
                                <p class="mb-1"><strong>Responsable:</strong> ${tirada.responsable}</p>
                                <p class="mb-0"><strong>Operadores:</strong> ${tirada.operadores}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tipo de Captura:</label>
                                <select id="tipo_captura" class="form-select">
                                    <option value="general">Captura General</option>
                                    <option value="individual">Captura Individual por Operador</option>
                                </select>
                            </div>
                            
                            <div id="captura_general" class="mb-3">
                                <label class="form-label">Cantidad Producida (General):</label>
                                <input type="number" id="cantidad_general" class="form-control" placeholder="Cantidad total">
                            </div>

                            <div id="captura_individual" class="mb-3" style="display: none;">
                                <label class="form-label">Producción por Operador:</label>
                                ${tirada.lista_operadores.map(op => `
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fas fa-user"></i> ${op.nombre}</span>
                                        <input type="number" class="form-control" data-id="${op.id}" placeholder="Cantidad producida por ${op.nombre}">
                                    </div>
                                `).join('')}
                            </div>
                        </div>    
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Finalizar Tirada',
                    cancelButtonText: 'Cancelar',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const tipo_captura = $('#tipo_captura').val();
                        const cantidad_general = $('#cantidad_general').val();
                        const producciones = [];

                        if (tipo_captura === 'general') {
                            if (!cantidad_general) {
                                Swal.showValidationMessage('Complete todos los campos');
                                return false;
                            }
                            producciones.push({
                                tipo: 'general',
                                cantidad: cantidad_general
                            });
                        } else {
                            $('#captura_individual .form-control').each(function () {
                                const id_operador = $(this).data('id');
                                const cantidad = $(this).val();
                                if (cantidad) {
                                    producciones.push({
                                        tipo: 'individual',
                                        operador: id_operador,
                                        cantidad: cantidad
                                    });
                                }
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
                }).then(result => {
                    if (result.isConfirmed) {
                        const producciones = result.value.producciones;
                        $.post('backend.php', {
                            action: 'capturar_produccion',
                            id_tirada: id_tirada,
                            producciones: JSON.stringify(producciones)
                        }, function (response) {
                            if (response === 'ok') {
                                Swal.fire('Producción capturada correctamente', '', 'success');
                            } else {
                                Swal.fire('Error al capturar la producción', '', 'error');
                            }
                        });
                    }
                });
            });
        }
    </script>