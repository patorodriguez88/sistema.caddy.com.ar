  
// Replace Math.random() with a pseudo-random number generator to get reproducible results in e2e tests
      // Based on https://gist.github.com/blixt/f17b47c62508be59987b
      $('#botonestadisticas').click(function(){
          
      var _seed = 42;
      Math.random = function() {
        _seed = _seed * 16807 % 2147483647;
        return (_seed - 1) / 2147483646;
      };
        var id = document.getElementById('codigo').value;
        $.ajax({
              data:{'Estadisticas':1,'idCliente':id},
              url:'https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/tablas.php',
              type:'post',
                beforeSend: function(){
                },
              success: function(response)
               {
               var jsonData=JSON.parse(response);
               var options = {
                series: [],
                chart: {
                height: 350,
                type: 'bar',
               },
                dataLabels: {
                enabled: false
              },
              title: {
                text: 'Ventas',
              },
              noData: {
                text: 'Loading...'
              },
              xaxis: {
                categories: jsonData.x,
                type: 'category',
                tickPlacement: 'on',
                labels: {
                  rotate: -45,
                  rotateAlways: true
                }
              }
              };  
                 var chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();
                chart.updateSeries([{
                name: 'Ventas',
                data: jsonData.y
                }])
              }
        });
      // Replace Math.random() with a pseudo-random number generator to get reproducible results in e2e tests
      // Based on https://gist.github.com/blixt/f17b47c62508be59987b
      var _seed = 42;
      Math.random = function() {
        _seed = _seed * 16807 % 2147483647;
        return (_seed - 1) / 2147483646;
      };

        $.ajax({
              data:{'EstadisticasEnvios':1,'idCliente':id},
              url:'https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/tablas.php',
              type:'post',
                beforeSend: function(){
                },
              success: function(response)
               {
               var jsonData=JSON.parse(response);
                 options = {
                series: [],
                chart: {
                height: 374,
                type: "line",
                shadow: {
                enabled: !1,
                color: "#bbb",
                top: 3,
                left: 2,
                blur: 3,
                opacity: 1
                }
              },
              stroke: {
              width: 5,
              curve: "smooth"
              },


              dataLabels: {
                enabled: false
              },
              title: {
                text: 'Ventas',
              },
                fill: {
                type: "gradient",
                gradient: {
                  shade: "dark",
                  gradientToColors: ["#fa5c7c"],
                  shadeIntensity: 1,
                  type: "horizontal",
                  opacityFrom: 1,
                  opacityTo: 1,
                  stops: [0, 100, 100, 100]
                }
              },
              markers: {
                size: 4,
                opacity: .9,
                colors: ["#ffbc00"],
                strokeColor: "#fff",
                strokeWidth: 2,
                style: "inverted",
                hover: {
                  size: 7
                }
              },
//               yaxis: {
//                 min: -10,
//                 max: 40,
//                 title: {
//                   text: "Engagement"
//                 }
//               },
              noData: {
                text: 'Loading...'
              },
              xaxis: {
                categories: jsonData.x,
                type: 'category',
                tickPlacement: 'on',
                labels: {
                  rotate: -45,
                  rotateAlways: true
                }
              },
//                 grid: {
//                   row: {
//                     colors: ["transparent", "transparent"],
//                     opacity: .2
//                   },
//                   borderColor: "#f1f3fa"
//                 },
                responsive: [{
                  breakpoint: 600,
                  options: {
                    chart: {
                      toolbar: {
                        show: !1
                      }
                    },
                    legend: {
                      show: !1
                    }
                  }
                }]
              };  
                 var chart = new ApexCharts(document.querySelector("#chart_envios"), options);
               chart.render();
               chart.updateSeries([{
                name: 'Ventas',
                data: jsonData.y
                }])
              }
        });

        //ENVIOS X DIA

          $.ajax({
            data:{'EstadisticasEnvios':2,'idCliente':id},
            url:'https://www.caddy.com.ar/SistemaTriangular/Clientes/Procesos/php/tablas.php',
            type:'post',
              beforeSend: function(){
              },
            success: function(response)
             {
             var jsonData=JSON.parse(response);
            
             var options = {
                series: [{
                name: 'Servicios Especiales',
                data: jsonData.data
              }, {
                name: 'Flex',
                data: jsonData.flex
              }],
                chart: {
                type: 'bar',
                height: 430
              },
              plotOptions: {
                bar: {
                  horizontal: true,
                  dataLabels: {
                    position: 'top',
                  },
                }
              },
              dataLabels: {
                enabled: true,
                offsetX: -6,
                style: {
                  fontSize: '12px',
                  colors: ['#fff']
                }
              },
              stroke: {
                show: true,
                width: 1,
                colors: ['#fff']
              },
              tooltip: {
                shared: true,
                intersect: false
              },
              xaxis: {
                categories: jsonData.name,
              },
              };



            //  var options = {
            //     series: [{
            //     data: jsonData.data,
            //     flex: jsonData.flex
            //   }],
            //     chart: {
            //     type: 'bar',
            //     height: 350
            //   },
            //   plotOptions: {
            //     bar: {
            //       borderRadius: 4,
            //       horizontal: true,
            //     }
            //   },
            //   dataLabels: {
            //     enabled: false
            //   },
            //   xaxis: {
            //     categories: jsonData.name,
            //   }
            //   };
      
  
          var chart = new ApexCharts(document.querySelector("#chart_envios_1"), options);
          chart.render();
            }
        });

});
