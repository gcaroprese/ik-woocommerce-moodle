<?php
/*

Template: Woocommerce Moodle - Panel

*/

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['valueurl']) && isset($_POST['valuehostname']) && isset($_POST['valueusuario']) && isset($_POST['valuedbname']) && isset($_POST['valueclave'])){

// Levanto las variables que submití en el form

$valueurl = sanitize_text_field($_POST['valueurl']);
$valuehostname = sanitize_text_field($_POST['valuehostname']);
$valuedbname = sanitize_text_field($_POST['valuedbname']);
$valueusuario = sanitize_text_field($_POST['valueusuario']);
$valueclave = sanitize_text_field($_POST['valueclave']);
$prefijo = sanitize_text_field($_POST['prefijo']);
    
    
    /* 
        Chequea si el sistema ya fue cargado con los datos de acceso 
        a la base de dato y URL de la web Moodle 
        para ver si hago un INSERT o un UPDATE en la base de datos
    */
    if (ik_woomoodle_configurado('config') === false){
        
        $config_woomoodle  = array (
                'valueurl' =>$valueurl,
                'valuehostname' =>$valuehostname,
                'valuedbname' => $valuedbname,
                'valueusuario' =>$valueusuario,
                'valueclave' =>$valueclave,
                'prefijo' =>$prefijo
        );       
        
        // Serialización del array
        $config_woomoodle_slz = maybe_serialize($config_woomoodle);
        
    	global $wpdb;
        $data_insert  = array (
        			'option_id' => NULL,
        			'option_name' => 'ik_woomoodle_config',
        			'option_value' => $config_woomoodle_slz,
        			'autoload' => 'yes',
    	);

		
		$tableInsert = $wpdb->prefix.'options';
		$rowResult = $wpdb->insert($tableInsert,  $data_insert , $format = NULL);

    } else {
                    
        $config_woomoodleup  = array (
                'valueurl'=>$valueurl,
                'valuehostname'=>$valuehostname,
                'valuedbname'=>$valuedbname,
                'valueusuario'=>$valueusuario,
                'valueclave' =>$valueclave,
                'prefijo' =>$prefijo
            );       
        
        // Serialización del array
        $config_woomoodle_slzp = maybe_serialize($config_woomoodleup);
        
    	global $wpdb;
        $data_update  = array (
        			'option_value' => $config_woomoodle_slzp,
    	);

		
		$tableUpdate = $wpdb->prefix.'options';
		
		// Donde se encuentra el valor de configuración en la tabla options
		$dondeUpdate = [ 'option_name' => 'ik_woomoodle_config' ];
		
		$rowResult = $wpdb->update($tableUpdate, $data_update, $dondeUpdate);
            
    }
}
?>
<script>
// chequeo que se ingrese una URL con https
function checkURL (abc) {
  var string = abc.value;
  if (!~string.indexOf("https")) {
    string = "https://" + string;
  }
  abc.value = string;
  return abc
}
</script>
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
#ik_conexionMoodle{
    display: inline-block;
    padding: 15px;
    color: #fff;
    min-width: 167px;
    text-align: center;
    font-size: 15px;
    text-transform: uppercase;
    margin-top: 30px;
    border: 2px solid #ccc;
}
.ik_errorConexion{
    background: red;
}
.ik_okConexion{
    background: green;
}
</style>
<div id="panel-form-woomoodle">
    <div class="ik_woomoodle_panel">
    <h2>Asociación Woocommerce - Moodle</h2>
    <form action="" method="post" id="db-woomoodle-form" enctype="multipart/form-data" autocomplete="no">
        
        <?php 
            // Variables si existen. Sino va a dar un valor vacío.
            $valueURL = ik_woomoodle_dataconfig('valueurl');
            $valueHostname = ik_woomoodle_dataconfig('valuehostname');
            $valueDBname = ik_woomoodle_dataconfig('valuedbname');
            $valueUsuario = ik_woomoodle_dataconfig('valueusuario');
            $valueClave = ik_woomoodle_dataconfig('valueclave');
            $prefijoDB = ik_woomoodle_dataconfig('prefijo');
        ?>
        
        <label>
            <span>Sitio Web de Moodle</span>
            <input required type="url" maxlength="80"  pattern="https?://.+" onblur="checkURL(this)" name="valueurl" value="<?php echo $valueURL; ?>" placeholder="Ingresá la URL de Moodle" autocomplete="off" />
        </label>
        <label>
            <span>Servidor de Base de datos</span>
            <input required type="text" name="valuehostname" value="<?php echo $valueHostname; ?>" placeholder="Ingresá el hostname o IP" autocomplete="off" />
        </label>        
        <label>
            <span>Nombre de la Base de datos</span>
            <input required type="text" name="valuedbname" value="<?php echo $valueDBname; ?>" placeholder="Ingresá el nombre de la DB" autocomplete="off" />
        </label>
        <label>
            <span>Usuario de la Base de Datos</span>
            <input required type="text" name="valueusuario" value="<?php echo $valueUsuario; ?>" placeholder="Ingresá el usuario" autocomplete="off" />
        </label>
        <label>
            <span>Contraseña de la Base de Datos</span>
            <input required type="password" name="valueclave" onfocus="this.removeAttribute('readonly');" value="<?php echo $valueClave; ?>" placeholder="Ingresá la contraseña" autocomplete="new-password" />
        </label>        
        <label>
            <span>Prefijo (Por ejemplo: "mdl_")</span>
            <input type="text" name="prefijo" onfocus="this.removeAttribute('readonly');" value="<?php echo $prefijoDB; ?>" placeholder="Ingresá el prefijo de la DB" autocomplete="off" />
        </label>
    
    	<input type="submit" value="Guardar">
    </form>
    
    <?php 
    // Muestro el estado de la conexión a la base de datos
    echo ik_conexion_dbmoodle();
    ?>
</div>