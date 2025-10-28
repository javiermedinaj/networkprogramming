# Sistema ABM de Depósitos - PHP4

## 📋 Descripción

Sistema completo de ABM (Alta, Baja, Modificación) para gestión de depósitos con soporte para documentos PDF almacenados en la base de datos.

## 🗂️ Archivos del Proyecto

### HTML/CSS/JS (Plantilla)
- **index.html** - Interfaz principal con tabla y formularios modales
- **styles.css** - Estilos responsivos
- **script.js** - Lógica del lado del cliente (fetch, modales, validaciones)

### PHP (Servidor)
- **datosConexionBase.php** - Configuración de conexión a MySQL
- **salidaJsonDepositos.php** - Lista depósitos con filtros y ordenamiento
- **salidaJsonTipos.php** - Lista tipos de depósito para los selects
- **alta.php** - Inserta nuevo depósito (con o sin PDF)
- **modi.php** - Modifica depósito existente (con o sin PDF)
- **baja.php** - Elimina depósito
- **tryDoc.php** - Recupera y muestra PDF almacenado

### SQL
- **agregar_columna_documento.sql** - Script para agregar columna BLOB

## 🔧 Configuración Inicial

### 1. Configurar credenciales
```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar con tus credenciales reales
nano .env
```

### 2. Ejecutar script SQL
```bash
mysql -h YOUR_HOST -u YOUR_USER -p YOUR_DATABASE < agregar_columna_documento.sql
```

O desde phpMyAdmin/MySQL Workbench ejecutar:
```sql
ALTER TABLE depositos ADD COLUMN documento LONGBLOB AFTER nro_muelles;
```

### 3. Verificar permisos de escritura
El servidor debe poder crear el archivo `errores.log`:
```bash
touch errores.log
chmod 666 errores.log
```

## 🚀 Uso del Sistema

### Cargar Datos
1. Al entrar a la página se cargan automáticamente los tipos de depósito (alerta)
2. Click en "Cargar Datos" para traer los depósitos (con filtros opcionales)
3. La tabla se genera dinámicamente con botones de acción por fila

### Alta de Depósito
1. Click en botón "Nuevo"
2. Llenar formulario (todos los campos obligatorios excepto PDF)
3. Opcionalmente seleccionar archivo PDF
4. Click en "Enviar Alta"
5. Confirmar en el diálogo
6. Ver respuesta del servidor en ventana modal

### Modificación
1. Click en botón "Modi" de la fila a modificar
2. El formulario se llena con datos actuales
3. Modificar los campos deseados (el botón se habilita al detectar cambios)
4. Opcionalmente reemplazar el PDF
5. Click en "Enviar Modificación"
6. Confirmar y ver respuesta

### Baja
1. Click en botón "Borrar" de la fila a eliminar
2. Confirmar en el diálogo
3. Ver respuesta del servidor
4. La tabla se recarga automáticamente

### Ver PDF
1. Click en botón "PDF" de la fila
2. Se abre ventana modal con el documento en un iframe
3. Si no hay PDF, muestra mensaje de error

## 📸 Explicación de Archivos Binarios en DB

### ¿Qué es un BLOB?
**BLOB** = Binary Large Object (Objeto Binario Grande)

Es un tipo de columna en MySQL que almacena datos binarios (bytes) en lugar de texto.

### Tipos de BLOB
- **TINYBLOB**: hasta 255 bytes
- **BLOB**: hasta 64 KB
- **MEDIUMBLOB**: hasta 16 MB
- **LONGBLOB**: hasta 4 GB ← *usamos este*

### ¿Cómo funciona?

#### 1. Subir archivo (alta.php / modi.php)
```php
// El navegador envía el archivo en $_FILES
$archivo = $_FILES['archivoDocumento'];

// Leer el archivo como bytes
$contenidoBinario = file_get_contents($archivo['tmp_name']);

// Guardar en la DB
$stmt->bindParam(':documento', $contenidoBinario, PDO::PARAM_LOB);
```

#### 2. Recuperar archivo (tryDoc.php)
```php
// Consultar el campo BLOB
$sql = "SELECT documento FROM depositos WHERE cod_deposito = :cod";
$fila = $stmt->fetch();

// Enviar al navegador como PDF
header("Content-Type: application/pdf");
echo $fila['documento']; // Los bytes se envían directo
```

### Proceso de Alta con Binario (según el profesor)

**El profesor usa 2 pasos:**

1. **Alta simple** (sin binario)
   ```php
   INSERT INTO depositos (cod_deposito, cod_tipo, direccion, ...)
   VALUES (:cod, :tipo, :dir, ...)
   ```

2. **Modificación con binario** (al mismo registro)
   ```php
   UPDATE depositos SET documento = :documento 
   WHERE cod_deposito = :cod
   ```

**¿Por qué?** Porque separa la lógica de datos normales de la lógica de archivos grandes.

### Ventajas y Desventajas

