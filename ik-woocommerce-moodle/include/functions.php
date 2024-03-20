<?php

/*
Template: Init IK Woo Moodle
Author: Gabriel Caroprese / Inforket.com
Update Date: 23/02/2022
*/
/* 
    Funció para chequear si ya hay datos de 
    configuración ingresados en la tabla options
*/
function ik_woomoodle_configurado($tipoDeDatosMoodle){
    global $wpdb;
    $woomoodle_config_q = "SELECT * FROM ".$wpdb->prefix."options WHERE option_name LIKE 'ik_woomoodle_".$tipoDeDatosMoodle."'";
    $check_si_configurado = $wpdb->get_results($woomoodle_config_q);
    
    if (isset($check_si_configurado[0]->option_id)){
        $woo_moodle_existe = true;
    } else {
        $woo_moodle_existe = false;
    }
    
    return $woo_moodle_existe;
}
/* 
    Función para devolver valores de configuración
    de la base de datos
*/
function ik_woomoodle_dataconfig($dato_config){
    global $wpdb;
    $woomoodle_configvals_q = "SELECT * FROM ".$wpdb->prefix."options WHERE option_name LIKE 'ik_woomoodle_config'";
    $woomoodle_configvalues = $wpdb->get_results($woomoodle_configvals_q);
    
    if (isset($woomoodle_configvalues[0]->option_id)){
        $woo_moodle_config = maybe_unserialize($woomoodle_configvalues[0]->option_value);
        $dato_config_woomoodle = $woo_moodle_config[$dato_config];
    } else if ($dato_config == 'prefijo'){
        $dato_config_woomoodle = "mdl_";
    } else if ($dato_config == 'valuehostname'){
         $dato_config_woomoodle = "localhost";
    } else {
        $dato_config_woomoodle = "";
    }
    
    return $dato_config_woomoodle;
}

/* 
    Función para devolver valores de configuración
    de la base de datos
*/
function ik_woomoodle_datainfo($dato_configinfo){
    global $wpdb;
    $dato_configinfo = sanitize_text_field($dato_configinfo);
    $woomoodle_infovals_q = get_option('ik_woomoodle_info');

    if ($woomoodle_infovals_q != NULL){
        if (isset($woomoodle_infovals_q[$dato_configinfo])){
            $dato_info_woomoodle = $woomoodle_infovals_q[$dato_configinfo];
        } else {
            $dato_info_woomoodle = "";
        }
    } else if ($dato_configinfo == 'supportemail'){
        $dato_info_woomoodle = get_option('admin_email');
    } else {
        $dato_info_woomoodle = "";
    }
    
    return $dato_info_woomoodle;
}

// Ajax para refrescar medios subidos
add_action( 'wp_ajax_ik_woomoodle_mediasubir', 'ik_woomoodle_mediasubir'   );
function ik_woomoodle_mediasubir() {
    if(isset($_GET['id']) ){
        $id_file = intval($_GET['id']);
        if ($id_file != 0){
            $logo_file = wp_upload_dir()['baseurl'].'/'.get_post_meta( $id_file, '_wp_attached_file', true);
        } else {
            //empty file
            $logo_file = '';
        }
        wp_send_json_success( $logo_file );
    } else {
        wp_send_json_error();
    }
}

// Función para llamar datos de conexión a la base de datos
function ik_llamar_pdo_dbexterna(){
    // Tomo las variables de conexión
    $servidordb = ik_woomoodle_dataconfig('valuehostname');
    $usuario = ik_woomoodle_dataconfig('valueusuario');
    $claveMoodle = ik_woomoodle_dataconfig('valueclave');
    $nombreDB = ik_woomoodle_dataconfig('valuedbname');
    
    $ik_dbconexion = new PDO('mysql:host='.$servidordb.';dbname='.$nombreDB.';charset=utf8', $usuario, $claveMoodle);
    return $ik_dbconexion;
}

