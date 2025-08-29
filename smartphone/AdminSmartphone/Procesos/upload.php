<?php
if (($_FILES["file"]["type"] == "image/pjpeg")
    || ($_FILES["file"]["type"] == "image/jpeg")
    || ($_FILES["file"]["type"] == "image/png")
    || ($_FILES["file"]["type"] == "image/gif")) {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], "../../../images/Photos/".$_FILES['file']['name'])) {
//     echo json_encode(array('success'=> 1));
      echo 'SI';
    } else {
      echo 'NO';
//     echo json_encode(array('success'=> 0));    
    }
}
?>