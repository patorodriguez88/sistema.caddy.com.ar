// Archivo: usuarios.js

$(document).ready(function () {
  cargarRoles();
  cargarPermisos();
  listarUsuarios();
  cargarUsuariosParaAsignar();
  cargarRolesParaAsignar();
  cargarSelectsAsignacion();
  verPermisos();

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
});

// Escuchar cambio en el select de roles
$("#selectRoles").on("change", function () {
  cargarPermisos(); // esto ahora va a traer también los asignados
});

function cargarUsuariosParaAsignar() {
  $.post(
    "Procesos/php/usuarios.php",
    { accion: "listar_usuarios" },
    function (res) {
      const r = typeof res === "string" ? JSON.parse(res) : res;
      const usuarios = r.data || [];
      const select = $("#usuario_select");
      select.empty().append('<option value="">Seleccione un usuario</option>');
      usuarios.forEach((u) => {
        select.append(
          `<option value="${u.id}">${u.Usuario} - ${u.nombre} ${u.apellido}</option>`
        );
      });
    }
  );
}

function cargarRolesParaAsignar() {
  $.post(
    "Procesos/php/usuarios.php",
    { accion: "listar_roles" },
    function (res) {
      const r = typeof res === "string" ? JSON.parse(res) : res;
      const select = $("#rol_select");
      select.empty().append('<option value="">Seleccione un rol</option>');
      r.forEach((rol) => {
        select.append(`<option value="${rol.id}">${rol.nombre}</option>`);
      });
    }
  );
}
function cargarRoles() {
  $.post(
    "Procesos/php/usuarios.php",
    { accion: "listar_roles" },
    function (res) {
      let r = typeof res === "string" ? JSON.parse(res) : res;
      const tabla = $("#tablaRoles tbody");
      tabla.empty();
      r.forEach((rol) => {
        tabla.append(`
            <tr>
              <td>${rol.nombre}</td>
              <td>
                <i class="mdi mdi-delete text-danger mdi-18px eliminar-rol" style="cursor:pointer;" data-id="${rol.id}"></i>
              </td>
            </tr>`);
      });
    }
  );
}

function verPermisos() {
  $.post(
    "Procesos/php/usuarios.php",
    { accion: "listar_permisos" },
    function (res) {
      console.log("Respuesta cruda permisos:", res); // <-- AGREGÁ ESTO

      let r = typeof res === "string" ? JSON.parse(res) : res;

      const tabla = $("#tablaPermisos tbody");
      tabla.empty();
      r.forEach((permiso) => {
        tabla.append(`
          <tr>
            <td>${permiso.nombre}</td>
            <td><i class="mdi mdi-delete text-danger mdi-18px eliminar-permiso ml-2" data-id="${permiso.id}" style="cursor:pointer;"></i></td>
          </tr>
        `);
      });
    }
  );
}

function guardarRol() {
  const nombre = $("#rol_nombre").val().trim(); // ✔ ID correcto

  if (!nombre) {
    Swal.fire("Campo vacío", "Ingresá un nombre para el rol", "warning");
    return;
  }

  $.post(
    "Procesos/php/usuarios.php",
    { accion: "crear_rol", nombre },
    function (res) {
      try {
        const r = typeof res === "string" ? JSON.parse(res) : res;
        if (r.success) {
          Swal.fire("Rol creado", "", "success");
          $("#rol_nombre").val(""); // limpiar input
          cargarRoles(); // actualizar tabla
        } else {
          Swal.fire("Error", r.error || "No se pudo crear el rol", "error");
        }
      } catch (e) {
        console.error("Error al parsear respuesta:", res);
        Swal.fire("Error", "Respuesta inválida del servidor", "error");
      }
    }
  );
}

function guardarPermiso() {
  const nombre = $("#permiso_nombre").val();
  if (!nombre) return;

  $.post(
    "Procesos/php/usuarios.php",
    { accion: "crear_permiso", nombre },
    function (res) {
      Swal.fire("Permiso creado", "", "success");
      $("#formPermiso")[0].reset(); // limpia el form
      cargarPermisos(); // actualiza checkboxes
      verPermisos(); // actualiza la tabla de la pestaña Permisos 👈
    }
  );
}

function asignarPermisosARol() {
  const rol_id = $("#selectRoles").val();
  const permisos = [];
  $("#checkboxPermisos input:checked").each(function () {
    permisos.push($(this).val());
  });

  $.post(
    "Procesos/php/usuarios.php",
    {
      accion: "asignar_permiso_rol", // este debe coincidir
      rol_id,
      permisos: JSON.stringify(permisos), // 👈 mandamos el array bien
    },
    function (res) {
      Swal.fire("Permisos asignados", "", "success");
    }
  );
}

