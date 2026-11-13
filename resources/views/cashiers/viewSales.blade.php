@extends('customers.index')
@section('heads')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
@endsection
@section('content')
    @include('cashiers.menuDown')
    <div class="container-fluid mt-5">
        <input type="hidden" id="event_id" value="{{$event->id}}">
        <div class="row">
            @if ($showAttendees)
            <div class="col-xl-12 mb-4">
                <div class="card p-4">
                    <h5 class="mb-3">Registros por persona</h5>
                    <table class="table table-striped w-100" id="tableAttendees">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Cargo</th>
                                <th>Boleto</th>
                                <th>Fecha y hora</th>
                                <th>Conferencias</th>
                                <th>Gafete</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            @if ($showCustomers)
            <div class="col-xl-12">
                <div class="card p-4">
                    @if ($showAttendees)
                        <h5 class="mb-3">Registros por compra</h5>
                    @endif
                    <table class="table table-striped" id="tableSales">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Fecha y hora</th>
                                <th>Boletos comprados</th>
                            </tr>
                        </thead>
                        <tbody>
    
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
@section('scripts')
    <script src="//cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    @if ($showAttendees)
        <script src="{{asset('js/BrowserPrint-3.1.250.min.js')}}"></script>
        <script src="{{asset('js/badge-zpl.js')}}"></script>
        <script src="{{asset('js/badge-print.js')}}"></script>
    @endif
    <script>
        $(document).ready(()=> {
            @if ($showCustomers)
                cashiersTable();
            @endif
            @if ($showAttendees)
                attendeesTable();
            @endif
        });

        function formatDate(value) {
            return value.substring(8, 10) + ' de ' + nameMonth(value) + ' de ' + value.substring(0, 4) + ' ' + value.substring(11, 16);
        }

        function escapeHtml(text) {
            return $('<div>').text(text == null ? '' : text).html();
        }

        var attendeesDt = null;

        $('#tableAttendees').on('click', '.btn-reprint', async function() {
            var row = attendeesDt.row($(this).closest('tr')).data();
            var result = await printBadges([{
                ticket_id:   row.conference_ticket_id,
                name:        row.name,
                occupation:  row.position ? row.position.name : '',
                ticket_name: row.ticket ? row.ticket.name : '',
            }]);
            if (result.failed > 0) {
                Swal.fire({ icon: 'error', title: 'No se pudo imprimir el gafete', text: result.error });
            } else {
                Swal.fire({ icon: 'success', text: 'Gafete reimpreso correctamente' });
            }
        });

        function attendeesTable() {
            attendeesDt = new DataTable('#tableAttendees', {
                ajax: {
                    url: $('#URL').val()+'viewAttendees',
                    type: 'POST',
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        event_id: $('#event_id').val()
                    }
                },
                order: [[6, 'desc']],
                lengthMenu: [[30, 60, 90, -1], [30, 60, 90, "Todos"]],
                processing: true,
                serverSide: true,
                columns: [
                    { data: 'id', searchable: false },
                    { data: 'name', name: 'name', render: (data) => escapeHtml(data) },
                    { data: 'email', name: 'email', render: (data) => escapeHtml(data) },
                    {
                        data: 'phone',
                        name: 'phone',
                        render: (data, type, row) => {
                            var d = (data || '').replace(/\D/g, '');
                            var formatted = d.length == 10 ? `${d.substring(0, 3)} ${d.substring(3, 6)} ${d.substring(6)}` : d;
                            return escapeHtml(`${row.country_code || ''} ${formatted}`.trim());
                        }
                    },
                    { data: 'position.name', name: 'position.name', orderable: false },
                    { data: 'ticket.name', name: 'ticket.name', orderable: false },
                    { data: 'created_at', name: 'created_at', searchable: false, render: (data) => formatDate(data) },
                    {
                        data: 'conference_sent_at',
                        orderable: false,
                        searchable: false,
                        render: (data) => data
                            ? '<span class="badge text-bg-success">Enviado</span>'
                            : '<span class="badge text-bg-warning">Pendiente</span>'
                    },
                    {
                        data: 'conference_ticket_id',
                        orderable: false,
                        searchable: false,
                        render: (data) => data
                            ? '<button class="btn btn-primary btn-sm btn-reprint" title="Reimprimir gafete"><i class="fa-solid fa-print"></i></button>'
                            : '<span class="text-secondary small" title="Aún no está en conferencias">—</span>'
                    },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                }
            });
        }

        function cashiersTable() {
            $('#tableSales').dataTable().fnDestroy();
            let table = new DataTable('#tableSales', {
                ajax: {
                    url: $('#URL').val()+'viewSales',
                    type: 'POST',
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        event_id: $('#event_id').val()
                    }
                },
                method: 'GET',
                order: [[4, 'desc']],
                lengthMenu: [[30, 60, 90, -1], [30, 60, 90, "Todos"]],
                processing: true,
                serverSide: true,
                columns: [
                    { data: 'id', searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    // { data: 'created_at', name: 'created_at' },
                    {
                        width: '25%',
                        name: 'created_at',
                        render: (data, type, row, meta) => {
                            var date = row.created_at.substring(8, 10);
                            date = date + ' de ' + nameMonth(row.created_at);
                            date = date + ' de ' + row.created_at.substring(0, 4) + ' ' + row.created_at.substring(11, 16);
                            return date;
                        }
                    },
                    {
                        orderable: false,
                        searchable: false,
                        render: (data, type, row, meta) => {
                            var html = '';
                            row.tickets.forEach(t => {
                                var txt = t.pivot.quantity == 1 ? 'boleto' : 'boletos';
                                html += `<span>${t.pivot.quantity} ${txt} ${t.name}</span><br>`;
                            });
                            // html += '</span>';
                            return html;
                        }
                    },
                ],
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
    </script>
@endsection