# Segui tus compras:

## Tecnologias

- Vue
- Vite
- Prime Vue
- MySQL
- Laravel 12
- Autenticacion OAUTH
- Prometheus
- Grafana
- K6

## Instalacion

**Requisitos**

- Tener docker instalado en la PC.
- Tener un archivo docker-compose.yml en una carpeta donde tengamos la consola abierta con lo siguiente:

```
version: '3.8'

services:
  migrator:
    build:
      context: ./segui-tus-compras-backend
    working_dir: /var/www
    volumes:
      - ./segui-tus-compras-backend:/var/www
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_DATABASE=laravel
      - DB_USERNAME=root
      - DB_PASSWORD=root
    command: sh -c "until php artisan migrate --force; do echo 'Waiting for DB...'; sleep 3; done"
    networks:
      - app_network

  app:
    image: ghcr.io/trejojulian/segui-tus-compras-backend:1.6
    container_name: laravel_app
    restart: unless-stopped
    working_dir: /var/www
    depends_on:
      - db
      - migrator
    environment:
      - DB_HOST=db
      - DB_DATABASE=laravel
      - DB_USERNAME=root
      - DB_PASSWORD=root
    expose:
      - "9000"
    networks:
      - app_network
    command: ["php-fpm"]

  nginx:
    image: nginx:stable-alpine
    container_name: nginx_app
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - ./segui-tus-compras-backend:/var/www
      - ./segui-tus-compras-backend/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - app_network

  frontend:
    image: ghcr.io/trejojulian/segui-tus-compras-frontend:1.5
    container_name: vue_app
    restart: unless-stopped
    ports:
      - "5173:5173"
    networks:
      - app_network
    depends_on:
      - nginx
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

  redis:
    image: redis:alpine
    container_name: redis_prometheus
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - app_network

  prometheus:
    image: prom/prometheus:latest
    container_name: prometheus
    restart: unless-stopped
    volumes:
      - ./prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    command:
      - --config.file=/etc/prometheus/prometheus.yml
      - --storage.tsdb.path=/prometheus
      - --web.console.libraries=/etc/prometheus/console_libraries
      - --web.console.templates=/etc/prometheus/consoles
      - --web.enable-lifecycle
    expose:
      - 9090
    ports:
      - 9090:9090
    networks:
      - app_network

  grafana:
    image: grafana/grafana-oss:latest
    container_name: grafana
    restart: unless-stopped
    ports:
      - "3000:3000"
    networks:
      - app_network
    volumes:
      - grafana_data:/var/lib/grafana
    environment:
      - GF_SECURITY_ADMIN_USER=admin
      - GF_SECURITY_ADMIN_PASSWORD=admin

  node_exporter:
    image: prom/node-exporter:latest
    container_name: node_exporter
    restart: unless-stopped
    ports:
      - "9100:9100"
    networks:
      - app_network

volumes:
  mysql_data:
  prometheus_data:
  grafana_data:

networks:
  app_network:
    driver: bridge

```

- Clonar el backend del proyecto en la carpeta que este el docker compose `git clone https://github.com/PDes-Grupo3-segui-tus-compras/segui-tus-compras-backend.git`.
- Agregar un archivo que se llame prometheus.yml a la misma altura que el docker compose con estas caracteristicas:

```
global:
  scrape_interval:     5s
scrape_configs:
  - job_name: 'laravel'
    static_configs:
      - targets:
          - nginx_app:80

  - job_name: 'node'
    static_configs:
      - targets: ['node_exporter:9100']
```

- La carpeta donde debemos pararnos en la consola deberia tener esta estructura:

