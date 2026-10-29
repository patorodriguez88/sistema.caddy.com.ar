// Componentes estilo Asana para Tareas y el panel rápido de Nueva tarea.
// - FechaAsana: selector de "Fecha de entrega" con la repetición adentro.
// - SelectAsana: botón con avatar/color que abre una lista con buscador sobre un <select> (oculto);
//   cada <option> puede traer data-ini (iniciales) o data-color. Responsable y Proyecto.
//
// FechaAsana (Tareas y panel rápido de Nueva tarea). Un botón muestra
// la fecha ("Sin fecha de entrega", "Hoy", "30 oct"...) y abre un calendario flotante con mes, semana
// de lunes a domingo, "Borrar" y el ícono de repetir, que despliega la periodicidad. Guarda los
// valores en inputs ocultos: fecha (YYYY-MM-DD), tipo (dia|habil|semana|mes|anio), cada y dias
// ("1,3,5", 1=lunes). Si se cambian esos inputs desde afuera hay que llamar a refrescar().
// JS sin jQuery; el CSS se agrega solo la primera vez.
(function () {
  "use strict";

  const MESES = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
  const DIAS = ["L", "M", "M", "J", "V", "S", "D"];
  const DIAS_LARGO = ["", "lun", "mar", "mié", "jue", "vie", "sáb", "dom"];
  const UNIDAD = { dia: ["día", "días"], semana: ["semana", "semanas"], mes: ["mes", "meses"], anio: ["año", "años"] };

  const CSS = `
  .fa-trigger { display: inline-flex; align-items: center; gap: .55rem; background: none; border: 0; padding: 2px 4px; border-radius: 8px; color: var(--bs-body-color, #212529); font-size: .9rem; cursor: pointer; max-width: 100%; }
  .fa-trigger:hover { background: var(--bs-tertiary-bg, #f3f3f5); }
  .fa-trigger .fa-ico { width: 28px; height: 28px; border-radius: 50%; border: 1.5px dashed var(--bs-secondary-color, #9aa0a6); display: inline-flex; align-items: center; justify-content: center; color: var(--bs-secondary-color, #6c757d); flex: none; }
  .fa-trigger.fa-con .fa-ico { border-style: solid; }
  .fa-trigger .fa-txt.vacio { color: var(--bs-secondary-color, #6c757d); }
  .fa-trigger .fa-txt.vencida { color: #e5484d; }
  .fa-trigger .fa-rep { color: var(--bs-secondary-color, #6c757d); font-size: 1rem; }
  .fa-pop { position: fixed; z-index: 2000; width: 300px; background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #212529); border: 1px solid var(--bs-border-color, #e3e3e8); border-radius: 10px; box-shadow: 0 12px 32px -8px rgba(0,0,0,.25); font-size: .88rem; }
  .fa-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 14px 6px; }
  .fa-head b { font-weight: 600; text-transform: capitalize; }
  .fa-nav { background: none; border: 0; width: 28px; height: 28px; border-radius: 6px; font-size: 1.1rem; color: inherit; cursor: pointer; }
  .fa-nav:hover { background: var(--bs-tertiary-bg, #f3f3f5); }
  .fa-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; padding: 4px 12px 10px; text-align: center; }
  .fa-grid .fa-dn { font-size: .75rem; font-weight: 600; color: var(--bs-secondary-color, #6c757d); padding: 4px 0; }
  .fa-d { width: 34px; height: 34px; margin: 0 auto; border-radius: 50%; border: 1.5px solid transparent; background: none; color: inherit; font-size: .85rem; cursor: pointer; }
  .fa-d:hover { background: var(--bs-tertiary-bg, #f0f0f3); }
  .fa-d.fuera, .fa-d.finde { color: var(--bs-secondary-color, #9aa0a6); }
  .fa-d.hoy { border-color: #4573d2; font-weight: 700; }
  .fa-d.sel { background: #4573d2; border-color: #4573d2; color: #fff; font-weight: 700; }
  .fa-pie { display: flex; align-items: center; gap: .3rem; border-top: 1px solid var(--bs-border-color, #e3e3e8); padding: 8px 10px; }
  .fa-pie .fa-btn-ico { background: none; border: 0; width: 34px; height: 34px; border-radius: 6px; font-size: 1.2rem; color: var(--bs-secondary-color, #6c757d); cursor: pointer; }
  .fa-pie .fa-btn-ico:hover, .fa-pie .fa-btn-ico.on { background: var(--bs-tertiary-bg, #f0f0f3); color: #4573d2; }
  .fa-pie .fa-borrar { margin-left: auto; background: none; border: 0; font-weight: 600; color: var(--bs-body-color, #212529); padding: 6px 10px; border-radius: 6px; cursor: pointer; }
  .fa-pie .fa-borrar:hover { background: var(--bs-tertiary-bg, #f0f0f3); }
  .fa-rep-box { border-top: 1px solid var(--bs-border-color, #e3e3e8); padding: 10px 14px 12px; display: flex; flex-direction: column; gap: .5rem; }
  .fa-rep-box select, .fa-rep-box input { border: 1px solid var(--bs-border-color, #dcdce2); border-radius: 6px; padding: 4px 6px; background: var(--bs-body-bg, #fff); color: inherit; font-size: .85rem; }
  .fa-rep-fila { display: flex; align-items: center; gap: .4rem; color: var(--bs-secondary-color, #6c757d); }
  .fa-rep-fila input { width: 54px; }
  .fa-rep-dias { display: flex; gap: .25rem; }
  .fa-rep-dias button { width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--bs-border-color, #dcdce2); background: none; font-size: .75rem; font-weight: 700; color: var(--bs-secondary-color, #6c757d); cursor: pointer; padding: 0; }
  .fa-rep-dias button.on { background: #4573d2; border-color: #4573d2; color: #fff; }
  .fa-rep-res { font-size: .78rem; color: var(--bs-secondary-color, #6c757d); }
  .sa-av { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .7rem; font-weight: 700; color: #fff; background: #727cf5; flex: none; }
  .sa-av.vacio { background: none; border: 1.5px dashed var(--bs-secondary-color, #9aa0a6); color: var(--bs-secondary-color, #6c757d); font-size: 1rem; }
  .sa-cuad { width: 14px; height: 14px; border-radius: 4px; background: var(--sa-c, #9aa5f8); flex: none; }
  .sa-pop { position: fixed; z-index: 2000; width: 280px; max-height: 320px; display: flex; flex-direction: column; background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #212529); border: 1px solid var(--bs-border-color, #e3e3e8); border-radius: 10px; box-shadow: 0 12px 32px -8px rgba(0,0,0,.25); font-size: .88rem; }
  .sa-pop input { margin: 8px; border: 1px solid var(--bs-border-color, #dcdce2); border-radius: 6px; padding: 6px 8px; background: var(--bs-body-bg, #fff); color: inherit; }
  .sa-lista { overflow: auto; padding: 0 6px 6px; }
  .sa-op { display: flex; align-items: center; gap: .55rem; width: 100%; background: none; border: 0; border-radius: 6px; padding: 6px 8px; text-align: left; color: inherit; cursor: pointer; }
  .sa-op:hover, .sa-op.sel { background: var(--bs-tertiary-bg, #f0f0f3); }
  .sa-op .sa-av { width: 24px; height: 24px; font-size: .62rem; }`;

  function css() {
    if (document.getElementById("fa-css")) return;
    const s = document.createElement("style");
    s.id = "fa-css";
    s.textContent = CSS;
    document.head.appendChild(s);
  }

  const iso = (d) => d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
  const deIso = (s) => (s && /^\d{4}-\d{2}-\d{2}$/.test(s) ? new Date(s + "T12:00:00") : null);
  const hoyIso = () => iso(new Date());

  function textoFecha(s) {
    const d = deIso(s);
    if (!d) return "Sin fecha de entrega";
    const h = new Date(hoyIso() + "T12:00:00");
    const dif = Math.round((d - h) / 86400000);
    if (dif === 0) return "Hoy";
    if (dif === 1) return "Mañana";
    if (dif === -1) return "Ayer";
    const corto = d.getDate() + " " + MESES[d.getMonth()].slice(0, 3);
    return d.getFullYear() === h.getFullYear() ? corto : corto + " " + d.getFullYear();
  }

  function textoRepetir(tipo, cada, dias) {
    if (!tipo) return "";
    if (tipo === "habil") return "Días hábiles (lun a vie)";
    const u = UNIDAD[tipo];
    let t = cada > 1 ? "Cada " + cada + " " + u[1] : { dia: "Todos los días", semana: "Cada semana", mes: "Cada mes", anio: "Cada año" }[tipo];
    if (tipo === "semana" && dias) t += " los " + dias.split(",").map((d) => DIAS_LARGO[d]).join(", ");
    return t;
  }

  function montar(op) {
    css();
    const { boton, fecha, tipo, cada, dias } = op;
    let pop = null;
    let mes = null; // primer día del mes que se muestra
    let verRep = false;

    function refrescar() {
      const f = fecha.value;
      const vencida = f && f < hoyIso();
      const rep = tipo && tipo.value;
      boton.classList.toggle("fa-con", !!f);
      boton.innerHTML =
        '<span class="fa-ico"><i class="mdi mdi-calendar-blank-outline"></i></span>' +
        '<span class="fa-txt' + (f ? (vencida ? " vencida" : "") : " vacio") + '">' + textoFecha(f) + "</span>" +
        (rep ? '<i class="mdi mdi-repeat fa-rep" title="' + textoRepetir(tipo.value, +cada.value || 1, dias.value) + '"></i>' : "");
      if (pop) pintar();
    }

    function cerrar() {
      if (!pop) return;
      pop.remove();
      pop = null;
      document.removeEventListener("mousedown", afuera, true);
      window.removeEventListener("resize", ubicar);
    }
    function afuera(e) {
      if (pop && !pop.contains(e.target) && !boton.contains(e.target)) cerrar();
    }
    function ubicar() {
      if (!pop) return;
      const r = boton.getBoundingClientRect();
      const alto = pop.offsetHeight;
      let top = r.bottom + 6;
      if (top + alto > window.innerHeight - 8) top = Math.max(8, r.top - alto - 6);
      pop.style.top = top + "px";
      pop.style.left = Math.max(8, Math.min(r.left, window.innerWidth - 308)) + "px";
    }

    function pintar() {
      const sel = fecha.value;
      const hoy = hoyIso();
      const ini = new Date(mes);
      ini.setDate(1 - ((ini.getDay() + 6) % 7)); // lunes de la primera semana
      let celdas = DIAS.map((d) => '<div class="fa-dn">' + d + "</div>").join("");
      for (let i = 0; i < 42; i++) {
        const d = new Date(ini);
        d.setDate(ini.getDate() + i);
        const s = iso(d);
        const cls = ["fa-d"];
        if (d.getMonth() !== mes.getMonth()) cls.push("fuera");
        if (d.getDay() === 0 || d.getDay() === 6) cls.push("finde");
        if (s === hoy) cls.push("hoy");
        if (s === sel) cls.push("sel");
        celdas += '<button type="button" class="' + cls.join(" ") + '" data-f="' + s + '">' + d.getDate() + "</button>";
      }
      const t = tipo ? tipo.value : "";
      const n = cada ? +cada.value || 1 : 1;
      const ds = dias && dias.value ? dias.value.split(",") : [];
      const repHtml = !tipo || !verRep ? "" :
        '<div class="fa-rep-box">' +
          '<select class="fa-rep-tipo">' +
            [["", "No se repite"], ["dia", "Diariamente"], ["habil", "Días hábiles (lun a vie)"], ["semana", "Semanalmente"], ["mes", "Mensualmente"], ["anio", "Anualmente"]]
              .map(([v, l]) => '<option value="' + v + '"' + (v === t ? " selected" : "") + ">" + l + "</option>").join("") +
          "</select>" +
          (UNIDAD[t] ? '<div class="fa-rep-fila">Cada <input type="number" min="1" max="99" class="fa-rep-cada" value="' + n + '"> ' + UNIDAD[t][n > 1 ? 1 : 0] + (t === "semana" ? " los" : "") + "</div>" : "") +
          (t === "semana" ? '<div class="fa-rep-dias">' + DIAS.map((d, i) => '<button type="button" data-d="' + (i + 1) + '" class="' + (ds.includes(String(i + 1)) ? "on" : "") + '">' + d + "</button>").join("") + "</div>" : "") +
          (t ? '<div class="fa-rep-res">' + textoRepetir(t, n, dias.value) + ". Al completarla se crea la siguiente.</div>" : "") +
        "</div>";
      pop.innerHTML =
        '<div class="fa-head"><button type="button" class="fa-nav" data-m="-1"><i class="mdi mdi-chevron-left"></i></button>' +
          "<b>" + MESES[mes.getMonth()] + " " + mes.getFullYear() + "</b>" +
          '<button type="button" class="fa-nav" data-m="1"><i class="mdi mdi-chevron-right"></i></button></div>' +
        '<div class="fa-grid">' + celdas + "</div>" +
        '<div class="fa-pie">' +
          (tipo ? '<button type="button" class="fa-btn-ico fa-btn-rep' + (verRep || t ? " on" : "") + '" title="Repetir"><i class="mdi mdi-repeat"></i></button>' : "") +
          '<button type="button" class="fa-borrar">Borrar</button></div>' +
        repHtml;
      ubicar();
    }

    function cambio() {
      fecha.dispatchEvent(new Event("change", { bubbles: true }));
      refrescar();
    }

    function abrir() {
      if (pop) return cerrar();
      const d = deIso(fecha.value) || new Date();
      mes = new Date(d.getFullYear(), d.getMonth(), 1, 12);
      verRep = !!(tipo && tipo.value);
      pop = document.createElement("div");
      pop.className = "fa-pop";
      document.body.appendChild(pop);
      pop.addEventListener("click", clic);
      pop.addEventListener("change", cambioRep);
      pop.addEventListener("input", cambioRep);
      pintar();
      document.addEventListener("mousedown", afuera, true);
      window.addEventListener("resize", ubicar);
    }

    function clic(e) {
      const b = e.target.closest("button");
      if (!b) return;
      if (b.dataset.m) {
        mes.setMonth(mes.getMonth() + +b.dataset.m);
        return pintar();
      }
      if (b.dataset.f) {
        fecha.value = b.dataset.f;
        cambio();
        if (!verRep) cerrar();
        return;
      }
      if (b.classList.contains("fa-borrar")) {
        fecha.value = "";
        if (tipo) { tipo.value = ""; dias.value = ""; cada.value = 1; }
        cambio();
        return cerrar();
      }
      if (b.classList.contains("fa-btn-rep")) {
        verRep = !verRep;
        return pintar();
      }
      if (b.dataset.d) {
        const set = new Set(dias.value ? dias.value.split(",") : []);
        set.has(b.dataset.d) ? set.delete(b.dataset.d) : set.add(b.dataset.d);
        dias.value = [...set].sort().join(",");
        return refrescar();
      }
    }

    function cambioRep(e) {
      if (e.target.classList.contains("fa-rep-tipo")) {
        tipo.value = e.target.value;
        cada.value = 1;
        dias.value = "";
        if (tipo.value === "semana") {
          // Por defecto, el día de la semana de la fecha de entrega (o de hoy)
          const d = deIso(fecha.value) || new Date();
          dias.value = String(d.getDay() || 7);
        }
        if (tipo.value && !fecha.value) fecha.value = hoyIso(); // una tarea que se repite necesita fecha
        cambio();
      } else if (e.target.classList.contains("fa-rep-cada") && e.type === "change") {
        cada.value = Math.max(1, Math.min(99, +e.target.value || 1));
        refrescar();
      }
    }

    boton.type = "button";
    boton.classList.add("fa-trigger");
    boton.addEventListener("click", abrir);
    refrescar();
    const api = { refrescar, cerrar };
    boton._fa = api;
    return api;
  }

  // ---------- SelectAsana ----------
  const COLORES_AV = ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00", "#39afd1", "#6b5eae", "#ff679b", "#02a8b5"];
  const escH = (t) => String(t == null ? "" : t).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  function iconoOpcion(op, tipo) {
    if (tipo === "proyecto") return '<span class="sa-cuad" style="--sa-c:' + escH(op.dataset.color || "#9aa5f8") + '"></span>';
    const ini = op.dataset.ini || op.textContent.trim().slice(0, 2).toUpperCase();
    return '<span class="sa-av" style="background:' + COLORES_AV[(+op.value || 0) % COLORES_AV.length] + '">' + escH(ini) + "</span>";
  }

  function montarSelect(op) {
    css();
    const { boton, select, vacio, tipo } = op;
    let pop = null;

    function refrescar() {
      const o = select.options[select.selectedIndex];
      boton.innerHTML = o && select.value
        ? iconoOpcion(o, tipo) + '<span class="fa-txt">' + escH(o.textContent) + "</span>"
        : (tipo === "proyecto" ? '<span class="sa-cuad" style="--sa-c:#d8d8de"></span>' : '<span class="sa-av vacio"><i class="mdi mdi-account-outline"></i></span>') +
          '<span class="fa-txt vacio">' + escH(vacio) + "</span>";
    }
    function cerrar() {
      if (!pop) return;
      pop.remove();
      pop = null;
      document.removeEventListener("mousedown", afuera, true);
    }
    function afuera(e) {
      if (pop && !pop.contains(e.target) && !boton.contains(e.target)) cerrar();
    }
    function lista(q) {
      q = (q || "").toLowerCase();
      pop.querySelector(".sa-lista").innerHTML = [...select.options]
        .filter((o) => o.value && o.textContent.toLowerCase().includes(q))
        .map((o) => '<button type="button" class="sa-op' + (o.value === select.value ? " sel" : "") + '" data-v="' + escH(o.value) + '">' + iconoOpcion(o, tipo) + "<span>" + escH(o.textContent) + "</span></button>")
        .join("") || '<div class="fa-rep-res" style="padding:6px 8px">Sin resultados</div>';
    }
    function abrir() {
      if (pop) return cerrar();
      if (boton.disabled) return;
      pop = document.createElement("div");
      pop.className = "sa-pop";
      pop.innerHTML = '<input type="text" placeholder="Buscar…"><div class="sa-lista"></div>';
      document.body.appendChild(pop);
      lista("");
      const r = boton.getBoundingClientRect();
      let top = r.bottom + 6;
      if (top + pop.offsetHeight > window.innerHeight - 8) top = Math.max(8, r.top - pop.offsetHeight - 6);
      pop.style.top = top + "px";
      pop.style.left = Math.max(8, Math.min(r.left, window.innerWidth - 288)) + "px";
      const input = pop.querySelector("input");
      input.addEventListener("input", () => lista(input.value));
      input.addEventListener("keydown", (e) => {
        if (e.key === "Escape") cerrar();
        if (e.key === "Enter") { e.preventDefault(); const b = pop.querySelector(".sa-op"); if (b) b.click(); }
      });
      pop.addEventListener("click", (e) => {
        const b = e.target.closest(".sa-op");
        if (!b) return;
        select.value = b.dataset.v;
        select.dispatchEvent(new Event("change", { bubbles: true }));
        refrescar();
        cerrar();
      });
      input.focus();
      document.addEventListener("mousedown", afuera, true);
    }

    select.style.display = "none";
    boton.type = "button";
    boton.classList.add("fa-trigger");
    boton.addEventListener("click", abrir);
    refrescar();
    const api = { refrescar, cerrar };
    boton._sa = api;
    return api;
  }

  window.FechaAsana = { montar, textoRepetir };
  window.SelectAsana = { montar: montarSelect };
})();
