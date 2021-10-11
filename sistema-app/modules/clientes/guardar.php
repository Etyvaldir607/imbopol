<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {

	// Verifica la existencia de los datos enviados
	if (isset($_POST['nombres']) && isset($_POST['ci']) ) {
		// Obtiene los datos del empleado
		$nombres = trim($_POST['nombres']);
		$ci = trim($_POST['ci']);
		$direccion= trim($_POST['direccion']);
		$telefono = trim($_POST['telefono']);

        if($_POST['id_cliente']!=0){
            $id = $_POST['id_cliente'];
            $cliente = array(
                'cliente' => $nombres,
                'nit' => $ci,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'estado' => 'si'
            );
            $db->where('id_cliente',$id)->update('inv_clientes', $cliente);
        }else{
            $bus = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombres, 'nit' => $ci))->fetch_first();

            if($bus){
                $_SESSION[temporary] = array(
                    'alert' => 'danger',
                    'title' => 'El cliente ya existe!',
                    'message' => 'El registro no se realizó correctamente.'
                );
            }else{
                $cliente = array(
                    'cliente' => $nombres,
                    'nit' => $ci,
                    'direccion' => $direccion,
                    'telefono' => $telefono,
                    'estado' => 'si'
                );
                $db->insert('inv_clientes', $cliente);
                $_SESSION[temporary] = array(
                    'alert' => 'success',
                    'title' => 'Se creo el nuevo cliente!',
                    'message' => 'El registro se realizó correctamente.'
                );
            }
        }
		// Redirecciona a la pagina principal
		redirect('?/clientes/listar');
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>