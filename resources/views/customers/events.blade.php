@extends('customers.index')
@section('heads')
    
@endsection
@section('content')
    <div class="container mt-5">
        <div class="col-xl-12 mb-5 ps-0 pe-0">
            <h3 class="text-info"><b>Listado de eventos</b></h3>
        </div>
        <div class="row">
            <div class="col-xl-8">
                @foreach ($events as $e)
                    <div class="col-xl-12 pe-5 mb-4">
                        <div class="bg-white p-4">
                            <h5><a class="text-dark none-decoration bold pointer" href="{{route('estadisticas', $e->id)}}">{{$e->name}}</a></h5>
                            <p>{{$e->initial_date_es.' - '.$e->final_date_es}}</p>
                            <a class="text-dark none-decoration" href="{{route('estadisticas', $e->id)}}">Ver información del evento</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="col-xl-3 offset-xl-1 card pt-3 pb-5 ps-4 pe-4 text-center height-f">
                <h5>OPCIONES</h5>
                <button class="btn btn-warning w-100 mt-4" data-bs-toggle="modal" data-bs-target="#createEventModal" onclick="clearInputs()">Crear nuevo evento</button>
            </div>
        </div>
    </div>
    @include('modals.customers.createEvent')
@endsection
@section('scripts')
    <script>
        $('#formCreateEvent').submit((e)=> {
            e.preventDefault();
            $.ajax({
                url: $('#URL').val()+'createEvent',
                method: 'POST',
                data: $('#formCreateEvent').serialize(),
                success: (res)=> {
                    location.href = $('#URL').val()+`administrar_boletos/${res.event.id}`;
                },
                error: ()=> {
                    console.log('ERROR');
                }
            });
        });

        function clearInputs() {
            $('#createEventModal .form-control').val('');
        }
    </script>
@endsection