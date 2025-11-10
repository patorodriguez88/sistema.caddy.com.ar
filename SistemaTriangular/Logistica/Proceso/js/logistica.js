var checkbox = document.getElementById('recorrido_t_2');
checkbox.addEventListener("change", validaCheckbox, false);

function validaCheckbox()
{
  var checked = checkbox.checked;
  if(checked){
    
    document.getElementById('recorrido_t_2').disabled=true;
    
  }else{
    document.getElementById('recorrido_t_2').disabled=false;
  }
}
