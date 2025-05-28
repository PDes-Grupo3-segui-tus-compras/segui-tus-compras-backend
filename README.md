# Segui tus compras:

## Tecnologias

- Vue
- Vite
- Prime Vue
- MySQL
- Laravel 12
- Autenticacion OAUTH

## Instalacion

**Requisitos**

- Tener docker instalado en la PC.
- Tener un archivo docker-compose.yml en una carpeta donde tengamos la consola abierta con lo siguiente:

```
version: '3.8'

services:
    app:
        image: ghcr.io/trejojulian/segui-tus-compras-backend:1.3
        container_name: laravel_app
        restart: unless-stopped
        working_dir: /var/www
        networks:
            - app_network
        depends_on:
            - db
        environment:
            - DB_HOST=db
            - DB_DATABASE=laravel
            - DB_USERNAME=root
            - DB_PASSWORD=root
        ports:
            - "8000:8000"
        command: sh -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"

    frontend:
        image: ghcr.io/trejojulian/segui-tus-compras-frontend:1.3
        container_name: vue_app
        restart: unless-stopped
        ports:
            - "5173:5173"
        networks:
            - app_network
        depends_on:
            - app
        environment:
            - VITE_HOST=0.0.0.0
            - CHOKIDAR_USEPOLLING=true
        working_dir: /app
        command: npm run dev 

    db:
        image: mysql:8
        container_name: mysql_db
        restart: unless-stopped
        environment:
            MYSQL_ROOT_PASSWORD: root
            MYSQL_DATABASE: laravel
        ports:
            - "3306:3306"
        networks:
            - app_network
        volumes:
            - mysql_data:/var/lib/mysql

volumes:
    mysql_data:

networks:
    app_network:
        driver: bridge

```

**Pasos**:

- Vincular docker con el usuario de github: `docker login ghcr.io -u {usuario de github sin mayusculas} -p {un token con todos los permisos}`
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-frontend:1.3` (Pedir permisos para pullear la misma)
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-backend:1.3` (Pedir permisos para pullear la misma)
- docker-compose up -d

Puede darse el caso donde ya tengamos el puerto de la base de datos expuesto, en ese caso detener el proceso que ocupa el puerto 3306 y volver a buildear y levantar.

## Como ver el proyecto funcionando:

Ingresar al container de laravel_app y correr el seeder `php artisan db:seed --class=UsersSeeder` para ingresar al usuario mail: admin@admin.com, password: admin y al usuario email: user@user.com, password: user al sistema.

Una vez levantados los contenedores ir http://localhost:5173/ para ingresar al frontend del proyecto.

## Tests:

Para correr los tests se debe:
- Ingresar al container de laravel_app.
- Correr los siguientes comandos `php artisan config:clear` `php artisan cache:clear` `php artisan config:cache`
- Correr el comando `./vendor/bin/pest`. Si este comando falla, quizas haya que ejecutar el comando `composer install` debido a una posible falla en la instalacion de las depedencias o por tener un proyecto con una version vieja previamente.

## Documentacion

Se puede ingresar a la documentacion de la API del proyecto en la URL: http://localhost:8000/api/documentation


## Funcionalidad actual

Usuario/Admin:

- Loguearse
- Buscar productos.
- Ingresar al detalle de productos.
- Efectuar una compra.
- Dejar una opinion en el producto.
- Modificar la opinion propia.
- Eliminar la opinion propia.
- Agregar un producto a favoritos.
- Quitar un producto de favoritos.

Admin:

- Eliminar opiniones de otros usuarios.
- Ir al panel de admin donde encontrara metricas (mockeado de momento) y el listado de usuarios del sistema.
