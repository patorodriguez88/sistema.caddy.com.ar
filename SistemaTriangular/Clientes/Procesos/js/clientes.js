function cargarRubrosEnSelect(selectId) {
  $.ajax({
    url: "Procesos/php/clientes.php", // ajustá ruta según tu estructura
    data: { cargar_rubros: 1 },
    type: "POST",
    dataType: "json",
    success: function (rubros) {
      const select = document.getElementById(selectId);
      if (!select) return;

      // Limpiar opciones anteriores
      select.innerHTML = "";

      // Crear el optgroup
      const optgroup = document.createElement("optgroup");
      optgroup.label = "Rubro";

      rubros.forEach(function (rubro) {
        const option = document.createElement("option");
        option.value = rubro.id;
        option.textContent = rubro.Rubro;
        optgroup.appendChild(option);
      });

      select.appendChild(optgroup);
    },
    error: function (err) {
      console.error("Error al cargar rubros:", err);
    },
  });
}

// Uso: cuando esté listo el DOM o al cargar un modal
cargarRubrosEnSelect("rubro");

function cargarRelacionEnSelect(selectId) {
  $.ajax({
    url: "Procesos/php/clientes.php", // ajustá ruta según tu estructura
    data: { cargar_relacion: 1 },
    type: "POST",
    dataType: "json",
    success: function (relaciones) {
      const select = document.getElementById(selectId);
      if (!select) return;

      // Limpiar opciones anteriores
      select.innerHTML = "";

      // Crear el optgroup
      const optgroup = document.createElement("optgroup");
      optgroup.label = "Relacion";

      relaciones.forEach(function (relacion) {
        const option = document.createElement("option");
        option.value = relacion.id;
        option.textContent = relacion.Relacion;
        optgroup.appendChild(option);
      });

      select.appendChild(optgroup);
    },
    error: function (err) {
      console.error("Error al cargar rubros:", err);
    },
  });
}
cargarRelacionEnSelect("");
