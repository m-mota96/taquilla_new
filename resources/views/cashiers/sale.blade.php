@extends('customers.index')
@section('heads')
    
@endsection
@section('content')
    @include('cashiers.menuDown')
    <div class="container-fluid mt-5">
        <div class="row">
            <input type="hidden" id="event_id" value="{{$event->id}}">
            <div class="col-xl-6">
                <div class="card p-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tipo de boleto</th>
                                <th class="text-center">Precio</th>
                                <th class="text-center">Folios</th>
                                <th class="text-center">Disponibles</th>
                                <th class="w-20 text-center">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody id="tbodySale">
                            @foreach ($folios as $key => $f)
                                <tr valign="middle" id="tr{{$f->id}}">
                                    <td>
                                        {{$f->ticket->name}}
                                        <input class="folio_id" type="hidden" value="{{$f->id}}">
                                    </td>
                                    <td class="text-center">
                                        ${{$f->ticket->price}}
                                        <input class="price" type="hidden" value="{{$f->ticket->price}}">
                                    </td>
                                    <td class="text-center">{{$f->initial_folio}} <br> {{$f->final_folio}}</td>
                                    <td class="text-center">
                                        <span class="txtAvailable">{{$f->quantity - $f->sold}}</span>
                                        <input class="available" type="hidden" value="{{$f->quantity - $f->sold}}">
                                    </td>
                                    <td class="w-20 text-center">
                                        <div class="input-group">
                                            <span class="input-group-text btn btn-primary btn-sm pt-2" onclick="moreMinus({{$f->id}}, 'minus')"><i class="fa-solid fa-minus"></i></span>
                                            <input type="text" class="form-control text-center quantity" value="0" readonly>
                                            <span class="input-group-text btn btn-primary btn-sm pt-2" onclick="moreMinus({{$f->id}}, 'add')"><i class="fa-solid fa-plus"></i></span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card p-4" id="divClient">
                    <h5 class="w-100 text-center mb-3">Datos del Cliente</h5>
                    <label>Nombre:</label>
                    <input class="form-control form-control-sm mb-3" type="text" id="name">
                    <label>Correo:</label>
                    <input class="form-control form-control-sm mb-3" type="text" id="email">
                    <label>Teléfono:</label>
                    <input class="form-control form-control-sm mb-3" type="text" id="phone">
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card p-4">
                    <h5 class="w-100 text-center mb-3">Resúmen del pago</h5>
                    <h5 class="mb-3 fw-n">
                        Total: 
                        <br><b>$</b><span class="bold" id="txtTotal">0.00</span>
                        <input type="hidden" id="total" value="0">
                    </h5>
                    <h5 class="mb-3 fw-n">Pago con: <input class="form-control form-control-sm mt-1" type="text" id="pay" onKeyPress="return soloNumeros(event)"></h5>
                    <h5 class="mb-3 fw-n">Cambio: <br><b>$</b><span class="bold" id="change">0.00</span></h5>
                    <h5 class="w-100 fw-n">Método de pago:</h5>
                    <div class="form-inline mb-4">
                        <input class="form-check-input pointer" type="radio" name="payment_method" id="cash" value="cash">
                        <label class="form-check-label pointer bold" for="cash">
                            Efectivo
                        </label>
                        <input class="form-check-input pointer ms-3" type="radio" name="payment_method" id="card" value="card">
                        <label class="form-check-label pointer bold" for="card">
                            Tarjeta
                        </label>
                    </div>
                    <button class="btn btn-success btn-sm w-100" onclick="registerSale()" id="registerSale">Registrar venta</button>
                </div>
                <div class="card mt-2 p-4">
                    <h5 class="fw-n">
                        Efectivo en caja: <br><b id="cashInBoxTxt">${{number_format((!empty($user->box) ? $user->box->cash : 0) + (!empty($user->cash) ? $user->cash->cash : 0))}}.00</b>
                    </h5>
                    <input type="hidden" id="cashInBox" value="{{(!empty($user->box) ? $user->box->cash : 0) + (!empty($user->cash) ? $user->cash->cash : 0)}}">
                    <input type="hidden" id="box" value="{{!empty($user->box) ? $user->box->cash : 0}}">
                    <input type="hidden" id="cashSale" value="{{!empty($user->cash) ? $user->cash->cash : 0}}">
                    <button class="btn btn-primary btn-sm w-100 mt-2" onclick="modalCuts()">Hacer corte de caja</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalCuts" tabindex="-1" aria-labelledby="modalCutsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="formcut">
                {{ csrf_field() }}
                <input type="hidden" name="event_id" value="{{$event->id}}">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCutsLabel">Corte de caja</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-6 mb-3">
                            <label>Caja chica:</label>
                            <input class="form-control" type="text" id="boxModal" readonly disabled>
                        </div>
                        {{-- <div class="col-xl-4 mb-3">
                            <label>Total de ventas:</label>
                            <input class="form-control" type="text" id="totalSale" readonly disabled>
                        </div> --}}
                        <div class="col-xl-6 mb-3">
                            <label>Total en caja:</label>
                            <input class="form-control" type="text" id="totalCash" readonly disabled>
                        </div>
                        {{-- <div class="col-xl-12 mb-3">
                            <span>
                                <b class="text-danger">Nota:</b><br>
                                - <b>Corte en sistema: </b> es la cantidad que se ingresa para que en sistema solo quede la caja chica como efectivo en caja.<br>
                                - <b>Corte de efectivo: </b> es la catidad de dinero que se retira de la caja ya que en ocasiones hay diferencia respecto a 
                                lo que marca el sistema, ya sea por cambios de boleto o algún otro factor.
                            </span>
                        </div> --}}
                        <div class="col-xl-6 mb-3">
                            <label class="mb-2">¿El total de dinero en efectivo equivale a <b id="totalCashInfo"></b>?</label><br>
                            {{-- <input class="form-control" type="text" id="cutCash" onKeyPress="return soloNumeros(event)" required> --}}
                            <input class="pointer question" type="radio" name="question" id="si" value="1" required>
                            <label class="pointer" for="si">Si</label>
                            <input class="pointer question ms-3" type="radio" name="question" id="no" value="0" required>
                            <label class="pointer" for="no">No</label>
                        </div>
                        <div class="col-xl-6 mb-3 hidden" id="divCut">
                            <label>Corte de caja: <span class="text-success" id="infoCutSys"></span></label>
                            <input class="form-control" type="text" id="cutSystem" name="cutSystem" onKeyPress="return soloNumeros(event)" readonly disabled>
                        </div>
                        <div class="col-xl-6 mb-3 hidden" id="divCash">
                            <label>Total de dinero en efectivo:</label>
                            <input class="form-control" type="text" name="actualCash" id="actualCash" onKeyPress="return soloNumeros(event)">
                        </div>
                        <div class="col-xl-6 mb-3 hidden" id="divRealCut">
                            <label>Corte de caja: <span class="text-success" id="infoCut"></span></label>
                            <input class="form-control" type="text" name="realCut" id="realCut" readonly disabled>
                        </div>
                        <div class="col-xl-12 mb-3">
                            <label>Comentarios:</label>
                            <textarea class="form-control" name="message" id="message" rows="5"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Hacer corte</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function registerSale() {
            $(`#name`).removeClass('border border-danger');
            if ($(`#name`).val() == '') {
                $(`#name`).addClass('border border-danger');
                Swal.fire({
                    icon: 'error',
                    text: 'El nombre del cliente es obligatorio'
                });
                return;
            }
            if ($('input:radio[name=payment_method]:checked').val() == undefined) {
                Swal.fire({
                    icon: 'error',
                    text: 'Elija un método de pago'
                });
                return;
            }
            var data = {
                _token: $("meta[name='csrf-token']").attr("content"),
                event_id: $('#event_id').val(),
                name: $(`#name`).val(),
                email: $(`#email`).val(),
                phone: $(`#phone`).val(),
                total: parseInt($(`#total`).val()),
                payment_method: $('input:radio[name=payment_method]:checked').val(),
                folios: []
            };
            var empty = true;
            $(`#tbodySale .folio_id`).each(function(i, el) {
                data.folios[i] = {};
                data.folios[i].id = parseInt($(this).val());
            });
            $(`#tbodySale .quantity`).each(function(i, el) {
                if ($(this).val() > 0) {
                    empty = false;
                }
                data.folios[i].quantity = parseInt($(this).val());
            });

            if (empty) {
                Swal.fire({
                    icon: 'error',
                    text: 'No ha elegido ningún boleto para vender'
                });
                return;
            }

            $('#registerSale').attr('disabled', true);
            $.ajax({
                url: $(`#URL`).val()+'registerSale',
                method: 'POST',
                data: data,
                success: (res)=> {
                    $('#registerSale').attr('disabled', false);
                    if (res.error == false) {
                        $(`#divClient .form-control`).val('');
                        $('#pay').val('');
                        $('input:radio[name=payment_method]:checked').prop('checked', false);
                        $(`#change`).text('0.00');
                        $(`#total`).val(0);
                        $(`#txtTotal`).text('0.00');
                        var totalCash = (res.cashBox.cash != null ? res.cashBox.cash.cash : 0) + (res.cashBox.box != null ? res.cashBox.box.cash : 0);
                        $('#cashInBoxTxt').text('$'+formatMoney(totalCash));
                        $('#cashInBox').val(totalCash);
                        $('#cashSale').val(res.cashBox.cash != null ? res.cashBox.cash.cash : 0);
                        $('#boxSale').val(res.cashBox.box != null ? res.cashBox.box.cash : 0);
                        res.data.forEach(d => {
                            if (d.status == 0) {
                                $(`#tr${d.id}`).remove();
                            } else {
                                $(`#tr${d.id} .quantity`).val(0);
                                $(`#tr${d.id} .txtAvailable`).text(d.quantity - d.sold);
                                $(`#tr${d.id} .available`).val(parseInt(d.quantity) - parseInt(d.sold));
                            }
                        });
                        Swal.fire({
                            icon: 'success',
                            text: 'La venta se registro con éxito'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: res.msj
                        });
                    }
                },
                error: ()=> {
                    $('#registerSale').attr('disabled', false);
                    console.log('ERROR');
                }
            });
        }

        $('#formcut').submit((e)=> {
            e.preventDefault();
            $('#actualCash').removeClass('border border-danger');
            var totalCash = parseInt($('#cashInBox').val());
            var box = parseInt($('#box').val());
            var cut = parseInt($('#cutSystem').val());
            var actualCash = parseInt($('#actualCash').val());
            var realCut = parseInt($('#realCut').val());
            if (cut > (totalCash - box)) {
                Swal.fire({
                    icon: 'error',
                    text: `El corte no puede ser mayor a $${(totalCash - box)}.00`
                });
                return;
            }
            if (realCut > (actualCash - box)) {
                Swal.fire({
                    icon: 'error',
                    text: `El corte no puede ser mayor a $${(actualCash - box)}.00`
                });
                return;
            }
            if ($('input:radio[name=question]:checked').val() == 0) {
                if (actualCash <= box) {
                    $('#actualCash').addClass('border border-danger');
                    Swal.fire({
                        icon: 'error',
                        text: 'El total en efectivo debe ser mayor que el valor de caja chica'
                    });
                    return;
                }
            }
            $.ajax({
                url: $('#URL').val()+'registerCut',
                method: 'POST',
                data: $('#formcut').serialize(),
                success: (res)=> {
                    if (res.error == false) {
                        var totalCash = (res.cashBox.cash != null ? res.cashBox.cash.cash : 0) + (res.cashBox.box != null ? res.cashBox.box.cash : 0);
                        $('#cashInBoxTxt').text('$'+formatMoney(totalCash));
                        $('#cashInBox').val(totalCash);
                        $('#cashSale').val(res.cashBox.cash != null ? res.cashBox.cash.cash : 0);
                        $('#boxSale').val(res.cashBox.box != null ? res.cashBox.box.cash : 0);
                        $('#modalCuts').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            html: res.msj
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

        $(`#pay`).keyup(function() {
            if ($(this).val() == '' || parseInt($(this).val()) <= parseInt($(`#total`).val()) || parseInt($(`#total`).val()) == 0) {
                $(`#change`).text('0.00');
            } else {
                $(`#change`).text(formatMoney(parseInt($(this).val()) - parseInt($(`#total`).val())));
            }
        });

        function moreMinus(folio_id, action) {
            var quantity = 0;
            if (action == 'add') {
                if ((parseInt($(`#tr${folio_id} .quantity`).val()) + 1) <= $(`#tr${folio_id} .available`).val()) {
                    quantity = parseInt($(`#tr${folio_id} .quantity`).val()) + 1;
                } else {
                    quantity = parseInt($(`#tr${folio_id} .quantity`).val());
                }
            } else {
                quantity = parseInt($(`#tr${folio_id} .quantity`).val()) - 1;
                if (quantity == -1) {
                    quantity = 0;
                }
            }
            $(`#tr${folio_id} .quantity`).val(quantity);
            calculateTotal();
        }

        function calculateTotal() {
            var data = [];
            $(`#tbodySale .quantity`).each(function(i, el) {
                data[i] = {};
                data[i].quantity = parseInt($(this).val());
            });
            $(`#tbodySale .price`).each(function(i, el) {
                data[i].price = parseInt($(this).val());
            });
            var total = 0;
            data.forEach(d => {
                total = total + (d.quantity * d.price);
            });
            $(`#txtTotal`).text(`${formatMoney(total)}`);
            $(`#total`).val(total);
        }

        function soloNumeros(e) {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8))
        }

        function modalCuts() {
            $('input:radio[name=question]:checked').prop('checked', false);
            $('#modalCuts .form-control').val('');
            $('#modalCuts #divCash').addClass('hidden');
            $('#modalCuts #divRealCut').addClass('hidden');
            $('#modalCuts #divCut').addClass('hidden');
            $('#modalCuts #infoCut').text('');
            $('#modalCuts #boxModal').val($('#box').val());
            // $('#modalCuts #totalSale').val($('#cashSale').val());
            $('#modalCuts #totalCash').val($('#cashInBox').val());
            $('#modalCuts #totalCashInfo').text('$'+formatMoney($('#cashInBox').val()));
            $('#modalCuts #infoCutSys').text(`${$('#cashInBox').val()} - ${$('#box').val()} = ${$('#cashSale').val()}`);
            $('#modalCuts #cutSystem').val($('#cashSale').val());
            $('#modalCuts').modal('show');
        }

        $('#modalCuts .question').click(function() {
            $('#modalCuts #cutSystem').val(parseInt($('#cashInBox').val()) - parseInt($('#box').val()));
            $('#modalCuts #actualCash').val('');
            $('#modalCuts #realCut').val('');
            $('#modalCuts #infoCut').text('');
            if ($(this).val() == 0) {
                $('#modalCuts #divCash').removeClass('hidden');
                $('#modalCuts #divRealCut').removeClass('hidden');
                $('#modalCuts #divCut').addClass('hidden');
                $('#modalCuts #cutSystem').prop('required', false);
                $('#modalCuts #actualCash').prop('required', true);
                $('#modalCuts #realCut').prop('required', true);
            } else {
                $('#modalCuts #divCash').addClass('hidden');
                $('#modalCuts #divRealCut').addClass('hidden');
                $('#modalCuts #divCut').removeClass('hidden');
                $('#modalCuts #cutSystem').prop('required', true);
                $('#modalCuts #actualCash').prop('required', false);
                $('#modalCuts #realCut').prop('required', false);
            }
        });

        $('#modalCuts #actualCash').keyup(function() {
            if ($(this).val() != '' && parseInt($(this).val()) > parseInt($('#box').val())) {
                $('#modalCuts #infoCut').text(`${$(this).val()} - ${$('#box').val()} = ${parseInt($(this).val()) - parseInt($('#box').val())}`);
                $('#modalCuts #realCut').val(parseInt($(this).val()) - parseInt($('#box').val()));
            } else {
                $('#modalCuts #infoCut').text('');
                $('#modalCuts #realCut').val('');
            }
        });

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