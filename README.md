🚀 Instalación

1. Requisitos Previos
   Servidor web (Apache, Nginx)

PHP 7.4 o superior

MySQL 5.7 o superior

Extensiones PHP: PDO, pdo_mysql

2. Configuración de la Base de Datos
   sql
   CREATE DATABASE inmobiliaria;

USE inmobiliaria;

CREATE TABLE propiedades (
id INT PRIMARY KEY AUTO_INCREMENT,
titulo VARCHAR(255) NOT NULL,
descripcion TEXT,
tipo VARCHAR(50) NOT NULL,
direccion VARCHAR(255) NOT NULL,
ciudad VARCHAR(100) NOT NULL,
precio DECIMAL(12,2) NOT NULL,
habitaciones INT,
banos INT,
metros_cuadrados INT,
fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 3. Configuración de Conexión
Edita el archivo config/database.php con tus credenciales:

php
private $host = "localhost";
private $db_name = "inmobiliaria";
private $username = "tu_usuario";
private $password = "tu_contraseña"; 4. Despliegue
Clona o descarga los archivos en tu servidor web

Asegúrate de que los permisos de escritura sean correctos

Accede a index.php desde tu navegador

📖 Uso del Sistema
Listado de Propiedades
Accede a index.php para ver todas las propiedades

Ordenamiento por fecha de creación (más recientes primero)

Agregar Nueva Propiedad
Haz clic en "Nueva Propiedad"

Completa el formulario con los datos requeridos

Haz clic en "Crear Propiedad"

Editar Propiedad
En el listado, haz clic en "Editar" junto a la propiedad

Modifica los campos necesarios

Haz clic en "Actualizar Propiedad"

Eliminar Propiedad
En el listado, haz clic en "Eliminar"

Confirma la acción en el diálogo emergente

🗂️ Campos de Propiedades
Campo Tipo Descripción
titulo String Título de la propiedad
descripcion Text Descripción detallada
tipo Enum Casa, Departamento, Local, Terreno
direccion String Dirección completa
ciudad String Ciudad donde se ubica
precio Decimal Precio en formato decimal
habitaciones Integer Número de habitaciones
banos Integer Número de baños
metros_cuadrados Integer Metros cuadrados
🔒 Seguridad
Prepared Statements: Uso de PDO para prevenir inyecciones SQL

Data Sanitization: Limpieza de datos de entrada con htmlspecialchars()

Validation: Validación básica de campos requeridos

Error Handling: Manejo de errores sin exponer información sensible

🎨 Personalización
Agregar Nuevos Tipos de Propiedad
Edita el campo select en formulario.php:

php

<option value="nuevo_tipo">Nuevo Tipo</option>
Modificar Estilos
Los estilos están incluidos en cada archivo PHP. Puedes:

Extraer CSS a archivos separados

Modificar colores y layouts en las secciones <style>

Agregar frameworks como Bootstrap

🔄 Funcionalidades Futuras
Subida de imágenes múltiples

Búsqueda y filtros avanzados

Paginación de resultados

Sistema de usuarios y roles

Exportación a PDF/Excel

API REST

Panel administrativo

🐛 Solución de Problemas
Error de Conexión a la Base de Datos
Verifica credenciales en config/database.php

Asegúrate de que MySQL esté ejecutándose

Confirma que la base de datos existe
