<?php
require_once './core/dbconex.php';
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

require_once 'metodos.php';

$errores = [];
$mensajesBuenos = [];

/* -------------------- ACCIONES CARPETAS -------------------- */

if (isset($_POST['nuevaCarpeta']) && !empty($_POST['nombreCarpeta'])) {
    crearCarpetas($_POST['nombreCarpeta']);
    $mensajesBuenos[] = "Carpeta creada correctamente";
}

if (isset($_POST['eliminarcarpeta'])) {
    eliminarCarpeta($_POST['idCarpeta']);
    $mensajesBuenos[] = "Carpeta eliminada correctamente.";
}

if (isset($_POST['tareaCarpeta']) && isset($_POST['tareaSeleccionada'])) {
    agregarTareaAcarpeta($_POST['tareaSeleccionada'], $_POST['idCarpeta']);
    $mensajesBuenos[] = "Tarea añadida a la carpeta correctamente.";
}

/* -------------------- ACCIONES TAREAS PRIVADAS -------------------- */

$limpiarCampos = false;
$contenidoText = '';

if (isset($_POST['botonAgregarTarea'])) {
    crearTarea(
        $_POST['titulotareas'],
        $_POST['contenido'],
        $_POST['fechaFin'],
        $_SESSION['usuario'],
        $errores,
        $mensajesBuenos
    );

    if (empty($errores)) {
        $limpiarCampos = true;
    }
}

if (isset($_POST['eliminarTarea'])) {
    eliminarTarea($_POST['idTarea'], $mensajesBuenos);
}

if (isset($_POST['editarTarea'])) {
    $tareaEncontrada = buscarTarea($_POST['idTarea']);
    $_POST['titulotareas'] = $tareaEncontrada['titulo'];
    $contenidoText = $tareaEncontrada['contenido'];
    $_POST['fechaFin'] = $tareaEncontrada['fechaFin'];
}

if (isset($_POST['modificarTarea'])) {
    editarTarea(
        $_POST['titulotareas'],
        $_POST['contenido'],
        $_POST['fechaFin'],
        $_SESSION['usuario'],
        $errores,
        $mensajesBuenos,
        $_POST['idEditarTarea']
    );

    $_POST['titulotareas'] = '';
    $contenidoText = '';
    $_POST['fechaFin'] = '';
    $_POST['idEditarTarea'] = '';
}

if (isset($_POST['cambiarEstadoTarea'])) {
    cambiarEstadoTarea(
        $_POST['idTarea'],
        $_POST['estado'],
        $errores,
        $mensajesBuenos
    );
}

/* -------------------- ACCIONES ADMIN -------------------- */

if (isset($_POST['eliminarUsuario']) && esSuperAdmin($_SESSION['usuario'])) {
    eliminarUsuarios($_POST['idUsuario']);
    $mensajesBuenos[] = "Usuario eliminado correctamente.";
}

if (isset($_POST['hacerAdmin']) && esSuperAdmin($_SESSION['usuario'])) {
    hacerAdmin($_POST['idUsuario'], $mensajesBuenos);
}

if (isset($_POST['quitarAdmin']) && esSuperAdmin($_SESSION['usuario'])) {
    quitarAdmin($_POST['idUsuario'], $mensajesBuenos);
}

if (isset($_POST['crearTareaGeneral']) && esAdmin($_SESSION['usuario'])) {
    crearTareaGeneral(
        $_POST['tituloGeneral'],
        $_POST['contenidoGeneral'],
        $_POST['fechaFinGeneral'],
        $_SESSION['usuario'],
        $errores,
        $mensajesBuenos
    );
}