$("#formAsignarPermisos").on("submit", function (e) {
  e.preventDefault();
  const rol_id = $("#selectRoles").val();
  const permisos = [];

  $("#checkboxPermisos input:checked").each(function () {
    permisos.push($(this).val());
  });

  $.post(
    "Procesos/php/usuarios.php",
    { accion: "asignar_permisos", rol_id, permisos },
    function (res) {
      Swal.fire("Permisos asignados", "", "success");
    }
  );
});

function asignarRolAUsuario() {
  const user_id = $("#selectUsuario").val();
  const rol_id = $("#selectRoles").val();

  $.post(
    "Procesos/php/usuarios.php",
    { accion: "asignar_rol_usuario", usuario_id: user_id, rol_id },
    function (res) {
      Swal.fire("Rol asignado", "", "success");
    }
  );
}

function listarUsuarios() {
  $.post(
    "Procesos/php/usuarios.php",
    { accion: "listar_usuarios" },
    function (res) {
      //   const r = JSON.parse(res);
      const r = typeof res === "string" ? JSON.parse(res) : res;
      const usuarios = r.data || [];
      const tabla = $("#tablaUsuarios tbody");
      tabla.empty();
      usuarios.forEach((user) => {
        tabla.append(
          `<tr>
            <td>${user.Usuario}</td>
            <td>${user.Nombre} ${user.Apellido}</td>
            <td>${user.Rol || "Sin rol"}</td>
          </tr>`
        );
      });
    }
  );
}
// 🗑️ Eliminar rol
$(document).on("click", ".eliminar-rol", function () {
  const id = $(this).data("id");

  Swal.fire({
    title: "¿Eliminar rol?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.post(
        "Procesos/php/usuarios.php",
        { accion: "eliminar_rol", id },
        function (res) {
          if (res.success) {
            Swal.fire("Eliminado", "El rol fue eliminado.", "success");
            cargarRoles(); // 🔁 recarga tabla
          } else {
            Swal.fire("Error", "No se pudo eliminar el rol.", "error");
          }
        }
      );
    }
  });
});
// function cargarSelectsAsignacion() {
//   // Usuarios
//   $.post(
//     "Procesos/php/usuarios.php",
//     { accion: "listar_usuarios" },
//     function (res) {
//       const r = typeof res === "string" ? JSON.parse(res) : res;
//       const selectUsuario = $("#usuario_select");
//       selectUsuario
//         .empty()
//         .append(`<option value="">Seleccione un usuario</option>`);
//       (r.data || []).forEach((user) => {
//         selectUsuario.append(
//           `<option value="${user.id}">${user.Usuario} - ${user.nombre} ${user.apellido}</option>`
//         );
//       });
//     }
//   );

//   // Roles
//   $.post(
//     "Procesos/php/usuarios.php",
//     { accion: "listar_roles" },
//     function (res) {
//       const r = typeof res === "string" ? JSON.parse(res) : res;
//       const selectRol = $("#rol_select");
//       selectRol.empty().append(`<option value="">Seleccione un rol</option>`);
//       r.forEach((rol) => {
//         selectRol.append(`<option value="${rol.id}">${rol.nombre}</option>`);
//       });
//     }
//   );
// }
function cargarSelectsAsignacion() {
  $.post(
    "Procesos/php/usuarios.php",
    { accion: "listar_roles" },
    function (res) {
      const r = typeof res === "string" ? JSON.parse(res) : res;
      const selectRol = $("#selectRoles");
      selectRol.empty().append(`<option value="">Seleccione un rol</option>`);
      r.forEach((rol) => {
        selectRol.append(`<option value="${rol.id}">${rol.nombre}</option>`);
      });
    }
  );
}
function cargarPermisos() {
  const rol_id = $("#selectRoles").val(); // Rol seleccionado

  if (!rol_id) return;

  $.post(
    "Procesos/php/usuarios.php",
    { accion: "listar_permisos_rol", rol_id },
    function (res) {
      const asignados = res.asignados || []; // IDs de permisos ya asignados
      const todos = res.todos || []; // Todos los permisos disponibles

      const contenedor = $("#checkboxPermisos");
      contenedor.empty();

      todos.forEach((permiso) => {
        // const checked = asignados.includes(permiso.id) ? "checked" : "";
        const checked = asignados.includes(Number(permiso.id)) ? "checked" : "";
        contenedor.append(`
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="${permiso.id}" id="permiso_${permiso.id}" ${checked}>
            <label class="form-check-label" for="permiso_${permiso.id}">
              ${permiso.nombre}
            </label>
          </div>
        `);
      });
    },
    "json"
  );
}
$("#tablaRolesPermisos").DataTable({
  ajax: {
    url: "Procesos/php/usuarios.php",
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
