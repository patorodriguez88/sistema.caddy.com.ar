<?php
  
  // include phpmailer class
  require_once '../../PhpMailer/class.phpmailer.php';
  // creates object
  $mail = new PHPMailer(true); 
  
  if(isset($_POST['btn_send']))
  {
   $full_name  = strip_tags($_POST['full_name']);
   $email      = strip_tags($_POST['email']);
   $subject    = "Sending HTML eMail using PHPMailer.";
   $text_message    = "Hello $full_name, <br /><br /> This is HTML eMail Sent using PHPMailer. isn't it cool to send HTML email rather than plain text, it helps to improve your email marketing.";      
   
   
   // HTML email starts here
   
   $message  = "<html><body>";
   
   $message .= "<table width='100%' bgcolor='#e0e0e0' cellpadding='0' cellspacing='0' border='0'>";
   
   $message .= "<tr><td>";
   
   $message .= "<table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='max-width:650px; background-color:#fff; font-family:Verdana, Geneva, sans-serif;'>";
    
   $message .= "<thead>
      <tr height='80'>
       <th colspan='4' style='background-color:#f5f5f5; border-bottom:solid 1px #bdbdbd; font-family:Verdana, Geneva, sans-serif; color:#333; font-size:34px;' >Programacion.net</th>
      </tr>
      </thead>";
    
   $message .= "<tbody>
      <tr align='center' height='50' style='font-family:Verdana, Geneva, sans-serif;'>
       <td style='background-color:#00a2d1; text-align:center;'><a href='http://programacion.net/articulos/c' style='color:#fff; text-decoration:none;'>C</a></td>
       <td style='background-color:#00a2d1; text-align:center;'><a href='http://programacion.net/articulos/php' style='color:#fff; text-decoration:none;'>PHP</a></td>
       <td style='background-color:#00a2d1; text-align:center;'><a href='http://programacion.net/articulos/ASP' style='color:#fff; text-decoration:none;' >ASP</a></td>
       <td style='background-color:#00a2d1; text-align:center;'><a href='http://programacion.net/articulos/java' style='color:#fff; text-decoration:none;' >Java</a></td>
      </tr>
      
      <tr>
       <td colspan='4' style='padding:15px;'>
        <p style='font-size:20px;'>Hi' ".$full_name.",</p>
        <hr />
        <p style='font-size:25px;'>Sending HTML eMail using PHPMailer</p>
        <img src='https://4.bp.blogspot.com/-rt_1MYMOzTs/VrXIUlYgaqI/AAAAAAAAAaI/c0zaPtl060I/s1600/Image-Upload-Insert-Update-Delete-PHP-MySQL.png' alt='Sending HTML eMail using PHPMailer in PHP' title='Sending HTML eMail using PHPMailer in PHP' style='height:auto; width:100%; max-width:100%;' />
        <p style='font-size:15px; font-family:Verdana, Geneva, sans-serif;'>".$text_message.".</p>
       </td>
      </tr>
      
      </tbody>";
    
   $message .= "</table>";
   
   $message .= "</td></tr>";
   $message .= "</table>";
   
   $message .= "</body></html>";
   
   // HTML email ends here
   
   try
   {
    $mail->IsSMTP(); 
    $mail->isHTML(true);
    $mail->SMTPDebug  = 0;                     
    $mail->SMTPAuth   = true;                  
    $mail->SMTPSecure = "ssl";                 
    $mail->Host       = "smtp.gmail.com";      
    $mail->Port       = 465;             
    $mail->AddAddress($email);
    $mail->Username   ="your_gmail_id@gmail.com";  
                         $mail->Password   ="your_gmail_password";            
                         $mail->SetFrom('your_gmail_id@gmail.com','Programacion net');
                         $mail->AddReplyTo("your_gmail_id@gmail.com","Programacion net");
    $mail->Subject    = $subject;
    $mail->Body    = $message;
    $mail->AltBody    = $message;
     
    if($mail->Send())
    {
     
     $msg = "<div class='alert alert-success'>
       Hi,<br /> ".$full_name." mail was successfully sent to ".$email." go and check, cheers :)
       </div>";
     
    }
   }
   catch(phpmailerException $ex)
   {
    $msg = "<div class='alert alert-warning'>".$ex->errorMessage()."</div>";
   }
  } 
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sending HTML eMail using PHPMailer in PHP</title>
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="navbar navbar-default navbar-static-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="http://www.programacion.net" title='Programming Blog'>Programacion.net</a>
            <a class="navbar-brand" href="http://programacion.net/articulos/c">C</a>
            <a class="navbar-brand" href="http://programacion.net/articulos/php">PHP</a>
            <a class="navbar-brand" href="http://programacion.net/articulos/java">Java</a>
        </div>
    </div>
</div>


<div class="container">

 <div class="page-header">
     <h1>Send HTML eMails using PHPMailer in PHP</h1>
    </div>
     
    <div class="email-form">
    
     <?php
  if(isset($msg))
  {
   echo $msg;
  }
  ?>
        
     <form method="post" class="form-control-static">
        
            <div class="form-group">
                <input class="form-control" type="text" name="full_name" placeholder="Full Name" />
            </div>
            
            <div class="form-group">
                <input class="form-control" type="text" name="email" placeholder="Your Mail" />
            </div>
            
            <div class="form-group">
                <button class="btn btn-success" type="submit" name="btn_send">
                <span class="glyphicon glyphicon-envelope"></span> Send Mail
                </button>
            </div>
        
     </form>
    </div>    
</div>

</body>

</html>