// Esta función devuelve el prefijo de la DB de Moodle
function ik_prefijo_moodle(){
    $prefijo_moodle = ik_woomoodle_dataconfig('prefijo');
    
    return $prefijo_moodle;
}

/*  Esta función lista options de select de los diferentes cursos de moodle
*/
function ik_moodle_selectcurso_options_select($CursoIDseleccionado){
    $select = '<div class="ik_id_moodle_curso_selector"><select class="_ik_id_moodle_curso" name="_ik_id_moodle_curso[]">';
    try{
        // Conexión a la base de datos
    	$dbc = ik_llamar_pdo_dbexterna();
    	$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	
        // Tomo datos de los nombres y IDs de los diferentes cursos de Moodle
        $datosCursos = "SELECT * FROM ".ik_prefijo_moodle()."course WHERE category != 0";
        $cursosInfo = $dbc->query($datosCursos);
        while($row = $cursosInfo ->fetchObject())
        {
            $cursoOptionValue[] = $row->id;
            $cursoOptionTexto[] = $row->shortname;
        }
                
        $optionCounter = 0;
        foreach ($cursoOptionValue as $cursoValue ) {
            if ($CursoIDseleccionado == $cursoValue){
                $cursoSelected = "selected";
            } else {
                $cursoSelected = "";
            }
            if (mb_detect_encoding($cursoOptionTexto[$optionCounter]) == "UTF-8"){
                $texto_curso = $cursoOptionTexto[$optionCounter];
            } else {
                $texto_curso = utf8_encode($cursoOptionTexto[$optionCounter]);
            }
            $select .= '<option '.$cursoSelected.' value="'.$cursoValue.'">'.$texto_curso.'</option>';
            $optionCounter = $optionCounter + 1;
        }
        
        $select .= '</select><div class="ik_delete_mdl_course_selector"><span class="dashicons dashicons-trash"></span></div></div>';
        
 
    } 
    catch (PDOException $e){
        $select = '<option value="">Desconectado de Moodle</option>';
    }

    return $select;
}

/*  Esta función convierte un campo de texto para asingar ID de curso en un select
    que muestra los nombres de los cursos en Moodle
*/
function ik_moodle_selectcurso_producto($CursoIDseleccionados){
    
        $ik_moodle_selectCursos = '<div id="ik_id_moodle_curso_select_wrapper">';

        if(is_array($CursoIDseleccionados)){
            foreach($CursoIDseleccionados as $CursoIDseleccionado){
                $ik_moodle_selectCursos .= ik_moodle_selectcurso_options_select($CursoIDseleccionado);
            }

        } else {
            $ik_moodle_selectCursos .= ik_moodle_selectcurso_options_select($CursoIDseleccionados);
        }
        
        $ik_moodle_selectCursos .= '</div><button id="ik_add_course_mld_course" class="button">Agregar</button>';

        echo "<script>
            jQuery( '#_ik_id_moodle_curso' ).replaceWith( '".$ik_moodle_selectCursos."' );
            jQuery( '.ik_id_moodle_curso .description' ).remove();
            jQuery('.product_data').on('click', '#ik_add_course_mld_course', function(){
                jQuery( '#ik_id_moodle_curso_select_wrapper .ik_id_moodle_curso_selector:first-child' ).clone().appendTo( '#ik_id_moodle_curso_select_wrapper' );
                jQuery( '#ik_id_moodle_curso_select_wrapper .ik_id_moodle_curso_selector:last-child select' ).val('');
                return false;
            }); 
            jQuery('.product_data').on('click', '#ik_id_moodle_curso_select_wrapper .ik_delete_mdl_course_selector', function(){
                jQuery(this).parent().remove();
                return false;
            }); 
        </script>
        <style>
        #ik_id_moodle_curso_select_wrapper{
            display: block;
        }
        .ik_id_moodle_curso{
            display: block;
            width: 100%;
            max-width: 200px;
        }
        ._ik_id_moodle_curso select {
            float: unset! important;
            display: block;
        }
        #ik_id_moodle_curso_select_wrapper .ik_delete_mdl_course_selector{
            cursor: pointer;
            position: relative;
            top: 4px;
            left: 2px;
        }
        .ik_id_moodle_curso_selector:first-child .ik_delete_mdl_course_selector {
            display: none;
        }
        .ik_id_moodle_curso_selector {
            display: flex;
            margin-bottom: 12px;
        }
        .ik_id_moodle_curso button{
            margin-top: 7px! important;
        }
        </style>";
}

