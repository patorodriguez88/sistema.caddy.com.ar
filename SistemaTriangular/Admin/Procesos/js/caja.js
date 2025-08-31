
var myToast =  $.toast({
    heading: 'Saldo Movimientos Seleccionados',
    text: "$ 0.00",
    hideAfter: false,
    allowToastClose: false,
    bgColor: '#F00000',
    textColor: 'white',
    position: 'bottom-right',
})


function seleccionarTodo(c) {
    var ids = document.querySelectorAll("input[type='checkbox']");
    for (let i=0; i < ids.length; i++) {
        if(ids[i].type === "checkbox") {
            if(c==true){
            ids[i].checked = true;
            sumar();
            }else{
            ids[i].checked = false;    
            sumar();            
            }
        }
    }
}

function sumar(){

	var ids = document.querySelectorAll("input[type='checkbox']:checked");
	var a=[];
    var total=0;

	for (var i=0; i<ids.length; i++) {
		
        a[i] += ids[i].id;
        total+=parseFloat(ids[i].id);
        
	}

    let num = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(total);
    // $('#movimientos_cierre_caja').val(num);
    myToast.update({
        
        text: num,
        bgColor: 'success',
        
        
    });    

    console.log('values',total);

    if(ids.length==0){
        $('#cierre_add').addClass('disabled');

    }else{
        $('#cierre_add').removeClass('disabled');
    }
}


function currencyFormat(num) {
  return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}


var datatable = $('#seguimiento').DataTable();
datatable.destroy();

var datatable = $('#cierre_caja').DataTable();
datatable.destroy();

     $.ajax({
      data:{'VerFechas':1},
      url:'Procesos/php/caja.php',
      type:'post',
      success: function(response)
       {
        var jsonData = JSON.parse(response);
        
        //TABLA DE CIERRES DE CAJA
        var datatable = $('#cierre_caja').DataTable({
            paging: false,
            searching: true,
            footerCallback: function(row, data, start, end, display) {
  
              total = this.api()
                .column(6, {page: 'current'})//para sumar solo la pagina actual
                .data()
                .reduce(function(a, b) {
                  return Number(a) + Number(b);
                }, 0);
              var saldo = currencyFormat(total);            
              $(this.api().column(6).footer()).html(saldo);
            },
  
        ajax: {
           url:"Procesos/php/caja.php",
           data:{'VerFechas':1},
           processing: true,
           type:'post',
          },
          columns: [
              {data:"id"},
              {data:"Fecha",
               render: function (data, type, row) {
                var Fecha=row.Date.split('-').reverse().join('.');
                return '<td><span style="display: none;">'+row.Date+'</span>'+Fecha+'</br>'+row.Usuario+'</td>';  
                }
              },

              {data:"SaldoAnterior",
              render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
              },
              {data:"MovConciliados",
              render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
              },{data:"SaldoFinal",
              render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
              },
              {data:"SaldoActual",
              render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
              },
              {data:"Diferencia"},
              {data:"TimeStamp"},
              {data:"id",

              render: function (data, type, row) {

                  return `<td class="table-action"><a data-id="${row.id}" id="${row.id}" onclick="print(this.id);" class="action-icon disabled"> <i class="mdi mdi-cloud-print-outline text-success"></i></a></td>`;
                
                }
              },
             
          ]
          });
  
        $.ajax({
            data:{'VerFechas':1},
            url:'Procesos/php/caja.php',
            type:'post',
            success: function(response)
             {
              var jsonData = JSON.parse(response);
            //   var Inicio = jsonData.Inicio;
            //   var Final =jsonData.Final;

                //TABLA DE MOVIMIENTOS DE CAJA    
                var datatable = $('#seguimiento').DataTable({
                paging: false,
                searching: true,
                footerCallback: function(row, data, start, end, display) {

                    total = this.api()
                    .column(6, {page: 'current'})//para sumar solo la pagina actual
                    .data()
                    .reduce(function(a, b) {
                        return Number(a) + Number(b);
                    }, 0);
                    var saldo = currencyFormat(total);            
                    $(this.api().column(6).footer()).html(saldo);
                },

                ajax: {
                    url:"Procesos/php/caja.php",
                    data:{'Pendientes':1},
                    processing: true,
                    type:'post',
                    },
                    columns: [                        
                        {data:"Fecha",
                        render: function (data, type, row) {
                        var Fecha=row.Fecha.split('-').reverse().join('.');
                        return '<td><span style="display: none;">'+row.Fecha+'</span>'+Fecha+'</td>';  
                        }
                        },
                        {data:"Usuario"},
                        {data:"FormaDePago"},
                        {data:"NombreCuenta"},
                        {data:"Observaciones"},
                        {data:"RazonSocial"},
                        {data:"Debe",
                        
                        render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
                        
                        },
                        {data:"Haber",
                        
                        render: $.fn.dataTable.render.number( ',', '.', 2, '$ ')
                            
                        },
                        {data:"id",
                        
                        render: function (data, type,row,full,meta) {
                        
                        var Total=parseFloat(row.Debe)-parseFloat(row.Haber);

                        return `<td class="dtr-control dt-checkboxes-cell checked" tabindex="0"><div class="form-check"><input data-id="${row.id}" id="${Total}" type="checkbox" onclick="sumar()" class="form-check-input dt-checkboxes"  `+ (full.checked ? ' checked' : '') + ` ><label class="form-check-label">&nbsp;</label></div></td>`;
                            // return `<td class="table-action"><a data-id="${row.id}" id="${row.id}" onclick="#" class="action-icon disabled"> <i class="mdi mdi-pencil text-muted"></i></a><a data-id="${row.id}" id="${row.id}" onclick="eliminar(${row.id});" class="action-icon"> <i class="mdi mdi-delete text-danger"></i></a></td>`;
                        }
                        },
                    
                    ]
                    });

                    datatable.on('change', 'thead input', function(evt) {
                        
                        let checked = this.checked;
                        console.log('dato',checked);
                        seleccionarTodo(checked);

                      });

        }
        });

    }  
});     


