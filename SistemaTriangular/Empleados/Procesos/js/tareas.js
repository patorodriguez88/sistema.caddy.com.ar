// Rec. Humanos > Tareas (Empleados/Tareas.php). Datos: Procesos/php/tareas_api.php.
// Vistas: bandeja (tareas donde soy responsable), un proyecto, o eliminados (30 días, recuperables).

var TK = {
  yo: 0,
  usuarios: {},
  listaUsuarios: [],
  proyectos: [],
  vista: { tipo: "bandeja", id: 0 },
  completadas: 0,
  tareas: [],
  abierta: null, // tarea abierta en el panel (null = alta nueva)
  diasEliminados: 30,
};
var TK_API = "Procesos/php/tareas_api.php";
var TK_COLORES = ["#727cf5", "#0acf97", "#39afd1", "#ffbc00", "#fa5c7c", "#6c757d", "#e24f30", "#9b59b6"];

// ---------- helpers ----------
function tkEsc(v) {
  return String(v == null ? "" : v)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}
function tkApi(accion, datos) {
  return $.post(TK_API, $.extend({ accion: accion }, datos || {}), null, "json").then(function (r) {
    if (!r || r.success != 1) return $.Deferred().reject(r && r.error ? r.error : "Respuesta inesperada del servidor").promise();
    return r;
  }, function (xhr) {
    var r = xhr && xhr.responseJSON;
    return $.Deferred().reject(r && r.error ? r.error : "No se pudo conectar con el servidor").promise();
  });
}
function tkError(msg) {
  toast("error", "Tareas", msg);
}
function tkHoy() {
  var d = new Date();
  return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
}
function tkDias(fecha) {
  var a = new Date(tkHoy() + "T00:00:00"), b = new Date(fecha + "T00:00:00");
  return Math.round((b - a) / 86400000);
}
function tkFecha(f) {
  var m = String(f || "").match(/^(\d{4})-(\d{2})-(\d{2})/);
  return m ? m[3] + "/" + m[2] + "/" + m[1] : "";
}
function tkFechaHora(f) {
  var m = String(f || "").match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
  return m ? m[3] + "/" + m[2] + " " + m[4] + ":" + m[5] : tkFecha(f);
}
function tkUsuario(id) {
  return TK.usuarios[id] || { id: id, nombre: "Usuario #" + id, iniciales: "?", usuario: "" };
}
function tkAvatar(id) {
  var u = tkUsuario(id);
  var c = TK_COLORES[Math.abs(parseInt(id, 10) || 0) % TK_COLORES.length];
  return '<span class="tk-avatar" style="--tk-av:' + c + '" title="' + tkEsc(u.nombre) + '">' + tkEsc(u.iniciales) + "</span>";
}
function tkUserChip(id) {
  return '<span class="tk-user">' + tkAvatar(id) + "<span>" + tkEsc(tkUsuario(id).nombre) + (id == TK.yo ? " (vos)" : "") + "</span></span>";
}
function tkVence(t) {
  if (!t.vence) return '<span class="tk-vence"><small>Sin fecha</small></span>';
  var d = tkDias(t.vence), cls = "", rel;
  if (t.completada) rel = "";
  else if (d < 0) { cls = "atrasada"; rel = "Atrasada " + -d + (d === -1 ? " día" : " días"); }
  else if (d === 0) { cls = "hoy"; rel = "Vence hoy"; }
  else if (d === 1) rel = "Mañana";
  else rel = "En " + d + " días";
  return '<span class="tk-vence ' + cls + '">' + tkFecha(t.vence) + (rel ? "<small>" + rel + "</small>" : "") + "</span>";
}
// Texto con @Nombre.Apellido → mención resaltada (y saltos de línea). Mismo apodo que arma la API.
function tkTextoHtml(txt) {
  var porHandle = {};
  TK.listaUsuarios.forEach(function (u) { porHandle[String(u.handle).toLowerCase()] = u; });
  return tkEsc(txt).replace(/\n/g, "<br>").replace(/@([\p{L}\p{N}._-]+)/gu, function (m, h) {
    var limpio = h.replace(/\.+$/, ""), u = porHandle[limpio.toLowerCase()];
    return u ? '<span class="tk-mencion">@' + tkEsc(u.nombre) + "</span>" + h.slice(limpio.length) : m;
  });
}