/* 
    Agrego dos campos, uno de checkbox y otro para ID de Moodle 
    para habilitar asociación con un producto específico 
    en la página de editar producto
*/
add_action( 'woocommerce_product_options_advanced', 'ik_moodle_wooo_activar_producto' );
function ik_moodle_wooo_activar_producto() {
    global $woocommerce, $post;
    
    // Chequear si el checkbox fue tildado anteriormente
    $checkbox_moodle_on = get_post_meta( $post->ID, '_ik_moodle_wooo_activar', true );
    if( empty( $checkbox_moodle_on ) ) $checkbox_moodle_on = '';
    
    // Creo los campos especiales para asociar a Moodle
    woocommerce_wp_checkbox( 
    	array( 
    		'id'            => '_ik_moodle_wooo_activar', 
    		'wrapper_class' => 'ik_moodle_wooo_activar', 
    		'label'         => __('Activar asociación con Moodle' ), 
    		'description'   => __( 'Al tildar acá vas a activar la asociación entre Moodle y Woocommerce' ),
    		'value'         => $checkbox_moodle_on,
    		)
    	);
    woocommerce_wp_text_input(
        array(
            'id'          => '_ik_id_moodle_curso',
            'wrapper_class' => 'ik_id_moodle_curso', 
            'placeholder' => 'Ingresá el ID',
            'label'       => 'ID asociado de curso de Moodle',
            'description' => 'ID de Moodle asociado a este producto.',
        )
    );
    
    /*  Si la conexión con Moodle funciona, 
        transformo el input text para el ID de Moodle en un select
    */
    $id_curso_configurado = get_post_meta( $post->ID, '_ik_id_moodle_curso', true );

    ik_moodle_selectcurso_producto($id_curso_configurado);
}
// Acción de salvado de campos especiales de Woocommerce en página de producto
add_action( 'woocommerce_process_product_meta', 'ik_moodle_wooo_guardar_datos_prod' );
function ik_moodle_wooo_guardar_datos_prod( $post_id ) {

    // Checkbox
    $ik_moodle_wooo_activar = isset( $_POST['_ik_moodle_wooo_activar'] ) ? 'yes' : '';
    update_post_meta( $post_id, '_ik_moodle_wooo_activar', $ik_moodle_wooo_activar );
	
	//Datos de ID de curso de Moodle
    if(isset($_POST['_ik_id_moodle_curso'])){
        if(is_array($_POST['_ik_id_moodle_curso'])){
            foreach($_POST['_ik_id_moodle_curso'] as $id_courses){
                $ik_id_moodle_curso[] = absint($id_courses);
            }
        } else {
            $ik_id_moodle_curso = absint($_POST['_ik_id_moodle_curso']);
        }
    } else {
        $ik_id_moodle_curso = '';
    }
    
    if(!empty($ik_id_moodle_curso)){
        update_post_meta( $post_id, '_ik_id_moodle_curso', $ik_id_moodle_curso);
    }
}

// función para generar una contraseña aleatoria
function ik_clave_aleatoria_moodle() {
    $caracteres = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789.,!";
    $clave = array();
    $caracteresLargo = strlen($caracteres) - 1; //put the length -1 in cache
    for ($i = 0; $i < 10; $i++) {
        $n = rand(0, $caracteresLargo);
        $clave[] = $caracteres[$n];
    }
    return implode($clave); //turn the array into a string
}

