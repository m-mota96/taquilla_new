@extends('customers.index')
@section('heads')
    
@endsection
@section('content')
    @include('customers.menuDown')
    <div class="container mt-4">
        <input type="hidden" id="event_id" value="{{$event->id}}">
        <div class="row">
            <div class="col-xl-12 mb-4">
                <h4>{{$event->name}}/<span class="text-primary">Estadísticas</span></h4>
            </div>
            <div class="col-xl-12">
                <div class="card p-4">
                    <div class="row">
                        <div class="col-xl-4 mb-4">
                            <h5>Fecha:</h5>
                            <input type="hidden" id="current_date" value="{{date('Y-m-d')}}">
                            <select class="form-control w-75" id="dates">
                                <option value="all">Todo</option>
                                @foreach ($dates as $d)
                                    <option value="{{$d['date']}}">{{$d['date_parse']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-8 mb-4">
                            <div class="row">
                                <div class="col-xl-4 text-center">
                                    <div class="col-xl-12 btn-totalSales pt-3 pb-3">
                                        <h1 class="text-btn-totalSales bold" id="salesCash"></h1>
                                        <p class="text-btn-totalSales">Ventas en efectivo</p>
                                    </div>
                                </div>
                                <div class="col-xl-4 text-center">
                                    <div class="col-xl-12 btn-totalPending pt-3 pb-3">
                                        <h1 class="text-btn-totalPending bold" id="salesCard"></h1>
                                        <p class="text-btn-totalPending">Ventas con tarjeta</p>
                                    </div>
                                </div>
                                <div class="col-xl-4 text-center">
                                    <div class="col-xl-12 btn-totalExpired pt-3 pb-3">
                                        <h1 class="text-btn-totalExpired bold" id="salesTotal"></h1>
                                        <p class="text-btn-totalExpired">Total</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <table class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>Boleto</th>
                                        <th class="text-center">Precio</th>
                                        <th class="text-center">Cantidad vendida</th>
                                        <th class="text-center">Total vendido</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodySales">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var dates = @json($dates);

        $(document).ready(()=> {
            dates.forEach(d => {
                if (d.date == $('#current_date').val()) {
                    $('#dates').val(d.date);
                }
            });
            chargingStats();
        });

        $('#dates').change(()=> {
            chargingStats();
        });

        function chargingStats() {
            $.ajax({
                url: $('#URL').val()+'stats',
                method: 'POST',
                data: {
                    _token: $("meta[name='csrf-token']").attr("content"),
                    event_id: $('#event_id').val(),
                    date: $('#dates').val(),
                },
                success: (res)=> {
                    var totalCash = 0, totalCard = 0, total = 0;
                    res.tickets.forEach(t => {
                        totalCash = totalCash + parseInt(t.price) * parseInt(t.sales_cash);
                        totalCard = totalCard + parseInt(t.price) * parseInt(t.sales_card);
                    });
                    $('#salesCash').text(`$${totalCash}`);
                    $('#salesCard').text(`$${totalCard}`);
                    $('#salesTotal').text(`$${totalCash + totalCard}`);
                    var html = '';
                    if (res.sales.length > 0) {
                        res.sales.forEach(s => {
                            html += `<tr>`;
                                html += `<td>${s.ticket.name}</td>`;
                                html += `<td class="text-center">$${s.ticket.price}</td>`;
                                html += `<td class="text-center">${s.sold}</td>`;
                                html += `<td class="text-center">$${s.sold * s.ticket.price}</td>`;
                            html += `</tr>`;
                        });
                    } else {
                        html = '<tr><td class="text-center" colspan="4">Ningún dato disponible en esta tabla</td></tr>';
                    }
                    $('#tbodySales').html(html);
                },
                error: ()=> {
                    console.log('ERROR');
                }
            });
        }
    </script>
@endsection