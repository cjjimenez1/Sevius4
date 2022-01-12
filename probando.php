<?php  
function mainEquipoGobierno($Sevius,$conexion,$tipo)
{
    //print_r ($Sevius);
    
    echo $_REQUEST['accion'];    
    switch(@$_REQUEST['accion'])
    {
        case 'detalles':
            detalle($Sevius,$conexion,$tipo,@$_REQUEST['cargo']);
            break; 
        case 'alta':
            return alta($Sevius,$conexion,$tipo,@$_REQUEST['cargo']);
            break;
        case 'baja':
            baja($Sevius,$conexion,$tipo,@$_REQUEST['cargo']);
        break;       
        case 'grabar':
            return grabar($Sevius,$conexion,@$_REQUEST['cargo']);
            break;
        default:
       break;
    }
    lista_cargos($Sevius,$conexion,$tipo);
}

function lista_cargos($Sevius,$conexion,$tipo)
{?>
        <form id='acciones' name='acciones' action='index.php'> <!--Esto es el nombre del formulario que está llamando a index.php Debajo vamos a crear varias variables del formulario para pasarlas al JS-->
        <input id='ronda' name='ronda' type='hidden' value='<?php $tipo ?>' > <!--Creamos la variable ronda que tiene de valor $tipo. En este caso "=8" para pasárselo al JS y que cuando vuelva a ejecutar index.php el tipo sea 8 que es ronda-->
        <input id='accion' name='accion' type='hidden' value='<?php $_REQUEST["accion"] ?>' > <!--Creamos la variable acción donde vamos a guardar la acción del botón a través del value que puede ser "detalles" para que cuando vuelva a ejecutar la pag entre por el switch en detalles-->
        <input id='cargo' name='cargo' type='hidden' value='<?php $_REQUEST["cargo"] ?>' > <!--Creamos la variable cargo que es el código del cargo del botón que hemos pulsado. 1 Rector. 2 Vicerector...-->
        
        </form>   <?php
    list($n,$t) = datos_lista_cargos($Sevius,$conexion,$tipo);
    if ($n==0){
        echo'No hay cargos que mostrar';
    }else{
        ?>

        <?php echo'<table class="tablaListado">';
            echo'<caption>Cargos del Equipo de Gobierno</caption>';
            $cargo='';
            for($i=0;$i<$n;$i++){
                $cargo=$conexion->data[$i]['CODNUM_CARGO'];
                echo '<tr><th>' .$cargo .'</th>';
                echo'<td>'.$conexion->data[$i]['DESCRIPCION'].'</td>';

                echo "<td><button id='detalles".$i ."' name='detalles" .$i ."'>Detalles</button>";
                echo " <button id='alta".$i ."' name='alta" .$i ."'>Alta</button></td></tr>";
                $detalles='detalles'.$i;
                $Sevius->jsEvent($detalles,'click',"funcase($tipo,'detalles',$cargo);");
                $alta='alta'.$i;
                $Sevius->jsEvent($alta,'click',"funcase($tipo,'alta',$cargo);");

//$Sevius->jsEvent($nombre,'click',"javascript:alert('$nombre');"); 
                //echo'<td><input type="submit" id="detalles" value="Detalles"></td></tr>';
            }
        echo'</table>';   
    }
    $Sevius->jsInicio();
    //Abrimos el JavaScript con 3 parámetros (t=tipo que es 8 de ronda, acc=acción que viene de request acción con la acción del botón y car=cargo. El código del cargo
    //La útlima linea submit vuelve a cargar la página con los valores que lleva. El código del cargo y la acción que sea.
?>
    function funcase(t,acc,car)
    {
       document.acciones.ronda.value=t;
       document.acciones.accion.value=acc;
       document.acciones.cargo.value=car;
       document.acciones.submit();
    }
    
<?php
    $Sevius->jsFin();

}