$('#cierre_add').click(function(){
    
    $('#modal_cierre_caja').modal('show');

    $('#cerrar_caja_ok').click(function(){
        let Fecha_=$('#date_cierre_caja').val();
        let Fecha=Fecha_.split("-").reverse().join("-");
        let SaldoUltimo=$('#saldo_ant_cierre_caja_number').val();
        let SaldoActual=$('#saldo_actual_cierre_caja').val();
        let Diferencia=$('#saldo_dif_cierre_caja_number').val();
        let SaldoFinal=$('#saldo_conciliar_number').val();        
        let MovConciliados=$('#movimientos_cierre_caja_number').val();
        var ids = document.querySelectorAll("input[type='checkbox']:checked");
        var a=[];                
    
        for (var i=0; i<ids.length; i++) {
            a.push(ids[i].dataset.id);
        }

        $.ajax({
            data:{'Agregar_cierre':1,'Fecha':Fecha,'MovConciliados':MovConciliados,'SaldoUltimo':SaldoUltimo,'SaldoActual':SaldoActual,'Diferencia':Diferencia,'ids':a,'SaldoFinal':SaldoFinal},
            url:'Procesos/php/caja.php',
            type:'post',
            success: function(response)
             {
              var jsonData = JSON.parse(response);
              
              if(jsonData.success==1){

                $('#modal_cierre_caja').modal('hide'); 
                
                var datatable = $('#cierre_caja').DataTable();
                datatable.ajax.reload();

                var datatable_1 = $('#seguimiento').DataTable();
                datatable_1.ajax.reload();
                
                //BLOQUEO EL BOTON
                $('#cierre_add').addClass('disabled');
                
                //MODIFICO EL TOAST
                myToast.update({
        
                    text: 0,
                    bgColor: 'success',

                });    
            
                }
             }
            });    
    });    
});


$('#modal_cierre_caja').on('show.bs.modal', function (e) {

    $.ajax({
        data:{'Ver_datos':1},
        url:'Procesos/php/caja.php',
        type:'post',
        success: function(response)
         {
          var jsonData = JSON.parse(response);
          let date=jsonData.Date.split('-').reverse();
          let saldo = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(jsonData.Saldo);
          $('#saldo_ant_cierre_caja').val(saldo);
          $('#saldo_ant_cierre_caja_number').val(jsonData.Saldo);

          $('#date_last_cierre_caja').val(date);  
          var ids = document.querySelectorAll("input[type='checkbox']:checked");
          var a=[];
          var total=0;
      
          for (var i=0; i<ids.length; i++) {
              a[i] += ids[i].id;
              total+=parseFloat(ids[i].id);
          }
          let num_0 = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(total);
          $('#movimientos_cierre_caja').val(num_0);
          $('#movimientos_cierre_caja_number').val(total);

            let SALDO=parseFloat(jsonData.Saldo)+parseFloat(total);
            let num = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(SALDO);
            $('#saldo_conciliar_number').val(SALDO);
            $('#saldo_conciliar').val(num);

        }
        });    
  })

              //AL CARGAR LOS DATOS DE CAJA FISICA
              function comprobar_diferencia(e){
                console.log('dif',e);
                let saldo=$('#saldo_conciliar_number').val();
                let dif=parseFloat(e)-parseFloat(saldo);
                let saldo_dif = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(dif);
                $('#saldo_dif_cierre_caja').val(saldo_dif);
                $('#saldo_dif_cierre_caja_number').val(dif);

                document.getElementById('cerrar_caja_ok').disabled=false;

            }

            $('#modal_cierre_caja').on('hidden.bs.modal', function () {
                // Resetear todos los formularios dentro del modal
                $(this).find('form').each(function () {
                    this.reset();
                });
            
                // Limpiar campos manuales (por si no están dentro de un <form>)
                $('#saldo_conciliar_number').val('');
                $('#saldo_dif_cierre_caja').val('');
            
                // Deshabilitar el botón
                document.getElementById('cerrar_caja_ok').disabled = true;
            });