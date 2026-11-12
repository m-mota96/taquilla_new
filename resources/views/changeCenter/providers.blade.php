@extends('changeCenter.index')
@section('heads')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="container pt-5">
        <div class="col-xl-12">
            <h2 class="text-primary">Expositores</h2>
        </div>
        <div class="card p-4">
            <div class="row">
                <div class="col-xl-12">
                    <table class="table table-striped" id="tableProviders">
                        <thead>
                            <tr>
                                <th>Expositor</th>
                                <th>Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($providers as $p)
                                <tr>
                                    <td>
                                        {{$p['customer_client']}}
                                    </td>
                                    <td>
                                        ${{number_format($p['sales'], 2)}}
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
            new DataTable('#tableProviders', {
                "order": [[ 1, 'desc' ]],
                lengthMenu: [[30, 60, 90, -1], [30, 60, 90, "Todos"]],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                },
            });
        });

        function tableProviders() {
            // $('#tableProviders').dataTable().fnDestroy();
            // let table = new DataTable('#tableProviders', {
            //     ajax: {
            //         url: $('#URL').val()+'cuts',
            //         type: 'POST',
            //         data: {
            //             _token: $("meta[name='csrf-token']").attr("content"),
            //             event_id: $('#event_id').val()
            //         }
            //     },
            //     order: [[0, 'ASC']],
            //     processing: true,
            //     serverSide: true,
            //     dom: 'lrtip',
            //     paging: false,
            //     columns: [
            //         { data: 'hour', orderable: false, width: '5%', className: 'text-center' },
            //         {
            //             orderable: false,
            //             width: '25%',
            //             name: 'date',
            //             render: (data, type, row, meta) => {
            //                 var date = row.date.substring(8, 10);
            //                 date = date + ' de ' + nameMonth(row.date);
            //                 date = date + ' de ' + row.date.substring(0, 4);
            //                 return date;
            //             }
            //         },
            //         { data: 'user.name', orderable: false, width: '20%' },
            //         {
            //             orderable: false,
            //             width: '10%',
            //             render: (data, type, row, meta) => {
            //                 return `$${formatMoney(row.systemCut)}`;
            //             }
            //         },
            //         {
            //             orderable: false,
            //             width: '10%',
            //             render: (data, type, row, meta) => {
            //                 return `$${formatMoney(row.realCut)}`;
            //             }
            //         },
            //         {
            //             orderable: false,
            //             width: '10%',
            //             render: (data, type, row, meta) => {
            //                 var difference = row.realCut - row.systemCut;
            //                 if (difference < 0) {
            //                     difference = '-$'+formatMoney(difference * -1);
            //                 } else {
            //                     difference = '$'+formatMoney(difference);
            //                 }
            //                 return difference;
            //             }
            //         },
            //         { data: 'comment', orderable: false, width: '30%' }
            //     ],
            //     initComplete: function () {
            //         // Apply the search
            //         this.api()
            //             .columns()
            //             .every(function () {
            //                 var that = this;
            //                 var titulos = ['Hora', 'Fecha'];
            //                 if(that.index() == 1) {
            //                     var select = $('<select class="form-control form-control-sm"><option value="">Todos los cortes de caja</option></select>')
            //                     .appendTo($(that.header()).empty())
            //                     .on('change', function () {
            //                         if (that.search() !== this.value) {
            //                             that.search(this.value).draw();
            //                         }
            //                     });
                                
            //                     for (var i = 0; i < dates.length; i++) {
            //                         select.append('<option value="' + dates[i].date + '">' + dates[i].date_parse + '</option>');
            //                     }
            //                 }
                            
            //             });
            //     },
            //     language: {
            //         url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
            //     }
            // });
        }
    </script>
@endsection