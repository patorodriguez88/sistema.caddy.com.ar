// const boton = document.getElementById('.boton_abrir');
// const sidebar = document.querySelector(".right-bar");
const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  
document.addEventListener('DOMContentLoaded', function() {
    // Obtén el botón
    var mostrarSidebarBtn = document.getElementById('mostrarSidebarBtn');

    // Agrega un evento de clic al botón
    mostrarSidebarBtn.addEventListener('click', function() {
        // Obtén el elemento body
        // var body = document.body;
        // body.classList.addClass('right-bar-enabled');
        // body.add('right-bar-enabled');
        
        // body.classList.toggleClass('right-bar-enabled');
        // sidebar.classList.toggle("right-bar-enabled");
        // console.log('class',sidebar.classList.contains());
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl)
          })
        // sidebar.classList.toggle('active');

        // sidebar.classList.add("right-bar-enabled");
        // document.getElementsByClassName('sidebar')[0].classList.toggle('active');

        // document.getElementsByClassName('sidebar')[0].classList.toggle('active');
// body.classList.remove('right-bar-enabled').addClass('rig')
        // Agrega o quita la clase right-bar-enabled según su presencia actual
        // if (body.classList.contains('right-bar-enabled')) {
        //     body.classList.remove('right-bar-enabled');
        // } else {
        //     body.classList.add('right-bar-enabled');
        // }
        // const offcanvasElementList = document.querySelectorAll('.offcanvas')
        // const offcanvasList = [...offcanvasElementList].map(offcanvasEl => new bootstrap.Offcanvas(offcanvasEl))
    });
});


// boton.addEventListener("click", function() {
 
//     sidebar.classList.toggle("active");
    
//    }
   


document.addEventListener('DOMContentLoaded', function() {
    // Obtén el elemento body
    var body = document.querySelector('body');

    console.log('al cargar',body);
    // Agrega la clase right-bar-enabled
    // body.classList.add('right-bar-enabled');
});
