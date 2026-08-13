// Archivo: usuarios.js
const USUARIOS_ENDPOINT = "Procesos/php/usuarios.php";

function post(accion, datos = {}) {
  return $.post(USUARIOS_ENDPOINT, { accion, ...datos }).then((res) =>
    typeof res === "string" ? JSON.parse(res) : res
  );
}

$(document).ready(function () {
  cargarRoles();
  cargarPermisos();
  listarUsuarios();
  cargarUsuariosParaAsignar();
  cargarRolesParaAsignar();
  cargarSelectsAsignacion();
  inicializarTablaRolesPermisos();

  $("#formAsignar").on("submit", function (e) {
    e.preventDefault();
    asignarRolAUsuario();
  });

  $("#formRol").on("submit", function (e) {
    e.preventDefault();
    guardarRol();
  });

  $("#formPermiso").on("submit", function (e) {
    e.preventDefault();
    guardarPermiso();
  });

  $("#formAsignarPermisos").on("submit", function (e) {
    e.preventDefault();
    asignarPermisosARol();
  });

  $("#btnCancelarEdicionRol").on("click", function () {
    resetFormRol();
  });

  $("#usuario_select").on("change", function () {
    mostrarRolActualUsuario($(this).val());
  });

  $("#selectRoles").on("change", function () {
    cargarPermisos_checkboxes();
  });

  // DataTables calcula mal el ancho de columnas si se inicializa con la pestaña oculta;
  // se recalcula cada vez que se muestra la pestaña "Ver Permisos Asignados".
  $('button[data-bs-target="#v-pills-permisos-asignados"]').on("shown.bs.tab", function () {
    const tabla = $("#tablaRolesPermisos").DataTable();
    tabla.columns.adjust();
  });
});

function cargarUsuariosParaAsignar() {
  if (!$("#usuario_select").length) return;
  post("listar_usuarios").then((r) => {
    const usuarios = r.data || [];
    const select = $("#usuario_select");
    select.empty().append('<option value="">Seleccione un usuario</option>');
    usuarios.forEach((u) => {
      select.append(
        `<option value="${u.id}">${u.Usuario} - ${u.nombre} ${u.apellido} (${u.nivel_nombre})</option>`
      );
    });
  });
}

function cargarRolesParaAsignar() {
  if (!$("#rol_select").length) return;
  post("listar_roles").then((roles) => {
    const select = $("#rol_select");
    select.empty().append('<option value="">Seleccione un rol</option>');
    roles.forEach((rol) => {
      select.append(`<option value="${rol.id}">${rol.nombre}</option>`);
    });
  });
}

function cargarRoles() {
  if (!$("#tablaRoles").length) return;
  post("listar_roles").then((roles) => {
    const tabla = $("#tablaRoles tbody");
    tabla.empty();
    if (!roles.length) {
      tabla.append('<tr><td colspan="2" class="text-muted">Sin roles creados todavía.</td></tr>');
      return;
    }
    roles.forEach((rol) => {
      tabla.append(`
        <tr>
          <td>${rol.nombre}</td>
          <td class="text-end">
            <i class="mdi mdi-pencil text-muted mdi-18px editar-rol me-2" style="cursor:pointer;" data-id="${rol.id}" data-nombre="${rol.nombre}"></i>
            <i class="mdi mdi-delete text-danger mdi-18px eliminar-rol" style="cursor:pointer;" data-id="${rol.id}"></i>
          </td>
        </tr>`);
    });
  });
}

function verPermisos() {
  if (!$("#tablaPermisos").length) return;
  post("listar_permisos").then((permisos) => {
    const tabla = $("#tablaPermisos tbody");
    tabla.empty();
    permisos.forEach((permiso) => {
      const accion =
        Number(permiso.es_sistema) === 1
          ? '<span class="badge bg-soft-secondary text-secondary">Sistema</span>'
          : `<i class="mdi mdi-delete text-danger mdi-18px eliminar-permiso" data-id="${permiso.id}" style="cursor:pointer;"></i>`;
      tabla.append(`
        <tr>
          <td>${permiso.nombre}</td>
          <td class="text-muted">${permiso.seccion || "-"}</td>
          <td class="text-end">${accion}</td>
        </tr>
      `);
    });
  });
}

