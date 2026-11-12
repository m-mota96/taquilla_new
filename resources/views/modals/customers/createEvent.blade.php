<!-- Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="formCreateEvent">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createEventModalLabel">Crear nuevo evento</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12">
                        {{ csrf_field() }}
                        <label>Nombre del evento:</label>
                        <input class="form-control mb-3" type="text" id="nameEvent" name="nameEvent" required>
                        <label>Fecha del evento:</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Del:</span>
                            <input class="form-control" type="date" id="initial_date" name="initial_date" required>
                            <span class="input-group-text">Al:</span>
                            <input class="form-control" type="date" id="final_date" name="final_date" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>