if (isset($_POST['asignarTareaGeneral']) && esAdmin($_SESSION['usuario'])) {
    asignarTareaGeneral(
        $_POST['tareaGeneral'],
        $_POST['usuarioAsignado'],
        $errores,
        $mensajesBuenos
    );
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio | Gestor de tareas</title>
    <link rel="stylesheet" href="./estilos/estiloInicio.css">
</head>

<body>

<main class="app">

    <header class="header">
        <div>
            <h1>Gestor de tareas</h1>
            <p>Bienvenida, <?= $_SESSION['nombre'] ?></p>
        </div>

        <a href="cerrarSesion.php" class="logout">Cerrar sesión</a>
    </header>

    <section class="cuaderno">

        <form method="get" class="formGeneral">
            <input type="submit" name="crearTareas" value="Tareas privadas">
            <input type="submit" name="tareasAsignadas" value="Tareas asignadas">
            <input type="submit" name="carpeta" value="Carpetas personales">
            <input type="submit" name="carpetaGeneral" value="Carpeta general">

            <?php if (esAdmin($_SESSION['usuario'])) { ?>
                <input type="submit" name="vistaAsignarTareas" value="Asignar tareas">
            <?php } ?>

            <?php if (esSuperAdmin($_SESSION['usuario'])) { ?>
                <input type="submit" name="mostrarUsuario" value="Usuarios">
            <?php } ?>
        </form>

        <!-- -------------------- USUARIOS / SUPER ADMIN -------------------- -->

        <?php if (isset($_GET['mostrarUsuario']) && esSuperAdmin($_SESSION['usuario'])) { ?>

            <section class="seccion">
                <h2>Usuarios registrados</h2>

                <div class="tablaContenedor">
                    <table class="tablaUsuarios">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo electrónico</th>
                                <th>Rol</th>
                                <th>Administración</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach (mostrarUsuarios() as $usuario) { ?>
                                <tr>
                                    <td><?= $usuario['nombre'] ?></td>
                                    <td><?= $usuario['correo'] ?></td>

                                    <td>
                                        <?php if ($usuario['id'] == 1) { ?>
                                            Super admin
                                        <?php } elseif ($usuario['admin']) { ?>
                                            Admin
                                        <?php } else { ?>
                                            Usuario
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <?php if ($usuario['id'] != 1) { ?>
                                            <form method="post" class="acciones">
                                                <input type="hidden" name="idUsuario" value="<?= $usuario['id'] ?>">

                                                <?php if ($usuario['admin']) { ?>
                                                    <input type="submit" name="quitarAdmin" value="Quitar admin" class="btn btn-secondary">
                                                <?php } else { ?>
                                                    <input type="submit" name="hacerAdmin" value="Hacer admin" class="btn btn-primary">
                                                <?php } ?>

                                                <input type="submit" name="eliminarUsuario" value="Eliminar" class="btn btn-danger">
                                            </form>
                                        <?php } else { ?>
                                            <span>No editable</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php } ?>

        <!-- -------------------- GESTIÓN ADMIN: CREAR Y ASIGNAR TAREAS -------------------- -->

        <?php if (isset($_GET['vistaAsignarTareas']) && esAdmin($_SESSION['usuario'])) { ?>

            <section class="seccion">
                <h2>Gestión de tareas generales</h2>

                <div class="adminGrid">

                    <article class="panelFormulario">
                        <h3>Crear tarea en carpeta general</h3>

                        <form method="post" class="formSelect">

                            <input 
                                type="text" 
                                name="tituloGeneral" 
                                class="campo" 
                                placeholder="Título de la tarea general">

                            <textarea 
                                name="contenidoGeneral" 
                                class="campo" 
                                placeholder="Contenido de la tarea general"
                                rows="5"></textarea>

                            <input 
                                type="date" 
                                name="fechaFinGeneral" 
                                min="<?= date('Y-m-d') ?>"
                                class="campo">

                            <input 
                                type="submit" 
                                name="crearTareaGeneral" 
                                value="Crear tarea general" 
                                class="btn btn-primary">

                        </form>
                    </article>

                    <article class="panelFormulario">
                        <h3>Asignar tarea general</h3>

                        <form method="post" class="formSelect">

                            <select name="tareaGeneral" class="campo">
                                <option disabled selected>Selecciona una tarea general</option>

                                <?php foreach (mostrarTareasGenerales() as $tarea) { ?>
                                    <option value="<?= $tarea['idTarea'] ?>">
                                        <?= $tarea['titulo'] ?>
                                    </option>
                                <?php } ?>
                            </select>

                            <select name="usuarioAsignado" class="campo">
                                <option disabled selected>Selecciona un usuario</option>

                                <?php foreach (mostrarUsuarios() as $usuario) { ?>
                                    <?php if ($usuario['id'] != $_SESSION['usuario']) { ?>
                                        <option value="<?= $usuario['id'] ?>">
                                            <?= $usuario['nombre'] ?> - <?= $usuario['correo'] ?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>

                            <input 
                                type="submit" 
                                name="asignarTareaGeneral" 
                                value="Asignar tarea" 
                                class="btn btn-primary">

                        </form>
                    </article>

                </div>
            </section>

        <?php } ?>

        <!-- -------------------- CARPETA GENERAL -------------------- -->

        <?php if (isset($_GET['carpetaGeneral'])) { ?>

            <section class="seccion">
                <h2>Carpeta general</h2>

                <?php if (!empty(mostrarTareasGenerales())) { ?>

                    <div class="tablaContenedor">
                        <table>
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Contenido</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach (mostrarTareasGenerales() as $tarea) { ?>
                                    <tr>
                                        <td><?= $tarea['titulo'] ?></td>
                                        <td><?= $tarea['contenido'] ?></td>
                                        <td><?= $tarea['fechaIni'] ?></td>
                                        <td><?= $tarea['fechaFin'] ?></td>
                                        <?php if (esAdmin($_SESSION['usuario'])) {?>
                                        <td>
                                            <form method="post">
                                                <input type="hidden" name="idTarea" value="<?= $tarea['idTarea'] ?>">

                                                <select name="estado" class="estadoSelect" onchange="this.form.submit()">
                                                    <option value="pendiente" <?= $tarea['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                                        Pendiente
                                                    </option>

                                                    <option value="en_curso" <?= $tarea['estado'] == 'en_curso' ? 'selected' : '' ?>>
                                                        En curso
                                                    </option>

                                                    <option value="finalizada" <?= $tarea['estado'] == 'finalizada' ? 'selected' : '' ?>>
                                                        Finalizada
                                                    </option>

                                                    <option value="cancelada" <?= $tarea['estado'] == 'cancelada' ? 'selected' : '' ?>>
                                                        Cancelada
                                                    </option>
                                                </select>

                                                <input type="hidden" name="cambiarEstadoTarea" value="1">
                                            </form>
                                        </td>
                                    <?php }else {?>
                                        <td
                                            style="<?php

                                                switch($tarea['estado']){

                                                    case 'pendiente':
                                                        echo 'background:white;';
                                                        break;

                                                    case 'en_curso':
                                                        echo 'background:#facc15;';
                                                        break;

                                                    case 'finalizada':
                                                        echo 'background:#22c55e;color:white;';
                                                        break;

                                                    case 'cancelada':
                                                        echo 'background:#4b5563;color:white;';
                                                        break;
                                                }

                                            ?>">
                                            <?= ucfirst(str_replace('_',' ',$tarea['estado'])) ?>
                                        </td>

                                    <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                <?php } else { ?>
                    <p class="noHay">No hay tareas en la carpeta general.</p>
                <?php } ?>

            </section>

        <?php } ?>

        <!-- -------------------- TAREAS ASIGNADAS -------------------- -->

        <?php if (isset($_GET['tareasAsignadas'])) { ?>

            <section class="seccion">
                <h2>Tareas asignadas</h2>

                <?php if (!empty(mostrarTareasAsignadas())) { ?>

                    <div class="tablaContenedor">
                        <table>
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Contenido</th>
                                    <th>Asignada</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach (mostrarTareasAsignadas() as $tarea) { ?>
                                    <tr>
                                        <td><?= $tarea['titulo'] ?></td>
                                        <td><?= $tarea['contenido'] ?></td>
                                        <td><?= $tarea['fechaIni'] ?></td>
                                        <td><?= $tarea['fechaFin'] ?></td>
                                        <td>
                                            <form method="post">
                                                <input type="hidden" name="idTarea" value="<?= $tarea['idTarea'] ?>">

                                                <select name="estado" class="estadoSelect" onchange="this.form.submit()">
                                                    <option value="pendiente" <?= $tarea['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                                        Pendiente
                                                    </option>

                                                    <option value="en_curso" <?= $tarea['estado'] == 'en_curso' ? 'selected' : '' ?>>
                                                        En curso
                                                    </option>

                                                    <option value="finalizada" <?= $tarea['estado'] == 'finalizada' ? 'selected' : '' ?>>
                                                        Finalizada
                                                    </option>

                                                    <option value="cancelada" <?= $tarea['estado'] == 'cancelada' ? 'selected' : '' ?>>
                                                        Cancelada
                                                    </option>
                                                </select>

                                                <input type="hidden" name="cambiarEstadoTarea" value="1">
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                <?php } else { ?>
                    <p class="noHay">Todavía no tienes tareas asignadas.</p>
                <?php } ?>

            </section>

        <?php } ?>

        <!-- -------------------- TAREAS PRIVADAS -------------------- -->

        <?php if (
            !isset($_GET['carpeta']) &&
            !isset($_GET['mostrarUsuario']) &&
            !isset($_GET['vistaAsignarTareas']) &&
            !isset($_GET['carpetaGeneral']) &&
            !isset($_GET['tareasAsignadas'])
        ) { ?>

            <section class="mostrarTareas">

                <article class="panelFormulario">
                    <h2><?= isset($_POST['editarTarea']) ? 'Editar tarea privada' : 'Nueva tarea privada' ?></h2>

                    <form method="post" id="formularioCrearTareas">

                        <input
                            type="text"
                            name="titulotareas"
                            class="campo"
                            placeholder="Título"
                            value="<?= $limpiarCampos ? '' : ($_POST['titulotareas'] ?? '') ?>">

                        <textarea
                            name="contenido"
                            class="campo"
                            placeholder="Contenido"
                            rows="6"><?= (!empty($contenidoText)) ? $contenidoText : '' ?></textarea>

                        <input
                            type="date"
                            name="fechaFin"
                            class="campo"
                            min="<?= date('Y-m-d') ?>"
                            id="fechaFin"
                            value="<?= $limpiarCampos ? '' : ($_POST['fechaFin'] ?? '') ?>">

                        <?php if (!isset($_POST['editarTarea'])) { ?>
                            <input type="submit" name="botonAgregarTarea" value="Agregar tarea" class="btn btn-primary">
                        <?php } else { ?>
                            <input type="hidden" name="idEditarTarea" value="<?= $_POST['idTarea'] ?>">
                            <input type="submit" name="modificarTarea" value="Guardar cambios" class="btn btn-edit">
                        <?php } ?>

                    </form>
                </article>

                <article class="panelListado">
                    <h2>Tareas privadas</h2>

                    <?php if (!empty(mostrarTareas())) { ?>

                        <div class="tablaContenedor">
                            <table class="tablaTareas">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Contenido</th>
                                        <th>Creación</th>
                                        <th>Fin</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach (mostrarTareas() as $tarea) { ?>
                                        <tr>
                                            <td><?= $tarea['titulo'] ?></td>
                                            <td><?= $tarea['contenido'] ?></td>
                                            <td><?= $tarea['fechaIni'] ?></td>
                                            <td><?= $tarea['fechaFin'] ?></td>
                                            <td>
                                                <form method="post">
                                                    <input type="hidden" name="idTarea" value="<?= $tarea['idTarea'] ?>">

                                                    <select name="estado" class="estadoSelect" onchange="this.form.submit()">
                                                        <option value="pendiente" <?= $tarea['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                                            Pendiente
                                                        </option>

                                                        <option value="en_curso" <?= $tarea['estado'] == 'en_curso' ? 'selected' : '' ?>>
                                                            En curso
                                                        </option>

                                                        <option value="finalizada" <?= $tarea['estado'] == 'finalizada' ? 'selected' : '' ?>>
                                                            Finalizada
                                                        </option>

                                                        <option value="cancelada" <?= $tarea['estado'] == 'cancelada' ? 'selected' : '' ?>>
                                                            Cancelada
                                                        </option>
                                                    </select>

                                                    <input type="hidden" name="cambiarEstadoTarea" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <form method="post" class="acciones">
                                                    <input type="hidden" name="idTarea" value="<?= $tarea['idTarea'] ?>">
                                                    <input type="submit" name="editarTarea" value="Editar" class="btn btn-edit">
                                                    <input type="submit" name="eliminarTarea" value="Eliminar" class="btn btn-danger">
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                    <?php } else { ?>
                        <p class="noHay">Todavía no tienes tareas privadas.</p>
                    <?php } ?>
                </article>

            </section>

        <?php } ?>

        <!-- -------------------- CARPETAS PERSONALES -------------------- -->

        <?php if (isset($_GET['carpeta'])) { ?>

            <section class="carpetasDiv">

                <article class="mostrarCarpeta">
                    <h2>Carpetas personales</h2>

                    <form method="post" class="creaCarpeta">
                        <input 
                            type="text" 
                            name="nombreCarpeta" 
                            placeholder="Nombre de la carpeta" 
                            class="campo">

                        <input 
                            type="submit" 
                            name="nuevaCarpeta" 
                            value="Crear carpeta" 
                            class="btn btn-primary">
                    </form>

                    <?php if (mostrarCarpetas()) { ?>

                        <div class="tablaContenedor">
                            <table class="tablaCarpeta">
                                <tbody>
                                    <?php foreach (mostrarCarpetas() as $carpeta) { ?>
                                        <tr>
                                            <td><?= $carpeta['nombre'] ?></td>
                                            <td>
                                                <form method="post" class="acciones">
                                                    <input type="hidden" name="idCarpeta" value="<?= $carpeta['idCarpeta'] ?>">
                                                    <input type="hidden" name="nombreCarpe" value="<?= $carpeta['nombre'] ?>">

                                                    <input type="submit" name="agregarTcarpeta" value="+ Tarea" class="btn btn-edit">
                                                    <input type="submit" name="eliminarcarpeta" value="Eliminar" class="btn btn-danger">

                                                    <?php if (!empty(buscarCarpetaEnTareas($carpeta['idCarpeta']))) { ?>
                                                        <input type="submit" name="verTareasEnCarpeta" value="Ver tareas" class="btn btn-secondary">
                                                    <?php } ?>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                    <?php } else { ?>
                        <p class="noHay">Todavía no tienes carpetas personales.</p>
                    <?php } ?>

                </article>

                <?php if (isset($_POST['agregarTcarpeta'])) { ?>

                    <article class="panelFormulario">
                        <h2>Añadir tarea privada a carpeta</h2>

                        <form method="post" name="selectTarea" class="formSelect">

                            <?php if (!empty(mostrarTareas())) { ?>

                                <select name="tareaSeleccionada" class="campo">
                                    <option disabled selected>Selecciona una tarea privada</option>

                                    <?php foreach (mostrarTareas() as $tarea) { ?>
                                        <option value="<?= $tarea['idTarea'] ?>">
                                            <?= $tarea['titulo'] ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <input type="hidden" name="carpeta">
                                <input type="hidden" name="idCarpeta" value="<?= $_POST['idCarpeta'] ?>">
                                <input type="submit" name="tareaCarpeta" value="Agregar" class="btn btn-primary">

                            <?php } else { ?>

                                <p class="noHay">No tienes tareas privadas para añadir.</p>

                            <?php } ?>

                        </form>
                    </article>

                <?php } ?>

                <?php if (isset($_POST['verTareasEnCarpeta'])) { ?>

                    <article class="panelListado">
                        <h2>Tareas de <?= $_POST['nombreCarpe'] ?></h2>

                        <div class="tablaContenedor">
                            <table class="tareasEnCarpeta">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Contenido</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach (mostrarTareasEnCarpeta($_POST['idCarpeta']) as $dato) { ?>
                                        <tr>
                                            <td><?= $dato['titulo_tarea'] ?></td>
                                            <td><?= $dato['contenido_tarea'] ?></td>
                                            <td><?= $dato['fecha_inicio'] ?></td>
                                            <td><?= $dato['fecha_fin'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                <?php } ?>

            </section>

        <?php } ?>

        <!-- -------------------- POPUP MENSAJES -------------------- -->

        <?php if (!empty($errores) || !empty($mensajesBuenos)) : ?>

            <div id="popup" class="popup <?= !empty($errores) ? 'error' : 'ok' ?>">
                <?php
                if (!empty($errores)) {
                    echo implode('<br>', $errores);
                } else {
                    echo implode('<br>', $mensajesBuenos);
                }
                ?>
            </div>

            <script>
                const popup = document.getElementById("popup");
                popup.classList.add("show");

                setTimeout(() => {
                    popup.classList.remove("show");
                }, 3000);
            </script>

        <?php endif; ?>

    </section>

</main>

</body>
</html>