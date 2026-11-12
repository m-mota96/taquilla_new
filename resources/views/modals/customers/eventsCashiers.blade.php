<!-- Modal -->
<div class="modal fade" id="eventsCashiersModal" tabindex="-1" aria-labelledby="eventsCashiersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formCreateEvent">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eventsCashiersModalLabel">Listado de eventos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <table class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Evento</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Activar/Desactivar</th>
                            </tr>
                        </thead>
                        <tbody id="bodyEventsCashiers">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </form>
    </div>
</div>