<?php
// canvas events
  canvas.onmousedown ( function(e) {
    draw(e.layerX, e.layerY);
    mousePressed = true;
  });
 
  canvas.onmousemove = function(e) {
    if (mousePressed) {
      draw(e.layerX, e.layerY);
    }
  };
 
  canvas.onmouseup = function(e) {
    mousePressed = false;
  };
   
  canvas.onmouseleave = function(e) {
    mousePressed = false;
  };
 
function draw(x, y) {
  if (mousePressed) {
    ctx.beginPath();
    ctx.strokeStyle = document.getElementById('color').value;
    ctx.lineWidth = 1;
    ctx.lineJoin = 'round';
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(x, y);
    ctx.closePath();
    ctx.stroke();
  }
  lastX = x; lastY = y;
}
?>
