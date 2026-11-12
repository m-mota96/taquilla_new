<!-- Modal -->
<div class="modal fade" id="createTicketModal" tabindex="-1" aria-labelledby="createTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form class="modal-content" id="formCreateTicket">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createTicketModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12">
                        {{ csrf_field() }}
                        <input type="hidden" name="eventId" id="eventId">
                        <input class="form-control" type="hidden" name="ticket_id" id="ticket_id">
                        <label>Nombre del boleto:</label>
                        <input class="form-control mb-3" type="text" id="name_ticket" name="name_ticket" required>
                        <label>Precio:</label>
                        <input class="form-control mb-3" type="text" id="price" name="price" required onKeyPress="return soloNumeros(event)">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="createTicketModalBtn">Guardar</button>
            </div>
        </form>
    </div>
</div>