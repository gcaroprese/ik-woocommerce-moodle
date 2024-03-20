<?php
/*

Template: Woocommerce Moodle - Panel

*/
wp_enqueue_media();
    // Enqueue custom script that will interact with wp.media
    wp_enqueue_script( 'Logo_Upload_MoodleConnector', plugins_url( '../js/logo-uploader.js' , __FILE__ ), array('jquery'), '0.1' );

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supportemail']) ){

// Levanto las variables que submití en el form

$supportEmail = sanitize_text_field($_POST['supportemail']);
$phone = sanitize_text_field($_POST['phone']);
$logoURL = sanitize_text_field($_POST['logourl']);
$logoID = sanitize_text_field($_POST['ik_moodle_image_id']);
    
    
    /* 
        Chequea si el sistema ya fue cargado con los datos de acceso 
        a la base de dato y URL de la web Moodle 
        para ver si hago un INSERT o un UPDATE en la base de datos
    */
    
    $config_woomoodle  = array (
            'logoid' =>$logoID,
            'logourl' =>$logoURL,
            'phone' =>$phone,
            'supportemail' =>$supportEmail
    );       
    
    if (ik_woomoodle_configurado('info') === false){
        
        add_option('ik_woomoodle_info', $config_woomoodle);

    } else {
                    
        update_option('ik_woomoodle_info', $config_woomoodle);
            
    }
}
?>
<style>
.error{ display: none; }
label, input {
    display: block;
    width: 200px;
    text-align: center;
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
</style>
<div id="panel-form-woomoodle">
    <div class="ik_woomoodle_panel">
    <h2>Asociación Woocommerce - Moodle</h2>
    <form action="" method="post" id="db-woomoodle-form" enctype="multipart/form-data" autocomplete="no">
        <?php 
            // Variables si existen. Sino va a dar un valor vacío.
            $URLlogo = ik_woomoodle_datainfo('logourl');
            $emailForSupport = ik_woomoodle_datainfo('supportemail');
            $imageID = ik_woomoodle_datainfo('logoid');
            $phone = ik_woomoodle_datainfo('phone');
        ?>
        <label for="upload_image">
            <span>Logo de cursos en Moodle</span>
        <?php
        if ($imageID != 0){
            $image = '<img id="ik_moodle_preview_image" src="'.$URLlogo.'" />';
            $buttonEliminar = '<input type="button" class="button-primary button_moodle_red" value="Eliminar" id="ik_moodle_eliminar_imagen">';
        } else {
            $image = '<img id="ik_moodle_preview_image" src="" style="display: none;" />';
            $buttonEliminar = '';
        }
        echo $image; 
        ?>
            <input type="hidden" name="ik_moodle_image_id" id="ik_moodle_image_id" value="<?php echo $imageID ; ?>" class="regular-text" />
            <input type='button' class="button-primary" value="<?php esc_attr_e( 'Subir Una Imagen' ); ?>" id="ik_moodle_media_manager"/>
            <input type="hidden" id="ik_moodle_logo_url" name="logourl" value="<?php echo $URLlogo; ?>" /><?php  echo $buttonEliminar; ?>
        </label>
        <label>
            <span>Email de Suporte</span>
            <input required type="text" name="supportemail" value="<?php echo $emailForSupport; ?>" placeholder="Ingresá un email" autocomplete="off" />
        </label>        
        <label>
            <span>Teléfono de Suporte</span>
            <input required type="text" name="phone" value="<?php echo $phone; ?>" placeholder="Ingresá un teléfono" autocomplete="off" />
        </label>  
    	<input type="submit" value="Guardar">
    </form>
</div>