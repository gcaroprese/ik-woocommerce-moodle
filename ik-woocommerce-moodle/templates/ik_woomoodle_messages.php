<?php
/*

Template: Woocommerce Moodle - Messages
Author: Gabriel Caroprese / Inforket.com
Update Date: 14/06/2021

*/


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_order']) && isset($_POST['message_subject']) && isset($_POST['email_message']) && isset($_POST['error_message']) ){

    // Etiquetas permitidas en HTML
    $html_filter = array(
                        'a' => array(
                        'href' => array(),
                        'target' => array(),
                        ),
                        'br' => array(),
                        'p' => array(
                            'style' => array(
                                'text-align' => array(),
                                'font-size' => array(),
                                'font-weight' => array(),
                                'font-style' => array(),
                                ),
                            ),
                        'div' => array(
                            'style' => array(
                                'text-align' => array(),
                                'font-size' => array(),
                                'font-weight' => array(),
                                'font-style' => array(),
                                ),
                            ),
                        'strong' => array(),
                        'b' => array(),
                        'img' => array(
                            'src' => array(),
                            'alt' => array(),
                            'width' => array(),
                            'height' => array(),
                            ),
                );

    // Levanto las variables que submití en el form
    $message_order = wp_kses($_POST['message_order'], $html_filter );
    $message_order = str_replace("\\", "", $message_order);
    $message_subject = str_replace("'", "", $_POST['message_subject']);
    $message_subject = str_replace('"', "", $message_subject);
    $message_subject = sanitize_text_field($message_subject);
    $message_subject = str_replace("\\", "", $message_subject);
    $email_message = wp_kses($_POST['email_message'], $html_filter );
    $email_message = str_replace("\\", "", $email_message);
    $error_message = wp_kses($_POST['error_message'], $html_filter );
    $error_message = str_replace("\\", "", $error_message);
        
    /* 
        Chequea si el sistema ya fue cargado con los datos de acceso 
        a la base de dato y URL de la web Moodle 
        para ver si hago un INSERT o un UPDATE en la base de datos
    */
    
    $messages_woomoodle  = array (
            'email' =>$email_message,
            'subject' =>$message_subject,
            'error' =>$error_message,
            'checkout' =>$message_order
    );       
    
    if (get_option('IK_MOODLEWOO_MESSAGES') == NULL){
        
        add_option('IK_MOODLEWOO_MESSAGES', $messages_woomoodle);

    } else {
                    
        update_option('IK_MOODLEWOO_MESSAGES', $messages_woomoodle);
            
    }

    $typage = wp_kses_normalize_entities($message_order);
    $subject = $message_subject;
    $emailMessage = wp_kses_normalize_entities($email_message);
    $errorMessage = wp_kses_normalize_entities($error_message);
    $actionDone = '<p>Actualizado</p>';
} else {
    $typage = ik_moodlewoo_get_messages('typage');
    $subject = ik_moodlewoo_get_messages('subject');
    $emailMessage = ik_moodlewoo_get_messages('email');
    $errorMessage = ik_moodlewoo_get_messages('error');
    $actionDone = '';
}
?>
<style>
.error{ display: none; }
label, input {
    display: block;
    width: 200px;
}
label{ margin-bottom: 20px; }
label span {
    padding-bottom: 2px;
    display: block;
}
input[type=submit] {
    background: #000;
    color: #fff;
    border: 0;
    cursor: pointer;
    padding: 7px 15px;
}
.ik_woomoodle_panel label img {
    width: 200px;
    max-height: 200px;
}
.button_moodle_red{
    background: red! important;
    border-color: red! important;
}
#panel-form-woomoodle h3 {
    text-align: left;
    font-size: 14px;
    margin-top: 25px;
    margin-bottom: -7px;
}
#panel-form-woomoodle h4 {
    text-align: left;
    padding-left: 3px;
    margin-bottom: 7px;
}
#panel-form-woomoodle label, #panel-form-woomoodle textarea, #panel-form-woomoodle input[type=text]{
    display: grid;
    min-width: 300px;
    width: 90%;
    max-width: 570px;
}
#panel-form-woomoodle textarea{
    min-height: 150px;
}
#ik_error_field_moodlewoo{
    min-height: 70px! important;
}
#db-woomoodle-form input[type=text] {
    padding-left: 6px! important;
    text-align: left;
}
</style>
<div id="panel-form-woomoodle">
    <div class="ik_woomoodle_panel">
    <h2>Configuración de Mensajes</h2>
    <h3> Permitido el uso de HTML y los siguientes atajos:</h3>
    <p>
    URL de Web Moodle: {{MOODLE_URL}}<br />
    EMAIL de estudiante: {{ESTUDIANTE_EMAIL}}<br />
    Separador con logo de la web: {{WEB_LOGO}}<br />
    Nombre de estudiante: {{ESTUDIANTE_NOMBRE}}<br />
    Apellido de estudiante: {{ESTUDIANTE_APELLIDO}}<br />
    Correo electrónico de soporte: {{EMAIL_SOPORTE}}<br />
    Teléfono de soporte: {{TELEFONO_SOPORTE}}<br />
    Nombre del curso: {{MOODLE_CURSO}}
    </p>
    <form action="" method="post" id="db-woomoodle-form" enctype="multipart/form-data" autocomplete="no">
        <label for="messages-order">
            <h4>Mensaje de Pedido Completado</h4>
            <textarea name="message_order" placeholder="Completa un mensaje para mostrar en la página de orden completada si el usuario fue creado." required><?php echo $typage; ?></textarea>
        </label>
        <label for="messages-email-asunto">
            <h4>Email Con Datos de Acceso - Asunto</h4>
            <input name="message_subject" type="text" placeholder="Completar Asunto" required value="<?php echo $subject; ?>"></textarea>
        </label>  
        <label for="messages-emailg">
            <h4>Email Con Datos de Acceso - Mensaje</h4>
            <textarea name="email_message" placeholder="Completa un mensaje para enviar por correo electrónico al hacerse la compra." required ><?php echo $emailMessage; ?></textarea>
        </label>  
        <label for="messages-error">
            <h4>Mensaje de error al querer dar datos de acceso</h4>
            <textarea name="error_message" id="ik_error_field_moodlewoo" placeholder="Completa un mensaje de error para mostrar si algo sale mal." required ><?php echo $errorMessage; ?></textarea>
        </label>  
    	<input type="submit" value="Guardar">
    </form>
    <?php echo $actionDone; ?>
</div>