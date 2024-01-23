
# Aaxis Test

![](https://img.shields.io/badge/PHP-7.4.33-777BB4?logo=php) ![](https://img.shields.io/badge/MariaDB-10.4.27-003545?logo=mariadb) ![](https://img.shields.io/badge/Symfony-5.4.34-000000?logo=symfony)

### Precondiciones

Tener instalado y configuradas las variables de entorno de PHP, Composer, MySQL, OpenSSL y Symfony CLI

## Instalación

Clonar el repositorio ejecutando el siguiente comando:

```console
git clone https://github.com/Lhezver/aaxis_test.git
```

Abrir una terminal dentro del directorio aaxis_test y ejecutar los siguientes comandos:

```console
composer install
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

Generar usuario:

Correr el siguiente SQL en la base de datos y reemplazar el campo password por el generado mediante la consola.
```sql
INSERT INTO `user` (`id`, `username`, `roles`, `password`) VALUES (NULL, 'admin', '[\"ROLE_ADMIN\"]', '$2y$13$Jwd2ORKLPddJ.HfgPlnT8.R2TZpB8DrHgV3.CmvgtfTke.bffrmIu');
```
Generar contraseña:
```console
symfony console security:hash-password
```

Iniciar el servidor de Symfony:
```console
symfony server:start -d
```

Autenticarse:

Realizar un POST a la url "https://127.0.0.1:8000/api/login_check" con la siguiente estructura de JSON:
```json
{
	"username":"admin",
	"password":"admin"
}
```

Consultar la API RESTful:

Ir a "https://127.0.0.1:8000/api/doc"
Ingresar en el botón de "Authorize" el token obtenido al autenticarse.
Si realizó todos los pasos correctamente podrá consultar la API RESTful por el periodo de una hora, luego de esto deberá volver a generar otro token.