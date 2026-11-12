@extends('changeCenter.index')
@section('heads')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="container pt-5">
        <div class="col-xl-12">
            <h2 class="text-primary">Clientes</h2>
        </div>
        <div class="card p-4">
            <div class="row">
                <div class="col-xl-3 mb-5">
                    <button class="btn btn-success" onclick="selectWinner()">Elegir ganador</button>
                </div>
                <div class="col-xl-3 mb-5" id="name">
                    @if (!empty($winner))
                        <label>Nombre: </label><br><b>{{$winner->customer->name}}</b>
                    @endif
                </div>
                <div class="col-xl-3 mb-5" id="email">
                    @if (!empty($winner))
                        <label>Correo: </label><br><b>{{$winner->customer->email}}</b>
                    @endif
                </div>
                <div class="col-xl-3 mb-5" id="phone">
                    @if (!empty($winner))
                        <label>Teléfono: </label><br><b>{{$winner->customer->phone}}</b>
                    @endif
                </div>
                <div class="col-xl-12">
                    <table class="table table-striped" id="tableCustomers">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Compra</th>
                                <th>Boletos obtenidos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $c)
                                <tr>
                                    <td>
                                        {{$c->name}}
                                    </td>
                                    <td>
                                        {{$c->email}}
                                    </td>
                                    <td>
                                        {{$c->phone}}
                                    </td>
                                    <td>
                                        ${{number_format($c->buy, 2)}}
                                    </td>
                                    <td>
                                        {{intval($c->buy / 500)}}
                                    </td>
                                </tr>
                            @endforeach
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
            new DataTable('#tableCustomers', {
                "order": [[ 3, 'desc' ]],
                lengthMenu: [[30, 60, 90, -1], [30, 60, 90, "Todos"]],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                },
            });
        });

        function selectWinner() {
            $.ajax({
                url: $('#URL').val()+'selectWinner',
                type: 'POST',
                data: {
                    _token: $("meta[name='csrf-token']").attr("content")
                },
                success: (res)=> {
                    $('#name').html(`<label>Nombre: </label><br><b>${res.winner.customer.name}</b>`);
                    $('#email').html(`<label>Correo: </label><br><b>${res.winner.customer.email}</b>`);
                    $('#phone').html(`<label>Teléfono: </label><br><b>${res.winner.customer.phone}</b>`);
                },
                error: ()=> {
                    console.log('ERROR');
                }
            });
        }
    </script>
@endsection