# Segui tus compras:

## Tecnologias

- Vue
- Vite
- Prime Vue
- MySQL
- Laravel 12

## Instalacion

**Requisitos**

- Tener docker instalado en la PC.
- Tener un archivo docker-compose.yml en una carpeta donde tengamos la consola abierta con lo siguiente:

```
version: '3.8'

services:
    app:
        image: ghcr.io/trejojulian/segui-tus-compras-backend:1.2
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
        image: ghcr.io/trejojulian/segui-tus-compras-frontend:1.2
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
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-frontend:1.1` (Pedir permisos para pullear la misma)
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-backend:1.1` (Pedir permisos para pullear la misma)
- docker-compose up -d

Puede darse el caso donde ya tengamos el puerto de la base de datos expuesto, en ese caso detener el proceso que ocupa el puerto 3306 y volver a buildear y levantar.

## Como ver el proyecto funcionando:

Una vez levantados los contenedores ir http://localhost:5173/
