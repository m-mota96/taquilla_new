@extends('customers.index')
@section('heads')
    
@endsection
@section('content')
    @include('customers.menuDown')
    <div class="container mt-4">
        <input type="hidden" id="event_id" value="{{$event->id}}">
        <div class="row">
            <div class="col-xl-12 mb-4">
                <h4>{{$event->name}}/<span class="text-primary">Boletos</span></h4>
            </div>
            <div class="col-xl-9 p-0">
                <div class="row" id="divTickets">
                    @foreach ($event->tickets as $t)
                        <div class="col-xl-4" id="card{{$t->id}}">
                            <div class="p-4 text-left card-ticket">
                                <p class="mt-3 ms-1 text-dark mb-1 name">{{$t->name}}</p>
                                <p class="ms-1 mb-0 text-dark price">${{number_format($t->price, 2)}} MXN</p>
                                <span class="ms-1 text-primary pointer mb-0 edit" onclick="modalTickets({{$t}})">Editar</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card pt-3 pb-5 ps-4 pe-4 text-center height-f">
                    <h5>OPCIONES</h5>
                    <button class="btn btn-warning w-100 mt-4" onclick="modalTickets()"><i class="fa-solid fa-plus bold"></i> Nuevo tipo de boleto</button>
                </div>
            </div>
        </div>
    </div>
    @include('modals.customers.createTickets')
@endsection
@section('scripts')
    <script>
        function modalTickets(data = '') {
            $('#createTicketModal #createTicketModalLabel').text('Nuevo boleto');
            $('#createTicketModal #createTicketModalBtn').text('Guardar');
            $('#createTicketModal .form-control').val('');
            $('#createTicketModal #eventId').val($('#event_id').val());
            if (data != '') {
                $('#createTicketModal #createTicketModalLabel').text('Editar boleto');
                $('#createTicketModal #createTicketModalBtn').text('Editar');
                $('#createTicketModal #ticket_id').val(data.id);
                $('#createTicketModal #name_ticket').val(data.name);
                $('#createTicketModal #price').val(data.price);
            }
            $('#createTicketModal').modal('show');
        }

        $('#formCreateTicket').submit((e)=> {
            e.preventDefault();
            $.ajax({
                url: $('#URL').val()+'createEditTicket',
                method: 'POST',
                data: $('#formCreateTicket').serialize(),
                success: (res)=> {
                    var ticket_id = $('#createTicketModal #ticket_id').val();
                    var data = JSON.stringify(res.data);
                    var regex = new RegExp("\"", "g");
                    data = data.replace(regex, "'");
                    if (ticket_id == '') {
                        var txt = 'guardo';
                        var html = ``;
                        html += `<div class="col-xl-4" id="card${res.data.id}">`;
                            html += `<div class="p-4 text-left card-ticket">`;
                                html += `<p class="mt-3 ms-1 text-secondary mb-1 name">${res.data.name}</p>`;
                                html += `<p class="ms-1 mb-0 text-secondary price">$${res.data.price}.00 MXN</p>`;
                                html += `<span class="ms-1 text-primary pointer mb-0 edit" onclick="modalTickets(${data})">Editar</span>`;
                            html += `</div>`;
                        html += `</div>`;
                        $('#divTickets').prepend(html);
                    } else {
                        var txt = 'modifico';
                        $(`#card${res.data.id} .name`).text(res.data.name);
                        $(`#card${res.data.id} .price`).text(`$${res.data.price}.00 MXN`);
                        $(`#card${res.data.id} .edit`).attr('onclick', `modalTickets(${data})`);
                    }
                    $('#createTicketModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        html: `El boleto se ${txt} con éxito`
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