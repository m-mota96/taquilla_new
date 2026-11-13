// Impresión de gafetes desde la taquilla (modo "un registro por persona").
// Usa badge90x150ZPL y printZPL de badge-zpl.js (copiados de conferencias) y SweetAlert2.

// ^ ~ y \ son caracteres de control en ZPL: si vienen en un nombre romperían la etiqueta.
function sanitizeZplText(text) {
    return String(text == null ? '' : text).replace(/[\^~\\]/g, ' ').replace(/\s+/g, ' ').trim();
}

function printErrorMessage(error) {
    if (typeof error === 'string') return error;
    if (error && error.message) return error.message;
    return 'Error desconocido. Verifique que Zebra Browser Print esté abierto y la impresora conectada.';
}

/**
 * Imprime los gafetes uno por uno mostrando el avance.
 * Si uno falla se detiene: casi siempre es la impresora (apagada, sin papel, sin Browser Print)
 * y seguir intentando solo haría esperar a la cajera.
 *
 * @param {Array<{ticket_id, name, occupation, ticket_name}>} badges
 * @returns {Promise<{ok:number, failed:number, error:string}>}
 */
async function printBadges(badges) {
    var ok = 0;

    for (var i = 0; i < badges.length; i++) {
        Swal.fire({
            title: `Imprimiendo gafete ${i + 1} de ${badges.length}...`,
            text: sanitizeZplText(badges[i].name),
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            var zpl = badge90x150ZPL({
                ticket_id:   badges[i].ticket_id,
                name:        sanitizeZplText(badges[i].name),
                occupation:  sanitizeZplText(badges[i].occupation),
                ticket_name: sanitizeZplText(badges[i].ticket_name),
            });
            await printZPL(zpl);
            ok++;
        } catch (error) {
            console.error('Error al imprimir gafete:', error);
            return { ok: ok, failed: badges.length - ok, error: printErrorMessage(error) };
        }
    }

    return { ok: ok, failed: 0, error: '' };
}

/**
 * Carga la imagen de patrocinadores en la memoria de la impresora (R:PATROCINADORES.GRF).
 * Igual que "cargar logos" de conferencias. Se hace una vez por impresora.
 *
 * @param {string} imageZpl  imagen GRF del tipo de evento (patrocinadoresWfZPL / patrocinadoresComvetZPL)
 */
async function loadBadgeLogos(imageZpl) {
    if (!imageZpl) {
        Swal.fire({ icon: 'error', title: 'Error al cargar los logos', text: 'No se encontró la imagen de logos de este evento. Recargue la página.' });
        return;
    }

    Swal.fire({
        title: 'Cargando logos en la impresora...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        var saveZPL = "^XA\n" + "~DG" + imageZpl + "^XZ\n";
        await printZPL(saveZPL);
        Swal.fire({ icon: 'success', text: 'Logos cargados correctamente' });
    } catch (error) {
        console.error('Error al cargar logos:', error);
        Swal.fire({ icon: 'error', title: 'Error al cargar los logos', text: printErrorMessage(error) });
    }
}
