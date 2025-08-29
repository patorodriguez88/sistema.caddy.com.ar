var datatable = $('#seguimiento').DataTable({
    ajax: {
         url:"Procesos/php/localidades.php",
         data:{'Pendientes':1},
         processing: true,
         type:'post'
         },
        columns: [
            {data:"Fecha",
             render: function (data, type, row) {
              var Fecha=row.Fecha.split('-').reverse().join('.');
              return '<td><span style="display: none;">'+row.Fecha+'</span>'+Fecha+'</td>';  
              }
            },
            {data:"NumeroComprobante"},
            {data:"RazonSocial",
            render: function (data, type, row) { 
            if(row.Retirado==0){
            var color='success';  
            }else{
            color='muted';    
            }  
           return '<td><b>'+row.RazonSocial+'</br>'+  
                     '<i class="mdi mdi-18px mdi-map-marker text-'+color+'"></i><a class="text-muted">'+row.DomicilioOrigen+'</td>';
              }
            },
            {data:"DomicilioDestino",
            render: function (data, type, row) { 
            if(row.Retirado==1){
            var color1='success';  
            }else{
            color1='muted';    
            }  
            return '<td><b>'+row.ClienteDestino+'</br>'+  
                     '<i class="mdi mdi-18px mdi-map-marker text-'+color1+'"></i><a class="text-muted">'+row.DomicilioDestino+'</td>';
              }
            },
            {data:"CodigoSeguimiento",
            render: function (data, type, row) {
              if(row.Retirado==1){
            var color='success';
            var servicio='Entrega';    
            }else{
            var color='muted';    
            var servicio='Origen';  
            }  
                return '<td class="table-action">'+
                '<a>'+row.CodigoSeguimiento+'</a><br/>'+
                '<a><b>'+servicio+'</b></a>'+
                '</td>';
              }
            },
//           {data:"Recorrido"},
            {data:"Recorrido",
           render: function (data, type, row) {
                return '<td class="table-action">'+
                '<a style="cursor:pointer" data-id="' + row.CodigoSeguimiento + '" id="'+row.CodigoSeguimiento+'" onclick="modificarrecorrido(this.id);" ><b class="text-primary">'+row.Recorrido+'</b></a>'+
                '</td>';
             }
            },
            {data:"id",
           render: function (data, type, row) {
                return '<td class="table-action">'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="modificar(this.id);" class="action-icon"> <i class="mdi mdi-pencil"></i></a>'+
                '<a data-id="' + row.id + '" id="'+row.id+'" onclick="eliminar(this.id);" class="action-icon"> <i class="mdi mdi-delete"></i></a>'+
                '</td>';
              }
            },
           
        ]
});