```
carpeta
│   docker-compose.yml
│   prometheus.yml   
└───segui-tus-compras-backend
    │   ---
    │   ---(Todo el proyecto)
    │   ---
```

  
- Vincular docker con el usuario de github: `docker login ghcr.io -u {usuario de github sin mayusculas} -p {un token con todos los permisos}`
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-frontend:1.5` (Pedir permisos para pullear la misma)
- Pullear la imagen de docker `docker pull ghcr.io/trejojulian/segui-tus-compras-backend:1.6` (Pedir permisos para pullear la misma)
- docker-compose up -d

Puede darse el caso donde ya tengamos el puerto de la base de datos expuesto, en ese caso detener el proceso que ocupa el puerto 3306 y volver a buildear y levantar.

## Como ver el proyecto funcionando:

Ingresar al container de laravel_app (`docker exec -it laravel_app bash`) y correr el seeder `php artisan db:seed --class=UsersSeeder`. 

Para ingresar al usuario admin tenemos las siguientes credenciales: 

- mail: admin@admin.com
- password: admin 

Para ingresar al usuario generico tenemos las siguientes credenciales:
 
- user@user.com 
- password: user

Una vez levantados los contenedores ir http://localhost:5173/ para ingresar al frontend del proyecto.

## Tests:

Para correr los tests se debe:
- Ingresar al container de laravel_app (`docker exec -it laravel_app bash`).
- Ejecutar el comando `composer install`
- Correr los siguientes comandos `php artisan config:clear` `php artisan cache:clear` `php artisan config:cache`
- Correr el comando `./vendor/bin/pest`.

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
- Listar favoritos
- Listar compras
- Ver perfiles
- Modificar contraseña

Admin:

- Eliminar opiniones de otros usuarios.
- Ir al panel de admin donde encontrara metricas  y el listado de usuarios del sistema.

## Crear usuario admin

Se debe correr el comando: `php artisan make:admin [EMAIL] [PASSWORD]`

## Monitoreo:

El proyecto cuenta con Prometheus y Grafana integrados, para acceder a los mismos podemos utilizar los siguientes links

- [Prometheus](http://localhost:9090/targets)
- [Grafana](http://localhost:3000/)

Tenemos un middleware que recolecta informacion sobre las llamadas a los endpoints de la aplicacion, dicha estadistica se puede consultar haciendole una query a la data recuperada de prometheus con el nombre `http_requests_total`. Tambien tenemos una metrica que esta siendo recabada a traves del servicio node_exporter `node_cpu_seconds_total` que nos indica la cantidad de CPU que esta siendo consumida.

### Como importar nuestro tablero personalizado de Grafana:

- Ir a la URL de grafana e ingresar con usuario: admin, password: admin

- Ir a Connections - Data sources. Add data source. Elegir Prometheus. En connection ingresar `http://prometheus:9090`. Guardar

- Ir a Explore. En el menu que esta a la derecha de Outline (que dice -- Grafana --) elegir prometheus.

- Apretar el boton mas de arriba a la derecha, clickear import dashbord e importar el json en import via json. Apretar load y ahora podemos ver el dashboard en dashboards.

<details>

<summary>Json a importar</summary>

