<?php
// if(!isset($_GET['text']) or !isset($_GET['phone'])){ die('Not enough data');}

// $apiURL = 'https://api.chat-api.com/instanceYYYYY/';
// $token = 'abcdefgh12345678';

// $message = $_GET['text'];
// $phone = $_GET['phone'];

// $data = json_encode(
//     array(
//         'chatId'=>$phone.'@c.us',
//         'body'=>$message
//     )
// );
// $url = $apiURL.'message?token='.$token;
// $options = stream_context_create(
//     array('http' =>
//         array(
//             'method'  => 'POST',
//             'header'  => 'Content-type: application/json',
//             'content' => $data
//         )
//     )
// );
// $response = file_get_contents($url,false,$options);
// echo $response; exit;
// ?>
<a href="https://api.whatsapp.com/send?phone=34695685920&text=hola,%20¿qué%20tal%20estás?">Mensaje</a>
<a title="Click para chatear" href="https://api.whatsapp.com/send?phone=5493516151944&text=Me%20gustaría%20saber%20el%20precio%20del%20sitio%20web" target="_blank" rel="noopener">Envíanos un mensaje por WhatsApp con un mensaje</a>