#### ✅ Ventajas
- Todo en un solo lugar (base de datos)
- Backups incluyen los archivos
- Control de acceso centralizado
- No se pierden archivos si mueves carpetas

#### ❌ Desventajas
- Base de datos crece mucho (1 PDF = varios MB)
- Más lento que archivos en disco
- Dificulta backups si la DB es muy grande
- Consume más memoria del servidor

### Alternativa: Guardar solo la ruta
```sql
ALTER TABLE depositos ADD COLUMN ruta_documento VARCHAR(255);
```

Guardas: `/uploads/deposito_DEP001.pdf`

Y el archivo físico está en el servidor en esa carpeta.

## 🔍 Flujo de Datos

### Carga de Depósitos
```
[index.html] 
    → Click "Cargar Datos"
    → [script.js] cargarDepositos()
        → fetch('salidaJsonDepositos.php', {filtros...})
            → [PHP] consulta DB con LIKE+bindParam
            → Retorna {depositos: [...], cuenta: X}
        → [JS] renderizarTabla(depositos)
            → Crea <tr> con botones Modi/Baja/PDF
```

### Alta de Depósito
```
[index.html]
    → Click "Nuevo"
    → [script.js] abrirFormularioAlta()
        → Muestra modal con formulario vacío
    → Usuario llena datos + selecciona PDF
    → Submit formulario
        → [JS] enviarFormulario()
            → FormData con todos los campos + archivo
            → fetch('alta.php', {formData})
                → [PHP] INSERT datos
                → [PHP] UPDATE con documento BLOB
                → Retorna HTML con resultados
            → [JS] mostrarRespuestaServidor()
```

### Modificación
```
Similar a Alta, pero:
- Formulario viene pre-llenado
- Código de depósito bloqueado (disabled)
- Botón solo se habilita si hay cambios
- Endpoint: modi.php (UPDATE en vez de INSERT)
```

### Baja
```
[Tabla] → Click "Borrar"
    → [JS] eliminarDeposito(cod)
        → confirm()
        → fetch('baja.php', {cod_deposito})
            → [PHP] DELETE WHERE cod_deposito = :cod
        → Muestra respuesta
        → Recarga tabla automáticamente
```

### Ver PDF
```
[Tabla] → Click "PDF"
    → [JS] verPDF(cod)
        → fetch('tryDoc.php?cod_deposito=DEP001')
            → [PHP] SELECT documento FROM depositos
            → header('Content-Type: application/pdf')
            → echo $bytes_del_pdf
        → [JS] Crea Blob
        → Crea <iframe> con ObjectURL
        → Muestra en modal
```

## 🛡️ Seguridad Implementada

1. **bindParam** - Previene SQL Injection
2. **PDO try/catch** - Manejo de errores
3. **Log de errores** - Registro en errores.log
4. **Validación HTML5** - required en inputs
5. **Confirmaciones** - confirm() antes de borrar/modificar

## 📱 Responsividad

- Headers sticky (fijos al hacer scroll)
- Columnas ocultas en móvil (.hidden-sm)
- Grid adaptativo para formularios
- Botones apilados en pantallas pequeñas

## 🔄 Diferencias con PHP3

| Aspecto | PHP3 | PHP4 |
|---------|------|------|
| Operaciones | Solo lectura (filtros) | CRUD completo |
| Formularios | No | 2 modales (alta/modi + respuesta) |
| Botones por fila | No | Sí (PDF, Modi, Borrar) |
| Archivos binarios | No | Sí (LONGBLOB) |
| Scripts PHP | 3 archivos | 6 archivos |
| Ventanas modales | No | 2 ventanas |
| Detección de cambios | No | Sí (habilita botón) |

## 📝 Notas del Profesor

- Siempre mostrar alerts con JSON y respuestas (para aprendizaje)
- Usar sleep(1) en PHP para simular demora de red
- Separar alta simple de alta con binario
- Desactivar fondo cuando hay modal abierto (contenedorPasivo)
- Log de errores con fecha/hora
- Usar FormData para enviar archivos
- Usar URLSearchParams para filtros simples

## 🎓 Conceptos Aplicados

- **MVT**: Modelo (PHP+PDO), Vista (PHP responses), Plantilla (HTML+JS+CSS)
- **CRUD**: Create (alta.php), Read (salidaJson), Update (modi.php), Delete (baja.php)
- **AJAX**: fetch() con Promises (.then/.catch)
- **Prepared Statements**: prepare() + bindParam() + execute()
- **File Upload**: FormData + $_FILES + file_get_contents()
- **Blob Handling**: PDO::PARAM_LOB
- **Modal Windows**: visibility toggle + blur effect
- **Event Delegation**: addEventListener en elementos dinámicos

---

**Autor**: Sistema generado para materia de Programación Web  
**Fecha**: 2025  
**Base**: Ejercicio php3 (filtros) + Apuntes PHP Parte 4 (CRUD + Binarios)