// ---------- navegación ----------
function tkCargarInicio() {
  return tkApi("init").done(function (r) {
    TK.yo = r.yo;
    TK.listaUsuarios = r.usuarios;
    TK.usuarios = {};
    r.usuarios.forEach(function (u) { TK.usuarios[u.id] = u; });
    TK.proyectos = r.proyectos;
    TK.diasEliminados = r.diasEliminados;
    $("#tk_cnt_bandeja").text(r.contadores.bandeja || "");
    $("#tk_cnt_eliminados").text(r.contadores.eliminadas || "");
    $("#tk_proyectos").html(
      r.proyectos.map(function (p) {
        return '<a href="#" class="tk-nav-item" data-vista="proyecto" data-id="' + p.id + '">' +
          '<span class="tk-dot" style="--tk-c:' + tkEsc(p.color) + '"></span><span>' + tkEsc(p.nombre) + "</span>" +
          "<b>" + (p.pendientes || "") + "</b></a>";
      }).join("") || '<div class="tk-nav-item text-muted"><span>Sin proyectos</span></div>',
    );
    tkMarcarNav();
    $("#tk_f_responsable").html(
      r.usuarios.map(function (u) {
        return '<option value="' + u.id + '">' + tkEsc(u.nombre) + (u.id == TK.yo ? " (vos)" : "") + "</option>";
      }).join(""),
    );
    $("#tk_f_proyecto").html(
      r.proyectos.map(function (p) { return '<option value="' + p.id + '">' + tkEsc(p.nombre) + "</option>"; }).join(""),
    );
  }).fail(tkError);
}
function tkMarcarNav() {
  $(".tk-nav-item").removeClass("active");
  if (TK.vista.tipo === "proyecto") $('.tk-nav-item[data-vista="proyecto"][data-id="' + TK.vista.id + '"]').addClass("active");
  else $('.tk-nav-item[data-vista="' + TK.vista.tipo + '"]').addClass("active");
}
function tkProyecto(id) {
  return TK.proyectos.filter(function (p) { return p.id == id; })[0];
}
function tkIrA(tipo, id) {
  TK.vista = { tipo: tipo, id: id || 0 };
  TK.completadas = 0;
  $("#tk_filtro button").removeClass("active").filter('[data-completadas="0"]').addClass("active");
  $("#tk_buscar").val("");
  tkMarcarNav();
  var p = tkProyecto(id);
  if (tipo === "bandeja") {
    $("#tk_titulo").text("Bandeja de entrada");
    $("#tk_sub").text("Tus tareas, de la que vence primero a la última.");
  } else if (tipo === "proyecto") {
    $("#tk_titulo").html('<span class="tk-dot" style="--tk-c:' + tkEsc(p ? p.color : "") + ';width:14px;height:14px"></span>' + tkEsc(p ? p.nombre : "Proyecto"));
    $("#tk_sub").text("Todas las tareas del proyecto, de cualquier responsable.");
  } else {
    $("#tk_titulo").text("Eliminados");
    $("#tk_sub").text("Las tareas eliminadas quedan acá " + TK.diasEliminados + " días: abrilas o tocá Recuperar para volverlas a su proyecto.");
  }
  $("#tk_proy_menu").prop("hidden", tipo !== "proyecto");
  $("#tk_btn_nueva").prop("hidden", tipo === "eliminados");
  $("#tk_filtro").prop("hidden", tipo === "eliminados");
  tkCargarLista();
}
$(document).on("click", ".tk-nav-item[data-vista]", function (e) {
  e.preventDefault();
  tkIrA($(this).data("vista"), $(this).data("id"));
});
$(document).on("click", "#tk_filtro button", function () {
  $("#tk_filtro button").removeClass("active");
  $(this).addClass("active");
  TK.completadas = parseInt($(this).data("completadas"), 10) || 0;
  tkCargarLista();
});
$("#tk_buscar").on("input", function () {
  tkPintarLista();
});

