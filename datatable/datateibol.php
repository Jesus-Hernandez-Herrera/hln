<link rel=stylesheet type="text/css" href="datatable/buttons.dataTables.min.css">
<link rel=stylesheet type="text/css" href="datatable/jquery.dataTables.min.css">
<link rel=stylesheet type="text/css" href="datatable/autoFill.dataTables.min.css">
<link rel=stylesheet type="text/css" href="datatable/fixedColumns.dataTables.min.css">
<link rel=stylesheet type="text/css" href="datatable/responsive.dataTables.min.css">
<link rel=stylesheet type="text/css" href="datatable/autoFill.dataTables.min.css">
<link rel=stylesheet type="text/css" href="datatable/fixedHeader.dataTables.min.css">
<script src="datatable/dataTables.colReorder.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/ColReorderWithResize.js" language="javascript" type="text/javascript"></script>
<script src="datatable/buttons.flash.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/buttons.print.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/dataTables.autoFill.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/dataTables.fixedHeader.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/dataTables.responsive.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/jquery-3.5.1.js" language="javascript" type="text/javascript"></script>
<script src="datatable/jquery.dataTables.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/dataTables.buttons.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/buttons.html5.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/jszip.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/pdfmake.min.js" language="javascript" type="text/javascript"></script>
<script src="datatable/vfs_fonts.js" language="javascript" type="text/javascript"></script>

<script src="datatable/buttons.colVis.min.js" language="javascript" type="text/javascript"></script>




