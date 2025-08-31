$(document).ready(function(){
        var calendar = $('#calendar').fullCalendar({  // assign calendar
            header:{
                left: 'prev,next today',
                center: 'title',
                right: 'agendaWeek,agendaDay'
            },
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            allDaySlot: false,
            
            events: "Proceso/php/demo-calendar1.php?view=1",  // request to load current events
   
            
            eventClick:  function(event, jsEvent, view) {  // when some one click on any event
                endtime = $.fullCalendar.moment(event.end).format('h:mm');
                starttime = $.fullCalendar.moment(event.start).format('dddd, MMMM Do YYYY, h:mm');
                var mywhen = starttime + ' - ' + endtime;
                $('#modalTitle').html(event.title);
                $('#modalWhen').text(mywhen);
                $('#eventID').val(event.id);
                $('#calendarModal').modal();
            },
            
            select: function(start, end, jsEvent) {  // click on empty time slot
                endtime = $.fullCalendar.moment(end).format('h:mm');
                starttime = $.fullCalendar.moment(start).format('dddd, MMMM Do YYYY, h:mm');
                var mywhen = starttime + ' - ' + endtime;
                start = moment(start).format();
                end = moment(end).format();
                $('#event-modal #startTime').val(start);
                $('#event-modal #endTime').val(end);
                $('#event-modal #when').text(mywhen);
                $('#event-modal').modal('toggle');
           },
           eventDrop: function(event, delta){ // event drag and drop
               $.ajax({
                   url: 'Proceso/php/demo-calendar1.php',
                   data: 'action=update&title='+event.title+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&id='+event.id+'&category='+event.cat,
                   type: "POST",
                   success: function(json) {
                   //alert(json);
                   }
               });
           },
           eventResize: function(event) {  // resize to increase or decrease time of event
               $.ajax({
                   url: 'Proceso/php/demo-calendar1.php',
                   data: 'action=update&title='+event.title+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&id='+event.id+'&category='+event.cat,
                   type: "POST",
                   success: function(json) {
                       //alert(json);
                   }
               });
           }
        });
               
       $('#submitButton').on('click', function(e){ // add event submit
           // We don't want this to act as a link so cancel the link action
           e.preventDefault();
           doSubmit(); // send to form submit function
       });
       
       $('#deleteButton').on('click', function(e){ // delete event clicked
           // We don't want this to act as a link so cancel the link action
           e.preventDefault();
           doDelete(); 
//          send data to delete function
       });
       
       function doDelete(){  // delete event 
           $("#calendarModal").modal('hide');
           var eventID = $('#eventID').val();
           $.ajax({
               url: 'Proceso/php/demo-calendar1.php',
               data: 'action=delete&id='+eventID,
               type: "POST",
               success: function(json) {
                   if(json == 1)
                        $("#calendar").fullCalendar('removeEvents',eventID);
                   else
                        return false;
                    
                   
               }
           });
       }
       function doSubmit(){ // add event
           $("#event-modal").modal('hide');
           var title = $('#event-title').val();
           var startTime = $('#startTime').val();
           var endTime = $('#endTime').val();
           var cat = $('#event-category').val();
           
           $.ajax({
               url: 'Proceso/php/demo-calendar1.php',
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
           
       }
    });
