<?php
sleep(3);

$codUsuario = isset($_POST['codUsuario']) ? $_POST['codUsuario'] : '';
$apellidoUsuario = isset($_POST['apellidoUsuario']) ? $_POST['apellidoUsuario'] : '';
$nombreUsuario = isset($_POST['nombreUsuario']) ? $_POST['nombreUsuario'] : '';

$objUsuario = new stdClass;
$objUsuario->codUsuario = $codUsuario;
$objUsuario->apellidoUsuario = $apellidoUsuario;
$objUsuario->nombreUsuario = $nombreUsuario;

$jsonUsuario = json_encode($objUsuario);

echo $jsonUsuario;
?>