function guardarRol() {
  const nombre = $("#rol_nombre").val().trim();
  const id = $("#rol_id").val();

  if (!nombre) {
    Swal.fire("Campo vacío", "Ingresá un nombre para el rol", "warning");
    return;
  }

  post("crear_rol", { nombre, id }).then((r) => {
    if (r.success) {
      Swal.fire(id ? "Rol actualizado" : "Rol creado", "", "success");
      resetFormRol();
      cargarRoles();
      cargarRolesParaAsignar();
      cargarSelectsAsignacion();
    } else {
      Swal.fire("Error", r.error || "No se pudo guardar el rol", "error");
    }
  });
}

function resetFormRol() {
  $("#rol_id").val("");
  $("#rol_nombre").val("");
  $("#btnCancelarEdicionRol").addClass("d-none");
}

function guardarPermiso() {
  const nombre = $("#permiso_nombre").val().trim();
  if (!nombre) return;

  post("crear_permiso", { nombre }).then((r) => {
    if (r.success) {
      Swal.fire("Permiso creado", "", "success");
      $("#formPermiso")[0].reset();
      verPermisos();
      cargarPermisos_checkboxes();
    } else {
      Swal.fire("Error", r.error || "No se pudo crear el permiso", "error");
    }
  });
}

function asignarPermisosARol() {
  const rol_id = $("#selectRoles").val();
  if (!rol_id) {
    Swal.fire("Elegí un rol", "", "warning");
    return;
  }

  const permisos = [];
  $("#checkboxPermisos input:checked").each(function () {
    permisos.push($(this).val());
  });

  post("asignar_permiso_rol", { rol_id, permisos: JSON.stringify(permisos) }).then((r) => {
    if (r.success) {
      Swal.fire("Permisos guardados", "", "success");
      $("#tablaRolesPermisos").DataTable().ajax.reload();
    } else {
      Swal.fire("Error", r.error || "No se pudieron guardar los permisos", "error");
    }
  });
}

function asignarRolAUsuario() {
  const usuario_id = $("#usuario_select").val();
  const rol_id = $("#rol_select").val();

  if (!usuario_id || !rol_id) {
    Swal.fire("Faltan datos", "Seleccioná un usuario y un rol", "warning");
    return;
  }

  post("asignar_rol_usuario", { usuario_id, rol_id }).then((r) => {
    if (r.success) {
      Swal.fire("Rol asignado", "", "success");
      mostrarRolActualUsuario(usuario_id);
      listarUsuarios();
    } else {
      Swal.fire("Error", r.error || "No se pudo asignar el rol", "error");
    }
  });
}

function mostrarRolActualUsuario(usuario_id) {
  const cont = $("#rolActualUsuario");
  if (!usuario_id) {
    cont.empty();
    return;
  }
  post("obtener_rol_usuario", { usuario_id }).then((r) => {
    if (r.rol_id) {
      cont.html(`
        <span class="caddy-rol-badge">
          <i class="mdi mdi-shield-check"></i> Rol actual: ${r.rol}
          <i class="mdi mdi-close-circle ms-1 quitar-rol" style="cursor:pointer;" data-id="${usuario_id}" title="Quitar rol"></i>
        </span>
      `);
    } else {
      cont.html('<span class="caddy-rol-badge sin-rol"><i class="mdi mdi-shield-off-outline"></i> Sin rol asignado — ve el menú completo</span>');
    }
  });
}

$(document).on("click", ".quitar-rol", function () {
  const usuario_id = $(this).data("id");
  Swal.fire({
    title: "¿Quitar el rol asignado?",
    text: "El usuario volverá a ver el menú completo hasta que se le asigne otro rol.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, quitar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      post("quitar_rol_usuario", { usuario_id }).then((r) => {
        if (r.success) {
          mostrarRolActualUsuario(usuario_id);
          listarUsuarios();
        }
      });
    }
  });
});

