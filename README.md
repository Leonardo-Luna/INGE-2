# Apunte sobre Symfony

Este documento sirve como referencia rápida sobre los conceptos clave en un proyecto Symfony y cómo interactúan entre sí.

## Conceptos Clave

### 🏛 Entity (Entidad)
Una **entidad** es una clase de PHP que representa una tabla en la base de datos. Su función principal es almacenar su lógica y datos, sin preocuparse por cómo se guardan o recuperan.

Ejemplo:
```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Usuario {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;
    
    #[ORM\Column(type: 'string', length: 255)]
    private string $nombre;
    
    // Getters y setters...
}
```

### 🛠 Service (Servicio)
Un **servicio** encapsula lógica de negocio que se puede reutilizar en varias partes del sistema. 

Ejemplo de un servicio que envía un correo electrónico:
```php
namespace App\Service;

class EmailService {
    public function enviarCorreo(string $destinatario, string $mensaje): void {
        // Lógica para enviar correo
    }
}
```

### 📦 Repository (Repositorio)
El **repositorio** es la capa encargada de manejar las consultas a la base de datos de una entidad. Symfony genera uno por defecto cuando creamos una entidad.

Ejemplo:
```php
namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UsuarioRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Usuario::class);
    }
    
    public function encontrarPorNombre(string $nombre): ?Usuario {
        return $this->findOneBy(['nombre' => $nombre]);
    }
}
```

### 🌐 Controller (Controlador)
Los **controladores** manejan las rutas del sistema y actúan como intermediarios entre las peticiones HTTP y la lógica de la aplicación.

Ejemplo:
```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UsuarioController extends AbstractController {
    #[Route('/usuario/{id}', name: 'ver_usuario')]
    public function verUsuario(int $id): Response {
        return new Response("Usuario con ID: $id");
    }
}
```

## 🛠 Uso de MakerBundle para simplificar tareas
El **MakerBundle** permite generar automáticamente archivos base para entidades, controladores y repositorios. Los comandos más usados son:

```bash
bin/console make:entity      # Crea una nueva entidad
bin/console make:controller  # Crea un nuevo controlador
bin/console make:repository  # Crea un nuevo repositorio
```

Estos comandos generan plantillas vacías listas para ser personalizadas.

## 🏗️ Actualización del esquema de base de datos en Symfony

El **esquema** de la base de datos define las tablas, columnas y relaciones entre ellas en función de las entidades de Doctrine. Cuando se realizan cambios en las entidades (como agregar nuevas propiedades o relaciones), es necesario actualizar el esquema para reflejar estos cambios en la base de datos.

### 📌 Comando para actualizar el esquema
Para aplicar los cambios en la base de datos sin perder datos existentes, ejecuta:

```bash
bin/console doctrine:schema:update --force


## 📚 Enlaces útiles
- 📖 [Documentación de Symfony](https://symfony.com/doc/current/index.html)
- 📖 [Documentación de Doctrine](https://www.doctrine-project.org/projects/orm.html)
- 📖 [Documentación de PHP](https://www.php.net/docs.php)

# Uso básico de Docker 🐳

Esta sección describe los comandos esenciales para manejar el proyecto con Docker.

## 🚀 1. Iniciar los contenedores
Posiciónate en el directorio del proyecto donde se encuentra el archivo `docker-compose.yml` y ejecuta:

```bash
docker compose up -d
```

Esto iniciará los contenedores en segundo plano.

## 🔍 2. Acceder a un contenedor
Para entrar a un contenedor y ejecutar comandos dentro de él, usa:

```bash
docker exec -it inge-app bash # o inge-db para acceder a la base de datos
```

Esto abrirá una terminal interactiva dentro del contenedor llamado `app-inge`.

## ⏹️ 3. Detener los contenedores
Para detener y eliminar los contenedores:

1. Sal del contenedor con `Ctrl + D` o escribiendo `exit`.
2. Ejecuta el siguiente comando desde tu máquina:

   ```bash
   docker compose down
   ```

Esto cerrará y limpiará los contenedores en ejecución.

Con estos comandos esenciales, podrás manejar el proyecto sin complicaciones. 🚀

- 📖 [Documentación de Docker](https://docs.docker.com/)

# Git Workflow 🚀

Este workflow establece cómo organizaremos el trabajo con ramas en el proyecto para mantener un desarrollo ordenado y minimizar conflictos.

## 🌱 1. Creación de ramas para cada tarea
Cada tarea debe desarrollarse en una rama específica con la nomenclatura:

```
[tipo]/[contenido]
```

Ejemplos:
- `feat/login` → Nueva funcionalidad de login.
- `fix/error_login` → Corrección de un bug en el login.
- `refactor/login` → Refactorización del código relacionado con login.

### 📌 Creación y subida de una nueva rama
Para crear una nueva rama y cambiar a ella:
```bash
git checkout -b [nombre]
```
Luego, para subirla a remoto:
```bash
git add .
git commit -m "mensaje"
git push origin [nombreRama]
```

## 🔄 2. Merge de `develop` en la rama de la tarea
Cuando se haya terminado la tarea en la rama correspondiente, se debe actualizar con los últimos cambios de `develop`:

```bash
git checkout [rama]
git merge develop
```

Si hay conflictos, deben resolverse antes de continuar.

## 🔀 3. Merge de la rama a `develop`
Una vez resueltos los conflictos, la rama se fusiona en `develop` para que la funcionalidad quede en espera de ser testeada:

```bash
git checkout develop
git merge [rama]
```

## ✅ 4. Integración final con `main`
Cuando todas las funcionalidades han sido testeadas y aprobadas:
1. Se hace un merge de `main` en `develop` para garantizar que esté actualizado:

   ```bash
   git checkout develop
   git merge main
   ```

2. Luego, se fusiona `develop` en `main` para liberar la versión estable:

   ```bash
   git checkout main
   git merge develop
   ```

## 📌 Consideraciones
- Cada desarrollador trabaja en su propia rama para evitar afectar el trabajo de los demás.
- Se recomienda actualizar `develop` regularmente en cada rama para minimizar conflictos.
- La rama de este apunte no necesita actualizarse con cada cambio.

Este flujo de trabajo nos permitirá colaborar sin problemas y mantener las ramas sincronizadas. 🚀
