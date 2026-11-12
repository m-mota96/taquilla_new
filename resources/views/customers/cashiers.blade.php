@extends('customers.index')
@section('heads')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-5">
    <div class="col-xl-12 mb-5 ps-0 pe-0">
        <h3 class="text-info"><b>Cajeros</b></h3>
    </div>
    <div class="row">
        <div class="card p-4">
            <div class="row">
                <div class="col-xl-12 mb-4">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createCashierModal" onclick="clearInputs()">Nuevo cajero/a</button>
                </div>
                <div class="col-xl-12">
                    <table class="table table-striped w-100" id="tableCashiers">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
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
@include('modals.customers.createCashier')
@include('modals.customers.eventsCashiers')
@endsection
@section('scripts')
    <script src="//cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
        // var array_events = @json($events);
        var events = @json($events);

        $(document).ready(()=> {
            cashiersTable();
        });

        function cashiersTable() {
            $('#tableCashiers').dataTable().fnDestroy();
            let table = new DataTable('#tableCashiers', {
                ajax: $('#URL').val()+'users',
                method: 'GET',
                order: [[0, 'desc']],
                processing: true,
                serverSide: true,
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'user' },
                    {
                        orderable: false,
                        searchable: false,
                        width: "15%",
                        className: "text-center",
                        render: (data, type, row, meta) => {
                            var events = JSON.stringify(row.access_events);
                            var regex = new RegExp("\"", "g");
                            events = events.replace(regex, "'");
                            return html = `<a class="text-primary pointer" onclick="viewEvents(${events}, ${row.id}, '${row.name}')"><i class="fa-solid fa-eye"></i> Eventos asignados</a>`;
                        }
                    },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                }
            });
        }

        function viewEvents(data, user_id, name) {
            var html = '';
            events.forEach(e => {
                var ban = false;
                data.forEach(d => {
                    if (d.id == e.id) {
                        ban = true;
                    }
                });
                html += `<tr>`;
                    html += `<td>${e.id}</td>`;
                    html += `<td>${e.name}</td>`;
                    if (ban) {
                        html += `<td class="text-center text-success">Activo para venta</td>`;
                        html += `<td class="text-center"><button class="btn btn-danger btn-sm" type="button" onclick="activarDesactivar(0, ${e.id}, ${user_id}, '${name}')">Desactivar</button></td>`;
                    } else {
                        html += `<td class="text-center text-danger">Inactivo para venta</td>`;
                        html += `<td class="text-center"><button class="btn btn-success btn-sm" type="button" onclick="activarDesactivar(1, ${e.id}, ${user_id}, '${name}')">Activar</button></td>`;
                    }
                html += `</tr>`;
            });
            $('#eventsCashiersModal #bodyEventsCashiers').html(html);
            $('#eventsCashiersModal').modal('show');
        }

        $('#formCreateCashier').submit((e) => {
            e.preventDefault();
            if ($('#password').val() != $('#confirm_password').val()) {
                Swal.fire({
                    icon: 'error',
                    text: 'Las contraseñas no coinciden',
                });
                return false;
            }
            $.ajax({
                url: $('#URL').val()+'createCashier',
                method: 'POST',
                data: $('#formCreateCashier').serialize(),
                success: (res)=> {
                    if (res.error == false) {
                        cashiersTable();
                        $('#createCashierModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            html: 'El usuario se gurado con éxito'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: res.msj
                        });
                    }
                },
                error: ()=> {
                    console.log('ERROR');
                }
            });
        });

        function activarDesactivar(ban, event_id, user_id, name) {
            $.ajax({
                url: $('#URL').val()+'enableDisableEventForSale',
                method: 'POST',
                data: {
                    _token: $("meta[name='csrf-token']").attr("content"),
                    ban: ban,
                    event_id: event_id,
                    user_id: user_id,
                },
                success: (res)=> {
                    if (res.error == false) {
                        cashiersTable();
                        $('#eventsCashiersModal').modal('hide');
                        var txt = ban == 0 ? 'desactivo' : 'activo';
                        Swal.fire({
                            icon: 'success',
                            html: `El evento se ${txt} para el cajero <b>${name}</b>`
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: res.msj
                        });
                    }
                },
                error: ()=> {
                    console.log('ERROR');
                }
            });
        }

        function clearInputs() {
            $('#createCashierModal .form-control').val('');
        }
    </script>
@endsection