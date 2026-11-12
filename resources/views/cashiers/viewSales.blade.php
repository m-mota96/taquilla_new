@extends('customers.index')
@section('heads')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
@endsection
@section('content')
    @include('cashiers.menuDown')
    <div class="container-fluid mt-5">
        <input type="hidden" id="event_id" value="{{$event->id}}">
        <div class="row">
            <div class="col-xl-12">
                <div class="card p-4">
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
        </div>
    </div>
@endsection
@section('scripts')
    <script src="//cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(()=> {
            cashiersTable();
        });

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