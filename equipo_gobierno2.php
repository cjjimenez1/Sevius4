<?php  
function mainEquipoGobierno($Sevius,$conexion,$tipo)
  {
//print_r ($Sevius);

$sql = 'select codnum_cargo, descripcion, nombre,apell1, apell2, fecha_alta_pers, fecha_baja_pers from cydeg_personascargos where codnum_cargo<=18 order by codnum_cargo';
$conexion->parse($sql);
$conexion->novalue();
$conexion->execute();
$conexion->fetch();
if ($conexion->rows>0){
    echo"<table class='tablaListado'>";
        echo'<caption>Alta de un nuevo cargo</caption>';
        echo '<tr>';
            echo'<td>Añadir persona a un cargo<br>Introduzca DNI</td>';
            //echo'<td>Introduzca DNI</td>';
            echo'<td><input type="text" id="DNI"</td>';
            echo'<td>Seleccione nuevo cargo</td>';
            echo'<td>';
                echo'<select name="cargo">';
                echo'<option value="rector" selected>Rector</option>';
                echo'<option value="vicerec">Vicerector</option>';
                echo'</select>';
            echo'</td>';
            echo'<td>Depende de</td>';
            echo'<td>';
                echo'<select name="cargo">';
                echo'<option value="rector" selected>Rector</option>';
                echo'<option value="vicerec">Vicerector</option>';
                echo'</select>';
            echo'</td>';
            echo'<td><input type="submit" id="nueva_pers" value="Añadir persona"></td>';
        echo'</tr>';
    echo'</table>';
    echo'<table class="tablaListado">';
        echo'<caption>Cargos del Equipo de Gobierno</caption>';
        for($i=0;$i<$conexion->rows;$i++){
            echo'<tr><th>'.$conexion->data[$i]['CODNUM_CARGO'].'</th>';
            echo'<td>'.$conexion->data[$i]['DESCRIPCION'].'</td>';
            echo'<td><input type="submit" id="detalles" value="Detalles"></td></tr>';
        }
    echo'</table>';
}
echo'<table class="tablaListado">';
    echo'<caption>Detalles del cargo</caption>';
    //$sqldetalles='select codnum, descripcion_cargo, nombre, apellido1, apellido2, fecha_alta, fecha_baja from cydeg_personahistorico';
    //$conexion->parse($sqldetalles);
    $conexion->parse('select codnum, descripcion_cargo, nombre, apellido1, apellido2, fecha_alta, fecha_baja from cydeg_personahistorico order by fecha_alta desc');
    $conexion->novalue();
    $conexion->execute();
    $conexion->fetch();
    if ($conexion->rows>0){
        echo'<tr><th>Código</th><th>Descripción</th><th>Nombre</th><th>Apellido1</th><th>Apellido2</th><th>Fecha alta</th><th>Fecha baja</th><th>Acciones</th></tr>';
        for($i=0;$i<$conexion->rows;$i++){
            echo'<tr><td><input type="text" id="codigo_cargo" value="'.$conexion->data[$i]['CODNUM'].'" readonly></td>';
            echo'<td><input type="text" id="descripcion" value="'.$conexion->data[$i]['DESCRIPCION_CARGO'].'" readonly></td>';
            echo'<td><input type="text" id="nombre" value="'.$conexion->data[$i]['NOMBRE'].'" readonly></td>';
            echo'<td><input type="text" id="ap1" value="'.$conexion->data[$i]['APELLIDO1'].'" readonly></td>';
            echo'<td><input type="text" id="ap2" value="'.$conexion->data[$i]['APELLIDO2'].'" readonly></td>'?>
            <!--//echo'<td><input type="text" id="f_alta" value="'.$conexion->data[$i]['FECHA_ALTA'].'" readonly></td>';-->
            <td><input type="text" id="f_alta" value="<?php echo $conexion->data[$i]['FECHA_ALTA']?>" readonly></td>
            <td><input type="text" id="f_baja" value="<?php echo $conexion->data[$i]['FECHA_BAJA']?>" readonly></td>
            <?php if ($conexion->data[$i]['FECHA_BAJA'])echo '<td></td></tr>';
                  else echo '<td><input type="submit" id="dar_baja" value="Dar de baja"></td></tr>';
                      
        }
        
echo'</table>';
    }
    
}
?>
