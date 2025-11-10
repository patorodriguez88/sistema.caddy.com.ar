document.addEventListener("DOMContentLoaded", function () {
  let currentMonth = new Date().getMonth() + 1;
  let currentYear = new Date().getFullYear();

  function renderCalendar(month, year) {
    fetch(`Proceso/php/calendario.php?m=${month}&y=${year}`)
      .then((res) => res.json())
      .then((json) => {
        if (!json.success) return;
        const data = json.days;
        const today = new Date().toISOString().split("T")[0];
        const firstDay = new Date(year, month - 1, 1);
        const startDow = firstDay.getDay() === 0 ? 7 : firstDay.getDay();
        const daysInMonth = new Date(year, month, 0).getDate();
        const dias = ["Lun", "Mar", "Mie", "Jue", "Vie", "Sab", "Dom"];

        let html = `
          <table class="table table-bordered align-middle text-center mb-0 calendar-table">
            <thead class="table-light"><tr>${dias
              .map((d) => `<th>${d}</th>`)
              .join("")}</tr></thead><tbody>
        `;

        let cell = 0,
          day = 1;
        for (let row = 0; row < 6; row++) {
          html += "<tr>";
          for (let col = 0; col < 7; col++) {
            cell++;
            if (cell < startDow || day > daysInMonth) {
              html += `<td class="bg-light"></td>`;
            } else {
              const dateStr = `${year}-${String(month).padStart(
                2,
                "0"
              )}-${String(day).padStart(2, "0")}`;
              const items = data[dateStr] || [];
              const isToday = dateStr === today;

              html += `<td class="text-start ${
                isToday ? "table-primary" : ""
              }" style="vertical-align:top;">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="badge rounded-pill ${
                      isToday ? "bg-primary" : "bg-secondary"
                    }">${day}</span>
                    ${
                      items.length
                        ? `<span class="badge bg-success">${items.length}</span>`
                        : ""
                    }
                  </div>`;

              if (items.length) {
                html += `<ul class="mt-2 mb-0 ps-3 small">`;
                items.forEach(
                  (r) =>
                    (html += `<li><b>R:</b> ${r.Recorrido} | ${r.Nombre}</li>`)
                );
                html += `</ul>`;
              }

              html += `</td>`;
              day++;
            }
          }
          html += "</tr>";
        }
        html += "</tbody></table>";
        document.getElementById("calendarContainer").innerHTML = html;
      })
      .catch((err) => console.error("Error al cargar calendario:", err));
  }

  document.getElementById("btnPrev").addEventListener("click", () => {
    currentMonth--;
    if (currentMonth < 1) {
      currentMonth = 12;
      currentYear--;
    }
    renderCalendar(currentMonth, currentYear);
  });

  document.getElementById("btnNext").addEventListener("click", () => {
    currentMonth++;
    if (currentMonth > 12) {
      currentMonth = 1;
      currentYear++;
    }
    renderCalendar(currentMonth, currentYear);
  });

  // Inicial
  renderCalendar(currentMonth, currentYear);
});