// ---------- lista ----------
function tkCargarLista() {
  $("#tk_lista").html('<div class="tk-vacio"><i class="mdi mdi-dots-circle mdi-spin"></i>Cargando…</div>');
  return tkApi("listar", { vista: TK.vista.tipo, idProyecto: TK.vista.id, completadas: TK.completadas })
    .done(function (r) {
      TK.tareas = r.tareas;
      tkPintarLista();
    })
    .fail(function (e) {
      $("#tk_lista").html('<div class="tk-vacio text-danger">' + tkEsc(e) + "</div>");
    });
}
function tkPintarKpis() {
  var mostrar = TK.vista.tipo === "bandeja" && !TK.completadas;
  $("#tk_kpis").prop("hidden", !mostrar);
  if (!mostrar) return;
  var k = { atrasadas: 0, hoy: 0, semana: 0, nuevas: 0 };
  TK.tareas.forEach(function (t) {
    if (t.nueva) k.nuevas++;
    if (!t.vence) return;
    var d = tkDias(t.vence);
    if (d < 0) k.atrasadas++;
    else if (d === 0) k.hoy++;
    else if (d <= 7) k.semana++;
  });
  $("#tk_k_atrasadas").text(k.atrasadas);
  $("#tk_k_hoy").text(k.hoy);
  $("#tk_k_semana").text(k.semana);
  $("#tk_k_nuevas").text(k.nuevas);
}
function tkPintarLista() {
  tkPintarKpis();
  var q = $("#tk_buscar").val().trim().toLowerCase();
  var filas = TK.tareas.filter(function (t) {
    return !q || (t.titulo + " " + t.proyecto.nombre).toLowerCase().indexOf(q) >= 0;
  });
  var tipo = TK.vista.tipo;
  if (!filas.length) {
    var vacio = q
      ? ["mdi-magnify", "Sin resultados", "Ninguna tarea coincide con la búsqueda."]
      : tipo === "eliminados"
        ? ["mdi-delete-empty-outline", "No hay tareas eliminadas", "Lo que elimines va a quedar acá " + TK.diasEliminados + " días."]
        : TK.completadas
          ? ["mdi-check-all", "Todavía no hay tareas completadas", ""]
          : tipo === "bandeja"
            ? ["mdi-party-popper", "¡Estás al día!", "No tenés tareas pendientes."]
            : ["mdi-clipboard-text-outline", "El proyecto no tiene tareas pendientes", "Creá una con “Nueva tarea”."];
    $("#tk_lista").html('<div class="tk-vacio"><i class="mdi ' + vacio[0] + '"></i><b>' + vacio[1] + "</b>" + vacio[2] + "</div>");
    return;
  }
  var cab, fila;
  if (tipo === "eliminados") {
    cab = '<th>Tarea</th><th class="tk-col-proy">Proyecto</th><th class="tk-col-user">Eliminada por</th><th class="tk-col-vence">Eliminada</th><th class="tk-col-acc"></th>';
    fila = function (t) {
      return "<td><div class=\"tk-tarea-titulo\">" + tkEsc(t.titulo) + "</div></td>" +
        '<td class="tk-col-proy">' + tkChipProyecto(t) + "</td>" +
        '<td class="tk-col-user">' + (t.eliminadaPor ? tkUserChip(t.eliminadaPor) : "") + "</td>" +
        '<td class="tk-col-vence"><span class="tk-vence">' + tkFecha(t.eliminadaEl) + "<small>Quedan " + t.diasRestantes + " días</small></span></td>" +
        '<td class="tk-col-acc"><button type="button" class="btn btn-sm btn-light tk-recuperar" data-id="' + t.id + '"><i class="mdi mdi-backup-restore"></i> Recuperar</button></td>';
    };
  } else {
    var otraCol = tipo === "bandeja" ? ["Proyecto", "tk-col-proy"] : ["Responsable", "tk-col-user"];
    cab = '<th class="tk-col-check"></th><th>Tarea</th><th class="' + otraCol[1] + '">' + otraCol[0] + '</th>' +
      (tipo === "bandeja" ? '<th class="tk-col-user">Asignada por</th>' : "") + '<th class="tk-col-vence">Vence</th>';
    fila = function (t) {
      var sub = [];
      if (t.comentarios) sub.push('<span><i class="mdi mdi-comment-outline"></i> ' + t.comentarios + "</span>");
      return '<td class="tk-col-check"><button type="button" class="tk-check' + (t.completada ? " on" : "") + '" data-id="' + t.id + '" title="' +
        (t.completada ? "Marcar pendiente" : "Marcar completada") + '"><i class="mdi mdi-check"></i></button></td>' +
        '<td><div class="tk-tarea-titulo">' + (t.nueva ? '<span class="tk-new" title="Nueva o con cambios que no viste"></span>' : "") + tkEsc(t.titulo) + "</div>" +
        (sub.length ? '<div class="tk-tarea-sub">' + sub.join("") + "</div>" : "") + "</td>" +
        (tipo === "bandeja"
          ? '<td class="tk-col-proy">' + tkChipProyecto(t) + '</td><td class="tk-col-user">' + tkUserChip(t.creador) + "</td>"
          : '<td class="tk-col-user">' + tkUserChip(t.responsable) + "</td>") +
        '<td class="tk-col-vence">' + tkVence(t) + "</td>";
    };
  }
  $("#tk_lista").html(
    '<div class="table-responsive"><table class="tk-tabla"><thead><tr>' + cab + "</tr></thead><tbody>" +
      filas.map(function (t) {
        return '<tr data-id="' + t.id + '"' + (t.completada ? ' class="tk-done"' : "") + ">" + fila(t) + "</tr>";
      }).join("") + "</tbody></table></div>",
  );
}
function tkChipProyecto(t) {
  return '<span class="tk-chip-proy"><span class="tk-dot" style="--tk-c:' + tkEsc(t.proyecto.color) + '"></span><span>' + tkEsc(t.proyecto.nombre) + "</span></span>";
}
function tkRefrescar() {
  tkCargarInicio();
  tkCargarLista();
  if (typeof cargarNotificaciones === "function") cargarNotificaciones();
}
$(document).on("click", ".tk-tabla tbody tr", function () {
  tkAbrir($(this).data("id"));
});
$(document).on("click", ".tk-check", function (e) {
  e.stopPropagation();
  var $b = $(this), nueva = !$b.hasClass("on");
  $b.toggleClass("on", nueva);
  tkApi("completar", { id: $b.data("id"), completada: nueva ? 1 : 0 })
    .done(function () {
      toast("success", nueva ? "Tarea completada" : "Tarea pendiente otra vez", "");
      tkRefrescar();
    })
    .fail(function (e) { $b.toggleClass("on", !nueva); tkError(e); });
});
$(document).on("click", ".tk-recuperar", function (e) {
  e.stopPropagation();
  tkRecuperar($(this).data("id"));
});

