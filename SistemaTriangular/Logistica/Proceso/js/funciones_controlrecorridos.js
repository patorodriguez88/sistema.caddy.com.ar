// var Fecha='2022-04-18';
function currencyFormat(num) {
    return '$ ' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
  }

$(document).ready(function(){
              
    var fechas;
    var datatable = $('#flota').DataTable();
    datatable.destroy();

    if (fechas==null){
        var fechas1=(new Date().getUTCMonth()+1)+'/'+new Date().getUTCDate()+'/'+new Date().getUTCFullYear();
        var fechas=fechas1+" - "+fechas1;    
    }
    
    init_datatable(fechas);

});

function info_order(i){
    
    $.ajax({
        data:{'Control_Recorrido':1,'idLogistica':i},
        url:'Logistica/Proceso/php/controlrecorridos.php',
        type:'post',
          beforeSend: function(){
  //           document.getElementById("spinner").style.display="block";
          },
        success: function(response)
         {
            var jsonData = JSON.parse(response);
            $('#title_modal_info').html('Recorrido | '+jsonData.RecName+' | '+jsonData.Rec+' | Orden N '+jsonData.No);            
            $('#total_packages').html(jsonData.data);
            $('#total_km').html(jsonData.km);
            
            if(jsonData.estima==1){
            
                $('#total_km').removeClass('my-2').addClass('my-2 text-danger');
                $('.text-nowrap.2').removeClass('text-nowrap 2').addClass('text-nowrap 2 text-danger').html('Valores Estimados');

            }else{

            }
            $('#total_value_paq').html(currencyFormat(Number(jsonData.total_value_paq)));
            $('#total_value_km').html(currencyFormat(Number(jsonData.total_value_km)));            
            $('#total_price').html(currencyFormat(Number(jsonData.price)));
            $('#total_paq').html(jsonData.Total_paq+'%');
            $('#prom_km').html(jsonData.prom_km+'%');
            $('#modal_info').modal('show');

         }
    });

}
$('#singledaterange').change( function() {

    fechas=$('#singledaterange').val();
    // console.log(fechas);

    if (fechas==null){
        var fechas1=(new Date().getUTCMonth()+1)+'/'+new Date().getUTCDate()+'/'+new Date().getUTCFullYear();
        var fechas=fechas1+" - "+fechas1;    
    }
    var datatable = $('#flota').DataTable();
    datatable.destroy();
    init_datatable(fechas);
});
    
function init_datatable(fechas){
    var new_date=fechas.split("-");
    var new_date1=new_date[0].split("/");
    var new_date2=new_date1[1]+'/'+new_date1[0]+'/'+new_date1[2];
    
    var new_date_1=new_date[1].split("/");
    var new_date_2=new_date_1[1]+'/'+new_date_1[0]+'/'+new_date_1[2];

$('#header_flota').html('Recorridos Fechas Desde '+new_date2+' Hasta '+new_date_2);
var Recorrido=$('#recorrido').html();
// console.log('Recorrido',Recorrido);

var datatable1 = $('#flota').DataTable({
    paging: true,
    searching: true,
    ajax: {
         url:"Proceso/php/controlrecorridos.php",
         data:{'Control':1,'Fechas':fechas,'Recorrido':Recorrido},
         type:'post'
         },
        columns: [
            {data:"Fecha",
            render: function (data,type,row){
                var Fecha=row.Fecha.split('-').reverse().join('.');
                return '<td><a style="display:none">'+row.Fecha+'</a>'+Fecha+'</td></br>'+
                '<small class="mr-2" style="font-size:8px"><span class="mdi mdi-clock"></span> '+ row.Hora+' </small>';                  
                }
            },
            {data:"Patente",
            render: function (data,type,row){                
                return '<td>'+row.Marca+ ' ' +row.Modelo+'</td></br>'+
                '<small class="mr-2" style="font-size:8px"><span class="mdi mdi-truck"></span> '+ row.Patente+' | '+row.NombreChofer +'-'+row.NombreChofer2+'</small>';  
                }  
        
        },
            {data:"Recorrido",
            render: function (data,type,row){           
                
                if(row.Estado=='Cargada'){
                    var color='success';  
                    }else if((row.Estado=='Cerrada') || (row.Estado=='Pendiente')){
                    var color='warning';    
                    }else{
                    var color='danger';      
                    }  
                    // return "<td>"+
                    //        "<i class='mdi mdi-circle text-"+ color +"'></i>"+ row.Estado +
                    //        "</td>"
                    //        }


                return '<td>'+row.Nombre+'</td></br>'+
                '<small class="mr-2" style="font-size:8px"><span class="mdi mdi-map-marker text-'+ color +'"></span> Rec. N: '+ row.Recorrido+' Estado: '+ row.Estado +'</small>';  
                }                
        },
            {data:"FechaRetorno",
            render: function (data,type,row){
                var FechaRetorno=row.FechaRetorno.split('-').reverse().join('.');
                
                return '<td>'+FechaRetorno+'</td></br>'+
                '<small class="mr-2" style="font-size:8px"><span class="mdi mdi-clock"></span> '+ row.HoraRetorno+' </small>';  
                }                
            },
            {data:null,
            render: function (data, type, row) {
                return "<td>"+
                "<i style='cursor:pointer' class='mdi mdi-18px mdi-go-kart-track text-success' onclick='initMap_order("+row.id+")'></i>"+
                "<i style='cursor:pointer' class='mdi mdi-18px mdi-information-outline text-warning' onclick='info_order("+row.id+")'></i>"+
                "</td>";                
                }
            }
            ]
});
}