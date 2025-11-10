<form action="/SistemaTriangular/smartphone/MP/procesar_pago.php" method="POST">
<h2>Codigo de Venta: <? echo $_GET[cd];?></h2>
   <script
    src="https://www.mercadopago.com.ar/integrations/v1/web-tokenize-checkout.js"
    data-button-label="Cobrar con Mercado Pago"
    data-public-key="TEST-b4e08010-5aff-4e12-828d-4c2ec6c1a153"
    data-transaction-amount="<? echo $datosql[Debe];?>"
    data-summary-product-label="Servicio de Envio">
  </script>
</form>