// ---------- panel de detalle ----------
var tkPanel = null;
function tkPanelObj() {
  if (!tkPanel) tkPanel = new bootstrap.Offcanvas(document.getElementById("tk_panel"));
  return tkPanel;
}
function tkAutoAlto(el) {
  el.style.height = "auto";
  el.style.height = el.scrollHeight + "px";
}
$("#tk_f_titulo").on("input", function () { tkAutoAlto(this); }).on("keydown", function (e) {
  if (e.key === "Enter") e.preventDefault();
});
function tkUrlTarea(id) {
  var url = new URL(location.href);
  if (id) url.searchParams.set("tarea", id); else url.searchParams.delete("tarea");
  history.replaceState(null, "", url.toString());
}
function tkNueva() {
  TK.abierta = null;
  $("#tk_panel").removeClass("solo-lectura");
  $("#tk_eliminada").prop("hidden", true);
  $("#tk_completar, #tk_eliminar, #tk_copiar_link").prop("hidden", true);
  $("#tk_panel_titulo").text("Nueva tarea");
  $("#tk_f_titulo").val("");
  $("#tk_f_desc").val("");
  $("#tk_f_vence").val("");
  $("#tk_f_responsable").val(TK.yo);
  var proy = TK.vista.tipo === "proyecto" ? TK.vista.id : (TK.proyectos[0] && TK.proyectos[0].id);
  var general = TK.proyectos.filter(function (p) { return p.nombre === "General"; })[0];
  if (TK.vista.tipo !== "proyecto" && general) proy = general.id;
  $("#tk_f_proyecto").val(proy);
  $("#tk_meta").text("Si asignás la tarea a otra persona, le llega un mail.");
  $("#tk_coment_box, #tk_adj_box").prop("hidden", true);
  $("#tk_guardar").html('<i class="mdi mdi-plus me-1"></i>Crear tarea');
  tkPanelObj().show();
  setTimeout(function () { $("#tk_f_titulo").trigger("focus"); tkAutoAlto($("#tk_f_titulo")[0]); }, 250);
}
$("#tk_btn_nueva").on("click", tkNueva);

