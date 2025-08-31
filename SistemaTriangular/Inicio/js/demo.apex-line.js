// Create our number formatter.
var formatter = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "USD",

  // These options are needed to round to whole numbers if that's what you want.
  //minimumFractionDigits: 0, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
  //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
});

// Replace Math.random() with a pseudo-random number generator to get reproducible results in e2e tests
// Based on https://gist.github.com/blixt/f17b47c62508be59987b
var _seed = 42;
Math.random = function () {
  _seed = (_seed * 16807) % 2147483647;
  return (_seed - 1) / 2147483646;
};

$.ajax({
  data: { Ventas: 1 },
  url: "../Inicio/php/funcionesAdmin.php",
  type: "post",
  beforeSend: function () {},
  success: function (response) {
    var jsonData = JSON.parse(response);
    var options = {
      series: [],
      chart: {
        height: 350,
        type: "bar",
      },
      dataLabels: {
        enabled: false,
      },
      title: {
        text: "Ventas",
      },
      noData: {
        text: "Loading...",
      },
      xaxis: {
        categories: jsonData.x,
        type: "category",
        tickPlacement: "on",
        labels: {
          rotate: -45,
          rotateAlways: true,
        },
      },
      yaxis: {
        labels: {
          /**
           * Allows users to apply a custom formatter function to yaxis labels.
           *
           * @param { String } value - The generated value of the y-axis tick
           * @param { index } index of the tick / currently executing iteration in yaxis labels array
           */
          formatter: function (val, index) {
            var valor = new Intl.NumberFormat("en-US", {
              style: "currency",
              currency: "USD",
            }).format(val);
            return valor;
          },
        },
      },
    };
    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
    chart.updateSeries([
      {
        name: "Ventas",
        data: jsonData.y,
      },
    ]);
  },
});

//VENTAS MENSUALES X RECORRIDO
$.ajax({
  data: { VentasRec: 1 },
  url: "../Inicio/php/funcionesAdmin.php",
  type: "post",
  beforeSend: function () {},
  success: function (response) {
    var jsonData = JSON.parse(response);
    var options = {
      series: [],
      chart: {
        height: 350,
        type: "bar",
        color: "#ccc",
      },
      dataLabels: {
        enabled: false,
      },
      title: {
        text: "Ventas",
      },
      noData: {
        text: "Loading...",
      },
      xaxis: {
        categories: jsonData.x,
        type: "category",
        tickPlacement: "on",
        labels: {
          rotate: -45,
          rotateAlways: true,
        },
      },
      yaxis: {
        labels: {
          /**
           * Allows users to apply a custom formatter function to yaxis labels.
           *
           * @param { String } value - The generated value of the y-axis tick
           * @param { index } index of the tick / currently executing iteration in yaxis labels array
           */
          formatter: function (val, index) {
            var valor = new Intl.NumberFormat("en-US", {
              style: "currency",
              currency: "USD",
            }).format(val);
            return valor;
          },
        },
      },
    };
    var chart = new ApexCharts(document.querySelector("#chartrec"), options);
    chart.render();
    chart.updateSeries([
      {
        name: "Ventas: $",
        data: jsonData.y,
      },
    ]);
  },
});

// VENTAS MENSUALES X RECORRIDO

// Replace Math.random() with a pseudo-random number generator to get reproducible results in e2e tests
// Based on https://gist.github.com/blixt/f17b47c62508be59987b
var _seed = 42;
Math.random = function () {
  _seed = (_seed * 16807) % 2147483647;
  return (_seed - 1) / 2147483646;
};
$.ajax({
  data: { Envios0: 1 },
  url: "../Inicio/php/funcionesAdmin.php",
  type: "post",
  beforeSend: function () {},
  success: function (response) {
    var jsonData = JSON.parse(response);
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
          opacity: 1,
        },
      },
      stroke: {
        width: 5,
        curve: "smooth",
      },

      dataLabels: {
        enabled: false,
      },
      title: {
        text: "Ventas",
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
          stops: [0, 100, 100, 100],
        },
      },
      markers: {
        size: 4,
        opacity: 0.9,
        colors: ["#ffbc00"],
        strokeColor: "#fff",
        strokeWidth: 2,
        style: "inverted",
        hover: {
          size: 7,
        },
      },
      noData: {
        text: "Loading...",
      },
      xaxis: {
        categories: jsonData.x,
        type: "category",
        tickPlacement: "on",
        labels: {
          rotate: -45,
          rotateAlways: true,
        },
      },
      responsive: [
        {
          breakpoint: 600,
          options: {
            chart: {
              toolbar: {
                show: !1,
              },
            },
            legend: {
              show: !1,
            },
          },
        },
      ],
    };
    var chart = new ApexCharts(
      document.querySelector("#chart_envios"),
      options
    );
    chart.render();
    chart.updateSeries([
      {
        name: "Ventas",
        data: jsonData.y,
      },
    ]);
  },
});

//TOTAL X MES EN PESOS

$.ajax({
  data: { Envios1: 1 },
  url: "../Inicio/php/funcionesAdmin.php",
  type: "post",
  beforeSend: function () {},
  success: function (response) {
    var jsonData = JSON.parse(response);
    var valor_y = formatter.format(jsonData.y);

    options = {
      series: [],
      chart: {
        height: 374,
        type: "line",
        shadow: {
          enabled: !1,
          color: "#C6CAE1",
          top: 3,
          left: 2,
          blur: 3,
          opacity: 1,
        },
      },
      stroke: {
        width: 5,
        curve: "smooth",
      },

      dataLabels: {
        enabled: false,
      },
      title: {
        text: "Ventas Totales",
      },
      fill: {
        type: "gradient",
        gradient: {
          shade: "dark",
          gradientToColors: ["#354FF8"],
          shadeIntensity: 1,
          type: "horizontal",
          opacityFrom: 1,
          opacityTo: 1,
          stops: [0, 100, 100, 100],
        },
      },
      markers: {
        size: 4,
        opacity: 0.9,
        colors: ["#36FA13"],
        strokeColor: "#fff",
        strokeWidth: 2,
        style: "inverted",
        hover: {
          size: 7,
        },
      },
      yaxis: {
        labels: {
          /**
           * Allows users to apply a custom formatter function to yaxis labels.
           *
           * @param { String } value - The generated value of the y-axis tick
           * @param { index } index of the tick / currently executing iteration in yaxis labels array
           */
          formatter: function (val, index) {
            var valor = new Intl.NumberFormat("en-US", {
              style: "currency",
              currency: "USD",
            }).format(val);
            return valor;
          },
        },
      },
      noData: {
        text: "Loading...",
      },
      xaxis: {
        categories: jsonData.x,
        type: "category",
        tickPlacement: "on",
        labels: {
          rotate: -45,
          rotateAlways: true,
        },
      },
      responsive: [
        {
          breakpoint: 600,
          options: {
            chart: {
              toolbar: {
                show: !1,
              },
            },
            legend: {
              show: !1,
            },
          },
        },
      ],
    };
    var chart = new ApexCharts(
      document.querySelector("#chart_envios_total"),
      options
    );

    var total_data = [];

    for (var i = 0; i < jsonData.x.length; i++) {
      total_data[i] = Math.round(
        Number(jsonData.y[i]) + Number(jsonData.yr[i])
      );
    }

    chart.render();
    chart.updateSeries([
      {
        name: "Ventas $",
        data: total_data,
      },
    ]);
  },
});