/* 
    Función para chequear conexión a la base de datos de Moodle
*/
function ik_conexion_dbmoodle(){

    $conectado = '<p><div id="ik_conexionMoodle" class="ik_okConexion">Conexión Establecida</div></p>';
    $errorMensaje = '<p><div id="ik_conexionMoodle" class="ik_errorConexion">Desconectado</div></p>';
        
    // Conexión a la base de datos
    try
    {
    	$dbc = ik_llamar_pdo_dbexterna();
    	$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$woomoodle_chequeo_user = "SELECT * FROM ".ik_prefijo_moodle()."user";
    	$resultTablaUser = $dbc->query($woomoodle_chequeo_user);    	
    	$woomoodle_chequeo_user = "SELECT * FROM ".ik_prefijo_moodle()."course";
    	$resultTablaUser = $dbc->query($woomoodle_chequeo_user);
    	echo $conectado;
    	$dbc = NULL;
    }
    catch (PDOException $e)
    {
        echo $errorMensaje;
    }
}

// Función para mostrar un header con el logo
function ik_moodlewoo_mostrarlogo(){
    // Si hay un logo muestro devuelvo un header
    if (ik_woomoodle_datainfo('logourl') != ""){
        $logoEnHeader = '<div style="padding: 20px; background: #f1f1f1; text-align: center; margin-bottom: 20px;"><img src="'.ik_woomoodle_datainfo('logourl').'"></div>';
    } else{
        $logoEnHeader = '';
    }
    return $logoEnHeader;
}


// Función para crear usuario en Moodle y asignar el usuario a un curso
function ik_moodlewoo_get_messages($tipoMensaje){
    $tipoMensaje = sanitize_text_field($tipoMensaje);
    if ($tipoMensaje == 'email'){
        $message_options = get_option('IK_MOODLEWOO_MESSAGES')['email'];
        if ($message_options != NULL){
           $message =  wp_kses_normalize_entities($message_options);
        } else {
            //Mensaje por defecto
            $message =  '{{WEB_LOGO}}<div style="max-width: 450px; margin: 0 auto;"><p>{{ESTUDIANTE_NOMBRE}},</p><p>A continuación te enviamos información acerca del nuevo curso al que te acabas de registrar:</p><p><b>Acceso a tu curso en:</b> <a target="_blank" href="{{MOODLE_URL}}">{{MOODLE_URL}}</a></p><p><b>Usuario:</b> {{ESTUDIANTE_EMAIL}}</p>
            <p><b>Contraseña:</b> {{ESTUDIANTE_PASSWORD}}</p><p>Ante cualquier problema o consulta, no dudes en contactarnos a través de nuestro sitio web: {{MOODLE_URL}} o escribiéndonos a {{EMAIL_SOPORTE}}</p></div>';
        }
    } else if ($tipoMensaje == 'subject'){
        $message_options = get_option('IK_MOODLEWOO_MESSAGES')['subject'];
        if ($message_options != NULL){
           $message =  $message_options;
        } else {
            //Mensaje por defecto
            $message =  '{{ESTUDIANTE_NOMBRE}}, tus datos de acceso al curso: {{MOODLE_CURSO}}';
        }
    } else if ($tipoMensaje == 'error'){
        $message_options = get_option('IK_MOODLEWOO_MESSAGES')['error'];
        if ($message_options != NULL){
           $message =  wp_kses_normalize_entities($message_options);
        } else {
            //Mensaje por defecto
            $message =  'Por favor, contactar a soporte para recibir tu usuario y contraseña para acceder al curso.';
        }
    } else {
        $message_options = get_option('IK_MOODLEWOO_MESSAGES')['checkout'];
        if ($message_options != NULL){
           $message =  wp_kses_normalize_entities($message_options);
        } else {
            //Mensaje por defecto
            $message =  '<p><b>Acceso a tu curso en:</b> {{MOODLE_URL}}</p><p><b>Usuario:</b> {{ESTUDIANTE_EMAIL}}</p>
            <p><b>Contraseña:</b> {{ESTUDIANTE_PASSWORD}}</p>';
        }
    }
    return $message;
}

