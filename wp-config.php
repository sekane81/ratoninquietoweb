<?php
/**
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'u596101992_raton');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'u596101992_raton');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', 'c4rrer4s');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'Ndv{[?#-+rlt$xx*Jghv.hMQT7@,fqUhZ$iIBw^T-6ub_-l;qJDY`{ Ke295&Z_C'); // Cambia esto por tu frase aleatoria.
define('SECURE_AUTH_KEY', '2;xD +^G>2Bc7d>*X(?~(|bzCkrXqqTp(hi5 *( e9-y,!61X,A!R^]++zlfi-fD'); // Cambia esto por tu frase aleatoria.
define('LOGGED_IN_KEY', ',xsX8iP-?{(V/GPYCvL)cO[%+si;<t=z[4 05bf{lHlgp3hbCe-X<%jNFfX}re3q'); // Cambia esto por tu frase aleatoria.
define('NONCE_KEY', '#~+xwE_X[H>%jv.~>z?2WQ`D)cL?BBN}6.<(YKrsIy/Tpz![*0%ZFY]+f H {(.J'); // Cambia esto por tu frase aleatoria.
define('AUTH_SALT', '/2(E$7AV_eQ{Ms_}r1:pdl|OUJH07?w[?ha-F1+9?`/(in3{3%T~+c~U2IajJNy6'); // Cambia esto por tu frase aleatoria.
define('SECURE_AUTH_SALT', ' {1 ,H{!zYlKY9u^/:~T:-sV[G{;=wP]~G>({uxM&3_.`<by5!k31V%:3LO#88RS'); // Cambia esto por tu frase aleatoria.
define('LOGGED_IN_SALT', 'qHLgoO0{a/Nu]j#jPXL=iX iuL$fUO~YtW|n5/.F+%sNrO.SZvq}[5DN@`Oh,Z/G'); // Cambia esto por tu frase aleatoria.
define('NONCE_SALT', '$+w9NRg|;MD+$&<QLs>U8vHq,)82c%4OKPf^p$p/%i711?4K<ALH.1~wxL0UAn8]'); // Cambia esto por tu frase aleatoria.

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'wp_';

/**
 * Idioma de WordPress.
 *
 * Cambia lo siguiente para tener WordPress en tu idioma. El correspondiente archivo MO
 * del lenguaje elegido debe encontrarse en wp-content/languages.
 * Por ejemplo, instala ca_ES.mo copiándolo a wp-content/languages y define WPLANG como 'ca_ES'
 * para traducir WordPress al catalán.
 */
define('WPLANG', 'es_ES');

/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
ini_set('log_errors','On');
ini_set('display_errors','Off');
ini_set('error_reporting', E_ALL );
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

