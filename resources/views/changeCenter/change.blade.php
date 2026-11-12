<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Centro de canje</title>
    <link rel="stylesheet" href="{{asset('css/bootstrap5.3.0.min.css')}}">
    <link rel="stylesheet" href="{{asset('fontawesome6.4.0/css/all.css')}}">
</head>
<body>
    <div class="container pt-5">
        <input type="hidden" id="URL" value="{{asset('')}}">
        <div class="card p-4  mt-5">
            <form class="row" id="registerTickets">
                {{ csrf_field() }}
                {{-- <div class="col-xl-6 offset-xl-3">
                    <label for="provider">Expositor:</label>
                    <select class="form-control mb-3" name="provider" id="provider" required>

                    </select>
                </div> --}}
                <div class="col-xl-12 text-center mb-4">
                    <h1>Centro de canje</h1>
                </div>
                <div class="col-xl-4 mb-4">
                    <h4>Total de compras: <span class="text-primary" id="buys">$0.00</span></h4>
                </div>
                <div class="col-xl-4 mb-4">
                    <h4>Boletos participantes: <span class="text-primary" id="tickets">0</span></h4>
                </div>
                <div class="col-xl-4"></div>
                <div class="col-xl-4">
                    <label for="name">Nombre del cliente:</label>
                    <input class="form-control mb-3" type="text" name="name" id="name" required>
                </div>
                <div class="col-xl-4">
                    <label for="email">Correo electrónico:</label>
                    <input class="form-control mb-3" type="email" name="email" id="email" required>
                </div>
                <div class="col-xl-4">
                    <label for="phone">Teléfono:</label>
                    <input class="form-control mb-3" type="text" name="phone" id="phone" required>
                </div>
                <div class="col-xl-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Expositor</th>
                                <th class="w-25">Monto</th>
                                <th class="text-center" style="width: 15%;">
                                    <button class="btn btn-success btn-sm" type="button" onclick="addBuy()">Añadir compra</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAmount">
                            <tr class="tr" id="tr0">
                                <td>
                                    <select class="form-control form-control-sm mb-3 provider" name="provider[0]" required>

                                    </select>
                                </td>
                                <td>
                                    <input class="form-control form-control-sm amount" type="text" name="amount[0]" onkeyup="calculateTickets()" required>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm delete" type="button" onclick="deleteTr(0)"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {{-- <div class="col-xl-6 offset-xl-3">
                    <label for="amount">Monto de compra:</label>
                    <input class="form-control mb-3" type="text" name="amount" id="amount" required>
                </div>
                <div class="col-xl-6 offset-xl-3">
                    <label for="amount2">Confirme el monto de compra:</label>
                    <input class="form-control mb-3" type="text" name="amount2" id="amount2" required>
                </div> --}}
                <div class="col-xl-12 text-center mt-2">
                    <button class="btn btn-primary" type="submit">Registrar compras</button>
                </div>
            </form>
        </div>
    </div>
<script src="{{asset('js/jquery-3.7.0.min.js')}}"></script>
<script src="{{asset('js/bootstrap5.3.0.bundle.min.js')}}"></script>
<script src="{{asset('js/sweetalert2.js')}}"></script>
<script>
    $(document).ready(()=> {
        extractProviders();
    });

    function addBuy() {
        var html = '';
        html += `<tr class="tr" id="tr${countTr()}">`;
            html += `<td>`;
                html += `<select class="form-control form-control-sm mb-3 provider" name="provider[${countTr()}]" required>${htmlProvider}</select>`;
            html += `</td>`;
            html += `<td>`;
                html += `<input class="form-control form-control-sm amount" type="text" name="amount[${countTr()}]" onkeyup="calculateTickets()" required>`;
            html += `</td>`;
            html += `<td class="text-center">`;
                html += `<button class="btn btn-danger btn-sm delete" type="button" onclick="deleteTr(${countTr()})"><i class="fa-solid fa-trash-can"></i></button>`;
            html += `</td>`;
        html += `</tr>`;
        $('#tbodyAmount').append(html);
    }

    function calculateTickets() {
        var total = 0;
        $('.amount').each(function() {
            if ($(this).val() != '' && parseInt($(this).val()) >= 500) {
                total = total + parseInt($(this).val());
            }
        });
        $('#buys').text('$'+formatMoney(total));
        $('#tickets').text(parseInt(total / 500));
    }

    function reorderTr() {
        var count = 0;
        $('.tr').each(function() {
            $(this).attr('id', `tr${count}`);
            $(`#tr${count} .provider`).attr('name', `provider[${count}]`);
            $(`#tr${count} .amount`).attr('name', `amount[${count}]`);
            $(`#tr${count} .delete`).attr('onclick', `deleteTr(${count})`);
            count++;
        });
    }

    function deleteTr(id) {
        if (countTr() > 1) {
            $('#tr'+id).remove();
            reorderTr();
        }
    }

    var countTr = ()=> {
        var count = 0;
        $('.tr').each(function() {
            count++;
        });
        return count;
    }

    $('#registerTickets').submit((e)=> {
        e.preventDefault();
        $('.amount').removeClass('border border-danger');
        // if ($('#amount').val() != $('#amount2').val()) {
        //     Swal.fire({
        //         icon: 'error',
        //         text: 'Los montos no coinciden'
        //     });
        //     return false;
        // }
        if(validateAmounts()) {
            Swal.fire({
                icon: 'error',
                html: 'La compra mínima por expositor <br>debe ser mayor o igual a <b>$500.00</b>'
            });
            return false;
        }
        $.ajax({
            url: $('#URL').val()+'registerTickets',
            type: 'POST',
            data: $('#registerTickets').serialize(),
            success: (res)=> {
                $('.form-control').val('');
                $('#tbodyAmount').html('');
                addBuy();
                $('#buys').text('$0.00');
                $('#tickets').text('0');
                Swal.fire({
                    icon: 'success',
                    text: 'La compra se registro correctamente'
                });
            },
            error: ()=> {
                console.log('ERROR');
            }
        });
    });

    function validateAmounts() {
        var error = false;
        $('.amount').each(function() {
            if ($(this).val() == '' || $(this).val() < 500) {
                $(this).addClass('border border-danger');
                error = true;
            }
        });
        return error;
    }

    var htmlProvider = '';
    function extractProviders() {
        $.ajax({
            url: 'https://crm.ticketland.mx/api/get-expositores-crm/1',
            type: 'GET',
            success: (res)=> {
                htmlProvider = '<option value="">Elija un expositor</option>';
                res.data.forEach(d => {
                    htmlProvider += `<option value="${d.id}">${d.customer_client}</option>`;
                });
                $('.provider').html(htmlProvider);
            },
            error: ()=> {
                console.log('ERROR');
            }
        });
    }

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
</body>
</html>