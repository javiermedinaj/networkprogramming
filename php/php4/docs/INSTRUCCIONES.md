# 🎯 INSTRUCCIONES DE USO - PHP4 ABM

## ✅ Sistema Creado Exitosamente

La carpeta `php4` ya está lista con **CRUD completo** (Alta, Baja, Modificación) y soporte para **archivos PDF en la base de datos**.

## 📂 Archivos Creados

```
php4/
├── index.html                      ← Interfaz principal
├── script.js                       ← Lógica JavaScript (fetch, modales)
├── styles.css                      ← Estilos responsivos
├── datosConexionBase.php          ← Configuración DB
├── salidaJsonDepositos.php        ← Lista depósitos (con filtros)
├── salidaJsonTipos.php            ← Lista tipos de depósito
├── alta.php                       ← Inserta nuevo depósito
├── modi.php                       ← Modifica depósito
├── baja.php                       ← Elimina depósito
├── tryDoc.php                     ← Muestra PDF almacenado
├── agregar_columna_documento.sql  ← Script SQL
├── ejecutar_alter_table.php       ← Script para ejecutar ALTER TABLE
└── README.md                      ← Documentación completa
```

## 🗄️ Base de Datos

✅ **Columna 'documento' agregada correctamente**

La tabla `depositos` ahora tiene:
- `documento LONGBLOB` - Para almacenar PDFs (hasta 4GB)

## 🚀 Cómo Usar

### 1. Abrir en el navegador
```
http://localhost/prof/php/php4/index.html
```

### 2. Flujo de Trabajo

**Al cargar la página:**
- Se muestra alert con tipos de depósito (JSON)
- Se llenan los selects de filtros y formulario

**Cargar datos:**
1. Click en "Cargar Datos"
2. Se muestran los depósitos en la tabla
3. Cada fila tiene 3 botones: PDF, Modi, Borrar

**Alta de depósito:**
1. Click en "Nuevo"
2. Llenar formulario (código, tipo, dirección, etc.)
3. **OPCIONAL**: Seleccionar archivo PDF
4. Click en "Enviar Alta"
5. Confirmar
6. Ver respuesta del servidor

**Modificación:**
1. Click en "Modi" en la fila deseada
2. El formulario se llena automáticamente
3. Modificar campos (el botón se habilita al detectar cambios)
4. **OPCIONAL**: Subir nuevo PDF
5. Click en "Enviar Modificación"
6. Confirmar y ver respuesta

**Baja:**
1. Click en "Borrar" en la fila
2. Confirmar
3. Ver respuesta
4. La tabla se recarga automáticamente

**Ver PDF:**
1. Click en "PDF" en la fila
2. Se abre modal con el documento
3. Si no hay PDF, muestra mensaje

## 📸 Explicación de Archivos Binarios (BLOB)

### ¿Qué es un BLOB?
**BLOB** = Binary Large Object (Objeto Grande Binario)

Es un tipo de columna que almacena datos en formato binario (bytes) en lugar de texto.

### Tipos de BLOB en MySQL
```
TINYBLOB    →  255 bytes
BLOB        →  64 KB
MEDIUMBLOB  →  16 MB
LONGBLOB    →  4 GB  ← Usamos este para PDFs
```

### ¿Cómo funciona?

#### SUBIR archivo al servidor (alta.php / modi.php)

```php
// 1. El navegador envía el archivo
$archivo = $_FILES['archivoDocumento'];
// Contiene: name, type, size, tmp_name

// 2. Leer el archivo como bytes
$contenidoBinario = file_get_contents($archivo['tmp_name']);

// 3. Guardar en la base de datos
$sql = "UPDATE depositos SET documento = :documento WHERE cod_deposito = :cod";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':documento', $contenidoBinario, PDO::PARAM_LOB);
$stmt->execute();
```

**PDO::PARAM_LOB** → Le dice a PDO que es un archivo grande (Large Object)

#### RECUPERAR archivo desde la DB (tryDoc.php)

```php
// 1. Consultar el campo BLOB
$sql = "SELECT documento FROM depositos WHERE cod_deposito = :cod";
$stmt->execute();
$fila = $stmt->fetch();

// 2. Enviar al navegador como PDF
header("Content-Type: application/pdf");
echo $fila['documento']; // Los bytes se envían directamente
```

El navegador recibe los bytes y los interpreta como PDF.

### Proceso de Alta en 2 Pasos (método del profesor)

**Paso 1: Alta Simple** (solo datos)
```sql
INSERT INTO depositos (cod_deposito, direccion, superficie, ...)
VALUES ('DEP001', 'Av. Libertador 123', 500, ...)
```

**Paso 2: Agregar Binario** (al mismo registro)
```sql
UPDATE depositos SET documento = [BYTES_DEL_PDF]
WHERE cod_deposito = 'DEP001'
```

**¿Por qué en 2 pasos?**
- Separa la lógica de datos normales y archivos grandes
- Si falla el PDF, los datos ya están guardados
- Más fácil de debuggear

### Ventajas vs Desventajas

#### ✅ Ventajas de BLOB
- Todo en un solo lugar (base de datos)
- Los backups incluyen los archivos
- No se pierden archivos si mueves carpetas
- Control de acceso centralizado (permisos de DB)

