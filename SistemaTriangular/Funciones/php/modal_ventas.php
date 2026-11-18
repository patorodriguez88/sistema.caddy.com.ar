<?php
// Modal "Agregar / Modificar venta" (18/11/2026). Uno solo para Clientes, starter, Pendientes y
// Redespachos: antes cada pantalla tenía su copia (fondo azul, textos de ejemplo grises que no se
// leían, columnas desparejas). Los ids son los mismos de siempre: lo manejan abmventas.js,
// pendientes.js y redespachos.js, y Funciones/js/ventas_curva.js (bonificación por cantidad y lista
// de servicios, que se carga sola).
?>
<style>
  #bs-ventas-modal-lg .modal-header { background: var(--bs-primary, #727cf5); color: #fff; }
  #bs-ventas-modal-lg .modal-header .modal-title { color: #fff; font-weight: 600; }
  #bs-ventas-modal-lg .modal-body { padding: 1.25rem 1.5rem 0.5rem; }
  #bs-ventas-modal-lg .form-label { font-weight: 600; margin-bottom: .3rem; }
  #bs-ventas-modal-lg .vm-seccion { font-size: .75rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; opacity: .65; margin: .25rem 0 .5rem; }
  #bs-ventas-modal-lg .vm-total { font-weight: 700; font-size: 1.05rem; }
  #bs-ventas-modal-lg #ventas_curva_aviso { min-height: 1.2em; }
  #bs-ventas-modal-lg .select2-container { width: 100% !important; }
</style>
<div class="modal fade" id="bs-ventas-modal-lg" tabindex="-1" role="dialog" aria-labelledby="header-ventas-modal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="header-ventas-modal">Modificar venta</h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        <input id="idPedido" type="hidden">
      </div>
      <form id="form_bs-ventas-modal-lg" onsubmit="return false">
        <div class="modal-body">
          <input id="id_ventas" type="hidden">

          <div class="vm-seccion">Datos de la venta</div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="servicio">Servicio</label>
              <select id="servicio" class="form-control servicio" data-toggle="select2" onchange="cargar(this.value)">
                <option value="">Seleccioná un servicio</option>
                <optgroup label="Tarifas Sistema"></optgroup>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="ventas_fecha">Fecha</label>
              <input id="ventas_fecha" type="date" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label" for="ventas_codigo">Código</label>
              <input id="ventas_codigo" type="text" class="form-control" placeholder="0000000000">
            </div>
            <div class="col-md-5">
              <label class="form-label" for="ventas_titulo">Título</label>
              <input id="ventas_titulo" type="text" class="form-control" readonly>
            </div>
          </div>

          <div class="vm-seccion mt-4">Importe</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="ventas_cantidad">Cantidad</label>
              <input value="1" id="ventas_cantidad" type="number" min="0" step="any" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="ventas_precio">Precio unitario</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input id="ventas_precio" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="ventas_total">Total</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input id="ventas_total" type="text" class="form-control vm-total" readonly>
              </div>
              <small id="ventas_curva_aviso" class="form-text text-muted d-block"></small>
            </div>
            <div class="col-12">
              <label class="form-label" for="ventas_observaciones">Observaciones</label>
              <input id="ventas_observaciones" type="text" class="form-control" placeholder="Opcional">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" id="id_modificar">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
          <button id="modificarventas_ok" type="button" class="btn btn-success">Guardar cambios</button>
          <button id="agregarventas_ok" type="button" class="btn btn-success" style="display:none">Agregar venta</button>
        </div>
      </form>
    </div>
  </div>
</div>
