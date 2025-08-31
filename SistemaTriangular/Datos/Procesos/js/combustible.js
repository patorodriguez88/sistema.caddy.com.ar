$.ajax({
    data:{'EstadisticasCombustible':1},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);

var options = {
    series: jsonData.Total,
    chart: {
    width: 380,
    type: 'pie',
  },
  labels: jsonData.Producto,
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 200
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
  };

  var chart = new ApexCharts(document.querySelector("#chart"), options);
  chart.render();
     
}
});

//X CONDUCTOR
$.ajax({
    data:{'EstadisticasCombustible_1':1},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);

var options = {
    series: jsonData.Total,
    chart: {
    width: 380,
    type: 'pie',
  },
  labels: jsonData.Conductor,
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 200
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
  };

  var chart = new ApexCharts(document.querySelector("#chart_choferes"), options);
  chart.render();
     
}
});

$.ajax({
    data:{'EstadisticasCombustible_0':1},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);     
     var formatoMoneda = jsonData.total_expend.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
     $('#total_expend').html(formatoMoneda);
    
    }
});

//TOTAL CONSUMO
$.ajax({
    data:{'EstadisticasCombustible_2':1},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);

var options = {
    series: [{
      name: "Desktops",
      data: jsonData.Total
  }],
    chart: {
    height: 350,
    type: 'line',
    zoom: {
      enabled: false
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'straight'
  },
  title: {
    text: 'Tendencia de gasto por mes',
    align: 'left'
  },
  grid: {
    row: {
      colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
      opacity: 0.5
    },
  },
  xaxis: {
    categories: jsonData.Mes,
  }
  };

  var chart = new ApexCharts(document.querySelector("#chart_consumo"), options);
  chart.render();

}
});

//X VEHICULO
$.ajax({
    data:{'EstadisticasCombustible_3':1},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);

     var options = {
        series: [{
        data: jsonData.Total
      }],
        chart: {
        type: 'bar',
        height: 350
      },
      plotOptions: {
        bar: {
          borderRadius: 4,
          horizontal: false,
        }
      },
      dataLabels: {
        enabled: false
      },
      xaxis: {
        categories: jsonData.Vehiculo
      },
      yaxis: {
        labels: {
          formatter: function (value) {
            return "$" + value.toLocaleString(); // Formatear el valor como moneda
          }
        }
      }
      };



  var chart = new ApexCharts(document.querySelector("#chart_x_vehiculo"), options);
  chart.render();
     }
});

//VEHICULO AA056 XV
$.ajax({
    data:{'EstadisticasCombustible_4':1},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);
        
        var options = {
            series: jsonData.Totales,
            chart: {
            type: 'donut',
        },
        labels:jsonData.Producto,
        responsive: [{
            breakpoint: 480,
            options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
        }],
        yaxis: {
            labels: {
              formatter: function (value) {
                return "$" + value.toLocaleString(); // Formatear el valor como moneda
              }
            }
          }

    };

  var chart = new ApexCharts(document.querySelector("#chart_x_vehiculo_1"), options);
  chart.render();

     
    
    }     
});

//VEHICULO OQR318
$.ajax({
    data:{'EstadisticasCombustible_4':2},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);
        
        var options = {
            series: jsonData.Totales,
            chart: {
            type: 'donut',
        },
        labels: jsonData.Producto,
        responsive: [{
            breakpoint: 480,
            options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
        }],
        yaxis: {
            labels: {
              formatter: function (value) {
                return "$" + value.toLocaleString(); // Formatear el valor como moneda
              }
            }
          }
    
    };

  var chart = new ApexCharts(document.querySelector("#chart_x_vehiculo_2"), options);
  chart.render();

     
    
    }     
});

//VEHICULO OQR318
$.ajax({
    data:{'EstadisticasCombustible_4':3},
    url:'https://www.caddy.com.ar/SistemaTriangular/Datos/Procesos/php/combustible.php',
    type:'post',
      beforeSend: function(){
      },
    success: function(response)
     {
     var jsonData=JSON.parse(response);
        
        var options = {
            series: jsonData.Totales,
            chart: {
            type: 'donut',
        },
        labels: jsonData.Producto,
        responsive: [{
            breakpoint: 480,
            options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
        }],
        yaxis: {
            labels: {
              formatter: function (value) {
                return "$" + value.toLocaleString(); // Formatear el valor como moneda
              }
            }
          }
    
    };

  var chart = new ApexCharts(document.querySelector("#chart_x_vehiculo_3"), options);
  chart.render();

     
    
    }     
});