function detalle($Sevius,$conexion,$tipo,$cargo)
{
    echo '<BR />hola ' .$tipo .' --- ' .$cargo.'<BR />';
    list ($n,$t) = datos_detalle($Sevius,$conexion,$tipo,$cargo);
    if ($n==0){
        $Sevius->SalidaError('No hay personas que mostrar con este cargo');
        //echo'No hay personas que mostrar con este cargo';
    }else{
        echo'<table class="tablaListado">';
        echo'<caption>Histórico del cargo</caption>';
        echo'<tr><th>Cargo</th><th>Nombre</th><th>Apellido1</th><th>Apellido2</th><th>Fecha alta</th><th>Fecha baja</th><th>Acciones</th></tr>';
        for($i=0;$i<$n;$i++){
            //echo'<tr><td><input type="text" id="codigo_cargo" value="'.$conexion->data[$i]['CODNUM'].'" readonly></td>';
            echo'<td><input type="text" id="descripcion" value="'.$conexion->data[$i]['DESCRIPCION_CARGO'].'" readonly></td>';
            echo'<td><input type="text" id="nombre" value="'.$conexion->data[$i]['NOMBRE'].'" readonly></td>';
            echo'<td><input type="text" id="ap1" value="'.$conexion->data[$i]['APELLIDO1'].'" readonly></td>';
            echo'<td><input type="text" id="ap2" value="'.$conexion->data[$i]['APELLIDO2'].'" readonly></td>'?>
            <!--//echo'<td><input type="text" id="f_alta" value="'.$conexion->data[$i]['FECHA_ALTA'].'" readonly></td>';-->
            <td><input type="text" id="f_alta" value="<?php echo $conexion->data[$i]['FECHA_ALTA']?>" readonly></td>
            <td><input type="text" id="f_baja" value="<?php echo $conexion->data[$i]['FECHA_BAJA']?>" readonly></td>
            <?php if ($conexion->data[$i]['FECHA_BAJA'])echo '<td></td></tr>';
                  else echo "<td><button id='baja".$i ."' name='baja" .$i ."'>Baja</button></td></tr>";
                  $baja='baja'.$i;
                  $Sevius->jsEvent($baja,'click',"funcase($tipo,'baja',$codnum);");

        }
    }
        
echo'</table>';
    
}
function alta($Sevius,$conexion,$tipo,$cargo)
{
    echo '<BR />alta ' .$tipo .' -- ' .$cargo.'<BR />';

}

function baja($Sevius,$conexion,$tipo,$codnum)
{
    echo '<BR />baja ' .$tipo .' -- ' .$id.'<BR />';
    list ($n,$t) = datos_baja($Sevius,$conexion,$tipo,$codnum);

}

function datos_detalle($Sevius,$conexion,$tipo,$cargo)
{
    //echo '<BR />hola2 ' .$tipo .' -- ' .$cargo.'<BR />';
    $conexion->parse('select distinct h.CARGO, h.codnum, h.DNI ,h.DESCRIPCION_CARGO, h.NOMBRE ,h.APELLIDO1, h.APELLIDO2, h.FECHA_ALTA, h.FECHA_BAJA
                    from cydeg_personascargos p, cydeg_personahistorico h
                    where p.CODNUM_CARGO = h.CARGO
                    and p.CODNUM_CARGO =:cargo
                    order by fecha_alta desc',true);//Este true hace que pinte la SQL para que veamos qué select está ejecutando y qué valores tienen los parámetros.
    $conexion->value('cargo',$cargo);
    $conexion->execute();
    return $conexion->fetch(true);//Este true pinta una tabla con el contenido del fetch. El resultado de la select.
}
function datos_lista_cargos($Sevius,$conexion,$tipo)
{
    $conexion->parse('select codnum_cargo, descripcion, nombre,apell1, apell2, fecha_alta_pers, fecha_baja_pers from cydeg_personascargos where codnum_cargo<=18 order by codnum_cargo');
    $conexion->novalue();
    $conexion->execute();
    return $conexion->fetch();
}
function datos_baja($Sevius,$conexion,$tipo,$codnum)
{
    $conexion->parse('update cydeg_personahistorico
                    set fecha_baja = sysdate
                    where codnum_cargo = :id',true);
    $conexion->value('id',$codnum);
    $conexion->execute();
    return $conexion->fetch(true);
    $conexion->commit();
}

   //$param = 'document.acciones.accion.value="detalle".$codnum;
//document.acciones.cargo.value=' .$codnum .'; document.acciones.submit();';
   /*$Sevius->jsEvent('btndetalle<?php $codnum ?>','click',"$param"); ?>*/

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
//<!--A partir de aquí es el código que muestra las tablas de tipos de cargo y debajo el detalle de un cargo-->
    
/*<?php   
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
 * 
 */
?>
