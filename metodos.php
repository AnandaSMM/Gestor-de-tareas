<?php
require_once './core/dbconex.php';

function crearTarea($titulo, $contenido, $fechaFin, $idUsuario, &$errores, &$mensajesBuenos)
{
    if (empty($titulo) || empty($contenido)) {
        $errores[] = "Asegúrese de rellenar todos los campos";
        return;
    }

    try {
        $titulo = htmlspecialchars(stripslashes(trim($titulo)));
        $contenido = htmlspecialchars(stripslashes(trim($contenido)));

        date_default_timezone_set('Europe/Madrid');
        $fechaIni = date('Y-m-d');

        if (!empty($fechaFin) && date('Y-m-d') >= $fechaFin) {
            $errores[] = "La fecha final no puede ser menor o igual que hoy";
            return;
        }

        $query = "INSERT INTO tarea 
                    (titulo, contenido, fechaFin, fechaIni, idUsuario, tipo) 
                  VALUES 
                    (:titulo, :contenido, :fechaFin, :fechaIni, :idUsuario, 'privada')";

        $stm = conex()->prepare($query);
        $stm->bindValue(':titulo', $titulo);
        $stm->bindValue(':contenido', $contenido);
        $stm->bindValue(':fechaFin', !empty($fechaFin) ? $fechaFin : null);
        $stm->bindValue(':fechaIni', $fechaIni);
        $stm->bindValue(':idUsuario', $idUsuario);
        $stm->execute();

        $mensajesBuenos[] = "Tarea privada agregada correctamente";

    } catch (PDOException $error) {
        echo "Error en agregar tarea: " . $error->getMessage();
    }
}

function crearTareaGeneral($titulo, $contenido, $fechaFin, $idAdmin, &$errores, &$mensajesBuenos)
{
    if (empty($titulo) || empty($contenido)) {
        $errores[] = "Rellena el título y el contenido de la tarea general.";
        return;
    }

    if (!esAdmin($idAdmin)) {
        $errores[] = "No tienes permisos para crear tareas generales.";
        return;
    }

    try {
        $titulo = htmlspecialchars(stripslashes(trim($titulo)));
        $contenido = htmlspecialchars(stripslashes(trim($contenido)));

        date_default_timezone_set('Europe/Madrid');
        $fechaIni = date('Y-m-d');

        if (!empty($fechaFin) && date('Y-m-d') >= $fechaFin) {
            $errores[] = "La fecha final no puede ser menor o igual que hoy.";
            return;
        }

        $query = "INSERT INTO tarea 
                    (titulo, contenido, fechaIni, fechaFin, tipo, idUsuario)
                  VALUES 
                    (:titulo, :contenido, :fechaIni, :fechaFin, 'general', :idUsuario)";

        $stm = conex()->prepare($query);
        $stm->bindValue(':titulo', $titulo);
        $stm->bindValue(':contenido', $contenido);
        $stm->bindValue(':fechaIni', $fechaIni);
        $stm->bindValue(':fechaFin', !empty($fechaFin) ? $fechaFin : null);
        $stm->bindValue(':idUsuario', $idAdmin);
        $stm->execute();

        $mensajesBuenos[] = "Tarea creada en la carpeta general.";

    } catch (PDOException $error) {
        echo "Error al crear tarea general: " . $error->getMessage();
    }
}