```
{
  "annotations": {
    "list": [
      {
        "builtIn": 1,
        "datasource": {
          "type": "grafana",
          "uid": "-- Grafana --"
        },
        "enable": true,
        "hide": true,
        "iconColor": "rgba(0, 211, 255, 1)",
        "name": "Annotations & Alerts",
        "type": "dashboard"
      }
    ]
  },
  "editable": true,
  "fiscalYearStartMonth": 0,
  "graphTooltip": 0,
  "id": 1,
  "links": [],
  "panels": [
    {
      "datasource": {
        "type": "prometheus",
        "uid": "cepz8xqf6zt34b"
      },
      "fieldConfig": {
        "defaults": {
          "color": {
            "mode": "palette-classic"
          },
          "custom": {
            "axisBorderShow": false,
            "axisCenteredZero": false,
            "axisColorMode": "text",
            "axisLabel": "",
            "axisPlacement": "auto",
            "barAlignment": 0,
            "barWidthFactor": 0.6,
            "drawStyle": "line",
            "fillOpacity": 0,
            "gradientMode": "none",
            "hideFrom": {
              "legend": false,
              "tooltip": false,
              "viz": false
            },
            "insertNulls": false,
            "lineInterpolation": "linear",
            "lineStyle": {
              "fill": "solid"
            },
            "lineWidth": 1,
            "pointSize": 3,
            "scaleDistribution": {
              "type": "linear"
            },
            "showPoints": "auto",
            "spanNulls": false,
            "stacking": {
              "group": "A",
              "mode": "none"
            },
            "thresholdsStyle": {
              "mode": "off"
            }
          },
          "mappings": [],
          "thresholds": {
            "mode": "absolute",
            "steps": [
              {
                "color": "green"
              },
              {
                "color": "red",
                "value": 80
              }
            ]
          }
        },
        "overrides": []
      },
      "gridPos": {
        "h": 10,
        "w": 12,
        "x": 0,
        "y": 0
      },
      "id": 1,
      "interval": "5",
      "options": {
        "legend": {
          "calcs": [],
          "displayMode": "list",
          "placement": "right",
          "showLegend": true
        },
        "tooltip": {
          "hideZeros": false,
          "mode": "single",
          "sort": "none"
        }
      },
      "pluginVersion": "12.0.2",
      "targets": [
        {
          "datasource": {
            "type": "prometheus",
            "uid": "cepz8xqf6zt34b"
          },
          "disableTextWrap": false,
          "editorMode": "code",
          "expr": "floor(sum by (route) (\r\n  increase(\r\n    http_requests_total{\r\n      route!=\".well-known/appspecific/com.chrome.devtools.json\",\r\n      route!=\"metrics\",\r\n      route!=\"l5-swagger.default.api\",\r\n      route!~\"api/users/.*/(favourites|purchases)\",\r\n      route!~\"api/profile/.*\"\r\n    }[1h]\r\n  )\r\n))",
          "fullMetaSearch": false,
          "includeNullMetadata": true,
          "legendFormat": "{{route}}",
          "range": true,
          "refId": "A",
          "useBackend": false
        }
      ],
      "title": "Total Requests calls last hour",
      "type": "timeseries"
    },
    {
      "datasource": {
        "type": "prometheus",
        "uid": "cepz8xqf6zt34b"
      },
      "description": "",
      "fieldConfig": {
        "defaults": {
          "color": {
            "mode": "thresholds"
          },
          "mappings": [],
          "thresholds": {
            "mode": "absolute",
            "steps": [
              {
                "color": "green"
              }
            ]
          }
        },
        "overrides": []
      },
      "gridPos": {
        "h": 10,
        "w": 6,
        "x": 12,
        "y": 0
      },
      "id": 3,
      "options": {
        "colorMode": "value",
        "graphMode": "area",
        "justifyMode": "auto",
        "orientation": "auto",
        "percentChangeColorMode": "standard",
        "reduceOptions": {
          "calcs": [
            "lastNotNull"
          ],
          "fields": "",
          "values": false
        },
        "showPercentChange": false,
        "textMode": "auto",
        "wideLayout": true
      },
      "pluginVersion": "12.0.2",
      "targets": [
        {
          "disableTextWrap": false,
          "editorMode": "builder",
          "expr": "floor(increase(http_requests_total{route=\"api/login\", status_code=\"200\"}[24h]))",
          "fullMetaSearch": false,
          "includeNullMetadata": true,
          "legendFormat": "__auto",
          "range": true,
          "refId": "A",
          "useBackend": false
        }
      ],
      "title": "Logins Today",
      "type": "stat"
    },
    {
      "datasource": {
        "uid": "cepz8xqf6zt34b"
      },
      "fieldConfig": {
        "defaults": {
          "color": {
            "mode": "thresholds"
          },
          "mappings": [],
          "thresholds": {
            "mode": "absolute",
            "steps": [
              {
                "color": "green"
              },
              {
                "color": "red",
                "value": 10
              }
            ]
          }
        },
        "overrides": []
      },
      "gridPos": {
        "h": 10,
        "w": 6,
        "x": 18,
        "y": 0
      },
      "id": 5,
      "options": {
        "colorMode": "value",
        "graphMode": "area",
        "justifyMode": "auto",
        "orientation": "auto",
        "percentChangeColorMode": "standard",
        "reduceOptions": {
          "calcs": [
            "lastNotNull"
          ],
          "fields": "",
          "values": false
        },
        "showPercentChange": false,
        "textMode": "auto",
        "wideLayout": true
      },
      "pluginVersion": "12.0.2",
      "targets": [
        {
          "editorMode": "code",
          "expr": "sum(increase(http_requests_total{status_code=~\"4..|5..\"}[1h])) \r\n/ \r\nsum(increase(http_requests_total[1h])) \r\n* 100",
          "legendFormat": "__auto",
          "range": true,
          "refId": "A"
        }
      ],
      "title": "Errors in the last hour",
      "type": "stat"
    },
    {
      "datasource": {
        "type": "prometheus",
        "uid": "cepz8xqf6zt34b"
      },
      "fieldConfig": {
        "defaults": {
          "color": {
            "mode": "palette-classic"
          },
          "custom": {
            "axisBorderShow": false,
            "axisCenteredZero": false,
            "axisColorMode": "text",
            "axisLabel": "",
            "axisPlacement": "auto",
            "barAlignment": 0,
            "barWidthFactor": 0.6,
            "drawStyle": "line",
            "fillOpacity": 0,
            "gradientMode": "none",
            "hideFrom": {
              "legend": false,
              "tooltip": false,
              "viz": false
            },
            "insertNulls": false,
            "lineInterpolation": "linear",
            "lineWidth": 1,
            "pointSize": 5,
            "scaleDistribution": {
              "type": "linear"
            },
            "showPoints": "auto",
            "spanNulls": false,
            "stacking": {
              "group": "A",
              "mode": "none"
            },
            "thresholdsStyle": {
              "mode": "off"
            }
          },
          "mappings": [],
          "thresholds": {
            "mode": "absolute",
            "steps": [
              {
                "color": "green"
              },
              {
                "color": "red",
                "value": 80
              }
            ]
          }
        },
        "overrides": []
      },
      "gridPos": {
        "h": 9,
        "w": 12,
        "x": 0,
        "y": 10
      },
      "id": 6,
      "options": {
        "legend": {
          "calcs": [],
          "displayMode": "list",
          "placement": "bottom",
          "showLegend": true
        },
        "tooltip": {
          "hideZeros": false,
          "mode": "single",
          "sort": "none"
        }
      },
      "pluginVersion": "12.0.2",
      "targets": [
        {
          "editorMode": "code",
          "expr": "100 - (avg by(instance) (rate(node_cpu_seconds_total{mode=\"idle\"}[5m])) * 100)",
          "legendFormat": "__auto",
          "range": true,
          "refId": "A"
        }
      ],
      "title": "CPU Use %",
      "type": "timeseries"
    },
    {
      "datasource": {
        "type": "prometheus",
        "uid": "cepz8xqf6zt34b"
      },
      "fieldConfig": {
        "defaults": {
          "color": {
            "mode": "thresholds"
          },
          "mappings": [],
          "thresholds": {
            "mode": "absolute",
            "steps": [
              {
                "color": "green"
              }
            ]
          }
        },
        "overrides": []
      },
      "gridPos": {
        "h": 9,
        "w": 12,
        "x": 12,
        "y": 10
      },
      "id": 4,
      "options": {
        "minVizHeight": 75,
        "minVizWidth": 75,
        "orientation": "auto",
        "reduceOptions": {
          "calcs": [
            "lastNotNull"
          ],
          "fields": "",
          "values": false
        },
        "showThresholdLabels": false,
        "showThresholdMarkers": true,
        "sizing": "auto"
      },
      "pluginVersion": "12.0.2",
      "targets": [
        {
          "editorMode": "code",
          "expr": "topk(3, floor(sum(increase(http_requests_total{route!=\".well-known/appspecific/com.chrome.devtools.json\", route!=\"metrics\"}[24h])) by (route)))",
          "legendFormat": "{{route}}",
          "range": true,
          "refId": "A"
        }
      ],
      "title": "Top Routes Visited Today",
      "type": "gauge"
    },
    {
      "datasource": {
        "type": "prometheus",
        "uid": "cepz8xqf6zt34b"
      },
      "fieldConfig": {
        "defaults": {
          "color": {
            "mode": "palette-classic"
          },
          "custom": {
            "hideFrom": {
              "legend": false,
              "tooltip": false,
              "viz": false
            }
          },
          "fieldMinMax": false,
          "mappings": []
        },
        "overrides": []
      },
      "gridPos": {
        "h": 9,
        "w": 12,
        "x": 0,
        "y": 19
      },
      "id": 7,
      "options": {
        "displayLabels": [
          "percent"
        ],
        "legend": {
          "displayMode": "list",
          "placement": "right",
          "showLegend": true
        },
        "pieType": "pie",
        "reduceOptions": {
          "calcs": [
            "lastNotNull"
          ],
          "fields": "",
          "values": false
        },
        "tooltip": {
          "hideZeros": false,
          "mode": "single",
          "sort": "none"
        }
      },
      "pluginVersion": "12.0.2",
      "targets": [
        {
          "editorMode": "code",
          "expr": "topk(5,\r\n  floor(sum by (route) (\r\n    increase(\r\n      http_requests_total{\r\n        status_code=~\"4..|5..\",\r\n        route!=\".well-known/appspecific/com.chrome.devtools.json\",\r\n        route!=\"metrics\",\r\n        route!~\"api/users/.*/(favourites|purchases)\"\r\n      }[1h]\r\n    )\r\n  ))\r\n)",
          "legendFormat": "__auto",
          "range": true,
          "refId": "A"
        }
      ],
      "title": "Top Endpoints with errors",
      "type": "piechart"
    },
    {
      "datasource": {
        "type": "prometheus",
        "uid": "cepz8xqf6zt34b"
      },
      "fieldConfig": {
        "defaults": {
          "color": {
            "mode": "palette-classic"
          },
          "custom": {
            "hideFrom": {
              "legend": false,
              "tooltip": false,
              "viz": false
            }
          },
          "mappings": []
        },
        "overrides": []
      },
      "gridPos": {
        "h": 9,
        "w": 12,
        "x": 12,
        "y": 19
      },
      "id": 2,
      "options": {
        "legend": {
          "displayMode": "list",
          "placement": "bottom",
          "showLegend": true
        },
        "pieType": "pie",
        "reduceOptions": {
          "calcs": [
            "lastNotNull"
          ],
          "fields": "",
          "values": false
        },
        "tooltip": {
          "hideZeros": false,
          "mode": "single",
          "sort": "none"
        }
      },
      "pluginVersion": "12.0.2",
      "targets": [
        {
          "disableTextWrap": false,
          "editorMode": "builder",
          "expr": "sum by(status_code) (http_requests_total)",
          "fullMetaSearch": false,
          "includeNullMetadata": true,
          "legendFormat": "__auto",
          "range": true,
          "refId": "A",
          "useBackend": false
        }
      ],
      "title": "Status Codes",
      "type": "piechart"
    }
  ],
  "preload": false,
  "refresh": "5s",
  "schemaVersion": 41,
  "tags": [],
  "templating": {
    "list": []
  },
  "time": {
    "from": "now-30m",
    "to": "now"
  },
  "timepicker": {},
  "timezone": "browser",
  "title": "Follow your purchases",
  "uid": "9ec68bdc-ea09-45cf-ac3f-2ae234019dbc",
  "version": 25
}

```

</details>

**Importante**: Si aparece un icono de error arriba a la izquierda de un panel, hay que entrar a la edicion del panel y correr la query
apretando el boton "run query" en la query row prometheus. Esto es algo que se debe hacer una unica vez, despues
se hace automaticamente cada 5 segundos.