function tkAbrir(id) {
  return tkApi("tarea", { id: id }).done(function (r) {
    var t = r.tarea;
    TK.abierta = t;
    tkUrlTarea(t.id);
    $("#tk_panel").toggleClass("solo-lectura", t.eliminada);
    $("#tk_eliminada").prop("hidden", !t.eliminada);
    if (t.eliminada) {
      $("#tk_eliminada_info").text(
        "Por " + tkUsuario(t.eliminadaPor).nombre + " el " + tkFecha(t.eliminadaEl) + " · se puede recuperar " + t.diasRestantes + " días más",
      );
    }
    $("#tk_completar, #tk_eliminar").prop("hidden", t.eliminada);
    $("#tk_copiar_link").prop("hidden", false);
    $("#tk_completar").toggleClass("on", t.completada).find("span").text(t.completada ? "Completada" : "Marcar completada");
    $("#tk_panel_titulo").html('<span class="tk-chip-proy"><span class="tk-dot" style="--tk-c:' + tkEsc(t.proyecto.color) + '"></span><span>' + tkEsc(t.proyecto.nombre) + "</span></span>");
    $("#tk_f_titulo").val(t.titulo);
    $("#tk_f_desc").val(t.descripcion);
    $("#tk_f_vence").val(t.vence || "");
    $("#tk_f_responsable").val(t.responsable);
    if (!$("#tk_f_proyecto option[value='" + t.proyecto.id + "']").length) {
      $("#tk_f_proyecto").append('<option value="' + t.proyecto.id + '">' + tkEsc(t.proyecto.nombre) + "</option>");
    }
    $("#tk_f_proyecto").val(t.proyecto.id);
    $("#tk_meta").html(
      "Creada por " + tkEsc(tkUsuario(t.creador).nombre) + " el " + tkFecha(t.creada) +
        (t.completada && t.completadaPor ? " · completada por " + tkEsc(tkUsuario(t.completadaPor).nombre) + " el " + tkFecha(t.completadaEl) : ""),
    );
    $("#tk_guardar").html('<i class="mdi mdi-content-save-outline me-1"></i>Guardar cambios').prop("hidden", t.eliminada);
    $("#tk_coment_box").prop("hidden", false);
    $(".tk-coment-nuevo").prop("hidden", t.eliminada);
    tkPintarComentarios(r.comentarios);
    $("#tk_adj_box").prop("hidden", t.eliminada && !r.adjuntos.length);
    $("#tk_adj_zona").prop("hidden", t.eliminada);
    tkPintarAdjuntos(r.adjuntos);
    tkPanelObj().show();
    setTimeout(function () { tkAutoAlto($("#tk_f_titulo")[0]); }, 60);
    // Al abrirla deja de ser "nueva": se actualiza la marca en la lista y la campanita
    var enLista = TK.tareas.filter(function (x) { return x.id == t.id; })[0];
    if (enLista && enLista.nueva) { enLista.nueva = false; tkPintarLista(); }
    if (typeof cargarNotificaciones === "function") cargarNotificaciones();
  }).fail(tkError);
}
function tkPintarComentarios(lista) {
  $("#tk_comentarios").html(
    lista.length
      ? lista.map(function (c) {
          return '<div class="tk-coment">' + tkAvatar(c.usuario) + '<div class="tk-coment-body"><div class="tk-coment-head"><b>' +
            tkEsc(tkUsuario(c.usuario).nombre) + "</b><span>" + tkFechaHora(c.fecha) + '</span></div><div class="tk-coment-texto">' +
            tkTextoHtml(c.texto) + "</div></div></div>";
        }).join("")
      : '<div class="tk-meta mb-2">Todavía no hay comentarios.</div>',
  );
}
// ---------- archivos adjuntos ----------
function tkTamano(b) {
  return b >= 1048576 ? (b / 1048576).toFixed(1).replace(".", ",") + " MB" : Math.max(1, Math.round(b / 1024)) + " KB";
}
function tkIconoArchivo(nombre) {
  var ext = String(nombre).split(".").pop().toLowerCase();
  if (ext === "pdf") return ["mdi-file-pdf-box", "#e2445c"];
  if (["xls", "xlsx", "csv"].indexOf(ext) >= 0) return ["mdi-file-excel-box", "#10a464"];
  if (["doc", "docx"].indexOf(ext) >= 0) return ["mdi-file-word-box", "#2b6ade"];
  if (["ppt", "pptx"].indexOf(ext) >= 0) return ["mdi-file-powerpoint-box", "#e2702b"];
  if (["jpg", "jpeg", "png", "gif", "webp", "heic"].indexOf(ext) >= 0) return ["mdi-file-image", "#727cf5"];
  return ["mdi-file-document-outline", "#6c757d"];
}
function tkPintarAdjuntos(lista) {
  $("#tk_adj_cant").text(lista.length ? "(" + lista.length + ")" : "");
  var soloLectura = TK.abierta && TK.abierta.eliminada;
  $("#tk_adjuntos").html(
    lista.map(function (a) {
      var url = TK_API + "?accion=descargar&id=" + a.id;
      var ver = a.imagen || /pdf$/.test(a.mime);
      var ico = tkIconoArchivo(a.nombre);
      return '<a class="tk-adj" href="' + url + (ver ? "&ver=1" : "") + '"' + (ver ? ' target="_blank" rel="noopener"' : "") +
        ' title="' + tkEsc(a.nombre) + '">' +
        (a.imagen
          ? '<img class="tk-adj-thumb" src="' + url + '&ver=1" alt="" loading="lazy">'
          : '<span class="tk-adj-ico" style="--tk-c:' + ico[1] + '"><i class="mdi ' + ico[0] + '"></i></span>') +
        '<span class="tk-adj-info"><b>' + tkEsc(a.nombre) + "</b><small>" + tkTamano(a.tamano) + " · " +
        tkEsc(tkUsuario(a.usuario).nombre.split(" ")[0]) + " · " + tkFechaHora(a.fecha) + "</small></span>" +
        (soloLectura ? "" : '<button type="button" class="tk-adj-del" data-id="' + a.id + '" title="Quitar archivo"><i class="mdi mdi-close"></i></button>') +
        "</a>";
    }).join(""),
  );
}
// Fotos del celular (3-8 MB) → JPEG de 1920px como máximo al 82%: ~300-500 KB sin diferencia a la vista.
// Las capturas PNG chicas quedan como están (el JPEG les ensucia el texto). HEIC y GIF no se tocan.
var TK_FOTO_MAX_LADO = 1920, TK_FOTO_CALIDAD = 0.82;
function tkComprimir(file) {
  var esFoto = /^image\/(jpeg|png|webp)$/.test(file.type);
  if (!esFoto || file.size < 400 * 1024 || (file.type === "image/png" && file.size < 1.5 * 1024 * 1024)) {
    return Promise.resolve(file);
  }
  return new Promise(function (ok) {
    var img = new Image(), url = URL.createObjectURL(file);
    img.onload = function () {
      var k = Math.min(1, TK_FOTO_MAX_LADO / Math.max(img.width, img.height));
      var c = document.createElement("canvas");
      c.width = Math.round(img.width * k);
      c.height = Math.round(img.height * k);
      var ctx = c.getContext("2d");
      ctx.fillStyle = "#fff"; // fondo blanco para PNG con transparencia
      ctx.fillRect(0, 0, c.width, c.height);
      ctx.drawImage(img, 0, 0, c.width, c.height);
      URL.revokeObjectURL(url);
      c.toBlob(function (blob) {
        if (!blob || blob.size >= file.size) return ok(file);
        ok(new File([blob], (file.name || "foto").replace(/\.[^.]+$/, "") + ".jpg", { type: "image/jpeg" }));
      }, "image/jpeg", TK_FOTO_CALIDAD);
    };
    img.onerror = function () { URL.revokeObjectURL(url); ok(file); };
    img.src = url;
  });
}
function tkSubir(files) {
  if (!TK.abierta || TK.abierta.eliminada || !files || !files.length) return;
  var lista = Array.prototype.slice.call(files);
  $("#tk_adj_zona").addClass("subiendo");
  $("#tk_adj_texto").text("Preparando " + lista.length + (lista.length > 1 ? " archivos…" : " archivo…"));
  Promise.all(lista.map(tkComprimir)).then(tkSubirListos);
}
function tkSubirListos(files) {
  var fd = new FormData();
  fd.append("accion", "subir");
  fd.append("id", TK.abierta.id);
  Array.prototype.forEach.call(files, function (f, i) {
    // Las capturas pegadas llegan como "image.png": se les pone un nombre con fecha y hora.
    var nombre = f.name && f.name !== "image.png" ? f.name : "captura_" + new Date().toISOString().slice(0, 19).replace(/[-:T]/g, "") + (i ? "_" + i : "") + ".png";
    fd.append("archivos[]", f, nombre);
  });
  var $z = $("#tk_adj_zona").addClass("subiendo");
  var textoOriginal = TK_ADJ_TEXTO;
  $("#tk_adj_texto").text("Subiendo " + files.length + (files.length > 1 ? " archivos…" : " archivo…"));
  $.ajax({ url: TK_API, type: "POST", data: fd, processData: false, contentType: false, dataType: "json" })
    .done(function (r) {
      if (r.adjuntos) tkPintarAdjuntos(r.adjuntos);
      if (r.subidos && r.subidos.length) toast("success", "Archivo" + (r.subidos.length > 1 ? "s" : "") + " subido" + (r.subidos.length > 1 ? "s" : ""), r.subidos.join(", "));
      if (r.errores && r.errores.length) toast("error", "No se subieron algunos archivos", r.errores.join(" · "));
      tkCargarLista();
    })
    .fail(function (xhr) {
      tkError((xhr.responseJSON && xhr.responseJSON.error) || "No se pudo subir: puede que el archivo sea muy grande.");
    })
    .always(function () {
      $z.removeClass("subiendo");
      $("#tk_adj_texto").html(textoOriginal);
      $("#tk_adj_input").val("");
    });
}
var TK_ADJ_TEXTO = $("#tk_adj_texto").html();
$("#tk_adj_input").on("change", function () { tkSubir(this.files); });
$("#tk_adj_zona")
  .on("dragover dragenter", function (e) { e.preventDefault(); $(this).addClass("drag"); })
  .on("dragleave dragend drop", function () { $(this).removeClass("drag"); })
  .on("drop", function (e) {
    e.preventDefault();
    tkSubir(e.originalEvent.dataTransfer.files);
  });