function asignarTareaGeneral($idTareaGeneral, $idUsuarioAsignado, &$errores, &$mensajesBuenos)
{
    if (empty($idTareaGeneral) || empty($idUsuarioAsignado)) {
        $errores[] = "Selecciona una tarea general y un usuario.";
        return;
    }

    try {
        $stm = conex()->prepare("
            SELECT titulo, contenido, fechaFin
            FROM tarea
            WHERE idTarea = :idTarea
            AND tipo = 'general'
        ");

        $stm->bindValue(':idTarea', $idTareaGeneral);
        $stm->execute();

        $tareaGeneral = $stm->fetch(PDO::FETCH_ASSOC);

        if (!$tareaGeneral) {
            $errores[] = "No se encontró la tarea general.";
            return;
        }

        date_default_timezone_set('Europe/Madrid');
        $fechaIni = date('Y-m-d');

        $query = "INSERT INTO tarea 
                    (titulo, contenido, fechaIni, fechaFin, tipo, idUsuario)
                  VALUES 
                    (:titulo, :contenido, :fechaIni, :fechaFin, 'asignada', :idUsuario)";

        $stm = conex()->prepare($query);
        $stm->bindValue(':titulo', $tareaGeneral['titulo']);
        $stm->bindValue(':contenido', $tareaGeneral['contenido']);
        $stm->bindValue(':fechaIni', $fechaIni);
        $stm->bindValue(':fechaFin', $tareaGeneral['fechaFin']);
        $stm->bindValue(':idUsuario', $idUsuarioAsignado);
        $stm->execute();

        $mensajesBuenos[] = "Tarea asignada correctamente.";

    } catch (PDOException $error) {
        echo "Error al asignar tarea general: " . $error->getMessage();
    }
}

function editarTarea($titulo, $contenido, $fechaFin, $idUsuario, &$errores, &$mensajesBuenos, $idTarea){
    if (empty($titulo) && empty($contenido)) { // que los dos no esten vacios
        $errores[] = "Asegurese de al menos un campo";
        return ;
    } else {
        try {
            $titulo = htmlspecialchars(stripcslashes(trim($titulo)));
            $contenido = htmlspecialchars(stripcslashes(trim($contenido)));
            if (empty($fechaFin)) {
                $query = "UPDATE tarea SET titulo = :titulo, contenido = :contenido  WHERE idTarea = :idTarea AND idUsuario = :idUsuario";
                $stm = conex()->prepare($query);
                $stm->bindValue(':titulo', $titulo);
                $stm->bindValue(':contenido', $contenido);
                $stm->bindValue(':idTarea', $idTarea); ///pasar el id por la sesion
                $stm->bindValue(':idUsuario', $idUsuario);
                $stm->execute();
            } else {
                if(date('Y-m-d')>= $fechaFin){
                    $errores[] = "La fecha del final de la tarea no puede ser menor que la fecha de hoy";
                    return ;
                }
                $query = "UPDATE tarea SET titulo = :titulo, contenido = :contenido, fechaFin = :fechaFin WHERE idTarea = :idTarea AND idUsuario = :idUsuario";
                $stm = conex()->prepare($query);
                $stm->bindValue(':titulo', $titulo);
                $stm->bindValue(':contenido', $contenido);
                $stm->bindValue(':fechaFin', $fechaFin);
                $stm->bindValue(':idTarea', $idTarea);
                $stm->bindValue(':idUsuario', $idUsuario); ///pasar el id por la sesion
                $stm->execute();
                
            }
            if ($stm->rowCount() > 0) {
                $mensajesBuenos[] = "Tarea modificada correctamente";
            } else {
                $errores[] = "No se encontró ninguna tarea que modificar o los datos son iguales.";
            }
        } catch (PDOException $error) {
            echo "Error en agregar : " . $error->getMessage();
        }
    }
}

function eliminarTarea($idTarea,&$mensajesBuenos ){
    try {
        $stm = conex()->prepare("DELETE FROM tarea WHERE idTarea = :idtarea");
        $stm->bindParam(':idtarea', $idTarea, PDO::PARAM_INT);
        $stm->execute();
        $mensajesBuenos[] = "Tarea eliminada correctamente";
    } catch (PDOException $error) {
        echo "Error en eliminar tarea : " . $error->getMessage();
    }
}

function buscarTarea($idTarea){
    try {
        $stm = conex()->prepare("SELECT * FROM tarea WHERE idTarea = :idTarea");
        $stm->bindValue(':idTarea', $idTarea);
        $stm->execute();
        $tareas = $stm->fetch();
        return $tareas;
    } catch (PDOException $error) {
        echo "Error en buscar tarea : " . $error->getMessage();
    }
}

function crearCarpetas($nombre)
{
    try {
        $query = "INSERT INTO carpeta (nombre, idUsuario) VALUES (:nombre, :idUsuario)";
        $stm = conex()->prepare($query);
        $stm->bindValue(':nombre', $nombre);
        $stm->bindValue(':idUsuario', $_SESSION['usuario']);
        $stm->execute();
    } catch (PDOException $error) {
        echo "error en crer carpetas: " . $error->getMessage();
    }
}

function eliminarCarpeta($id)
{
    try {
        $stm = conex()->prepare("UPDATE tarea SET idCarpeta = null WHERE idCarpeta = :idcarpeta");
        $stm->bindValue(':idcarpeta', $id);
        $stm->execute();
        $stm = conex()->prepare("DELETE FROM carpeta WHERE idCarpeta = :id ");
        $stm->bindValue(':id', $id);
        $stm->execute();
    } catch (PDOException $error) {
        echo "error en eliminar carpetas: " . $error->getMessage();
    }
}

//agregar tareas a carpetas
function agregarTareaAcarpeta($idTarea, $idCarpeta)
{
    try {
        $stm = conex()->prepare("
            UPDATE tarea 
            SET idCarpeta = :idCarpeta 
            WHERE idTarea = :idTarea
            AND idUsuario = :idUsuario
            AND tipo = 'privada'
        ");

        $stm->bindValue(':idTarea', $idTarea);
        $stm->bindValue(':idCarpeta', $idCarpeta);
        $stm->bindValue(':idUsuario', $_SESSION['usuario']);
        $stm->execute();

    } catch (PDOException $error) {
        echo "Error al agregar tarea a carpeta: " . $error->getMessage();
    }
}

//sacar tareas de carpetas
function eliminarTareaDeCarpeta($idTarea){
    try {
        $stm = conex()->prepare("UPDATE tarea SET idCarpeta = null WHERE idTarea = :idTarea");
        $stm->bindValue(':idTarea', $idTarea);
        $stm->execute();
    } catch (PDOException $error) {
        echo "error al eliminar de la carpeta: " . $error->getMessage();
    }
}

//buscar carpeta en tareas
function buscarCarpetaEnTareas($idCarpeta){
    try {
        $stm = conex()->prepare("SELECT idCarpeta FROM tarea WHERE idCarpeta = :idCarpeta");
        $stm->bindValue(':idCarpeta', $idCarpeta);
        $stm->execute();
        $esta = $stm->fetch();
        return $esta;
    } catch (PDOException $error) {
        echo "error al agregar: " . $error->getMessage();
    } 
}

//eliminar usuarios, solo admin
function eliminarUsuarios($id){
    try {
        $stm = conex()->prepare("DELETE FROM tarea WHERE idUsuario = :idUsuario");
        $stm->bindValue(':idUsuario', $id);
        $stm->execute();
        $stm = conex()->prepare("DELETE FROM carpeta WHERE idUsuario = :idUsuario");
        $stm->bindValue(':idUsuario', $id);
        $stm->execute();
        $stm = conex()->prepare("DELETE FROM usuario WHERE id = :idUsuario");
        $stm->bindValue(':idUsuario', $id);
        $stm->execute();
    } catch (PDOException $error) {
        echo "Error en eliminar usuario : " . $error->getMessage();
    }
}


function esAdmin($id){
    try {
        $stm = conex()->prepare("SELECT admin FROM usuario WHERE id = :id");
        $stm->bindValue(':id', $id);
        $stm->execute();
        $usuarios = $stm->fetch(PDO::FETCH_ASSOC);
        return $usuarios && $usuarios['admin']==1;
    } catch (PDOException $error) {
        echo "Error en es admin  : " . $error->getMessage();
        return false;
    }
}

function esSuperAdmin($id)
{
    return $id == 1;
}

function hacerAdmin($idUsuario, &$mensajesBuenos)
{
    try {
        $stm = conex()->prepare("UPDATE usuario SET admin = 1 WHERE id = :id");
        $stm->bindValue(':id', $idUsuario);
        $stm->execute();

        $mensajesBuenos[] = "Usuario convertido en administrador correctamente.";
    } catch (PDOException $error) {
        echo "Error al hacer admin: " . $error->getMessage();
    }
}

function quitarAdmin($idUsuario, &$mensajesBuenos)
{
    try {
        $stm = conex()->prepare("UPDATE usuario SET admin = 0 WHERE id = :id AND id != 1");
        $stm->bindValue(':id', $idUsuario);
        $stm->execute();

        $mensajesBuenos[] = "Administrador eliminado correctamente.";
    } catch (PDOException $error) {
        echo "Error al quitar admin: " . $error->getMessage();
    }
}

function cambiarEstadoTarea($idTarea, $estado, &$errores, &$mensajesBuenos)
{
    $estadosPermitidos = ['pendiente', 'en_curso', 'finalizada', 'cancelada'];

    if (!in_array($estado, $estadosPermitidos)) {
        $errores[] = "Estado no válido.";
        return;
    }

    try {
        $stm = conex()->prepare("
            UPDATE tarea
            SET estado = :estado
            WHERE idTarea = :idTarea
            AND idUsuario = :idUsuario
        ");

        $stm->bindValue(':estado', $estado);
        $stm->bindValue(':idTarea', $idTarea);
        $stm->bindValue(':idUsuario', $_SESSION['usuario']);
        $stm->execute();

        $mensajesBuenos[] = "Estado actualizado correctamente.";

    } catch (PDOException $error) {
        echo "Error al cambiar estado: " . $error->getMessage();
    }
}

//mostrar
function mostrarTareasAsignadas()
{
    try {
        $stm = conex()->prepare("
            SELECT idTarea, titulo, contenido, fechaIni, fechaFin, tipo, estado
            FROM tarea 
            WHERE idUsuario = :id
            AND tipo = 'asignada'
            ORDER BY fechaIni DESC
        ");

        $stm->bindValue(':id', $_SESSION['usuario']);
        $stm->execute();

        return $stm->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $error) {
        echo "Error en mostrar tareas asignadas: " . $error->getMessage();
    }

    return [];
}

function mostrarTareasGenerales()
{
    try {
        $stm = conex()->prepare("
            SELECT idTarea, titulo, contenido, fechaIni, fechaFin, estado
            FROM tarea
            WHERE idUsuario = :idUsuario
            AND idCarpeta IS NULL
        ");

        $stm->bindValue(':idUsuario', $_SESSION['usuario']);
        $stm->execute();

        return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $error) {
        echo "Error al mostrar carpeta general: " . $error->getMessage();
    }

    return [];
}

function mostrarUsuarios()
{
    try {
        $stm = conex()->prepare("SELECT * FROM usuario");
        $stm->execute();
        $usuarios = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $usuarios;
    } catch (PDOException $error) {
        echo "Error en mostrar usuario : " . $error->getMessage();
    }
}

function mostrarCarpetas()
{
    try {
        $stm = conex()->prepare("SELECT * FROM carpeta WHERE idUsuario = :id");
        $stm->bindValue(':id', $_SESSION['usuario']);
        $stm->execute();
        $carpetas = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $carpetas;
    } catch (PDOException $error) {
        echo "Error en mostrar carpetas : " . $error->getMessage();
    }
}

function mostrarTareasEnCarpeta($idCarpeta)
{
    try {
        $stm = conex()->prepare("
            SELECT 
                carpeta.nombre AS nombre_carpeta,
                tarea.titulo AS titulo_tarea,
                tarea.contenido AS contenido_tarea,
                tarea.fechaIni AS fecha_inicio,
                tarea.fechaFin AS fecha_fin,
                tarea.tipo AS tipo_tarea,
                usuario.nombre AS nombre_usuario
            FROM carpeta
            INNER JOIN tarea ON carpeta.idCarpeta = tarea.idCarpeta
            INNER JOIN usuario ON tarea.idUsuario = usuario.id
            WHERE tarea.idCarpeta = :idCarpeta
            AND tarea.idUsuario = :idUsuario
            AND tarea.tipo = 'privada'
        ");

        $stm->bindValue(':idCarpeta', $idCarpeta);
        $stm->bindValue(':idUsuario', $_SESSION['usuario']);
        $stm->execute();

        return $stm->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $error) {
        echo "Error al mostrar tareas de la carpeta: " . $error->getMessage();
    }

    return [];
}

function mostrarTareas()
{
    try {
        $stm = conex()->prepare("
            SELECT idTarea, titulo, contenido, fechaIni, fechaFin, tipo, estado
            FROM tarea 
            WHERE idUsuario = :id
            AND tipo = 'privada'
            ORDER BY fechaIni DESC
        ");

        $stm->bindValue(':id', $_SESSION['usuario']);
        $stm->execute();

        return $stm->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $error) {
        echo "Error en mostrar tareas privadas: " . $error->getMessage();
    }

    return [];
}
