## Product Badges - Módulo para PrestaShop

Este repositorio contiene un módulo para PrestaShop 1.7 llamado `productbadges` que proporciona insignias visuales reutilizables para productos (por ejemplo: “NUEVO”, “OFERTA”).

## Instalación

1. Copia la carpeta `modules/productbadges` dentro del directorio `modules/` de tu instalación de PrestaShop.
2. En el panel de administración de PrestaShop, ve a **Módulos > Gestor de módulos** e instala el módulo `productbadges`.
3. Abre la configuración del módulo (**Configurar**) para establecer las opciones globales y después utiliza la pestaña de administración **“Manage badges”** para crear/editar insignias y asignar IDs de productos.

## Notas técnicas

* Versión objetivo de PrestaShop: 1.7.8.x (pensado para 1.7.8.11)
* PHP: 7.4 / 8.1
* `bootstrap: true`
* Sin dependencias de Composer

## Funcionalidades implementadas

* Crear/editar/eliminar insignias (texto por idioma, color de fondo/texto, posición y estado activo).
* Asignar insignias a productos usando un campo simple de IDs de producto separados por comas en el formulario de edición de insignias (relación muchos-a-muchos almacenada en base de datos).
* Mostrar insignias en la página de producto y en los listados de productos (hooks) según la configuración del módulo.
* Configuración del módulo:

  * activar/desactivar módulo,
  * mostrar en listados,
  * mostrar en página de producto,
  * número máximo de insignias visibles.
* Soporte multilenguaje para el texto de las insignias (utiliza los idiomas de PrestaShop mediante campos multilenguaje de `ObjectModel`).
* Instalación/desinstalación limpia:

  * crea y elimina tres tablas de base de datos,
  * registra/desregistra una pestaña de administración,
  * y las claves de configuración correspondientes.

## Notas / Limitaciones conocidas

* La interfaz para asignar productos es básica (IDs separados por comas). Se podría añadir un selector de productos más avanzado con autocompletado.
* Las plantillas y el CSS son mínimos y pueden requerir ajustes según el tema utilizado (el posicionamiento depende del marcado HTML del tema).
* No incluye lógica de invalidación de caché en frontend.

Consulta `IA.md` para ver notas sobre el uso de IA en este proyecto.

