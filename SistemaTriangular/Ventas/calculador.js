              if(dimensiones<4860 && varpeso<2){
                  if(res<25){
                  costo=150;
                  var tarifa='1 | A';  
                  }else if(res<50){
                  costo=200;  
                  var tarifa='1 | B';  
                  }else if(res>51){
                  costo=250;
                  var tarifa='1 | C';  
                  }
               }else if(dimensiones<5460 && varpeso<4){
                  if(res<25){  
                  costo=180;
                  var tarifa='2 | A';  
  
                  }else if(res<50){
                  costo=240; 
                  var tarifa='2 | B';  

                  }else if(res>51){
                  costo=300; 
                  var tarifa='2 | C';  

                  }
               }else if(dimensiones<18375 && varpeso<10){
                if(res<25){  
                costo=220; 
                var tarifa='3 | A';  

                }else if(res<50){
                costo=295;  
                var tarifa='3 | B';  

                }else if(res>51){
                costo=365;    
                var tarifa='3 | C';  

                }
               }else if(dimensiones<22050 && varpeso<=15){ //tarifa 4
                if(res<25){  
                costo=250;  
                var tarifa='4 | A';  

                }else if(res<335){
                costo=335;  
                var tarifa='4 | B';  

                }else if(res>420){
                costo=420;    
                var tarifa='4 | C';  

                }
               }else if(dimensiones<42875 && varpeso<=20){ //TARIFA 5
                  if(res<25){  
                  costo=300;  
                  var tarifa='5 | A';  

                  }else if(res<335){
                  costo=400;  
                  var tarifa='5 | B';  

                  }else if(res>51){
                  costo=500;    
                  var tarifa='5 | C';  

                  }
               }else if(dimensiones<64000 && varpeso<=25){
                  if(res<25){  
                  costo=350; 
                  var tarifa='6 | A';  

                  }else if(res<50){
                  costo=465;  
                  var tarifa='6 | B';  

                  }else if(res>51){
                  costo=585;    
                  var tarifa='6 | C';  

                  }
               }else if(dimensiones<80000 && varpeso<=25){
                  if(res<25){  
                  costo=400;  
                  var tarifa='7 | A';  

                  }else if(res<50){
                  costo=535;  
                  var tarifa='7 | B';  

                  }else if(res>51){
                  costo=665;    
                  var tarifa='7 | C';  

                  }
               }else if(dimensiones<99000 && varpeso<=25){
                  if(res<25){  
                  costo=500;  
                  var tarifa='8 | A';  

                  }else if(res<50){
                  costo=665;  
                  var tarifa='8 | B';  

                  }else if(res>51){
                  costo=850;    
                  var tarifa='8 | C';  

                  }
               }