//funcion para crear usuario o enviar email al completar orden o quedar en procesando
add_action('save_post','ik_moodlewoo_crear_usuario_enviar_email');
function ik_moodlewoo_crear_usuario_enviar_email($post_id){
	$post = get_post( $post_id );
	
	//Me fijo que sea un pedido
    if ($post->post_type != 'shop_order'){
        return;
    } else {
		
        global $woocommerce;
        $order = wc_get_order($post_id);
        
        /*  Me fijo si la orden ya fue pagada o marcada como completada o procesando, 
            para asegurarme que le doy accesos a Moodle a la persona correcta
        */
        if ($order != false){
            if($order->is_paid() || $order->has_status('processing') || $order->has_status('completed')) {
                
            // Chequeo si entre los productos comprados hay uno asociado a Moodle    
                foreach ($order->get_items() as $item_id => $item ) {
                    
                    // Chequeo si el producto está activado y tiene un ID asociado para la asociación con Moodle
                    if ( get_post_meta( $item->get_product_id(), '_ik_moodle_wooo_activar', true) !== NULL && get_post_meta( $item->get_product_id(), '_ik_id_moodle_curso', true) !== NULL )  {
                        if ( get_post_meta( $item->get_product_id(), '_ik_moodle_wooo_activar', true) == "yes" )  {
                            
                            /*  Llamo la función que crea o/y asignar el usuario, 
                                envía un email y muestra en pantalla los datos 
                                de acceso al curso de Moodle
                            */
                            ik_moodlewoo_crearasociar_usermoodle($post_id, $item->get_product_id(), false);
                        }
                        
                    }
                }
            }
        }
		
    }
}


