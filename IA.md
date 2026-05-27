# Uso de IA en este proyecto

## 1. Herramientas utilizadas

| Herramienta | Versión / Modelo | Modo de uso | Aprox. % del trabajo |
|---|---|---|---|
| OpenCode (esta sesión) | gpt-5-mini | Generación de esqueleto de módulo, archivos y parches iniciales | 60% |
| Ninguna | — | Desarrollo manual, ajustes, pruebas locales | 40% |

## 2. Configuración del proyecto

### CLAUDE.md / AGENTS.md
ninguno

### settings.json u otra configuración equivalente
ninguno

## 3. Skills personalizadas
ninguna

## 4. Slash commands personalizados
ninguno

## 5. Sub-agentes invocados
ninguno

## 6. MCPs (Model Context Protocol)
ninguno

## 7. Prompts importantes

### Prompt 1
- Herramienta: OpenCode
- Prompt: "Crea un módulo PrestaShop 1.7 llamado productbadges con estructura básica"
- Qué generó (resumen): Estructura de carpetas y archivos principales (productbadges.php, sql, controllers, views).
- Qué hice con el output: Revisé y modifiqué el código para cumplir con requisitos de seguridad y de PrestaShop.

### Prompt 2
- Herramienta: OpenCode
- Prompt: "Genera un ObjectModel para badges con campos multilenguaje"
- Qué generó (resumen): Clase Badge con definición para tabla y campos multilenguaje.
- Qué hice con el output: Adapté los nombres de campos y validaciones a un esquema simple.

## 8. Errores de la IA que detecté

- Qué generó la IA (mal): Código inicial sin gestión de settings del módulo ni registro/limpieza de la pestaña admin.
- Por qué estaba mal: El requisito pedía una pantalla de configuración y una desinstalación limpia.
- Cómo lo corregiste: Añadí configuración (HelperForm), valores por defecto en install() y borrado en uninstall(), y registré/eliminé la pestaña admin manualmente.

- Qué generó la IA (mal): Helpers/HelperForm generados sin propiedades mínimas (token, currentIndex), que hubieran dado un formulario incompleto en BO.
- Por qué estaba mal: HelperForm necesita algunas propiedades para renderizar correctamente.
- Cómo lo corregiste: Configuré currentIndex, token y default_form_language antes de generar el formulario.

## 9. Partes que NO usé IA

- Integración fina con el tema (CSS/posicionamiento): manual. Preferí ajustes manuales para asegurar comportamiento coherente.
- Lógica de guardado de asociaciones producto-etiqueta: implementada manualmente para asegurar sanitización.

## 10. Reflexión final

- ¿Qué te ahorró la IA en este ejercicio? Creación rápida de la estructura de archivos y borradores de código repetitivo.
- ¿En qué te entorpeció o te llevó por mal camino? Generó esqueletos incompletos que necesitaban revisar validaciones, tokens y limpieza en uninstall.
- ¿Qué cambiarías de tu flujo con IA si lo repitieras? Usaría IA para generar primero los archivos y tests, y siempre inspeccionar manualmente los puntos de integración con PrestaShop (hooks, tabs, ObjectModel).
