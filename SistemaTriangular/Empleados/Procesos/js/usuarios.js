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
  post("listar_usuarios").then((r) => {
    const usuarios = r.data || [];
    const tabla = $("#tablaUsuarios tbody");
    tabla.empty();
    usuarios.forEach((user) => {
      const rolHtml = user.rol
        ? `<span class="caddy-rol-badge">${user.rol}</span>`
        : '<span class="caddy-rol-badge sin-rol">Sin rol</span>';
      tabla.append(
        `<tr>
          <td>${user.Usuario}</td>
          <td>${user.nombre} ${user.apellido}</td>
          <td>${user.nivel_nombre}</td>
          <td>${rolHtml}</td>
        </tr>`
      );
    });
  });
}

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
