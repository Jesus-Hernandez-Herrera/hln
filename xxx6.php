<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tiradas</title>

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
            position: relative;
            overflow-y: auto;
            max-height: 300px;
        }

        .operador-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 8px 12px;
            margin: 5px 0;
            cursor: grab;
            transition: all 0.3s ease;
            user-select: none;
            position: relative;
        }

        .operador-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .operador-item:active {
            cursor: grabbing;
        }

        .operador-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }

        .drag-over {
            background-color: #e3f2fd !important;
            border-color: #2196f3 !important;
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

        /* Mejorar la apariencia de los placeholders */
        .operadores-disponibles p.text-muted,
        .operadores-seleccionados p.text-muted {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            pointer-events: none;
        }

        /* Asegurar que los contenedores mantengan su altura */
        .operadores-disponibles:empty,
        .operadores-seleccionados:empty {
            min-height: 200px;
        }

        /* Mejorar la selección de operadores */
        .operadores-seleccionados {
            background-color: #f8f9fa;
        }

        .operadores-seleccionados .operador-item {
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .operadores-seleccionados .operador-item:hover {
            background-color: #c3e6cb;
        }

        /* Indicador visual para double click */
        .operador-item::after {
            content: "Doble clic para mover";
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10px;
            color: #6c757d;
            opacity: 0;
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }

        .operador-item:hover::after {
            opacity: 1;
        }

        /* Estilos para las tablas */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section-title {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            margin: 0;
            font-size: 1.1em;
        }

        .table-responsive {
            border-radius: 0 0 8px 8px;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .operadores-disponibles,
            .operadores-seleccionados {
                min-height: 150px;
                max-height: 200px;
            }
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

        <div class="row">
            <!-- Formulario de Registro (Izquierda) -->
            <div class="col-lg-6">
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
                                    <h6><i class="fas fa-users"></i> Operadores Disponibles</h6>
                                    <div class="operadores-disponibles" id="operadores-disponibles">
                                        <p class="text-muted text-center">Cargando operadores...</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-user-check"></i> Operadores Seleccionados</h6>
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

                        <!-- Instrucciones -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6><i class="fas fa-info-circle"></i> Instrucciones</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-hand-rock text-primary"></i> <strong>Arrastra:</strong> Mantén presionado y arrastra operadores</li>
                                <li><i class="fas fa-mouse-pointer text-success"></i> <strong>Doble clic:</strong> Haz doble clic para mover operadores</li>
                                <li><i class="fas fa-check text-info"></i> <strong>Responsable:</strong> Selecciona el responsable de la tirada</li>
                                <li><i class="fas fa-cog text-warning"></i> <strong>Máquina:</strong> Elige la máquina para la producción</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tiradas en Proceso (Derecha) -->
            <div class="col-lg-6">
                <div class="table-container">
                    <h5 class="section-title">
                        <i class="fas fa-cogs"></i> Tiradas en Proceso
                    </h5>
                    <div class="table-responsive">
                        <table id="tablaTiradas" class="table table-striped table-hover mb-0">
                            <!-- Cambiar esto en la tabla #tablaTiradas -->
<thead class="table-dark">
    <tr>
        <th>ID</th>
        <th>Fecha Inicio</th>
        <th>Responsable</th>
        <th>Máquina</th>
        <th>Operadores</th>
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

        <!-- Tiradas Finalizadas (Abajo) -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="section-title">
                        <i class="fas fa-check-circle"></i> Tiradas Finalizadas
                    </h5>
                    <div class="table-responsive">
                        <table id="tablaTiradasFinalizadas" class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Final</th>
                                    <th>Responsable</th>
                                    <th>Máquina</th>
                                    <th>Operadores</th>
                                    <th>Productos Producidos</th>
                                    <th>Total Producido</th>
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

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.12/sweetalert2.min.js"></script>
    <script type="text/javascript" src="js_tiradas.js"></script>
</body>

</html>