<?php
date_default_timezone_set('America/El_Salvador'); // Establece la hora de El Salvador


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

include 'conexion.php'; 

if (isset($_POST['nie'])) {
    $nie = $_POST['nie'];
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');
    $registro_exitoso = false;
    $tipo_movimiento = "";
    $mensaje_resultado = ""; // Inicializamos la variable

    $consulta_estudiante = mysqli_query($conexion, "SELECT * FROM estudiantes WHERE nie = '$nie'");
    
    if (mysqli_num_rows($consulta_estudiante) > 0) {
        $datos_alumno = mysqli_fetch_assoc($consulta_estudiante);
        $nombre_completo = $datos_alumno['nombre'] . " " . $datos_alumno['apellido'];

        $chequeo_asistencia = mysqli_query($conexion, "SELECT * FROM asistencias WHERE nie_estudiante = '$nie' AND fecha = '$fecha_actual'");

        if (mysqli_num_rows($chequeo_asistencia) == 0) {
            // ENTRADA
            $insertar = mysqli_query($conexion, "INSERT INTO asistencias (nie_estudiante, fecha, hora_entrada, estado) VALUES ('$nie', '$fecha_actual', '$hora_actual', 'Presente')");
            $mensaje_resultado = "ENTRADA REGISTRADA: " . $nombre_completo; 
            $tipo_movimiento = "ENTRADA";
            $registro_exitoso = true;
        } else {
            // SALIDA
            $actualizar = mysqli_query($conexion, "UPDATE asistencias SET hora_salida = '$hora_actual' WHERE nie_estudiante = '$nie' AND fecha = '$fecha_actual'");
            $mensaje_resultado = "SALIDA REGISTRADA: " . $nombre_completo;
            $tipo_movimiento = "SALIDA";
            $registro_exitoso = true;
        }

       
        if ($registro_exitoso && !empty($datos_alumno['correo_responsable'])) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'itsiindi718@gmail.com'; 
                $mail->Password   = '********* ***********'; //colocar la contraseña de aplicacion aqui
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('itsiindi718@gmail.com', 'Asistencia INDI');
                $mail->addAddress($datos_alumno['correo_responsable'], $datos_alumno['nombre_responsable']);

                $mail->isHTML(true);
                $mail->Subject = "Notificación de $tipo_movimiento - $nombre_completo";
                $mail->Body    = "Señor/a padre o madre de familia,le informamos que  su hijo/a $nombre_completo ha registrado su $tipo_movimiento del INDI a las " . date('h:i A');
                $mail->send();
            } catch (Exception $e) {
                // Error silencioso
            }
        }
    } else {
        $mensaje_resultado = "ERROR: Estudiante no encontrado.";
    }
}
?>

<?php if (!empty($mensaje_resultado)): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 20px; border-radius: 10px; border-left: 8px solid #28a745; margin: 20px 0; font-family: Arial, sans-serif;">
        <h2 style="margin: 0;"><?php echo $mensaje_resultado; ?></h2>
        <p style="font-size: 1.2em; margin: 5px 0 0 0;">
            <strong>Hora:</strong> <?php echo $hora_actual; ?>
        </p>
    </div>
<?php endif; ?>