// Función para crear usuario en Moodle y asignar el usuario a un curso
function ik_moodlewoo_crearasociar_usermoodle($idOrdenWoo, $IDproducto, $show = true){
    
    global $woocommerce, $post;
    $idOrdenWoo = intval($idOrdenWoo);
    $IDproducto = intval($IDproducto);

    // Asigno las variables para hacer las diferentes tareas
    $nombre_curso_producto = get_the_title( $IDproducto );
    $moodleURL = ik_woomoodle_dataconfig('valueurl');
    $u_moodle = get_post_meta( $idOrdenWoo, '_billing_email', true );
    $claveCreada = ik_clave_aleatoria_moodle();
    $hp_moodle = password_hash($claveCreada, PASSWORD_DEFAULT);
    $nombreUsuario = get_post_meta( $idOrdenWoo, '_billing_first_name', true );
    $apellidoUsuario = get_post_meta( $idOrdenWoo, '_billing_last_name', true );
    $email = get_post_meta( $idOrdenWoo, '_billing_email', true );
    $IDcursos = get_post_meta( $IDproducto, '_ik_id_moodle_curso', true );  
    
    
    try{
       
        // Conexión a la base de datos
    	$dbc = ik_llamar_pdo_dbexterna();
    	$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	
        // Chequeo que el usuario a crear no exista
        $chequearUsuario = "SELECT * FROM ".ik_prefijo_moodle()."user WHERE email = '".$email."'";
        $resultusuario = $dbc->query($chequearUsuario);
        while($row = $resultusuario->fetchObject())
        {
            $usuarioRep = $row->id;
        }
        
        
        //Recibo los datos de los mensajes y lo decodifico
        $mensajeCheckout = ik_moodlewoo_get_messages('checkout');
        $mensajeSubject = ik_moodlewoo_get_messages('subject');
        $mensajeEmail = ik_moodlewoo_get_messages('email');
        $errorMensaje = '<div class="ik-datos-usuario-moodle">'.ik_moodlewoo_get_messages('error').'</p></div>';
        $mensajeCheckout = str_replace("{{MOODLE_URL}}", $moodleURL, $mensajeCheckout);
        $mensajeEmail = str_replace("{{MOODLE_URL}}", $moodleURL, $mensajeEmail);
        $mensajeSubject = str_replace("{{MOODLE_URL}}", $moodleURL, $mensajeSubject);
        $errorMensaje = str_replace("{{MOODLE_URL}}", $moodleURL, $errorMensaje);
        $mensajeCheckout = str_replace("{{ESTUDIANTE_EMAIL}}", $email, $mensajeCheckout);
        $mensajeEmail = str_replace("{{ESTUDIANTE_EMAIL}}", $email, $mensajeEmail);
        $mensajeSubject = str_replace("{{ESTUDIANTE_EMAIL}}", $email, $mensajeSubject);
        $errorMensaje = str_replace("{{ESTUDIANTE_EMAIL}}", $email, $errorMensaje);
        $mensajeCheckout = str_replace("{{WEB_LOGO}}", ik_moodlewoo_mostrarlogo(), $mensajeCheckout);
        $mensajeEmail = str_replace("{{WEB_LOGO}}", ik_moodlewoo_mostrarlogo(), $mensajeEmail);
        $mensajeSubject = str_replace("{{WEB_LOGO}}", ik_moodlewoo_mostrarlogo(), $mensajeSubject);
        $errorMensaje = str_replace("{{WEB_LOGO}}", ik_moodlewoo_mostrarlogo(), $errorMensaje);
        $mensajeCheckout = str_replace("{{ESTUDIANTE_NOMBRE}}", $nombreUsuario, $mensajeCheckout);
        $mensajeEmail = str_replace("{{ESTUDIANTE_NOMBRE}}", $nombreUsuario, $mensajeEmail);
        $mensajeSubject = str_replace("{{ESTUDIANTE_NOMBRE}}", $nombreUsuario, $mensajeSubject);
        $errorMensaje = str_replace("{{ESTUDIANTE_NOMBRE}}", $nombreUsuario, $errorMensaje);
        $mensajeCheckout = str_replace("{{ESTUDIANTE_APELLIDO}}", $apellidoUsuario, $mensajeCheckout);
        $mensajeEmail = str_replace("{{ESTUDIANTE_APELLIDO}}", $apellidoUsuario, $mensajeEmail);
        $mensajeSubject = str_replace("{{ESTUDIANTE_APELLIDO}}", $apellidoUsuario, $mensajeSubject);
        $errorMensaje = str_replace("{{ESTUDIANTE_APELLIDO}}", $apellidoUsuario, $errorMensaje);
        $mensajeCheckout = str_replace("{{EMAIL_SOPORTE}}", ik_woomoodle_datainfo('supportemail'), $mensajeCheckout);
        $mensajeEmail = str_replace("{{EMAIL_SOPORTE}}", ik_woomoodle_datainfo('supportemail'), $mensajeEmail);
        $mensajeSubject = str_replace("{{EMAIL_SOPORTE}}", ik_woomoodle_datainfo('supportemail'), $mensajeSubject);
        $errorMensaje = str_replace("{{EMAIL_SOPORTE}}", ik_woomoodle_datainfo('supportemail'), $errorMensaje);
        $mensajeCheckout = str_replace("{{TELEFONO_SOPORTE}}", ik_woomoodle_datainfo('phone'), $mensajeCheckout);
        $mensajeEmail = str_replace("{{TELEFONO_SOPORTE}}", ik_woomoodle_datainfo('phone'), $mensajeEmail);
        $mensajeSubject = str_replace("{{TELEFONO_SOPORTE}}", ik_woomoodle_datainfo('phone'), $mensajeSubject);
        $errorMensaje = str_replace("{{TELEFONO_SOPORTE}}", ik_woomoodle_datainfo('phone'), $errorMensaje);
        $mensajeCheckout = str_replace("{{MOODLE_CURSO}}", $nombre_curso_producto, $mensajeCheckout);
        $mensajeEmail = str_replace("{{MOODLE_CURSO}}", $nombre_curso_producto, $mensajeEmail);
        $mensajeSubject = str_replace("{{MOODLE_CURSO}}", $nombre_curso_producto, $mensajeSubject);
        $errorMensaje = str_replace("{{MOODLE_CURSO}}", $nombre_curso_producto, $errorMensaje);


        //Defino una password por defecto
        $password = "****Tu Contraseña Actual****";
        
        
        // Si el usuario no existe
        
        if (!isset($usuarioRep)){
            $crearUsuario = "INSERT INTO ".ik_prefijo_moodle()."user (auth, confirmed, mnethostid, username, password, firstname, lastname, email)
            VALUES ('manual', 1, 1, '".$u_moodle."', '".$hp_moodle."', '".$nombreUsuario."', '".$apellidoUsuario."', '".$email."')";
            $result = $dbc->prepare($crearUsuario);
            $result->execute();
    
            // si la clave no fue mostrada antes
            if (get_post_meta( $idOrdenWoo, 'ik_moodle_datos_mostrados', true ) != 1){
                $password = $claveCreada;
            }
        }
        
        //Defino el dato de password a mostrar
        $mensajeCheckout = str_replace("{{ESTUDIANTE_PASSWORD}}", $password, $mensajeCheckout);
        $mensajeEmail = str_replace("{{ESTUDIANTE_PASSWORD}}", $password, $mensajeEmail);

        
        /* 
            Voy a asociar el usuario al curso. 
            Para esto voy a conseguir el ID de usuario creado o existente. 
            Este ID lo necesito para asociar al usuario con el curso.
        */
        $verIDusuario = "SELECT * FROM ".ik_prefijo_moodle()."user WHERE email='".$email."'";
        $result = $dbc->query($verIDusuario);
        while($row = $result->fetchObject())
        {
            $id = $row->id; // Este es el ID del usuario creado o existente.
        }
       
        
        $IDcursos = (is_array($IDcursos)) ? $IDcursos : array($IDcursos);

        foreach ($IDcursos as $IDcurso){
            $enrolamientoID = "SELECT id FROM ".ik_prefijo_moodle()."enrol WHERE courseid=".$IDcurso." AND enrol='manual'";
            $result = $dbc->query($enrolamientoID);
            while($row = $result->fetchObject())
            {
                $idenrol = $row->id; 
            }        
            
            ///Voy a conseguir el contexto basado en el ID de curso
            $conseguirContexto = "SELECT id FROM ".ik_prefijo_moodle()."context WHERE contextlevel=50 AND instanceid=".$IDcurso; // Con context 50 en Moodle se refiere a cursos
            $result = $dbc->query($conseguirContexto);
            while($row = $result->fetchObject())
            {
                $idcontext = $row->id; 
            }   
            
            
            // Voy a chequear que no haya una asociación previa al curso de este usuario
                // Chequeo que el usuario no exista
            $chequearUsuario = "SELECT * FROM ".ik_prefijo_moodle()."user_enrolments WHERE userid = ".$id." AND enrolid = ".$idenrol;
            $resultusuario = $dbc->query($chequearUsuario);
            while($row = $resultusuario->fetchObject())
            {
                $cursoAsocRep = $row->id;
            }
            // Si la asociación no existe
            if (!isset($cursoAsocRep)){
            
                ///Esto es para asociar la duración del curso. Le voy a poner cero para que sea ilimitado
                $time = time();
                $ntime = 0;
                $asociarTiempoCurso = "INSERT INTO ".ik_prefijo_moodle()."user_enrolments (status, enrolid, userid, timestart, timeend, timecreated, timemodified)
                VALUES (0, ".$idenrol.", ".$id.", '".$time."', '".$ntime."', '".$time."', '".$time."')";
                $result = $dbc->prepare($asociarTiempoCurso);
                $result->execute();
                
                $asociarCurso = "INSERT INTO ".ik_prefijo_moodle()."role_assignments (roleid, contextid, userid, timemodified)
                VALUES (5, ".$idcontext.", '".$id."', '".$time."')"; // El roleid 5 es el de estudiante
                $result = $dbc->prepare($asociarCurso);
                $result->execute();
            }
            
            // Envío un email la primera vez que es mostrada la clave y los datos de la orden
                        
            //Me fijo si fue enviado antes
            $enviado_antes = get_post_meta($idOrdenWoo, 'ik_moodle_email_enviado', true);
    
            if (intval($enviado_antes) != 1){
                if (get_post_meta( $idOrdenWoo, 'ik_moodle_datos_mostrados', true ) != 1){
                    $para = $email;
                    $asunto = $mensajeSubject;
                    $cuerpoMensaje = $mensajeEmail;
                    $cabecera = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail( $para, $asunto, $cuerpoMensaje, $cabecera );
                    
                /* Marco la contraseña como mostrada para que no se muestre 
                    constantemente al cargar la página, ni tampoco envíe el email
                */
                 add_post_meta($idOrdenWoo,'ik_moodle_datos_mostrados','1');
                 
                 //Marco email enviado
                 add_post_meta($idOrdenWoo,'ik_moodle_email_enviado','1');
                 
                }
            }

        }
		
        // Devuelvo el mensaje con los datos del curso pagado y su registración
        if ($show == true){
            echo '<div id="ik-datos-usuario-moodle">'.$mensajeCheckout.'</div>';
        }
        
        //I close the connection
        $dbc = NULL;
    }
    catch (PDOException $e){
        if ($show == true){
            echo '<div id="ik-datos-usuario-moodle">'.$errorMensaje.'</div>';
        }
    }
}

