
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

- Instalar composer y laravel (Necesitamos mejorar esto para no requerirlo):

  - (Windows) Correr en una powershell como administrador:
  
      Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol =  [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))
  
  - composer global require laravel/installer
  
- Tener el proyecto del backend y del frontend clonados a la misma altura en el directorio. 
- Tener un archivo docker-compose.yml con el siguiente contenido a la misma altura que los dos proyectos:

```
version: '3.8'

services:
    app:
        image: ghcr.io/trejojulian/segui-tus-compras-backend:1.0
        container_name: laravel_app
        restart: unless-stopped
        working_dir: /var/www
        volumes:
            - ./segui-tus-compras-backend:/var/www
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
        command: php artisan serve --host=0.0.0.0 --port=8000

    frontend:
        image: ghcr.io/trejojulian/segui-tus-compras-frontend:1.0
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
        volumes:
            - ./segui-tus-compras-frontend:/app
            - /app/node_modules
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

- cd al proyecto del backend (mejorar para que lo haga docker)
- composer install (mejorar para que lo haga docker)
- copiar el archivo .env.example en un archivo .env (mejorar para que lo haga docker)
- en el archivo .env cambiar las variables de la base de datos a las siguientes (mejorar para que lo haga docker)
    - DB_CONNECTION=mysql
    - DB_HOST=db
    - DB_PORT=3306
    - DB_DATABASE=laravel
    - DB_USERNAME=root
    - DB_PASSWORD=root
- php artisan key:generate (mejorar para que lo haga docker)
- Vincular docker con el usuario de github: `docker login ghcr.io -u {usuario de github sin mayusculas} -p {un token con todos los permisos}`
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-frontend:1.0` (Pedir permisos para pullear la misma)
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-backend:1.0` (Pedir permisos para pullear la misma)
- Volver a la carpeta donde estan los proyectos y el docker compose
- docker-compose up --build -d
- docker-compose exec app php artisan migrate --force

Puede darse el caso donde ya tengamos el puerto de la base de datos expuesto, en ese caso detener el proceso que ocupa el puerto 3306 y volver a buildear y levantar.

## Como ver el proyecto funcionando:

Una vez levantados los contenedores ir http://localhost:5173/
