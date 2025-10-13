<?php
if (isset($_POST['varDeEntrada'])) {
    sleep(3);
    
    $varDeEntrada = $_POST['varDeEntrada'];
    $claveEncriptadaMd5 = md5($varDeEntrada);
    $claveEncriptadaSha1 = sha1($varDeEntrada);
    
    $objDatos = new stdClass;
    $objDatos->valor = $varDeEntrada;
    $objDatos->encriptadaMd5 = $claveEncriptadaMd5;
    $objDatos->encriptadaSha1 = $claveEncriptadaSha1;
    
    
?>
<html>
<head>
    <title>Formulario</title>
</head>
<body>
    <div id="formulario">
        <h1>Ingrese dato de entrada:</h1>
        <form method="post" action="">
            <input type="text" name="varDeEntrada" required>
            <button type="submit">Enviar</button>
        </form>
        <div id="estado">
            <p>Estado del requerimiento: CUMPLIDO</p>
        </div>
    </div>
    
    <div id="encriptar">
        <h2>Encriptar</h2>
    </div>
    
    <div id="resultado">
        <h2>Resultado:</h2>
        <p>Request_method: <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
        <p>Clave: <?php echo htmlspecialchars($varDeEntrada); ?></p>
        <p>Clave encriptada en md5 (128 bits o 16 pares hexadecimales):</p>
        <p><?php echo $claveEncriptadaMd5; ?></p>
        <p>Clave encriptada en sha1 (160 bits o 20 pares hexadecimales):</p>
        <p><?php echo $claveEncriptadaSha1; ?></p>
      
    </div>
</body>
</html>
<?php
} else {
?>
<html>
<head>
    <title>Formulario</title>
</head>
<body>
    <div id="formulario">
        <h1>Ingrese dato de entrada:</h1>
        <form method="post" action="">
            <input type="text" name="varDeEntrada" required>
            <button type="submit">Enviar</button>
            <button class="volver-atras">
        <a class="volver-atras" href="../index.php">Volver atras</a>
    </button>
        </form>
        <div id="estado">
            <p>Estado del requerimiento:</p>
        </div>
    </div>
    
    <div id="encriptar">
        <h2>Encriptar</h2>
    </div>
    
    <div id="resultado">
        <h2>Resultado:</h2>
    </div>

    
</body>
</html>
<?php
}
?>