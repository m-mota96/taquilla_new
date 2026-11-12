@extends('customers.index')
@section('heads')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
@endsection
@section('content')
    @include('customers.menuDown')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-xl-12 mb-4">
                <h4>{{$event->name}}/<span class="text-primary">Cortes de caja</span></h4>
            </div>
            <div class="col-xl-12">
                <div class="card p-4">
                    <div class="row">
                        <div class="col-xl-12 mb-5">
                            <h5>Ayuda:</h5>
                            <p class="mb-1">
                                <b>Corte en sistema: </b><span>Es la cantidad que se corta en el sistema para dejar cuadrada la caja chica y este monto no se tiene en físico.</span>
                            </p>
                            <p class="mb-1">
                                <b>Corte físico: </b><span>Es el dinero físico en efectivo que debe tener en caja sin contar la caja chica.</span>
                            </p>
                            <p class="mb-1">
                                <b>Diferencia: </b><span>Es el resultado del corte físico menos el corte en sistema, en caso de ser iguales ésta debe ser 0.</span>
                            </p>
                        </div>
                        <div class="col-xl-12">
                            <input type="hidden" id="event_id" value="{{$event->id}}">
                            <table class="table table-striped w-100" id="tableCuts">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Fecha</th>
                                        <th>Cajero/a</th>
                                        <th>Caja chica</th>
                                        <th>Corte en sistema</th>
                                        <th>Corte físico</th>
                                        <th>Diferencia</th>
                                        <th>Comentarios</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="//cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
        var dates = @json($dates);
        var cashiers = @json($event->users);
        
        $(document).ready(()=> {
            cutsTable();
        });

        function cutsTable() {
            $('#tableCuts').dataTable().fnDestroy();
            let table = new DataTable('#tableCuts', {
                ajax: {
                    url: $('#URL').val()+'cuts',
                    type: 'POST',
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        event_id: $('#event_id').val()
                    }
                },
                order: [[0, 'ASC']],
                processing: true,
                serverSide: true,
                dom: 'lrtip',
                paging: false,
                columns: [
                    { data: 'hour', orderable: false, width: '5%', className: 'text-center' },
                    {
                        orderable: false,
                        width: '25%',
                        name: 'date',
                        render: (data, type, row, meta) => {
                            var date = row.date.substring(8, 10);
                            date = date + ' de ' + nameMonth(row.date);
                            date = date + ' de ' + row.date.substring(0, 4);
                            return date;
                        }
                    },
                    { data: 'user.name', name: 'cuts.user_id', orderable: false, width: '20%' },
                    { 
                        orderable: false,
                        width: '10%',
                        render: (data, type, row, meta) => {
                            return `$${formatMoney(row.user.box.cash)}`;
                        }
                    },
                    {
                        orderable: false,
                        width: '10%',
                        render: (data, type, row, meta) => {
                            return `$${formatMoney(row.systemCut)}`;
                        }
                    },
                    {
                        orderable: false,
                        width: '10%',
                        render: (data, type, row, meta) => {
                            return `$${formatMoney(row.realCut)}`;
                        }
                    },
                    {
                        orderable: false,
                        width: '10%',
                        render: (data, type, row, meta) => {
                            var difference = row.realCut - row.systemCut;
                            if (difference < 0) {
                                difference = '-$'+formatMoney(difference * -1);
                            } else {
                                difference = '$'+formatMoney(difference);
                            }
                            return difference;
                        }
                    },
                    { data: 'comment', orderable: false, width: '30%' }
                ],
                initComplete: function () {
                    // Apply the search
                    this.api()
                        .columns()
                        .every(function () {
                            var that = this;
                            var titulos = ['Hora', 'Fecha', 'Cajero/a'];
                            if(that.index() == 1) {
                                var select = $('<select class="form-control form-control-sm"><option value="">Todos los cortes de caja</option></select>')
                                .appendTo($(that.header()).empty())
                                .on('change', function () {
                                    if (that.search() !== this.value) {
                                        that.search(this.value).draw();
                                    }
                                });
                                
                                for (var i = 0; i < dates.length; i++) {
                                    select.append('<option value="' + dates[i].date + '">' + dates[i].date_parse + '</option>');
                                }
                            }

                            if(that.index() == 2) {
                                var select = $(`<select class="form-control form-control-sm"><option value="">${titulos[that.index()]}</option></select>`)
                                .appendTo($(that.header()).empty())
                                .on('change', function () {
                                    if (that.search() !== this.value) {
                                        that.search(this.value).draw();
                                    }
                                });
                                
                                for (var i = 0; i < cashiers.length; i++) {
                                    select.append('<option value="' + cashiers[i].id + '">' + cashiers[i].name + '</option>');
                                }
                            }
                        });
                },
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                }
            });
        }

        function nameMonth(date) {
            var months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            var month = date.substring(5, 7);
            return months[parseInt(month) - 1];
        }

        function formatMoney(number, decPlaces, decSep, thouSep) {
            decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
            decSep = typeof decSep === "undefined" ? "." : decSep;
            thouSep = typeof thouSep === "undefined" ? "," : thouSep;
            var sign = number < 0 ? "-" : "";
            var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
            var j = (j = i.length) > 3 ? j % 3 : 0;
            
            return sign +
                (j ? i.substr(0, j) + thouSep : "") +
                i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
                (decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
        }
    </script>
@endsection