#### ❌ Desventajas de BLOB
- La base de datos crece **mucho** (1 PDF = varios MB)
- Más lento que archivos en disco
- Dificulta los backups si la DB es muy grande
- Consume más memoria del servidor

### Alternativa: Guardar solo la ruta

En lugar de BLOB, usar:
```sql
ALTER TABLE depositos ADD COLUMN ruta_documento VARCHAR(255);
```

Guardas: `"/uploads/deposito_DEP001.pdf"`

Y el archivo físico está en el servidor en esa carpeta.

**Ventaja:** DB más liviana  
**Desventaja:** Hay que gestionar carpetas y archivos manualmente

## 🔄 Diferencias entre PHP3 y PHP4

| Característica | PHP3 | PHP4 |
|----------------|------|------|
| **Operaciones** | Solo lectura (SELECT) | CRUD completo (INSERT, UPDATE, DELETE) |
| **Formularios** | No tiene | 2 ventanas modales (formulario + respuesta) |
| **Botones por fila** | No | Sí (PDF, Modi, Borrar) |
| **Archivos binarios** | No | Sí (LONGBLOB para PDFs) |
| **Scripts PHP** | 3 archivos | 6 archivos |
| **Detección de cambios** | No | Sí (habilita botón solo si hay cambios) |
| **Recarga automática** | No | Sí (después de borrar) |

## 🛡️ Seguridad Implementada

1. **bindParam()** - Previene SQL Injection
2. **PDO try/catch** - Manejo robusto de errores
3. **Log de errores** - Registro en `errores.log` con fecha/hora
4. **Validación HTML5** - Atributo `required` en inputs
5. **Confirmaciones** - `confirm()` antes de borrar/modificar
6. **Headers CORS** - `Access-Control-Allow-Origin: *`

## 📱 Responsividad

- **Headers sticky** - Fijos al hacer scroll
- **Columnas ocultas** - `.hidden-sm` en móviles
- **Grid adaptativo** - 2 columnas en desktop, 1 en móvil
- **Botones responsive** - Se apilan en pantallas pequeñas

## 🐛 Troubleshooting

### No se muestra el PDF
**Problema:** Click en "PDF" pero no aparece nada  
**Solución:** 
1. Verificar que el depósito tiene documento cargado
2. Revisar consola del navegador (F12)
3. Verificar que `tryDoc.php` retorna `Content-Type: application/pdf`

### No se suben archivos
**Problema:** Formulario no envía el PDF  
**Solución:**
1. Verificar que el `<form>` NO tiene `enctype` (FormData lo maneja)
2. Verificar límite de upload en `php.ini`:
   ```ini
   upload_max_filesize = 20M
   post_max_size = 20M
   ```

### Error "Column not found: documento"
**Problema:** La columna no existe en la tabla  
**Solución:**
```bash
php ejecutar_alter_table.php
```

### Botón "Enviar Modi" no se habilita
**Problema:** Modifico datos pero el botón sigue deshabilitado  
**Solución:** El JS detecta cambios con JSON.stringify. Verifica que los valores realmente cambien.

## 📚 Conceptos Aplicados (para estudiar)

1. **MVT Architecture**
   - **Modelo**: PHP + PDO (acceso a datos)
   - **Vista**: PHP responses (HTML generado)
   - **Plantilla**: HTML + CSS + JS (presentación)

2. **CRUD Operations**
   - **Create**: alta.php (INSERT)
   - **Read**: salidaJsonDepositos.php (SELECT)
   - **Update**: modi.php (UPDATE)
   - **Delete**: baja.php (DELETE)

3. **AJAX con Fetch API**
   ```javascript
   fetch('endpoint.php', {method: 'POST', body: formData})
       .then(response => response.json())
       .then(data => { /* procesar */ })
       .catch(error => { /* manejar error */ });
   ```

4. **Prepared Statements** (seguridad)
   ```php
   $stmt = $pdo->prepare($sql);
   $stmt->bindParam(':param', $valor, PDO::PARAM_STR);
   $stmt->execute();
   ```

5. **File Upload**
   - HTML: `<input type="file" accept=".pdf">`
   - JS: `new FormData(formulario)`
   - PHP: `$_FILES['nombre']`

6. **BLOB Handling**
   - `PDO::PARAM_LOB` para archivos grandes
   - `file_get_contents()` para leer bytes
   - `echo $binario` para enviar al navegador

7. **Modal Windows**
   - CSS: `visibility` + `position: fixed`
   - JS: toggle entre `ventanaModalPrendido` / `ventanaModalApagado`
   - Blur effect: `filter: blur(2px)`

## 📖 Referencias

- **Apunte PHP Parte 4 CRUD.pdf** - Teoría de ABM
- **Apunte Lectura y Actualización Binarios.pdf** - Manejo de BLOB
- **Transcript de la clase** - Explicación del profesor

## 🎓 Para la Entrega

Asegúrate de que tu implementación tenga:

✅ Alta con y sin PDF  
✅ Modificación con detección de cambios  
✅ Baja con confirmación  
✅ Visualización de PDFs en iframe  
✅ Filtros funcionando  
✅ Ordenamiento por columnas  
✅ Alerts mostrando JSON (para aprendizaje)  
✅ Ventanas modales con blur en el fondo  
✅ Log de errores con fecha/hora  
✅ Código comentado y legible  

---

**¡Listo para usar! 🚀**

Si tienes dudas, revisa el `README.md` completo con ejemplos de código.