// Pegar una captura (Cmd+V / Ctrl+V) con el panel abierto, fuera de los campos de texto
document.addEventListener("paste", function (e) {
  if (!TK.abierta || !$("#tk_panel").hasClass("show")) return;
  var files = e.clipboardData && e.clipboardData.files;
  if (!files || !files.length) return;
  e.preventDefault();
  tkSubir(files);
});
$(document).on("click", ".tk-adj-del", function (e) {
  e.preventDefault();
  e.stopPropagation();
  var id = $(this).data("id");
  Swal.fire({ title: "¿Quitar el archivo?", icon: "warning", showCancelButton: true, confirmButtonText: "Sí, quitar",
              cancelButtonText: "Cancelar", confirmButtonColor: "#fa5c7c" }).then(function (res) {
    if (!res.isConfirmed) return;
    tkApi("eliminar_adjunto", { id: id }).done(function (r) { tkPintarAdjuntos(r.adjuntos); }).fail(tkError);
  });
});

document.getElementById("tk_panel").addEventListener("hidden.bs.offcanvas", function () {
  tkUrlTarea(null);
  $("#tk_mention").prop("hidden", true);
});

$("#tk_guardar").on("click", function () {
  var $b = $(this).prop("disabled", true);
  var esNueva = !TK.abierta;
  tkApi("guardar_tarea", {
    id: TK.abierta ? TK.abierta.id : 0,
    titulo: $("#tk_f_titulo").val(),
    descripcion: $("#tk_f_desc").val(),
    idProyecto: $("#tk_f_proyecto").val(),
    idResponsable: $("#tk_f_responsable").val(),
    vence: $("#tk_f_vence").val(),
  })
    .done(function (r) {
      var avisados = (r.avisados || []).map(function (id) { return tkUsuario(id).nombre; });
      toast("success", esNueva ? "Tarea creada" : "Cambios guardados", avisados.length ? "Avisamos por mail a " + avisados.join(", ") + "." : "");
      tkRefrescar();
      tkAbrir(r.id);
    })
    .fail(tkError)
    .always(function () { $b.prop("disabled", false); });
});
$("#tk_completar").on("click", function () {
  if (!TK.abierta) return;
  var nueva = !TK.abierta.completada;
  tkApi("completar", { id: TK.abierta.id, completada: nueva ? 1 : 0 })
    .done(function () {
      toast("success", nueva ? "Tarea completada" : "Tarea pendiente otra vez", "");
      tkRefrescar();
      tkAbrir(TK.abierta.id);
    })
    .fail(tkError);
});
$("#tk_eliminar").on("click", function () {
  if (!TK.abierta) return;
  var t = TK.abierta;
  Swal.fire({
    title: "¿Eliminar la tarea?",
    html: "<b>" + tkEsc(t.titulo) + "</b><br>Va a quedar en <b>Eliminados</b> " + TK.diasEliminados + " días y la podés recuperar.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#fa5c7c",
  }).then(function (res) {
    if (!res.isConfirmed) return;
    tkApi("eliminar", { id: t.id })
      .done(function () {
        tkPanelObj().hide();
        toast("success", "Tarea eliminada", "La podés recuperar desde Eliminados durante " + TK.diasEliminados + " días.");
        tkRefrescar();
      })
      .fail(tkError);
  });
});
function tkRecuperar(id) {
  tkApi("recuperar", { id: id })
    .done(function () {
      toast("success", "Tarea recuperada", "Volvió a su proyecto.");
      tkRefrescar();
      if (TK.abierta && TK.abierta.id == id) tkAbrir(id);
    })
    .fail(tkError);
}
$("#tk_recuperar").on("click", function () {
  if (TK.abierta) tkRecuperar(TK.abierta.id);
});
$("#tk_copiar_link").on("click", function () {
  if (!TK.abierta) return;
  var url = location.origin + location.pathname + "?tarea=" + TK.abierta.id;
  if (navigator.clipboard) navigator.clipboard.writeText(url);
  toast("success", "Link copiado", url);
});
function tkComentar() {
  var txt = $("#tk_f_coment").val().trim();
  if (!txt || !TK.abierta) return;
  var $b = $("#tk_comentar").prop("disabled", true);
  tkApi("comentar", { id: TK.abierta.id, texto: txt })
    .done(function (r) {
      $("#tk_f_coment").val("");
      var avisados = (r.avisados || []).map(function (id) { return tkUsuario(id).nombre; });
      if (avisados.length) toast("success", "Comentario enviado", "Avisamos por mail a " + avisados.join(", ") + ".");
      tkAbrir(TK.abierta.id);
      tkCargarLista();
    })
    .fail(tkError)
    .always(function () { $b.prop("disabled", false); });
}
$("#tk_comentar").on("click", tkComentar);
$("#tk_f_coment").on("keydown", function (e) {
  if (e.key === "Enter" && (e.ctrlKey || e.metaKey) && $("#tk_mention").prop("hidden")) {
    e.preventDefault();
    tkComentar();
  }
});