function listarUsuarios() {
  if (!$("#tablaUsuarios").length) return;
  const tabla = $("#tablaUsuarios tbody");

  post("listar_usuarios")
    .then((r) => {
      const usuarios = r.data || [];
      tabla.empty();

      if (usuarios.length === 0) {
        tabla.append(
          `<tr><td colspan="7" class="caddy-tabla-vacia">
            <i class="uil-users-alt"></i>
            No hay usuarios de sistema para mostrar.
          </td></tr>`
        );
        return;
      }

      usuarios.forEach((user) => {
        const rolHtml = user.rol
          ? `<span class="caddy-rol-badge">${user.rol}</span>`
          : '<span class="caddy-rol-badge sin-rol">Sin rol</span>';

        const notifHtml =
          Number(user.NotificacionAccesoEnviada) === 1
            ? `<span class="badge bg-success">Enviada</span>`
            : `<span class="badge bg-warning text-dark">Pendiente</span>`;

        // Se confirma que la persona entró de verdad (no solo que el mail se mandó)
        // cuando UltimoAcceso queda seteado en el primer login exitoso.
        const pendiente = !user.UltimoAcceso;
        const accesoHtml = pendiente
          ? `<span class="badge bg-warning text-dark">Pendiente</span>`
          : `<span class="badge bg-success" title="${user.UltimoAcceso}">Confirmado</span><br><small class="text-muted">${user.UltimoAcceso.substring(0, 10).split("-").reverse().join("/")}</small>`;

        const btnClass = pendiente
          ? "caddy-btn-reenviar is-pendiente btn-reenviar-acceso"
          : "caddy-btn-reenviar btn-reenviar-acceso";

        tabla.append(
          `<tr>
            <td>${user.Usuario}</td>
            <td>${user.nombre} ${user.apellido}</td>
            <td>${user.nivel_nombre}</td>
            <td>${rolHtml}</td>
            <td>${notifHtml}</td>
            <td>${accesoHtml}</td>
            <td>
              <button type="button" class="${btnClass}" data-id="${user.id}" data-mail="${user.Usuario}" title="Genera una contraseña temporal nueva y la manda por mail">
                <i class="uil-repeat"></i> Reenviar acceso
              </button>
            </td>
          </tr>`
        );
      });
    })
    .catch(() => {
      tabla.empty();
      tabla.append(
        `<tr><td colspan="7" class="caddy-tabla-vacia">
          <i class="uil-exclamation-triangle"></i>
          No se pudo cargar la lista de usuarios. Puede faltar una migración de base de datos — avisá a sistemas.
        </td></tr>`
      );
    });
}

// 📧 Reenviar mail de acceso (genera contraseña temporal nueva)
$(document).on("click", ".btn-reenviar-acceso", function () {
  const $btn = $(this);
  const id = $btn.data("id");
  const mail = $btn.data("mail");

  Swal.fire({
    title: "¿Reenviar acceso?",
    html: `Se va a generar una <b>contraseña temporal nueva</b> y se le va a mandar un mail a <b>${mail}</b>.`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, reenviar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#E24F30",
  }).then((result) => {
    if (!result.isConfirmed) return;

    const htmlOriginal = $btn.html();
    $btn
      .prop("disabled", true)
      .html('<i class="uil-sync caddy-spin"></i> Enviando...');

    post("reenviar_acceso", { usuario_id: id })
      .then((r) => {
        if (r.success) {
          Swal.fire("Listo", "Se envió el mail con la contraseña temporal nueva.", "success");
        } else {
          Swal.fire("No se pudo enviar", r.error || "Revisá la configuración de mail.", "error");
          $btn.prop("disabled", false).html(htmlOriginal);
        }
        listarUsuarios();
      })
      .catch(() => {
        Swal.fire("Error", "No se pudo conectar con el servidor.", "error");
        $btn.prop("disabled", false).html(htmlOriginal);
      });
  });
});

