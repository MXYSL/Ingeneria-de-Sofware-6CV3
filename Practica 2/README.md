
# Práctica 2 – Sistema Web con Docker

Este proyecto contiene una aplicación web PHP conectada a MySQL, usando Docker para facilitar la instalación y despliegue.

## 🚀 Servicios incluidos:
- **PHP 8.2 + Apache** – Servidor web.
- **MySQL 8.0** – Base de datos.
- **phpMyAdmin** – Interfaz gráfica para gestionar MySQL.

---

## 🐳 Ejecución con Docker

### 1. Clona el proyecto y ubícate en la carpeta:
```bash
git clone (https://github.com/MXYSL/Ingeneria-de-Sofware-6CV3/tree/main/Practica%202)
cd Practica\ 2
```

### 2. Levanta los contenedores:
```bash
docker-compose up -d
```

---

## 🌐 Acceso a la aplicación

| Servicio         | URL                             |
|------------------|---------------------------------|
| Aplicación Web   | [http://localhost:8080](http://localhost:8080) |
| phpMyAdmin       | [http://localhost:8081](http://localhost:8081) |

- **phpMyAdmin Login**:
  - Usuario: `user`
  - Contraseña: `password`

---

## 🗄️ Base de datos

Al iniciar Docker, se crea automáticamente la base de datos `spring_auth` y se carga el archivo `spring_auth.sql`.

---

## 📂 Estructura relevante
```
Practica 2/
├── Dockerfile
├── docker-compose.yml
├── database/
│   └── spring_auth.sql
└── templates/
    └── *.php / *.html
```

---

## 🛑 Detener contenedores
```bash
docker-compose down
```

---

## 📬 Notas
- El código PHP se encuentra en `templates/` y se mapea automáticamente al servidor Apache.
- Puedes modificar y recargar sin reiniciar el contenedor.