// ---------- @menciones ----------
var tkMen = { $campo: null, inicio: 0, opciones: [], activa: 0 };
function tkMenCerrar() {
  $("#tk_mention").prop("hidden", true);
  tkMen.$campo = null;
}
$(document).on("input click", ".tk-mencionable", function () {
  var el = this, pos = el.selectionStart, antes = el.value.slice(0, pos);
  var m = antes.match(/(^|\s)@([A-Za-zÀ-ÿ0-9._-]*)$/);
  if (!m) return tkMenCerrar();
  var q = m[2].toLowerCase();
  tkMen.opciones = TK.listaUsuarios.filter(function (u) {
    return !q || u.nombre.toLowerCase().indexOf(q) >= 0 || String(u.handle).toLowerCase().indexOf(q) >= 0;
  }).slice(0, 8);
  if (!tkMen.opciones.length) return tkMenCerrar();
  tkMen.$campo = $(el);
  tkMen.inicio = pos - m[2].length - 1;
  tkMen.activa = 0;
  var r = el.getBoundingClientRect();
  $("#tk_mention").css({ left: r.left + 8, top: Math.min(r.bottom + 4, window.innerHeight - 250) }).prop("hidden", false);
  tkMenPintar();
});
function tkMenPintar() {
  $("#tk_mention").html(
    tkMen.opciones.map(function (u, i) {
      return '<div class="tk-mention-item' + (i === tkMen.activa ? " active" : "") + '" data-i="' + i + '">' + tkAvatar(u.id) +
        "<div>" + tkEsc(u.nombre) + "<br><small>@" + tkEsc(u.handle) + "</small></div></div>";
    }).join(""),
  );
}
function tkMenElegir(i) {
  var u = tkMen.opciones[i];
  if (!u || !tkMen.$campo) return;
  var el = tkMen.$campo[0], fin = el.selectionStart;
  var texto = "@" + u.handle + " ";
  el.value = el.value.slice(0, tkMen.inicio) + texto + el.value.slice(fin);
  var p = tkMen.inicio + texto.length;
  el.setSelectionRange(p, p);
  el.focus();
  tkMenCerrar();
}
$(document).on("keydown", ".tk-mencionable", function (e) {
  if ($("#tk_mention").prop("hidden")) return;
  if (e.key === "ArrowDown" || e.key === "ArrowUp") {
    e.preventDefault();
    var n = tkMen.opciones.length;
    tkMen.activa = (tkMen.activa + (e.key === "ArrowDown" ? 1 : n - 1)) % n;
    tkMenPintar();
  } else if (e.key === "Enter" || e.key === "Tab") {
    e.preventDefault();
    tkMenElegir(tkMen.activa);
  } else if (e.key === "Escape") {
    tkMenCerrar();
  }
});
$(document).on("mousedown", ".tk-mention-item", function (e) {
  e.preventDefault();
  tkMenElegir(parseInt($(this).data("i"), 10));
});
$(document).on("blur", ".tk-mencionable", function () {
  setTimeout(tkMenCerrar, 150);
});

