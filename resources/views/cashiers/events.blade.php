@extends('customers.index')
@section('heads')
    
@endsection
@section('content')
    <div class="container mt-5">
        <div class="col-xl-12 mb-5 ps-0 pe-0">
            <h3 class="text-info"><b>Listado de eventos</b></h3>
        </div>
        <div class="row">
            @foreach ($events as $e)
                <div class="col-xl-6 mb-4">
                    <div class="bg-white p-4">
                        <h5>
                            @if ($user->getRoleNames()[0] == 'customer')
                                <a class="text-dark none-decoration bold pointer" href="">{{$e->name}}</a>
                            @else
                                <a class="text-dark none-decoration bold pointer" href="{{route('venta', $e->id)}}">{{$e->name}}</a>
                            @endif
                        </h5>
                        <p>{{$e->initial_date_es.' - '.$e->final_date_es}}</p>
                        {{-- <a class="text-dark none-decoration" href="{{route('administrar_evento', $e->id)}}">Editar</a> --}}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        
    </script>
@endsection