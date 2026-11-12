<!-- Modal -->
<div class="modal fade" id="createCashierModal" tabindex="-1" aria-labelledby="createCashierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form class="modal-content" id="formCreateCashier">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createCashierModalLabel">Nuevo/a cajero/a</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12">
                        {{ csrf_field() }}
                        <label>Nombre:</label>
                        <input class="form-control mb-3" type="text" id="name" name="name" required autocomplete="off">
                        <label>Usuario:</label>
                        <input class="form-control mb-3" type="text" id="user" name="user" required autocomplete="off">
                        <label>Contraseña:</label>
                        <input class="form-control mb-3" type="text" id="password" name="password" required>
                        <label>Confirmar contraseña:</label>
                        <input class="form-control mb-3" type="text" id="confirm_password" name="confirm_password" required>
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