/*  Si el producto o uno de los productos comprados está asociado a Moodle, 
    esta función va a solicitar la creación de un usuario en Moodle, brindar un link de acceso 
    y enviarle un correo electrónico con la confirmación
*/
add_action('woocommerce_thankyou', 'ik_moodle_wooo_datosdeacceso', 5);
function ik_moodle_wooo_datosdeacceso($atts){
    global $woocommerce, $wp;
    $order_id  = absint( $wp->query_vars['order-received'] );
    $order = wc_get_order($order_id);
    
    /*  Me fijo si la orden ya fue pagada o marcada como completada o procesando, 
        para asegurarme que le doy accesos a Moodle a la persona correcta
    */
    if ($order != false){
        if($order->is_paid() || $order->has_status('processing') || $order->has_status('completed')) {
            
        // Chequeo si entre los productos comprados hay uno asociado a Moodle    
            foreach ($order->get_items() as $item_id => $item ) {
                
                // Chequeo si el producto está activado y tiene un ID asociado para la asociación con Moodle
                if ( get_post_meta( $item->get_product_id(), '_ik_moodle_wooo_activar', true) !== NULL && get_post_meta( $item->get_product_id(), '_ik_id_moodle_curso', true) !== NULL )  {
                    if ( get_post_meta( $item->get_product_id(), '_ik_moodle_wooo_activar', true) == "yes" )  {
                        
                        /*  Llamo la función que crea o/y asignar el usuario, 
                            envía un email y muestra en pantalla los datos 
                            de acceso al curso de Moodle
                        */
                        ik_moodlewoo_crearasociar_usermoodle($order_id, $item->get_product_id());
                        echo '<style>
                        #ik-datos-usuario-moodle {
                            padding: 20px;
                            background: #f1f1f1;
                            max-width: 500px;
                            margin: 10px auto 40px;
                        }
                        </style>';
                    }
                    
                }
            }
        }
    }
	
}

?>