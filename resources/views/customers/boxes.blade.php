@extends('customers.index')
@section('heads')
    
@endsection
@section('content')
    @include('customers.menuDown')
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 mb-4">
                <h4>{{$event->name}}/<span class="text-primary">Caja chica</span></h4>
            </div>
            <div class="col-xl-12">
                <div class="card p-4">
                    <form class="row" id="formBoxes">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="event_id" value="{{$event->id}}">
                        <table class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Caja chica</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($event->users as $key => $u)
                                    <tr>
                                        <td>{{$u->name}}</td>
                                        <td>{{$u->user}}</td>
                                        <td class="w-25">
                                            <input type="hidden" value="{{$u->id}}" name="user_id[{{$key}}]">
                                            @if (empty($u->box))
                                                <input class="form-control form-control-sm" type="text" name="box[{{$key}}]" onKeyPress="return soloNumeros(event)">
                                            @else
                                                <input class="form-control form-control-sm" type="text" name="box[{{$key}}]" value="{{$u->box->cash}}" onKeyPress="return soloNumeros(event)">
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="col-xl-12 mt-3 mb-2 text-center">
                            <button class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $('#formBoxes').submit((e)=> {
            e.preventDefault();
            $.ajax({
                url: $('#URL').val()+'saveBoxes',
                method: 'POST',
                data: $('#formBoxes').serialize(),
                success: (res)=> {
                    Swal.fire({
                        icon: 'success',
                        text: 'Los datos fueron guardados correctamente'
                    });
                },
                error: ()=> {
                    console.log('ERROR');
                }
            });
        });

        function soloNumeros(e) {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8))
        }
    </script>
@endsection