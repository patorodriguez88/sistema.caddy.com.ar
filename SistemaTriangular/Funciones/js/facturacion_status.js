/**
 * Estados de Facturacion.Status — fuente única de verdad (JS)
 * Incluir este archivo en cualquier página que muestre o modifique el estado de facturación.
 *
 * 0 = Pendiente   (rojo)
 * 1 = Notificado  (azul)   — se envió la factura al cliente
 * 2 = En Revisión (naranja) — se registró un reclamo
 * 3 = Solucionado (verde)  — cobrado / resuelto
 */

const FACTURACION_STATUS = {
    0: { label: 'Pendiente',    cls: 'bg-danger',            icon: 'ri-time-line' },
    1: { label: 'Notificado',   cls: 'bg-primary',           icon: 'ri-mail-check-line' },
    2: { label: 'En Revisión',  cls: 'bg-warning text-dark', icon: 'ri-search-eye-line' },
    3: { label: 'Solucionado',  cls: 'bg-success',           icon: 'ri-checkbox-circle-line' },
};

function facturacionStatusBadge(status) {
    const s = FACTURACION_STATUS[parseInt(status)] ?? FACTURACION_STATUS[0];
    return `<span class="badge ${s.cls}">${s.label}</span>`;
}

function facturacionStatusLabel(status) {
    return (FACTURACION_STATUS[parseInt(status)] ?? FACTURACION_STATUS[0]).label;
}

function facturacionStatusClass(status) {
    return (FACTURACION_STATUS[parseInt(status)] ?? FACTURACION_STATUS[0]).cls;
}

/** Genera el <select> HTML para elegir estado */
function facturacionStatusSelect(selectedStatus, id = 'modal_nuevo_status') {
    let html = `<select class="form-select mt-2" id="${id}">`;
    Object.entries(FACTURACION_STATUS).forEach(([val, s]) => {
        const sel = parseInt(val) === parseInt(selectedStatus) ? 'selected' : '';
        html += `<option value="${val}" ${sel}>${s.label}</option>`;
    });
    html += '</select>';
    return html;
}
