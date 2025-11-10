        <meta charset="utf-8" />
        <title>Error 404 | Caddy</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- App css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

    </head>

    <body class="loading authentication-bg" data-layout-config='{"leftSideBarTheme":"dark","layoutBoxed":false, "leftSidebarCondensed":false, "leftSidebarScrollable":false,"darkMode":false, "showRightSidebarOnStart": true}'>

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5">
                        <div class="card">
                            <!-- Logo -->
                            <div class="card-header pt-4 pb-4 text-center bg-primary">
                                <a href="index.html">
                                    <span><img src="assets/images/logo.png" alt="" height="18"></span>
                                </a>
                            </div>

                            <div class="card-body p-4">
                                <div class="text-center">
                                    <h1 class="text-error">4<i class="mdi mdi-emoticon-sad"></i>4</h1>
                                    <h4 class="text-uppercase text-danger mt-3">Page Not Found</h4>
                                    
                                      <button class="form-control" id='mail' name='mail' onclick="mailsend()">Enviar</button>
                                    <a class="btn btn-info mt-3" href="index.html"><i class="mdi mdi-reply"></i> Return Home</a>
                                </div>
                            </div> <!-- end card-body-->
                        </div>
                        <!-- end card -->
                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

        <footer class="footer footer-alt">
          <script type="text/javascript">
            function mailsend(){
                  $.ajax({
                    data: {'enviar':1,'id':6494},
                    url:'Procesos/enviarmailproximo.php',
                    type:'post',
              //         beforeSend: function(){
              //         $("#buscando").html("Buscando...");
              //         },
            success: function(response)
             {
              var jsonData = JSON.parse(response);
              if (jsonData.success == "1")
              {
                alert(jsonData.success);
              }
              }
            });
           }
          </script>


          
            <script>document.write(new Date().getFullYear())</script> © Hyper - Coderthemes.com
        </footer>

        <!-- bundle -->
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>
      
      <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<!--       <script type="text/javascript" src="js/d3.v3.min.js"></script> -->
        
    </body>
</html>