<style>
    thead input {
        width: 100%;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        var exampleTable = $('#example');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);
        // Normalizar tanto el valor de búsqueda como los datos de la tabla
        $.fn.dataTable.ext.type.search.string = function (data) {
            return !data ?
                '' :
                typeof data === 'string' ?
                    data.normalize('NFD').replace(/[\u0300-\u036f]/g, '') :
                    data;
        };
        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            //fixedHeader: true,
            //scrollY: "600px",
            initComplete: function () {
                var api = this.api();
                // Filtro por columna
                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();

                    filterCell.html('<input type="text" placeholder="' + title + '" />');

                    var input = filterCell.find('input');

                    input.on('keyup change', function () {
                        var val = input.val().normalize('NFD').replace(
                            /[\u0300-\u036f]/g, '');
                        api.column(colIndex).search(val, false, false).draw();
                    });
                });
            },
            search: {
                smart: false,
                caseInsensitive: true,
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            pageLength: 10,
            scrollY: '400px',
            scrollCollapse: false,
            scrollX: true,
            dom: 'Blfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            ordering: true,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
        // Sobrescribir la función de búsqueda para normalizar los acentos en la búsqueda general
        dataTable.on('preDraw', function () {
            var searchInput = $('#example_filter input');
            var searchVal = searchInput.val().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            dataTable.search(searchVal);
        });
        // Normalizar la entrada general de búsqueda al escribir
        $('#example_filter input').on('keyup change', function () {
            var val = $(this).val().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            dataTable.search(val).draw();
        });
    });

    $(document).ready(function () {
        var exampleTable = $('#example2');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);

        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            // fixedHeader: true,
            // scrollY: "600px",
            initComplete: function () {
                var api = this.api();

                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();

                    filterCell.html('<input type="text" placeholder="' + title + '" />');

                    var input = filterCell.find('input');

                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var regexr = '({search})';
                            api.column(colIndex).search(
                                input.val() !== '' ? regexr.replace('{search}',
                                    '(((' + input.val() + ')))') : '',
                                input.val() !== '',
                                input.val() === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true,
            dom: 'Bfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            ordering: false,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
    });



    $(document).ready(function () {
        var exampleTable = $('#Tabla_SIN_SORT');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);
        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            //fixedHeader: true,
            //scrollY: "600px",
            initComplete: function () {
                var api = this.api();
                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();
                    filterCell.html('<input type="text" placeholder="' + title + '" />');
                    var input = filterCell.find('input');
                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var regexr = '({search})';
                            api.column(colIndex).search(
                                input.val() !== '' ? regexr.replace('{search}',
                                    '(((' + input.val() + ')))') : '',
                                input.val() !== '',
                                input.val() === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true,
            dom: 'Bfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            ordering: false,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
    });

    $(document).ready(function () {
        var exampleTable = $('#exampleXXX');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);
        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            //fixedHeader: true,
            //scrollY: "600px",
            initComplete: function () {
                var api = this.api();
                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();
                    filterCell.html('<input type="text" placeholder="' + title + '" />');
                    var input = filterCell.find('input');
                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var regexr = '({search})';
                            api.column(colIndex).search(
                                input.val() !== '' ? regexr.replace('{search}',
                                    '(((' + input.val() + ')))') : '',
                                input.val() !== '',
                                input.val() === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true,
            dom: 'Bfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            ordering: false,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
    });


    $(document).ready(function () {
        var exampleTable = $('#exampleXXX2');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);
        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            //fixedHeader: true,
            //scrollY: "600px",
            initComplete: function () {
                var api = this.api();
                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();
                    filterCell.html('<input type="text" placeholder="' + title + '" />');
                    var input = filterCell.find('input');
                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var regexr = '({search})';
                            api.column(colIndex).search(
                                input.val() !== '' ? regexr.replace('{search}',
                                    '(((' + input.val() + ')))') : '',
                                input.val() !== '',
                                input.val() === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            pageLength: -1, // Mostrar todos los registros por defecto
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true,
            dom: 'Blfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            ordering: false,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
    });




    $(document).ready(function () {
        var exampleTable = $('#cartera');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);

        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            //fixedHeader: true,
            //scrollY: "600px",
            initComplete: function () {
                var api = this.api();

                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();

                    filterCell.html('<input type="text" placeholder="' + title + '" />');

                    var input = filterCell.find('input');

                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var regexr = '({search})';
                            api.column(colIndex).search(
                                input.val() !== '' ? regexr.replace('{search}',
                                    '(((' + input.val() + ')))') : '',
                                input.val() !== '',
                                input.val() === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true,
            dom: 'Bfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            order: [
                [0, 'asc']
            ], // Add ordering here (0 is the first column, 'asc' is ascending)
            ordering: true, // Enable ordering
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
    });

    //  Ordena los pagos pendientes del mayor al menor
    $(document).ready(function () {
        var exampleTable = $('#ordenarPagos');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);

        // Normalizar tanto el valor de búsqueda como los datos de la tabla
        $.fn.dataTable.ext.type.search.string = function (data) {
            return !data ? '' : typeof data === 'string' ? data.normalize('NFD').replace(/[\u0300-\u036f]/g,
                '') : data;
        };

        var dataTable = exampleTable.DataTable({
            orderCellsTop: true, // Activa la posibilidad de ordenar las celdas en la parte superior de la tabla.
            fixedHeader: true, // Fija el encabezado de la tabla mientras se hace scroll.
            scrollY: "600px", // Fija una altura de 600px para el scroll vertical.
            order: [
                [1, 'desc']
            ], // Ordena por la segunda columna (índice 1) en orden descendente.
            ordering: true, // Habilita la funcionalidad de ordenamiento.
            initComplete: function () {
                var api = this.api();

                // Configuración de filtros para cada columna.
                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();

                    filterCell.html('<input type="text" placeholder="' + title + '" />');

                    var input = filterCell.find('input');

                    // Manejadores de eventos para los inputs de los filtros.
                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var regexr = '({search})';
                            api.column(colIndex).search(
                                input.val() !== '' ? regexr.replace('{search}',
                                    '(((' + input.val().normalize('NFD').replace(
                                        /[\u0300-\u036f]/g, '') + ')))') : '',
                                input.val() !== '',
                                input.val() === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true, // Activa el scroll horizontal.
            dom: 'Bfrtip', // Define la estructura del DOM para incluir botones.
            language: {
                url: "datatable/Spanish.json" // URL para el archivo de idioma en español.
            },
            search: {
                smart: false, // Desactiva la búsqueda inteligente.
                caseInsensitive: true // Habilita la búsqueda sin distinción entre mayúsculas y minúsculas.
            },
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });

        // Sobrescribir la función de búsqueda para normalizar los acentos en la búsqueda general
        dataTable.on('preDraw', function () {
            var searchInput = $('#ordenarPagos_filter input');
            var searchVal = searchInput.val().normalize('NFD').replace(/[\u0300-\u036f']/g, '');
            dataTable.search(searchVal);
        });

        // Normalizar la entrada general de búsqueda al escribir
        $('#ordenarPagos_filter input').on('keyup change', function () {
            var val = $(this).val().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            dataTable.search(val).draw();
        });
    });


    $(document).ready(function () {
        var exampleTable = $('#Tabla_Planificacion');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);

        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            //fixedHeader: true,
            //scrollY: "600px",
            initComplete: function () {
                var api = this.api();

                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();

                    filterCell.html('<input type="text" placeholder="' + title + '" />');

                    var input = filterCell.find('input');

                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var regexr = '({search})';
                            api.column(colIndex).search(
                                input.val() !== '' ? regexr.replace('{search}',
                                    '(((' + input.val() + ')))') : '',
                                input.val() !== '',
                                input.val() === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true,
            dom: 'Bfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            ordering: false,
            "paging": false,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
    });
    $(document).ready(function () {
        var exampleTable = $('#Tabla_ListadoGenEstCoord');
        var exampleTableHead = exampleTable.find('thead');
        var filtersRow = exampleTableHead.find('tr').clone(true).addClass('filters').appendTo(exampleTableHead);

        var dataTable = exampleTable.DataTable({
            orderCellsTop: true,
            //fixedHeader: true,
            //scrollY: "600px",
            initComplete: function () {
                var api = this.api();

                api.columns().eq(0).each(function (colIndex) {
                    var headerCell = $(api.column(colIndex).header());
                    var filterCell = filtersRow.find('th').eq(headerCell.index());
                    var title = filterCell.text();

                    filterCell.html('<input type="text" placeholder="' + title + '" />');

                    var input = filterCell.find('input');

                    input.off('keyup change').on('change', $.fn.dataTable.util.throttle(
                        function (e) {
                            input.attr('title', input.val());
                            var searchTerm = input.val();

                            // Normalizar el texto eliminando acentos
                            searchTerm = searchTerm.normalize("NFD").replace(
                                /[\u0300-\u036f]/g, "");

                            var regexr = '({search})';
                            api.column(colIndex).search(
                                searchTerm !== '' ? regexr.replace('{search}',
                                    '(((' + searchTerm + ')))') : '',
                                searchTerm !== '',
                                searchTerm === ''
                            ).draw();
                        }, 300)).on('keyup', function (e) {
                            e.stopPropagation();
                            input.trigger('change');
                        });
                });
            },
            lengthMenu: [
                [10, 50, -1],
                [10, 50, "Todos"]
            ],
            scrollX: true,
            dom: 'Bfrtip',
            language: {
                url: "datatable/Spanish.json"
            },
            ordering: false,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                },
                filename: 'Reporte_Estudiantes_' + new Date().toISOString().slice(0,
                    10), // Nombre con fecha
                title: 'Reporte de Estudiantes', // Título dentro del archivo
            },
            {
                extend: 'pdfHtml5',
                download: 'open'
            },
            {
                extend: 'csvHtml5',
                download: 'open'
            }
            ]
        });
    });
</script>