<?php
  function mainMaster($Sevius,$conexion)
  {
    echo "hola master <br>";
    echo "DESTINO: " .$Sevius->destino(true,true) ."<br>"; 
    echo @$_REQUEST['variable'] ."<br>";
    if (!empty(@$_REQUEST['variable']))
      echo "El valor del input hidden devuelto es " .$_REQUEST['variable'] ."<br><br>{$_REQUEST['accion']}";
?>
    <form name='frmprueba' method="post" action='<?php echo $Sevius->destino(true,true); ?>'>
    <input name='accion' type='hidden' value=''>
    <input name='variable' type='hidden' value=''>
    <table>
        <tr>
            <th>Encabezado1</th>
            <th>Encabezado2</th>
            <th>Encabezado3</th>
        </tr>
        <tr>
            <td>Probando</td>
            <td><input id='botonaceptar' type='submit' value='Aceptar'>
            <input id='botonplin' type='submit' value='Plin'>
            </td>
            <td id='celda'>Celda 3.
            <?php
                echo @$_REQUEST['variable']; 
            ?>
            </td>
        </tr>
    </table>
    </form>
<?php
    $Sevius->JSEvent("botonaceptar",'click',"aceptar('yyyyyyyyyyy');");
    $Sevius->JSEvent("botonaceptar",'click',"aceptar('xxxxxxxxxxxxxxx');");
    $Sevius->JSEvent("botonplin",'click',"evt.preventDefault();alert(document.getElementById('celda').innerHTML);document.getElementById('celda').innerHTML='plin';");
    $Sevius->jsInicio();
?>
    function aceptar(i)
    {
      alert(i);
      document.frmprueba.accion.value='aceptar';
      document.frmprueba.variable.value=i;
      document.frmprueba.submit();
    }

<?php
      $Sevius->jsFin();
  }
?>
