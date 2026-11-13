// Impresión de gafetes en Zebra (ZPL). Copiado de frontend-conferences:
//   src/utils/zplTemplates.js  -> badge90x150ZPL
//   src/utils/printZpl.js      -> printZPL
// Única diferencia con conferencias: los logos van 5 mm más arriba (ver LOGOS_UP).
// Si cambias el diseño del gafete en conferencias, vuelve a copiarlo aquí conservando ese ajuste.
// Requiere BrowserPrint-3.1.250.min.js y la app Zebra Browser Print instalada en la PC.

window.badge90x150ZPL =({ name, occupation = '', ticket_id, ticket_name = '' }) => {
    if (!name || !ticket_id) return '';

    const nameText = name.toUpperCase();
    const occupationText = occupation.toUpperCase();
    const ticketNameText = (ticket_name || '').toUpperCase();

    // Margen del nombre con las orillas del gafete (8 puntos = 1 mm): el bloque se estrecha y queda centrado.
    const namePaddingX = 40;
    const nameBlockWidth = 720 - (namePaddingX * 2);

    // Ancho medio de una letra mayúscula como fracción de la altura de la letra (fuente 0). Es una
    // estimación algo holgada: si los nombres se parten antes de tiempo, bájala; si se salen, súbela.
    const NAME_CHAR_WIDTH_RATIO = 0.58;

    // Cuántos renglones ocupa el nombre a ese tamaño, partiendo por palabras como lo hace ^FB.
    const countNameLines = (text, fontSize) => {
        const maxChars = Math.floor(nameBlockWidth / (fontSize * NAME_CHAR_WIDTH_RATIO));
        let lines = 1;
        let current = 0;

        for (const word of text.split(/\s+/).filter(Boolean)) {
            const wordLines = Math.ceil(word.length / maxChars);   // una palabra más larga que el renglón se parte
            if (current === 0) {
                current = word.length;
            } else if (current + 1 + word.length <= maxChars) {
                current += 1 + word.length;
            } else {
                lines += 1;
                current = word.length;
            }
            if (wordLines > 1) lines += wordLines - 1;
        }
        return lines;
    };

    // Se usa la letra más grande con la que el nombre quepa en 2 renglones como máximo.
    // y: dónde empieza el nombre; space: aire extra entre el QR y la ocupación (solo con la letra más chica).
    const NAME_SIZES = [
        { fontSize: 60, y: 130, space: 0 },
        { fontSize: 50, y: 125, space: 0 },
        { fontSize: 42, y: 122, space: 0 },
        { fontSize: 35, y: 120, space: 0 },
        { fontSize: 30, y: 110, space: 30 },
    ];
    const nameSize = NAME_SIZES.find(({ fontSize }) => countNameLines(nameText, fontSize) <= 2)
        ?? NAME_SIZES[NAME_SIZES.length - 1];

    // Espacio extra entre renglones del nombre (^FB): 0 = pegados, negativo los junta más, positivo los separa.
    // Con 0, dos renglones miden justo nameFontSize * 2, que es lo que reserva nameBlockHeight.
    const nameFontSize = nameSize.fontSize;
    const lineSpacing = 0;
    const nameY = nameSize.y;
    const spaceQrcodeAndOccupation = nameSize.space;

    const nameBlockHeight = (nameFontSize * 2);
    // const nameBlockHeight = (nameFontSize * 3) + (lineSpacing * 2);

    // Nombre del ticket debajo del nombre: una línea, o dos si es largo (~34 caracteres caben a 35 pt).
    // Sin nombre de ticket no se reserva espacio y el QR queda donde estaba.
    const ticketFontSize = 35;
    const ticketLines = ticketNameText.length > 34 ? 2 : 1;
    const ticketY = nameY + nameBlockHeight + 10;   // 10 de aire bajo el segundo renglón del nombre
    const ticketBlockHeight = ticketNameText ? (ticketFontSize * ticketLines) + 10 : 0;

    // La ocupación no se mueve: sigue 400 puntos debajo de donde empezaba el QR.
    // El nombre sube 20 y esos 20 se devuelven aquí, para que solo baje por el nombre del ticket.
    const qrSlotY = ticketY + ticketBlockHeight + spaceQrcodeAndOccupation + 20;
    const occupationY = qrSlotY + 400 + spaceQrcodeAndOccupation;

    // Tamaño real del QR (^BQ modelo 2, ECC L, módulo de 12 puntos): cada versión suma 4 módulos.
    // La capacidad en bytes por versión (1 a 10) depende de la longitud del ticket_id.
    const QR_MODULE = 12;
    const QR_BYTE_CAPACITY_L = [17, 32, 53, 78, 106, 134, 154, 192, 230, 271];
    const qrVersion = QR_BYTE_CAPACITY_L.findIndex((capacity) => String(ticket_id).length <= capacity) + 1;
    const qrSize = qrVersion > 0 ? (17 + 4 * qrVersion) * QR_MODULE : 400;

    // Se centra el QR: misma distancia al texto de arriba (nombre del ticket) que a la ocupación.
    const textAboveQrBottom = ticketNameText ? ticketY + (ticketFontSize * ticketLines) : ticketY;
    const qrY = Math.round(textAboveQrBottom + (occupationY - textAboveQrBottom - qrSize) / 2);

    const occupationBlockHeight = (40 * 2) + 25;
    // [Taquilla] Los logos van 5 mm (40 puntos) más arriba que en conferencias cuando la ocupación
    // cabe en un renglón (~31 caracteres a 40 pt). Con 2 renglones se quedan donde estaban para no encimarse.
    const LOGOS_UP = occupationText.length <= 31 ? 40 : 0;
    const imageY = occupationY + occupationBlockHeight + 20 - LOGOS_UP;

    const labelWidth = 720;
    const imageWidth = 608;  // 76 bytes × 8 bits
    const imageX = (labelWidth - imageWidth) / 2;


    return `
^XA
^CI28
^MMT
^POI
^PW720
^LL1200
^LH0,0
^LT0
^LS0
^PR5
^FO${namePaddingX},${nameY}
^A0N,${nameFontSize},${nameFontSize}
^FB${nameBlockWidth},3,${lineSpacing},C,0
^FH\\
^FD${nameText}^FS${ticketNameText ? `
^FO0,${ticketY}
^A0N,${ticketFontSize},${ticketFontSize}
^FB720,${ticketLines},10,C,0
^FH\\
^FD${ticketNameText}^FS` : ''}
^FO210,${qrY}
^BQN,2,12
^FDLA,${ticket_id}^FS
^FO0,${occupationY}
^A0N,40,40
^FB720,2,25,C,0
^FH\\
^FD${occupationText}^FS
^FO${imageX},${imageY}
^XGR:PATROCINADORES.GRF,1,1
^FS
^XZ
`;
};


window.printZPL = (zpl) => {
    console.log('entrando a printZPL ', zpl);

    return new Promise((resolve, reject) => {
        if (!window.BrowserPrint) {
            console.error('BrowserPrint no existe');
            reject('BrowserPrint no disponible');
            return;
        }

        BrowserPrint.getDefaultDevice(
            "printer",
            (device) => {
                console.log('Dispositivo detectado:', device);

                if (!device) {
                    reject('No se detectó impresora Zebra');
                    return;
                }

                device.send(
                    zpl,
                    () => {
                        console.log('ZPL enviado correctamente');
                        resolve('Etiqueta impresa');
                    },
                    (err) => {
                        console.error('Error send:', err);
                        reject(err);
                    }
                );
            },
            (err) => {
                console.error('Error getDefaultDevice:', err);
                reject(err);
            }
        );
    });
};
 