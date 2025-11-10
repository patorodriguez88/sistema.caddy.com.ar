function currencyFormat(num) {
  return '$' + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}
$.ajax({
        data:{'GastosGrafico':1},
        url:'https://www.caddy.com.ar/SistemaTriangular/Inicio/php/funcionesAdmin.php',
        type:'post',
        beforeSend: function(){
        },
        success: function(response)
        {
        
        var jsonData=JSON.parse(response);
 
        var options = {
          series: [{
            name: "2021",
            data: jsonData.y
          }
        //   {
        //     name: "Page Views",
        //     data: currencyFormat(num)
        //   },
        //   {
        //     name: 'Total Visits',
        //     data: [87, 57, 74, 99, 75, 38, 62, 47, 82, 56]
        //   }
        ],
          chart: {
          height: 350,
          type: 'line',
          zoom: {
            enabled: false
          },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          width: [5, 7, 5],
          curve: 'straight',
          dashArray: [0, 8, 5]
        },
        title: {
          text: 'Miles de Pesos',
          align: 'left'
        },
        legend: {
          tooltipHoverFormatter: function(val, opts) {
            return val + ' - ' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + ''
          }
        },
        markers: {
          size: 0,
          hover: {
            sizeOffset: 6
          }
        },
        xaxis: {
          categories: jsonData.x,
        },
        tooltip: {
          y: [
            {
              title: {
                formatter: function (val) {
                  return val + " $ "
                }
              }
            },
            {
              title: {
                formatter: function (val) {
                  return val + " per session"
                }
              }
            },
            {
              title: {
                formatter: function (val) {
                  return val;
                }
              }
            }
          ]
        },
        grid: {
          borderColor: '#f1f1f1',
        }
        };
   var chart = new ApexCharts(document.querySelector("#line-expenses"), options);
    chart.render();

    }
});
     
