<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Configuracion encabezados no-cache
header('Cache-Control: private, no-store, no-cache, must-revalidate, post-check = 0, pre-check = 0');
header('Expires: -1000');
header('Pragma: no-cache');

// Configuracion de la zona horaria
date_default_timezone_set('America/La_Paz');

// Ambiente de trabajo production/development
define('environment', 'production');

// Informacion del desarrollador
define('name_autor', 'wilnicho');
define('email_autor', 'wilnicho@gmail.com');
define('site_autor', 'https://www.checkcode.bo');
define('phone_autor', '591-65193161');
define('credits', '&copy; 2019 www.checkcode.bo');

// Informacion del proyecto
define('name_app', 'sistema-app');
define('name_project', 'sistema');

// Rutas globales
define('ip_server', 'http://localhost');
define('path_app', ip_server . '/' . name_app);
define('path_project', ip_server . '/' . name_project);
define('ip_local', ip_server . ':9000/');

// Directorios principales
define('app', '../' . name_app);
define('project', '../' . name_project);

// Directorios privados de la aplicacion
define('config', app . '/config');
define('files', app . '/files');
define('libraries', app . '/libraries');
define('modules', app . '/modules');
define('start', app . '/start');
define('storage', app . '/storage');
define('templates', app . '/templates');
define('profiles', files . '/profiles');
define('institucion', files . '/institucion');
define('productos', files . '/productos');

// Directorios publicos de la aplicacion
define('css', project . '/css');
define('imgs', project . '/imgs');
define('js', project . '/js');
define('media', project . '/media');
define('themes', project . '/themes');

// Paginas principales
define('home', 'home');
define('site', 'site');
define('tools', 'tools');
define('index_private', '?/' . home . '/index');
define('index_public', '?/' . site . '/login');

// Variables de sesiones
define('user', 'user-sistema');
define('locale', 'locale-sistema');
define('temporary', 'temporary-sistema');

// Variables para cookies
define('remember', 'remember-sistema');

// Variables de seguridad
define('prefix', '@w1N');

// Variables de base de datos
define('host', 'localhost');
define('username', 'root');
define('password', '');
define('database', 'beta');
define('port', '3306');

?>