// 🗑️ Eliminar rol
$(document).on("click", ".eliminar-rol", function () {
  const id = $(this).data("id");

  Swal.fire({
    title: "¿Eliminar rol?",
    text: "Los usuarios que lo tengan asignado quedarán sin rol (ven el menú completo).",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      post("eliminar_rol", { id }).then((r) => {
        if (r.success) {
          Swal.fire("Eliminado", "El rol fue eliminado.", "success");
          cargarRoles();
          cargarRolesParaAsignar();
          cargarSelectsAsignacion();
          listarUsuarios();
        } else {
          Swal.fire("Error", r.error || "No se pudo eliminar el rol.", "error");
        }
      });
    }
  });
});

// ✏️ Editar rol
$(document).on("click", ".editar-rol", function () {
  $("#rol_id").val($(this).data("id"));
  $("#rol_nombre").val($(this).data("nombre"));
  $("#btnCancelarEdicionRol").removeClass("d-none");
  $("#rol_nombre").focus();
});

// 🗑️ Eliminar permiso
$(document).on("click", ".eliminar-permiso", function () {
  const id = $(this).data("id");

  Swal.fire({
    title: "¿Eliminar permiso?",
    text: "Se va a quitar de todos los roles que lo tengan asignado.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      post("eliminar_permiso", { id }).then((r) => {
        if (r.success) {
          Swal.fire("Eliminado", "El permiso fue eliminado.", "success");
          verPermisos();
          cargarPermisos_checkboxes();
        } else {
          Swal.fire("Error", r.error || "No se pudo eliminar el permiso.", "error");
        }
      });
    }
  });
});

function cargarSelectsAsignacion() {
  if (!$("#selectRoles").length) return;
  post("listar_roles").then((roles) => {
    const selectRol = $("#selectRoles");
    selectRol.empty().append(`<option value="">Seleccione un rol</option>`);
    roles.forEach((rol) => {
      selectRol.append(`<option value="${rol.id}">${rol.nombre}</option>`);
    });
  });
}

// Alias: cargarPermisos() refresca la tabla de la pestaña "Permisos"; el checklist de
// la pestaña "Asignación" se arma aparte porque depende del rol seleccionado.
function cargarPermisos() {
  verPermisos();
}

function cargarPermisos_checkboxes() {
  const rol_id = $("#selectRoles").val();
  const contenedor = $("#checkboxPermisos");
  if (!contenedor.length) return;

  if (!rol_id) {
    contenedor.html('<p class="text-muted">Elegí un rol para ver/editar sus permisos.</p>');
    return;
  }

  post("listar_permisos_rol", { rol_id }).then((res) => {
    const asignados = (res.asignados || []).map(Number);
    const todos = res.todos || [];

    contenedor.empty();
    let seccionActual = null;

    todos.forEach((permiso) => {
      const seccion = permiso.seccion || "Otros";
      if (seccion !== seccionActual) {
        contenedor.append(`<div class="caddy-permiso-seccion">${seccion}</div>`);
        seccionActual = seccion;
      }
      const checked = asignados.includes(Number(permiso.id)) ? "checked" : "";
      const esSistema = Number(permiso.es_sistema) === 1;
      contenedor.append(`
        <div class="form-check caddy-permiso-check mb-1 ${esSistema ? "border-warning" : ""}">
          <input class="form-check-input" type="checkbox" value="${permiso.id}" id="permiso_${permiso.id}" ${checked}>
          <label class="form-check-label" for="permiso_${permiso.id}">
            ${permiso.nombre} ${esSistema ? '<i class="mdi mdi-alert text-warning" title="Otorga acceso para gestionar roles y permisos"></i>' : ""}
          </label>
        </div>
      `);
    });
  });
}

function inicializarTablaRolesPermisos() {
  if (!$("#tablaRolesPermisos").length) return;
  $("#tablaRolesPermisos").DataTable({
    ajax: {
      url: USUARIOS_ENDPOINT,
      type: "POST",
      data: { accion: "listar_roles_permisos" },
      dataSrc: "data",
    },
    columns: [
      { data: "rol" },
      {
        data: "permisos",
        render: function (data) {
          return data.length > 0 ? data.join("<br>") : "<em>Sin permisos</em>";
        },
      },
    ],
  });
}
