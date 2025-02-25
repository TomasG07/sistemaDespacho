# 🚀 **Sistema de Gestión de Despachos**
Este es un sistema web para gestionar despachos de productos, registrar retiros y generar reportes en formato **PDF** usando **TCPDF**.

---

## 📌 **Características**
✅ Registro de despachos y retiros  
✅ Seguimiento de productos pendientes y despachados  
✅ Generación de reportes en **PDF** con **TCPDF**  
✅ Gestión de productos y stock  
✅ Panel administrativo con estadísticas  

---

## 📦 **Instalación**
### 🔹 1. Clonar el Repositorio
Abre una terminal y ejecuta:

```sh
git clone https://github.com/TuUsuario/TuRepositorio.git
cd TuRepositorio
```

### 🔹 2. Configurar la Base de Datos
1️⃣ Crea una base de datos en **MySQL**.  
2️⃣ Importa el archivo **`database.sql`** incluido en el proyecto.  
3️⃣ Configura los datos de conexión en **`config.php`**:

```php
<?php
$host = "localhost"; // Servidor de BD
$user = "root"; // Usuario
$password = ""; // Contraseña
$database = "despachos_db"; // Nombre de la BD

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
```

### 🔹 3. Instalar Dependencias con Composer
Ejecuta el siguiente comando en la terminal para instalar **TCPDF**:

```sh
composer install
```

Si no tienes **Composer**, descárgalo desde [https://getcomposer.org/](https://getcomposer.org/) y luego ejecuta el comando.

> **📌 Importante:**  
> Asegúrate de que la carpeta **`vendor/`** está en el archivo **`.gitignore`**, ya que las dependencias deben instalarse en cada entorno de desarrollo.

---

## 🖥 **Ejecución del Proyecto**
1️⃣ Inicia un servidor local con **XAMPP**, **Laragon**, o usa PHP directamente:

```sh
php -S localhost:8000
```

2️⃣ Abre en tu navegador:

```
http://localhost/sistemaDespacho/
```

---

## 📄 **Generación de PDF con TCPDF**
El sistema genera informes en PDF con información de los despachos y retiros.

### 🔹 **Verificar instalación de TCPDF**
Para comprobar que TCPDF está instalado, ejecuta:

```sh
php -r "require 'vendor/autoload.php'; echo 'TCPDF instalado correctamente';"
```

Si ves el mensaje `TCPDF instalado correctamente`, entonces está todo listo. 🎉

### 🔹 **Cómo generar un PDF**
Desde la interfaz web, en la pantalla de **Historial de Retiros**, presiona el botón **📄 Generar PDF** para ver el reporte.

También puedes generar un PDF manualmente visitando:

```
http://localhost/sistemaDespacho/generar_pdf.php?despacho_id=1
```

(Sustituye `1` por el ID de un despacho válido en la base de datos).

---

## 🛠 **Estructura del Proyecto**
```
📂 sistemaDespacho/
│── 📂 vendor/              # Dependencias instaladas con Composer (TCPDF aquí)
│── 📂 images/              # Logo y otras imágenes
│── 📂 css/                 # Archivos CSS personalizados
│── 📂 js/                  # Archivos JavaScript
│── 📂 api/                 # Endpoints para AJAX
│── 📂 database/            # Scripts SQL para la BD
│── config.php              # Configuración de la base de datos
│── index.php               # Página principal
│── historial_despachos.php # Pantalla de historial de despachos
│── generar_pdf.php         # Código para generar PDF con TCPDF
│── README.md               # Este archivo 📌
│── .gitignore              # Ignorar archivos innecesarios en Git
```

---

## 🛠 **Solución de Problemas**
### ❌ Error: `TCPDF ERROR: Unable to create output file`
👉 Asegúrate de que el servidor web tenga permisos de escritura en la carpeta donde se guardarán los PDFs.

```sh
chmod -R 777 /ruta/proyecto/
```

### ❌ Error: `Call to undefined function imagecreatefrompng()`
👉 Instala la extensión **GD** en PHP:

```sh
sudo apt install php-gd # En Linux
```

o habilítala en `php.ini`:

```ini
extension=gd
```

### ❌ No se muestra el logo en el PDF
👉 Verifica que la imagen **`images/logo.png`** existe y que el código la llama correctamente:

```php
$pdf->Image('images/logo.png', 15, 10, 30);
```

---

## 🏆 **Contribuciones**
¡Las contribuciones son bienvenidas! 🎉  
Si quieres mejorar este proyecto, sigue estos pasos:

1. **Haz un fork** del repositorio  
2. **Crea una nueva rama**:  
   ```sh
   git checkout -b mi-mejora
   ```
3. **Realiza tus cambios** y haz commit:  
   ```sh
   git commit -m "Mejora en la generación de PDF"
   ```
4. **Envía un pull request** 🚀

---

## 📄 **Licencia**
Este proyecto está bajo la licencia **MIT**, por lo que puedes modificarlo y distribuirlo libremente.

---

¡Listo! Con este **README.md**, cualquier persona podrá instalar y ejecutar tu proyecto sin problemas. 🚀🔥

