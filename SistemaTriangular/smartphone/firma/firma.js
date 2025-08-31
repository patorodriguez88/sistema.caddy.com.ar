
	    var ctx, color = "#000";

	    document.addEventListener("DOMContentLoaded", function () {
	        // setup a new canvas for drawing wait for device init
	        setTimeout(function () {
	            newCanvas();
	        }, 1000);
	    }, false);



	    // function to setup a new canvas for drawing
	    function newCanvas() {
	        //define and resize canvas

	        var canvas = '<canvas id="canvas" width="500px" height="300px"></canvas>';
	        document.getElementById("content").innerHTML = canvas;

	        // setup canvas
	        ctx = document.getElementById("canvas").getContext("2d");
	        ctx.strokeStyle = color;
	        ctx.lineWidth = 8;

	        // setup to trigger drawing on mouse or touch
	        drawTouch();
	     //   drawPointer();
	        drawMouse();

	      
	    }

	    function selectColor(el) {


	        ctx.beginPath();
	        ctx.strokeStyle = "#000";
	    }



	    // prototype to	start drawing on touch using canvas moveTo and lineTo
	    var drawTouch = function () {
	        var start = function (e) {
	            ctx.beginPath();
	            var divcanvas = document.getElementById("canvas");
	            x = e.changedTouches[0].pageX - getDimensions(divcanvas).x;
	            y = e.changedTouches[0].pageY - getDimensions(divcanvas).y;
	            ctx.moveTo(x, y);
	        };
	        var move = function (e) {
	            e.preventDefault();
	            var divcanvas = document.getElementById("canvas");
	            x = e.changedTouches[0].pageX - getDimensions(divcanvas).x;
	            y = e.changedTouches[0].pageY - getDimensions(divcanvas).y;
	            ctx.lineTo(x, y);
	            ctx.stroke();
	        };
	        document.getElementById("canvas").addEventListener("touchstart", start, false);
	        document.getElementById("canvas").addEventListener("touchmove", move, false);
	    };





	    // prototype to	start drawing on pointer(microsoft ie) using canvas moveTo and lineTo
	    var drawPointer = function () {
	        var start = function (e) {
	            e = e.originalEvent;
	            ctx.beginPath();
	            x = e.pageX;
	            y = e.pageY;
	            ctx.moveTo(x, y);
	        };
	        var move = function (e) {
	            e.preventDefault();
	            e = e.originalEvent;
	            x = e.pageX;
	            y = e.pageY;
	            ctx.lineTo(x, y);
	            ctx.stroke();
	        };
	        document.getElementById("canvas").addEventListener("MSPointerDown", start, false);
	        document.getElementById("canvas").addEventListener("MSPointerMove", move, false);
	    };


	    // prototype to	start drawing on mouse using canvas moveTo and lineTo
	    var drawMouse = function () {
	        var clicked = 0;
	        var start = function (e) {
	            clicked = 1;
	            ctx.beginPath();


	            var divcanvas = document.getElementById("canvas");

	            x = e.pageX - getDimensions(divcanvas).x;
	            y = e.pageY - getDimensions(divcanvas).y;
	            ctx.moveTo(x, y);



	        };
	        var move = function (e) {
	            if (clicked) {

	                var divcanvas = document.getElementById("content");

	                x = e.pageX - getDimensions(divcanvas).x;
	                y = e.pageY - getDimensions(divcanvas).y;
	                ctx.lineTo(x, y);
	                ctx.stroke();
	         
	            }
	        };
	        var stop = function (e) {
	            clicked = 0;
	        };
	        document.getElementById("canvas").addEventListener("mousedown", start, false);
	        document.getElementById("canvas").addEventListener("mousemove", move, false);
	        document.addEventListener("mouseup", stop, false);
	    };







	    getDimensions = function (oElement) {
	        var x, y, w, h;
	        x = y = w = h = 0;
	        if (document.getBoxObjectFor) { // Mozilla
	            var oBox = document.getBoxObjectFor(oElement);
	            x = oBox.x - 1;
	            w = oBox.width;
	            y = oBox.y - 1;
	            h = oBox.height;
	        }
	        else if (oElement.getBoundingClientRect) { // IE
	            var oRect = oElement.getBoundingClientRect();
	            x = oRect.left - 2;
	            w = oElement.clientWidth;
	            y = oRect.top - 2;
	            h = oElement.clientHeight;
	        }
	        return { x: x, y: y, w: w, h: h };
	    }
    // Send the drawn image to the server
        $('#sendBtn').live('click', function () {
            var Pic = document.getElementById("canvas").toDataURL("image/jpg");
            Pic = Pic.replace(/^data:image\/(png|jpg);base64,/, "")

            // Sending the image data to Server
            $.ajax({
                type: 'POST',
                url: 'WebForm2.aspx/UploadPic',
                data: '{ "imageData" : "' + Pic + '" }',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                success: function (msg) {
                    alert("Done, Picture Uploaded.");
                }
            });
        });
    