@extends('customers.index')
@section('heads')

@endsection
@section('content')
    @include('customers.menuDown')
    @php
        $saleMode  = $setting ? (int) $setting->sale_mode : \App\Models\EventSetting::SALE_MODE_PER_PURCHASE;
        $eventType = $setting ? $setting->event_type : null;
    @endphp
    <div class="container mt-4">
        <div class="row">
            <div class="col-xl-12 mb-4">
                <h4>{{$event->name}}/<span class="text-primary">Configuraciones</span></h4>
            </div>
            <div class="col-xl-8 mb-4">
                <div class="card p-4">
                    <form class="row" id="formSettings">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">

                        <div class="col-xl-12 mb-4">
                            <h5 class="mb-3">Modo de venta</h5>
                            <div class="form-check mb-2">
                                <input class="form-check-input pointer" type="radio" name="sale_mode" id="mode1" value="1" {{$saleMode == 1 ? 'checked' : ''}}>
                                <label class="form-check-label pointer" for="mode1">
                                    <b>Un registro por compra</b><br>
                                    <small class="text-secondary">Se captura el nombre, correo y teléfono de una sola persona, aunque compre uno o más boletos.</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input pointer" type="radio" name="sale_mode" id="mode2" value="2" {{$saleMode == 2 ? 'checked' : ''}}>
                                <label class="form-check-label pointer" for="mode2">
                                    <b>Un registro por persona</b><br>
                                    <small class="text-secondary">Se captura un formulario por cada boleto (nombre, correo, teléfono y cargo) y cada registro se envía a la plataforma de conferencias.</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-xl-12 {{$saleMode == 2 ? '' : 'hidden'}}" id="divPerPerson">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="mb-1">Tipo de evento:</label>
                                    <select class="form-select" name="event_type" id="event_type">
                                        <option value="">Seleccione...</option>
                                        @foreach ($eventTypes as $key => $name)
                                            <option value="{{$key}}" {{$eventType == $key ? 'selected' : ''}}>{{$name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="mb-1">UUID del evento en conferencias:</label>
                                    <input class="form-control" type="text" name="uuid_events_conferences" id="uuid_events_conferences" value="{{$event->uuid_events_conferences}}" maxlength="100">
                                </div>
                                <div class="col-xl-12 mb-3">
                                    <span class="text-secondary">Cargos que verá la cajera:</span>
                                    @foreach ($eventTypes as $key => $name)
                                        <div class="positions-list {{$eventType == $key ? '' : 'hidden'}}" data-type="{{$key}}">
                                            @foreach ($positions->get($key, collect()) as $p)
                                                <span class="badge text-bg-light border me-1 mb-1">{{$p->name}}</span>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 mt-2 text-center">
                            <button class="btn btn-primary" id="btnSave">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-4 mb-4">
                <div class="card p-4">
                    <h5 class="mb-3">Envío a conferencias</h5>
                    <p class="mb-1">Registros pendientes de enviar:</p>
                    <h3 class="bold mb-3" id="pendingCount">{{$pending}}</h3>
                    <p class="text-secondary small">
                        Si la plataforma de conferencias no respondió al registrar una venta, los registros quedan pendientes aquí.
                        Se reenvían hasta {{\App\Http\Controllers\EventSettingController::RESEND_BATCH}} por clic.
                    </p>
                    <button class="btn btn-success w-100" id="btnResend" {{$pending > 0 ? '' : 'disabled'}}>
                        <i class="fa-solid fa-paper-plane"></i> Reenviar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $('input[name=sale_mode]').change(function() {
            $('#divPerPerson').toggleClass('hidden', $(this).val() != 2);
        });

        $('#event_type').change(function() {
            $('.positions-list').addClass('hidden');
            $(`.positions-list[data-type="${$(this).val()}"]`).removeClass('hidden');
        });

        $('#formSettings').submit((e)=> {
            e.preventDefault();
            $('#btnSave').attr('disabled', true);
            $.ajax({
                url: $('#URL').val()+'configuraciones/{{$event->id}}',
                method: 'POST',
                data: $('#formSettings').serialize(),
                success: (res)=> {
                    $('#btnSave').attr('disabled', false);
                    Swal.fire({
                        icon: res.error ? 'error' : 'success',
                        text: res.msj
                    });
                },
                error: ()=> {
                    $('#btnSave').attr('disabled', false);
                    Swal.fire({ icon: 'error', text: 'Ocurrió un error al guardar' });
                }
            });
        });

        $('#btnResend').click(function() {
            var btn = $(this);
            btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Enviando...');
            $.ajax({
                url: $('#URL').val()+'configuraciones/{{$event->id}}/reenviar',
                method: 'POST',
                data: { _token: $("meta[name='csrf-token']").attr("content") },
                success: (res)=> {
                    btn.html('<i class="fa-solid fa-paper-plane"></i> Reenviar');
                    if (res.error) {
                        btn.attr('disabled', false);
                        Swal.fire({ icon: 'error', text: res.msj });
                        return;
                    }
                    $('#pendingCount').text(res.pending);
                    btn.attr('disabled', res.pending == 0);
                    Swal.fire({
                        icon: res.failed > 0 ? 'warning' : 'success',
                        html: `Enviados: <b>${res.sent}</b><br>Fallidos: <b>${res.failed}</b><br>Pendientes: <b>${res.pending}</b>`
                    });
                },
                error: ()=> {
                    btn.attr('disabled', false).html('<i class="fa-solid fa-paper-plane"></i> Reenviar');
                    Swal.fire({ icon: 'error', text: 'Ocurrió un error al reenviar' });
                }
            });
        });
    </script>
@endsection
