<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA INDI - Control de Asistencia</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root { --azul-indi: #003366; --gris-fondo: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: var(--gris-fondo); display: flex; flex-direction: column; height: 100vh; }
        
        header { background: var(--azul-indi); color: white; text-align: center; padding: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 10; }
        .logo-contenedor { display: inline-block; transition: transform 0.5s ease; }
        .logo-contenedor:hover { transform: scale(1.1) rotate(5deg); }
        header img { width: 85px; height: 85px; border-radius: 50%; border: 3px solid white; background: white; object-fit: contain; }
        header h1 { margin: 10px 0 0 0; font-size: 1.6rem; text-transform: uppercase; }

        .main-wrapper { display: grid; grid-template-columns: 60% 40%; flex: 1; overflow: hidden; }

        #seccion-lector { padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: white; }
        

        #reader { 
            width: 100%; 
            max-width: 480px; 
            min-height: 300px; //
            border: 8px solid var(--azul-indi) !important; 
            border-radius: 20px; 
            background: #eee;
        }
        
        #mensaje-status { margin-top: 25px; padding: 15px 30px; border-radius: 50px; background: #666; color: white; font-weight: bold; font-size: 1.2rem; min-width: 350px; text-align: center; }

        #seccion-historial { padding: 25px; background: #eef2f3; border-left: 6px solid var(--azul-indi); overflow-y: auto; }
        .tarjeta { background: white; padding: 15px; border-radius: 12px; margin-bottom: 12px; border-left: 6px solid #28a745; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        
        button { background: var(--azul-indi) !important; color: white !important; border: none !important; padding: 10px 20px !important; border-radius: 8px !important; cursor: pointer !important; font-weight: bold !important; margin: 10px 0 !important; }
        /* Estilo para el Footer Profesional */
.footer-desarrollador {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.9); /* Fondo blanco con ligera transparencia */
    padding: 10px 20px;
    border-radius: 50px; /* Bordes redondeados tipo cápsula */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Sombra suave para dar profundidad */
    border-right: 5px solid #004a99; /* Línea de color institucional al lado derecho */
    transition: all 0.3s ease; /* Efecto de transición suave */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    z-index: 1000;
}

/* Efecto al pasar el mouse (Hover) */
.footer-desarrollador:hover {
    transform: translateY(-5px); /* Se eleva un poquito */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    background: #ffffff; /* Se vuelve sólido al tocarlo */
}

.footer-desarrollador p {
    margin: 0;
    font-size: 13px;
    color: #333;
}

.footer-desarrollador span {
    color: #004a99;
    font-weight: bold;
}
    </style>
</head>
<body>

<header>
    <div class="logo-contenedor">
        <img src="logo.jpg" alt="Logo INDI">
    </div>
    <h1>REGISTRO DE ASISTENCIA<br> Instituto Nacional de Ilobasco</h1>
</header>

<div class="main-wrapper">
    <section id="seccion-lector">
        <h2 style="color: var(--azul-indi); margin-bottom: 15px;">Control de Asistencia</h2>
        <div id="reader"></div> <div id="mensaje-status">Esperando cámara...</div>
    </section>

    <section id="seccion-historial">
        <h3 style="color: var(--azul-indi); border-bottom: 3px solid var(--azul-indi); padding-bottom: 10px;">Registros Recientes</h3>
        <div id="contenedor-registros">
            <p style="text-align: center; color: #999;">Listo para escanear.</p>
        </div>
    </section>
</div>

<script>
    let escaneando = true;

    function onScanSuccess(decodedText) {
        if (!escaneando) return;
        escaneando = false;
        html5QrcodeScanner.pause();

        const audio = new Audio('https://www.soundjay.com/button/beep-07.mp3');
        audio.play().catch(e => console.log("Audio bloqueado"));

        const statusBox = document.getElementById('mensaje-status');
        statusBox.style.background = "#ffc107";
        statusBox.innerHTML = "Procesando: " + decodedText;

        let datos = new FormData();
        datos.append('nie', decodedText);

        fetch('registrar.php', { method: 'POST', body: datos })
        .then(res => res.text())
        .then(texto => {
            statusBox.style.background = "#28a745";
            statusBox.innerHTML = texto;
            
            const lista = document.getElementById('contenedor-registros');
            if(lista.innerHTML.includes("Listo para escanear")) lista.innerHTML = "";
            
            const hora = new Date().toLocaleTimeString();
            const nuevaTarjeta = document.createElement('div');
            nuevaTarjeta.className = 'tarjeta';
            nuevaTarjeta.innerHTML = `<strong>${texto}</strong><br><small>Hora: ${hora}</small>`;
            lista.insertBefore(nuevaTarjeta, lista.firstChild);

            setTimeout(() => {
                escaneando = true;
                html5QrcodeScanner.resume();
                statusBox.style.background = "#003366"; // Usamos color fijo para evitar errores de variable
                statusBox.innerHTML = "Listo para el siguiente...";
            }, 3000); 
        })
        .catch(err => {
            escaneando = true;
            html5QrcodeScanner.resume();
        });
    }

    let html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
        fps: 15, 
        qrbox: {width: 250, height: 250}
    });
    html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
<div class="footer-desarrollador">
    <p>Desarrollado por: <span> Nelson E. Peña</span></p>
    <p><small>Contacto: nepv2308@live.com</small></p>
</div>
</html>