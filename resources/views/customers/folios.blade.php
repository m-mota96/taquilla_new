@extends('customers.index')
@section('heads')
    
@endsection
@section('content')
    @include('customers.menuDown')
    <div class="container mt-4">
        <input type="hidden" id="event_id" value="{{$event->id}}">
        <div class="row">
            <div class="col-xl-12 mb-4">
                <h4>{{$event->name}}/<span class="text-primary">Folios</span></h4>
            </div>
            @foreach ($event->tickets as $key => $t)
                <div class="col-xl-12 mb-3">
                    <div class="card p-4">
                        <h5 class="mb-3">{{$t->name}}</h5>
                        <table class="table table-striped w-100 mb-3">
                            <thead>
                                <tr>
                                    <th>Folio inicial</th>
                                    <th>Folio final</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Vendidos</th>
                                    <th class="text-center">Disponibles</th>
                                    <th class="w-25">Cajero/a</th>
                                    <th class="text-center">
                                        <button class="btn btn-success btn-sm tooltips" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Agregar folios" onclick="addFolios({{$t->id}})">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody{{$t->id}}">
                                @if (sizeof($t->folios) == 0)
                                    <tr class="tr0 tr">
                                        <td>
                                            <input class="folio_id" type="hidden">
                                            <input class="form-control form-control-sm initial_folio" type="text" onKeyPress="return soloNumeros(event)">
                                        </td>
                                        <td><input class="form-control form-control-sm final_folio" type="text" onKeyPress="return soloNumeros(event)"></td>
                                        <td class="text-center"><span class="quantity">0</span></td>
                                        <td class="text-center"><span class="sold">0</span></td>
                                        <td class="text-center"><span class="available">0</span></td>
                                        <td class="w-25">
                                            <select class="form-control form-control-sm cashier">
                                                <option value="" selected disabled>Asigne un cajero</option>
                                                @foreach ($users as $u)
                                                    <option value="{{$u->id}}">{{$u->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-danger btn-sm tooltips delete" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar folios" onclick="deleteFolios({{$t->id}}, 0)">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($t->folios as $key2 => $f)
                                        <tr class="tr{{$key2}} tr">
                                            <td>
                                                <input class="folio_id" type="hidden" value="{{$f->id}}">
                                                <input class="form-control form-control-sm initial_folio" type="text" onKeyPress="return soloNumeros(event)" value="{{$f->initial_folio}}" @if ($f->sold > 0) readonly disabled @endif>
                                            </td>
                                            <td>
                                                <input class="form-control form-control-sm final_folio" type="text" onKeyPress="return soloNumeros(event)" value="{{$f->final_folio}}" @if ($f->sold > 0) readonly disabled @endif>
                                            </td>
                                            <td class="text-center"><span class="quantity">{{$f->quantity}}</span></td>
                                            <td class="text-center"><span class="sold">{{$f->sold}}</span></td>
                                            <td class="text-center"><span class="available">{{$f->quantity - $f->sold}}</span></td>
                                            <td class="w-25">
                                                @if(empty($f->user_id))
                                                    <select class="form-control form-control-sm cashier">
                                                        <option value="" selected disabled>Asigne un cajero</option>
                                                        @foreach ($users as $u)
                                                            <option value="{{$u->id}}">{{$u->name}}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input class="form-control form-control-sm cashier" type="text" value="{{$f->user->name}}" readonly disabled>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($f->sold == 0)
                                                    <button class="btn btn-danger btn-sm tooltips delete" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar folios" onclick="deleteFolios({{$t->id}}, {{$key2}}, {{$f->id}})">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="col-xl-12 text-center mt-3">
                            <button class="btn btn-primary btn-sm" onclick="saveChanges({{$t->id}})">Guardar</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var users = @json($users);
        $(document).ready(()=> {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        });

        function soloNumeros(e) {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8))
        }

        function saveChanges(ticket_id) {
            var data = [];
            var empty = false;
            $(`#tbody${ticket_id} .folio_id`).each(function(i, el) {
                data[i] = {};
                data[i].ticket_id = ticket_id;
                data[i].folio_id = $(this).val();
            });
            $(`#tbody${ticket_id} .initial_folio`).each(function(i, el) {
                data[i].initial_folio = $(this).val();
                if ($(this).val() == '') {
                    $(this).addClass('border border-danger');
                    empty = true;
                } else {
                    $(this).removeClass('border border-danger');
                }
            });
            $(`#tbody${ticket_id} .final_folio`).each(function(i, el) {
                data[i].final_folio = $(this).val();
                if ($(this).val() == '') {
                    $(this).addClass('border border-danger');
                    empty = true;
                } else {
                    $(this).removeClass('border border-danger');
                }
            });
            $(`#tbody${ticket_id} .cashier`).each(function(i, el) {
                data[i].cashier = $(this).val();
            });

            if (empty == false) {
                var max = false;
                data.forEach(d => {
                    if (parseInt(d.initial_folio) > parseInt(d.final_folio)) {
                        max = true;
                    }
                });
                if (max == false) {
                    $.ajax({
                        url: $('#URL').val()+'addEditFolios',
                        method: 'POST',
                        data: {
                            _token: $("meta[name='csrf-token']").attr("content"),
                            ticket_id: ticket_id,
                            folios: data
                        },
                        success: (res)=> {
                            if (res.error == false) {
                                $(`#tbody${ticket_id}`).html('');
                                res.data.forEach(d => {
                                    addFolios(ticket_id, d);
                                });
                                Swal.fire({
                                    icon: 'success',
                                    html: 'La información se guardo con éxito'
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
                } else {
                    Swal.fire({
                        icon: 'error',
                        text: 'Los folios finales deben ser mayor o igual que los iniciales'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    text: 'Debe completar al menos el folio inicial y final en todas las filas'
                });
            }
        }

        function addFolios(ticket_id, data = '') {
            if (data == '') {
                data = {
                    id: '',
                    initial_folio: '',
                    final_folio: '',
                    cashier: '',
                    quantity: 0,
                    sold: 0
                };
            }
            var readonly = '';
            var disabled = '';
            if (data.sold > 0) {
                readonly = 'readonly';
                disabled = 'disabled';
            }
            var html = `<tr class="tr${countTr(ticket_id)} tr">`;
                html += `<td>`;
                    html += `<input class="folio_id" type="hidden" value="${data.id}">`;
                    html += `<input class="form-control form-control-sm initial_folio" type="text" value="${data.initial_folio}" onKeyPress="return soloNumeros(event)" ${readonly} ${disabled}>`;
                html += `</td>`;
                html += `<td>`;
                    html += `<input class="form-control form-control-sm final_folio" type="text" value="${data.final_folio}" onKeyPress="return soloNumeros(event)" ${readonly} ${disabled}>`;
                html += `</td>`;
                html += `<td class="text-center"><span class="quantity">${data.quantity}</span></td>`;
                html += `<td class="text-center"><span class="sold">${data.sold}</span></td>`;
                html += `<td class="text-center"><span class="available">${data.quantity - data.sold}</span></td>`;
                html += `<td class="w-25">`;
                    if(data.user_id == null) {
                        html += `<select class="form-control form-control-sm cashier">`;
                            html += `<option value="" selected disabled>Asigne un cajero</option>`;
                            users.forEach(u => {
                                html += `<option value="${u.id}">${u.name}</option>`;
                            });
                        html += `</select>`;
                    } else {
                        html += `<input class="form-control form-control-sm cashier" type="text" value="${data.user.name}" readonly disabled>`;
                    }
                html += `</td>`;
                html += `<td class="text-center">`;
                    if (data.sold == 0) {
                        if (data.id == '') {
                            html += `<button class="btn btn-danger btn-sm tooltips delete" onclick="deleteFolios(${ticket_id}, ${countTr(ticket_id)})">`;
                        } else {
                            html += `<button class="btn btn-danger btn-sm tooltips delete" onclick="deleteFolios(${ticket_id}, ${countTr(ticket_id)}, ${data.id})">`;
                        }
                            html += `<i class="fa-solid fa-trash-can"></i>`;
                        html += `</button>`;
                    }
                html += `</td>`;
            html += `</tr>`;
            $(`#tbody${ticket_id}`).append(html);
        }

        let countTr = (ticket_id)=> {
            var cont = 0;
            $(`#tbody${ticket_id} .tr`).each(function() {
                cont++;
            });
            return cont;
        }

        function deleteFolios(ticket_id, idDom, folio_id = '') {
            if (folio_id == '') {
                if (countTr(ticket_id) > 1) {
                    $(`#tbody${ticket_id} .tr${idDom}`).remove();
                    orderElements(ticket_id);
                }
            } else {
                Swal.fire({
                    title: '¿Seguro que desea eliminar el registro?',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    reverseButtons: true,
                    }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        $.ajax({
                            url: $('#URL').val()+'deleteFolios',
                            method: 'POST',
                            data: {
                                _token: $("meta[name='csrf-token']").attr("content"),
                                folio_id: folio_id
                            },
                            success: (res)=> {
                                if (res.error == false) {
                                    $(`#tbody${ticket_id} .tr${idDom}`).remove();
                                    orderElements(ticket_id);
                                    Swal.fire({
                                        icon: 'success',
                                        text: 'El registro se elimino correctamente'
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
                });
            }
        }

        function orderElements(ticket_id) {
            var cont = 0;
            $(`#tbody${ticket_id} .tr`).each(function(i, el) {
                $(this).attr('class', `tr${cont} tr`);
                if ($(`#tbody${ticket_id} .tr${cont} .folio_id`).val() == undefined || $(`#tbody${ticket_id} .tr${cont} .folio_id`).val() == '') {
                    $(`#tbody${ticket_id} .tr${cont} .delete`).attr('onclick', `deleteFolios(${ticket_id}, ${cont})`);
                } else {
                    $(`#tbody${ticket_id} .tr${cont} .delete`).attr('onclick', `deleteFolios(${ticket_id}, ${cont}, ${$(`#tbody${ticket_id} .tr${cont} .folio_id`).val()})`);
                }
                cont++;
            });
        }

        // var areYouReallySure = false;
        // function areYouSure() {
        //     if(allowPrompt){
        //         if (!areYouReallySure && true) {
        //             areYouReallySure = true;
        //             var confMessage = "***************************************nn E S P E R A !!! nnAntes de abandonar nuestra web, síguenos en nuestras redes sociales como Facebook, Twitter o Instagram.nnnYA PUEDES HACER CLIC EN EL BOTÓN CANCELAR SI QUIERES...nn***************************************";
        //             return confMessage;
        //         }
        //     }else{
        //         allowPrompt = true;
        //     }
        // }

        // var allowPrompt = true;
        // window.onbeforeunload = areYouSure;
    </script>
@endsection