// ---------- proyectos ----------
var tkProyEditando = 0;
function tkModalProyecto(p) {
  tkProyEditando = p ? p.id : 0;
  $("#tk_p_titulo").text(p ? "Editar proyecto" : "Nuevo proyecto");
  $("#tk_p_nombre").val(p ? p.nombre : "");
  var color = p ? p.color : TK_COLORES[TK.proyectos.length % TK_COLORES.length];
  $("#tk_p_colores").html(
    TK_COLORES.map(function (c) {
      return '<button type="button" class="tk-color' + (c === color ? " active" : "") + '" data-color="' + c + '" style="--tk-c:' + c + '"></button>';
    }).join(""),
  );
  bootstrap.Modal.getOrCreateInstance(document.getElementById("tk_modal_proy")).show();
  setTimeout(function () { $("#tk_p_nombre").trigger("focus"); }, 300);
}
$(document).on("click", ".tk-color", function () {
  $(".tk-color").removeClass("active");
  $(this).addClass("active");
});
$("#tk_btn_proyecto").on("click", function () { tkModalProyecto(null); });
$("#tk_proy_editar").on("click", function (e) {
  e.preventDefault();
  tkModalProyecto(tkProyecto(TK.vista.id));
});
$("#tk_p_nombre").on("keydown", function (e) {
  if (e.key === "Enter") $("#tk_p_guardar").click();
});
$("#tk_p_guardar").on("click", function () {
  tkApi("guardar_proyecto", { id: tkProyEditando, nombre: $("#tk_p_nombre").val(), color: $(".tk-color.active").data("color") })
    .done(function (r) {
      bootstrap.Modal.getInstance(document.getElementById("tk_modal_proy")).hide();
      toast("success", tkProyEditando ? "Proyecto actualizado" : "Proyecto creado", "");
      tkCargarInicio().done(function () { tkIrA("proyecto", r.id); });
    })
    .fail(tkError);
});
$("#tk_proy_eliminar").on("click", function (e) {
  e.preventDefault();
  var p = tkProyecto(TK.vista.id);
  if (!p) return;
  Swal.fire({
    title: "¿Eliminar el proyecto?",
    html: "<b>" + tkEsc(p.nombre) + "</b><br>Solo se puede si no tiene tareas.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#fa5c7c",
  }).then(function (res) {
    if (!res.isConfirmed) return;
    tkApi("eliminar_proyecto", { id: p.id })
      .done(function () {
        toast("success", "Proyecto eliminado", "");
        tkCargarInicio().done(function () { tkIrA("bandeja"); });
      })
      .fail(tkError);
  });
});

// ---------- arranque ----------
$(function () {
  tkCargarInicio().done(function () {
    tkIrA("bandeja");
    var abrir = new URL(location.href).searchParams.get("tarea");
    if (abrir) tkAbrir(abrir);
  });
});
