<?php
session_start();
include("../conexionmy.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Mobilestore website for Iphone, Android, Smartphone Mobile Website Template | Contact :: w3layouts</title>
		<link href="smartphone/css/style.css" rel="stylesheet" type="text/css"  media="all" />
		<meta name="keywords" content="Mobilestore iphone web template, Andriod web template, Smartphone web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design" />
		<link href='http://fonts.googleapis.com/css?family=Londrina+Solid|Coda+Caption:800|Open+Sans' rel='stylesheet' type='text/css'>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
		</script>
		<link rel="stylesheet" href="smartphone/css/responsiveslides.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<link href="smartphone/css/menu.css" rel="stylesheet" type="text/css" media="all"/>
		<script type="text/javascript">window.onload = function() { w3Init(); };</script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script src="smartphone/js/mobile.js"></script>
</head>
<body>
<?									

//---------------------Cliente
echo "<div id='myDiv' style='visibility:visible'>";

$Recorrido=$_SESSION['Recorrido'];
$q=$_POST['q'];
echo "<div><label style='font-size: 1.8em;'>Cliente</label><select name='ciudad_t' style='width:150px;' size='1'>";
$Grupo="SELECT NdeCliente FROM Kioscos WHERE Recorrido=$Recorrido";
$estructura= mysql_query($Grupo);
while ($row = mysql_fetch_row($estructura)){
$GrupoS=$row[0];
echo "<option value='".$GrupoS."'";

	if ($row[0]=='$Localidad'){
	echo "selected>'".$GrupoS."'</option>";	
	}else{
	echo ">".$GrupoS."</option>";	
	}
}
$_SESSION['Localidad']=$_POST['ciudad_t'];
echo "</select></div>";
echo "</div>";
//---------------------------------
?>
</select></div>
							<div>
								<span><label style='font-size: 1.8em;'>IMPORTE</label></span>
								<span><input style='font-size: 1.8em;' type="number" value=""></span>
						    </div>
						   <div>
						   		<span><input style='float:center;width:152px;height:45px;font-size:1.8em;' type="submit" value="Aceptar"></span>
						  </div>
					  </form>
				    </div>
  				</div>				
			  </div>
			  	 <div class="clear"> </div>
	</div>
	  </div>		
	</section>	
  </div>		
</body>
</html>
