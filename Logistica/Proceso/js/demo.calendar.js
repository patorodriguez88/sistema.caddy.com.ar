! function(l) {
  "use strict";
  function e() {
    this.$body = l("body"), 
      this.$modal = l("#event-modal"), 
      this.$calendar = l("#calendar"), 
      this.$formEvent = l("#form-event"), 
      this.$btnNewEvent = l("#btn-new-event"), 
      this.$btnDeleteEvent = l("#btn-delete-event"), 
      this.$btnSaveEvent = l("#btn-save-event"), 
      this.$modalTitle = l("#modal-title"), 
      this.$calendarObj = null, 
      this.$selectedEvent = null, 
      this.$newEventData = null
  }
  e.prototype.onEventClick = function(e) {
    console.log('obs',e);
      $("#select").hide(),
      $("#event-title").show(),  
      this.$btnDeleteEvent.show(), 
      this.$formEvent[0].reset(), 
      this.$formEvent.removeClass("was-validated"), 
      this.$newEventData = null, 
      this.$btnDeleteEvent.show(), 
      this.$modalTitle.text("Editar Evento"), 
      $("#idbd").val(e.event._def.publicId),
      this.$modal.modal({
      backdrop: "static"
    }), 
      this.$selectedEvent = e.event,
      l("#event-title").attr('readonly', true), 
      l("#event-title").val(this.$selectedEvent.title),  
//       l("#event-title").val($('#observaciones').val()),    
//       l("#event-title").val(e.event._def.extendedProps.Observaciones),    
      l("#event-category").val(this.$selectedEvent.classNames[0])
  }, e.prototype.onSelect = function(e) {
     console.log('veoveo',e);
      $("#event-title").hide(),
      $("#select").show(),  
      this.$formEvent[0].reset(), 
      this.$formEvent.removeClass("was-validated"), 
      this.$selectedEvent = null, 
      this.$newEventData = e, 
      this.$btnDeleteEvent.hide(), 
      this.$modalTitle.text("Agregar Recorrido al "+e.dateStr), 
//       $("#startTime").val(e.date);
//       $("#endtTime").val(e.date);
      this.$modal.modal({
      backdrop: "static"
    }), 
      this.$calendarObj.unselect()
  }, e.prototype.init = function() {
    var e = new Date(l.now());
    
    new FullCalendar.Draggable(document.getElementById("external-events"), {
      itemSelector: ".external-event",
      eventData: function(e) {
        return {
          title: e.innerText,
          className: l(e).data("class"),
        }
      },
      
    });
      var t="Proceso/php/demo-calendar.php?view=1",
      a = this;
      a.$calendarObj = new FullCalendar.Calendar(a.$calendar[0], {
      slotDuration: "00:15:00",
      slotMinTime: "00:00:00",
      slotMaxTime: "24:00:00",
      themeSystem: "bootstrap",
      bootstrapFontAwesome: !1,
      buttonText: {
        today: "Today",
        month: "Mes",
        week: "Semana",
        day: "Dia",
        list: "Lista",
        prev: "Prev",
        next: "Next"
      },
      initialView: "dayGridMonth",
      handleWindowResize: !0,
      height: l(window).height() - 200,
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth"
      },
      initialEvents: "Proceso/php/demo-calendar.php?view=1",
      editable: !0,
      droppable: !0,
      selectable: !0,
      dateClick: function(e) {
        a.onSelect(e)
      },
      eventClick: function(e) {
        a.onEventClick(e)
      },
      drop: function(e) {
        var dropTitulo='Recorrido';
            $.ajax({
           url: 'Proceso/php/demo-calendar.php',
           data: 'action=add&title='+e.draggedEl.innerText+'&start='+moment(e.date).format('dddd, MMMM Do YYYY, h:mm')+'&end='+moment(e.date).format('h:mm')+'&category='+e.draggedEl.dataset.class+"&id="+e.draggedEl.dataset.id,
           type: "POST",
           success: function(json) {
           $('#observaciones').val(json.Obs);
             
           }
       }); 
      },
      eventDrop: function(info) {
      $.ajax({
           url: 'Proceso/php/demo-calendar.php',
           data: 'action=update&title='+info.event.title+'&start='+moment(info.event.start).format()+'&id='+info.event.id,
           type: "POST",
           success: function(json) {
           $.NotificationApp.send("Listo!","Movimos el registro correctamente ","bottom-right","#FFFFFF","info");     
           }
         });
      }
      }), a.$calendarObj.render(), 
      a.$btnNewEvent.on("click", function(e) {
      a.onSelect({
        date: new Date,
        allDay: !0
      })
    }), 
      a.$formEvent.on("submit", function(e) {
      console.log('new event',a);
      e.preventDefault();
       var t, n = a.$formEvent[0];
       var title = $('#event-title1').val();
       var en = $("#startTime").val();
       var cat= 'bg-danger';
       var hs = $("#hora-salida").val();
       var hr = $("#hora-regreso").val(); 
       var startTime = moment(a.$newEventData.date).format('dddd, MMMM Do YYYY,'+hs);
       var endTime = moment(a.$newEventData.dateStr).format('dddd, MMMM Do YYYY,'+hr);
      
       $.ajax({
           url: 'Proceso/php/demo-calendar.php',
           data: 'action=add&title='+title+'&start='+startTime+'&end='+endTime+'&category='+cat,
           type: "POST",
           success: function(json) {
               $("#calendar").fullCalendar('renderEvent',
               {
                   id: json.id,
                   title: title,
                   start: startTime,
                   end: endTime,
               },
               true);
           }
       });
      
      n.checkValidity() ? (a.$selectedEvent ? (a.$selectedEvent.setProp("title",a.$selectedEvent.title), 
      a.$selectedEvent.setProp("classNames", 
      [l("#event-category").val()])) : (t = {
        title: l("#event-title1").val(),
        start: a.$newEventData.date,
        allDay: a.$newEventData.allDay,
        className: l("#event-category").val()
      }, 
      a.$calendarObj.addEvent(t)), 
      a.$modal.modal("hide")) : (e.stopPropagation(), 
      n.classList.add("was-validated"))
    }), 
      l(a.$btnDeleteEvent.on("click", function(e) {
          console.log('delete',e);    
       var idbd = $("#idbd").val(); 
      $.ajax({
           url: 'Proceso/php/demo-calendar.php',
           data: 'action=delete&id='+idbd,
           type: "POST",
           success: function(json) {
           if(json==1){
           $.NotificationApp.send("Listo!","Eliminaste el registro correctamente ","bottom-right","#FFFFFF","success");   
           }  
           }
       });   
      a.$selectedEvent && (a.$selectedEvent.remove(), a.$selectedEvent = null, a.$modal.modal("hide"))
    }))
        
      }, l.CalendarApp = new e, l.CalendarApp.Constructor = e
}(window.jQuery),
function() {
  "use strict";
  window.jQuery.CalendarApp.init()
}();