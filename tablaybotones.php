<?php
  function mainMaster($Sevius,$conexion)
  {
    echo "hola master <br>";
    echo "DESTINO: " .$Sevius->destino(true,true) ."<br>"; 
    echo @$_REQUEST['variable'] ."<br>";
    if (!empty(@$_REQUEST['variable']))
      echo "El valor del input hidden devuelto es " .$_REQUEST['variable'] ."<br><br>{$_REQUEST['accion']}<br>";
      echo var_dump($_REQUEST);
?>
    <form name='frmprueba' method="post" action='<?php echo $Sevius->destino(true,true); ?>'>
    <input name='accion' type='hidden' value=''><!--Mete en $_REQUEST['accion'] la cadena "aceptar" que es el valor de document.frmprueba.accion.value-->
    <input name='variable' type='hidden' value=''><!--Mete en $_REQUEST['variable'] la cadena "Probando alert 2"-->
    <table>
        <tr>
            <th>Encabezado 1</th>
            <th>Encabezado 2</th>
            <th>Encabezado 3</th>
            <th>Encabezado 4</th>
            <th>Encabezado 5</th>
        </tr>
        <tr>
            <td>Probando</td>
            <td><input id='botonaceptar' type='submit' value='Aceptar'>
            <input id='botonplin' type='submit' value='Plin'>
            </td>
            <td id='celda3'>Celda 3.
            <?php
                echo @$_REQUEST['variable']; 
            ?>
            </td>
            <td id='celda4'>Celda 4. Poner aquí un botón.
                <input id='boton4' type='submit' value='Aceptar 4'>
            </td>
            <td id='celda5'>Celda 5.
            </td>
        </tr>
    </table>
    </form>
<?php
    $Sevius->JSEvent("botonaceptar",'click',"aceptar('Probando alert 1');");//Al pulsar el botón Aceptar de la celda 2 abre un alert con "Probando alert 1"
    $Sevius->JSEvent("botonaceptar",'click',"aceptar('Probando alert 2');");//Al pulsar el botón Aceptar del alert anterior abre un alert con "Probando alert 2"
    $Sevius->JSEvent("botonplin",'click',"evt.preventDefault();alert(document.getElementById('celda3').innerHTML);document.getElementById('celda3').innerHTML='hola plin';");
    $Sevius->JSEvent("boton4",'click',"evt.preventDefault();document.getElementById('celda5').innerHTML='Has pulsado el botón de la celda 4';");
    //Al pulsar el botón "Aceptar 4" de la celda 4, imprime "Has pulsado el botón de la celda 4" en la celda 5.
    $Sevius->jsInicio();
?>
    function aceptar(i)
    {
      alert(i);
      document.frmprueba.accion.value='aceptar';
      document.frmprueba.variable.value=i;
      document.frmprueba.submit();
    }
<!--Esta función es necesaria para los alert del botón de la celda 2. El botón de la 4 funciona bien sin esta función-->
<?php
      $Sevius->jsFin();
  }
?>

////////////////////////////////////////////////////////////////////////
//Para aclarar los eventos java en Sevius4//////

$Sevius->JSEvent("botonplin",'click',"evt.preventDefault();alert(document.getElementById('celda3').innerHTML);");
//Al pulsar el botón "Plin" de la celda 2, imprime en un alert lo que hay en la celda 3

$Sevius->JSEvent("botonplin",'click',"evt.preventDefault();document.getElementById('celda3').innerHTML='hola plin';");
//Al pulsar el botón "Plin" de la celda 2, imprime en la celda3 'hola plin'

$Sevius->JSEvent("botonplin",'click',"evt.preventDefault();alert(document.getElementById('celda3').innerHTML);document.getElementById('celda3').innerHTML='hola plin';");
//Al pulsar el botón "Plin" de la celda 2, hace las 2 cosas. Primero saca un alert con el contenido de la celda 3 y al dar a "Aceptar" en la ventana del alert, muestra en la celda 3 'hola plin'
///////////////////////////////////////////////////////
