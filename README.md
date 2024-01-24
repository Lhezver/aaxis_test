
# Aaxis Test

![](https://img.shields.io/badge/PHP-7.4.33-777BB4?logo=php) ![](https://img.shields.io/badge/PostgreSQL-16.1.1-679CC7?logo=postgresql) ![](https://img.shields.io/badge/Symfony-5.4.34-000000?logo=symfony)

### Precondiciones

Tener instalado y configuradas las variables de entorno de PHP, Composer, PostgreSQL, OpenSSL y Symfony CLI

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
symfony console lexik:jwt:generate-keypair
```

Generar usuario:

Correr el siguiente SQL en la base de datos y reemplazar el campo password por el generado mediante la consola*.
```sql
INSERT INTO public."user" (id, username, roles, "password") VALUES (1, 'admin', '["ROLE_USER"]', '$2y$13$3huKqGWIByeJyUiv5qO9quX5DowMbPG4jcWUU/3rh9dUmmaRkVDA2');
```
*Generar contraseña:
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