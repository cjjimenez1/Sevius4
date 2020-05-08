<?php
  function mainDepartamento($Sevius,$conexion)
  {
    echo "hola departamento<br>";
    echo "Probando hacer un sql * a una tabla<br><br>";
    $sql = "select * from cyd_mastercoord";
    $conexion->parse($sql);
    $conexion->value('id',$Sevius);
    $conexion->execute();
    $conexion->fetch();
    if ($conexion->rows>0){
        echo "tabla";
        ?>
        <table class='tablaListado'>
            <caption>Tabla Coordinadores master</caption>
            <tr><th>Código master</th><th>DNI Coordinador</th></tr>
            <?php
            for ($i=0;$i<$conexion->rows;$i++){
            ?>
            <tr><td><?php echo $conexion->data[$i]['PLA_CODALF']; ?></td>
                <td><?php echo $conexion->data[$i]['PRS_DNIPRS']; ?></td></tr>
            <?php
            //también es puede poer así: echo "<td>" . $conexion->data[$i]['PRS_DNIPRS'] . "</td></tr>";
            }
            ?>
        </table>
        <?php
    }
       
    
}
?>
