# Sistema de Tickets (Symfony)

Sistema básico de gestión de tickets desarrollado con **Symfony 7**, orientado a aprender buenas prácticas de backend, autenticación y CRUD completo.

---

## 🚀 Funcionalidades

- Registro de usuarios
- Login / Logout con sistema de seguridad de Symfony
- Creación de tickets
- Listado de tickets por usuario
- Visualización de ticket individual
- Edición de tickets
- Eliminación de tickets con protección CSRF
- Cambio de estado del ticket (Abierto / Cerrado)
- Control de acceso: cada usuario solo puede ver y modificar sus propios tickets

---

## 🛠️ Tecnologías usadas

- PHP 8+
- Symfony 7
- Doctrine ORM
- Twig
- Symfony Security
- Bootstrap 5
- MySQL / MariaDB

---

## 🔐 Seguridad

- Autenticación con `security` bundle de Symfony
- Protección de rutas con roles (`ROLE_USER`)
- Validación de propiedad del ticket (owner check)
- Protección CSRF en formularios POST (delete)
- Validación de datos con Symfony Validator

---

## 📊 Modelo principal

### Ticket

- id
- title
- description
- createdAt
- creator (User)
- status (Enum: OPEN / CLOSED)

### User

- id
- email
- roles
- password

---

## 🔄 Estados del ticket

Los tickets pueden tener dos estados:

- 🟢 Abierto
- 🔴 Cerrado

El estado puede cambiarse desde la vista de detalle del ticket.

---

## 🧠 Aprendizajes

Este proyecto me ha permitido practicar:

- Arquitectura MVC en Symfony
- Doctrine ORM y relaciones ManyToOne
- Formularios con Symfony Form
- Validación con Constraints
- Seguridad con Symfony Security
- Uso de Enum en PHP
- Gestión de migraciones con Doctrine
- Buenas prácticas en control de acceso

---

## ▶️ Cómo ejecutar el proyecto

Clonar el repositorio e instalar depencias
```bash
git clone <repo-url>
cd sistema_de_tickets
composer install
```

Configurar la base de datos en el archivo .env del proyecto
```bash
DATABASE_URL="mysql://user:password@127.0.0.1:3306/sistema_tickets"
````

Crear la BD y ejecutar migraciones
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
````

Iniciar el servidor
```bash
symfony serve
```

Una vez iniciado, acceder a:
```md
http://127.0.0.1:8000
```
