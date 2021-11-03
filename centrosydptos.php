<?php
  function mainCentro($Sevius,$conexion,$tipo)
  {
    if(isset($_REQUEST['editando']) && @$_REQUEST['grabar']=='S')
    {
      $info = "Ronda: {$Sevius->usuario['ID']} ({$Sevius->usuario['UVUS']})";
      $error = null;

      if($_REQUEST['editando']=='alta')
      {
        $conexion->parse('select nvl(max(codnum),0)+1 n from cyd_centroydpto');
        $conexion->novalue();
        $conexion->execute();
        $conexion->fetch();
        if($conexion->data[0]['N']) $_REQUEST['editando']=$conexion->data[0]['N'];
        else $error='Imposible obtener secuencia';

        if(!$error)
        {
          $conexion->parse('insert into cyd_centroydpto (codnum,tipo,infomod) values (:codigo,:tipo,:info)');
          $conexion->value('codigo',$_REQUEST['editando']);
          $conexion->value('tipo',$tipo);
          $conexion->value('info',$info);
          $conexion->execute();
          $error=$conexion->error();
          if($error) $error.=' (alta)';
        }
      }

      if(!$error)
      {
        $conexion->parse("update cyd_centroydpto
                          set    nombre = :nombre,
                                 name = :name,
                                 cyd_ubicado = :cyd_ubicado,
                                 campus = :campus,
                                 cen_codnum = :cen_codnum,
                                 dep_codalf = :dep_codalf,
                                 cen_hominis = :cen_hominis,
                                 uni_hominis = :uni_hominis,
                                 direccion = :direccion,
                                 localidad = :localidad,
                                 cp = :cp,
                                 telefono = :telefono,
                                 fax = :fax,
                                 email = :email,
                                 www = :www,
                                 creacion = :creacion,
                                 publicacion = :publicacion,
                                 convenio = :convenio,
                                 entidad = :entidad,
                                 fecha_baja = to_date(:fecha_baja,'dd/mm/yyyy'),
                                 otrosdatos = :otrosdatos,
                                 url_horario = :url_horario,
                                 url_calendario = :url_calendario,
                                 infomod = :info
                          where  codnum = :codigo
                          and    tipo = :tipo");
        $conexion->value('codigo',$_REQUEST['editando']);
        $conexion->value('tipo',$tipo);
        $conexion->value('info',$info);
        $conexion->value('nombre',$_REQUEST['nombre']);
        $conexion->value('name',$_REQUEST['name']);
        $conexion->value('cyd_ubicado',$_REQUEST['ubicacion']);
        $conexion->value('campus',$_REQUEST['campus']);
        $conexion->value('cen_codnum',$_REQUEST['centroac']);
        $conexion->value('dep_codalf',$_REQUEST['departamento']);
        $conexion->value('cen_hominis',$_REQUEST['centrorh']);
        $conexion->value('uni_hominis',$_REQUEST['unidad']);
        $conexion->value('direccion',$_REQUEST['direccion']);
        $conexion->value('localidad',$_REQUEST['localidad']);
        $conexion->value('cp',$_REQUEST['cp']);
        $conexion->value('telefono',$_REQUEST['telefono']);
        $conexion->value('fax',$_REQUEST['fax']);
        $conexion->value('email',$_REQUEST['email']);
        $conexion->value('www',$_REQUEST['www']);
        $conexion->value('creacion',$_REQUEST['creacion']);
        $conexion->value('publicacion',$_REQUEST['publicacion']);
        $conexion->value('convenio',$_REQUEST['convenio']);
        $conexion->value('entidad',$_REQUEST['entidad']);
        $conexion->value('fecha_baja',$_REQUEST['baja']);
        $conexion->value('otrosdatos',$_REQUEST['otros']);
        $conexion->value('url_horario',$_REQUEST['horario']);
        $conexion->value('url_calendario',$_REQUEST['calendario']);
        $conexion->execute();
        $error=$conexion->error();
        if(!$error && $conexion->numRows()==0) $error='No encontrado';
        if($error) $error.=' (a)';
      }

      if(!$error)
      {
        $conexion->parse("update cyd_cargo set infomod = '# '||:info
                          where  cyd_codnum = :codigo");
        $conexion->value('codigo',$_REQUEST['editando']);
        $conexion->value('info',$info);
        $conexion->execute();
        $error=$conexion->error();
        if($error) $error.=' (b)';
      }

      if(!$error)
      {
        $conexion->parse("delete cyd_cargo
                          where  cyd_codnum = :codigo");
        $conexion->value('codigo',$_REQUEST['editando']);
        $conexion->execute();
        $error=$conexion->error();
        if($error) $error.=' (c)';
      }

      for($j=0;!$error && $j<$_REQUEST['N'];$j++)
      {
        if(isset($_REQUEST['id'][$j]))
        {
          $conexion->parse("insert into cyd_cargo
                             (cyd_codnum,id,infomod,car_codnum,subcargo,prs_dniprs,estado)
                             values
                             (:codigo,:id,:info,:car_codnum,:subcargo,:prs_dniprs,:estado)");
          $conexion->value('codigo',$_REQUEST['editando']);
          $conexion->value('id',$_REQUEST['id'][$j]);
          $conexion->value('info',$info);
          $conexion->value('car_codnum',$_REQUEST['cargo'][$j]);
          $conexion->value('subcargo',$_REQUEST['subcargo'][$j]);
          $conexion->value('prs_dniprs',$_REQUEST['dni'][$j]);
          $conexion->value('estado',$_REQUEST['estado'][$j]);
          $conexion->execute();
          $error=$conexion->error();
          if($error) $error.=" ($j)";
        }
      }

      if($error) $conexion->rollback();
      else $conexion->commit();
    }
    else if(isset($_REQUEST['eliminar']))
    {
      $info = "Ronda: {$Sevius->usuario['ID']} ({$Sevius->usuario['UVUS']})";
      $error = null;

      if(!$error)
      {
        $conexion->parse("update cyd_cargo set infomod = '# '||:info
                          where  cyd_codnum = :codigo
                          and    tipo = :tipo");
        $conexion->value('codigo',$_REQUEST['eliminar']);
        $conexion->value('tipo',$tipo);
        $conexion->value('info',$info);
        $conexion->execute();
        $error=$conexion->error();
        if(!$error && $conexion->numRows()==0) $error='No encontrado';
        if($error) $error.=' (z)';
      }

      if(!$error)
      {
        $conexion->parse("delete cyd_cargo
                          where  cyd_codnum = :codigo");
        $conexion->value('codigo',$_REQUEST['eliminar']);
        $conexion->execute();
        $error=$conexion->error();
        if($error) $error.=' (y)';
      }

      if(!$error)
      {
        $conexion->parse("update cyd_centroydpto
                          set    infomod = '# '||:info
                          where  codnum = :codigo");
        $conexion->value('codigo',$_REQUEST['eliminar']);
        $conexion->value('info',$info);
        $conexion->execute();
        $error=$conexion->error();
        if($error) $error.=' (x)';
      }

      if(!$error)
      {
        $conexion->parse("delete cyd_centroydpto
                          where  codnum = :codigo");
        $conexion->value('codigo',$_REQUEST['eliminar']);
        $conexion->execute();
        $error=$conexion->error();
        if($error) $error.=' (w)';
      }

      if($error) $conexion->rollback();
      else $conexion->commit();
    }
    else $error = null;

    if(isset($_REQUEST['editando']))
    {
      $conexion->parse("select * from cyd_centroydpto where tipo in (1,2) order by codnum");
      $conexion->novalue();
      $conexion->execute();
      $conexion->fetch();
      $valoresCentro=array(''=>'');
      for($i=0;$i<$conexion->rows;$i++) $valoresCentro[$conexion->data[$i]['CODNUM']]=$conexion->data[$i]['NOMBRE'];

      $conexion->parse("select * from cyd_campus order by codalf");
      $conexion->novalue();
      $conexion->execute();
      $conexion->fetch();
      $valoresCampus=array(''=>'');
      for($i=0;$i<$conexion->rows;$i++) $valoresCampus[$conexion->data[$i]['CODALF']]=$conexion->data[$i]['DESID'];

      $conexion->parse("select * from cyd_tipocargo order by codnum");
      $conexion->novalue();
      $conexion->execute();
      $conexion->fetch();
      $valoresCargo=array(''=>'');
      for($i=0;$i<$conexion->rows;$i++) $valoresCargo[$conexion->data[$i]['CODNUM']]=$conexion->data[$i]['DESID'];

      $valoresEstado=array(''=>'','T'=>'Titular','F'=>'En funciones','S'=>'Suplente');
    }

    $conexion->parse("select a.*,b.desid campus2,
                             c.desid tipo2,c.cargo1,c.cargo2,
                             e.desid elcargo1,f.desid elcargo2,
                             d.nombre ubicado
                      from   cyd_centroydpto a,
                             cyd_campus b,
                             cyd_tipocentro c,
                             cyd_centroydpto d,
                             cyd_tipocargo e,
                             cyd_tipocargo f
                      where  a.tipo = :tipo
                      and    b.codalf (+)= a.campus
                      and    c.codnum (+)= a.tipo
                      and    d.codnum (+)= a.cyd_ubicado
                      and    e.codnum (+)= c.cargo1
                      and    f.codnum (+)= c.cargo2
                      order by a.codnum");
    $conexion->value('tipo',$tipo);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    for($i=0;$i<$n;$i++)
    {
      $conexion->parse("select a.*,
                               decode(a.estado,'T','Titular','F','En funciones','S','Suplente') estado2,
                               case when c.sexprs='D' then b.desidf when c.sexprs='H' then desidm else b.desidf end cargo,
                               case when c.sexprs='D' then 'Dña.' when c.sexprs='H' then 'D.' else 'Don/Doña' end tratamiento,
                               nomprs,ll1prs,ll2prs,sexprs
                        from   cyd_cargo a,
                               cyd_tipocargo b,
                               tuib_persona c
                        where  cyd_codnum = :codigo
                        and    b.codnum (+)= a.car_codnum
                        and    c.dniprs (+)= a.prs_dniprs
                        order by id");
      $conexion->value('codigo',$t[$i]['CODNUM']);
      $conexion->execute();
      list($t[$i]['N'],$t[$i]['T']) = $conexion->fetch();
    }

    ?>
      <div class='btnSuperior'>
        <a id='altanuevo1' <?php echo $Sevius->icono('plus');?>> Añadir nuevo</a>
      </div>

      <table class='tablaListado'>
        <?php
          for($i=0;$i<$n || $i==$n && @$_REQUEST['editando']=='alta';$i++)
          {
            if($i==$n)
            {
              $t[$i]['CODNUM']='alta';
              $t[$i]['NOMBRE']='Registro para alta';
              $t[$i]['N']=0;
            }

            echo "<tr><th>{$t[$i]['CODNUM']}</th><td>{$t[$i]['NOMBRE']}</td>".
                 "<td><a href='#{$t[$i]['CODNUM']}' ".$Sevius->icono('browser')."></a></td></tr>";
         }
       ?>
      </table>

      <div class='btnSuperior'>
        <a id='altanuevo2' <?php echo $Sevius->icono('plus');?>> Añadir nuevo</a>
      </div>

      <table class='bloque'>
        <?php
          for($i=0;$i<$n || $i==$n && @$_REQUEST['editando']=='alta';$i++)
          {
            ?>
              <tr><td class='bloqueHoja'>
                <div class='bloqueHoja'><a name='<?php echo $t[$i]['CODNUM'];?>'></a>
                  <?php
                    if(isset($_REQUEST['editando']) && $_REQUEST['editando']==$t[$i]['CODNUM'])
                    {
                      if($error) $Sevius->salidaError($error);

                      $Sevius->formularioAbrir("cancelar$i",true,true,"#{$t[$i]['CODNUM']}");
                      $Sevius->formularioCerrar();
                      $Sevius->formularioAbrir("datos$i",true,true,"#{$t[$i]['CODNUM']}");
                      $Sevius->formularioInput('grabar','S');
                      $Sevius->formularioInput('editando',$t[$i]['CODNUM']);
                      ?>
                        Código <?php echo $t[$i]['CODNUM']."<input type='hidden' id='N' name='N' value='{$t[$i]['N']}'>"; ?>
                        <br>
                        <table class='campos'>
                          <tr><th>Nombre</th><td><?php $Sevius->formularioInput('nombre',@$t[$i]['NOMBRE'],'text',125); ?></td></tr>
                          <tr><th>En inglés</th><td><?php $Sevius->formularioInput('name',@$t[$i]['NAME'],'text',125); ?></td></tr>
                          <tr><th>Ubicación</th><td><?php $Sevius->formularioInput('ubicacion',@$t[$i]['CYD_UBICADO'],'select',$valoresCentro); ?></td></tr>
                          <tr><th>Campus</th><td><?php $Sevius->formularioInput('campus',@$t[$i]['CAMPUS'],'select',$valoresCampus); ?></td></tr>
                          <tr><th>Código centro UXXI-AC</th><td><?php $Sevius->formularioInput('centroac',@$t[$i]['CEN_CODNUM'],'text',7); ?></td></tr>
                          <tr><th>Código de departamento</th><td><?php $Sevius->formularioInput('departamento',@$t[$i]['DEP_CODALF'],'text',8); ?></td></tr>
                          <tr><th>Código centro UXXI_RH</th><td><?php $Sevius->formularioInput('centrorh',@$t[$i]['CEN_HOMINIS'],'text',10); ?></td></tr>
                          <tr><th>Código de unidad</th><td><?php $Sevius->formularioInput('unidad',@$t[$i]['UNI_HOMINIS'],'text',10); ?></td></tr>
                          <tr><th>Direccion</th><td><?php $Sevius->formularioInput('direccion',@$t[$i]['DIRECCION'],'text',64); ?></td></tr>
                          <tr><th>Localidad</th><td><?php $Sevius->formularioInput('localidad',@$t[$i]['LOCALIDAD'],'text',40); ?></td></tr>
                          <tr><th>C.P.</th><td><?php $Sevius->formularioInput('cp',@$t[$i]['CP'],'text',5); ?></td></tr>
                          <tr><th>Teléfono</th><td><?php $Sevius->formularioInput('telefono',@$t[$i]['TELEFONO'],'text',40); ?></td></tr>
                          <tr><th>Fax</th><td><?php $Sevius->formularioInput('fax',@$t[$i]['FAX'],'text',40); ?></td></tr>
                          <tr><th>Correo electrónico</th><td><?php $Sevius->formularioInput('email',@$t[$i]['EMAIL'],'text',100); ?></td></tr>
                          <tr><th>Direccion web</th><td><?php $Sevius->formularioInput('www',@$t[$i]['WWW'],'text',150); ?></td></tr>
                          <tr><th>Fecha de creacion</th><td><?php $Sevius->formularioInput('creacion',@$t[$i]['CREACION'],'text',64); ?></td></tr>
                          <tr><th>Fecha de publicación</th><td><?php $Sevius->formularioInput('publicacion',@$t[$i]['PUBLICACION'],'text',64); ?></td></tr>
                          <tr><th>Convenio</th><td><?php $Sevius->formularioInput('convenio',@$t[$i]['CONVENIO'],'text',64); ?></td></tr>
                          <tr><th>Entidad</th><td><?php $Sevius->formularioInput('entidad',@$t[$i]['ENTIDAD'],'text',50); ?></td></tr>
                          <tr><th>Fecha de baja</th><td><?php $Sevius->formularioInput('baja',@$t[$i]['FECHA_BAJA'],'text',10); ?></td></tr>
                          <tr><th>Otros datos</th><td><?php $Sevius->formularioInput('otros',@$t[$i]['OTROSDATOS'],'text',125); ?></td></tr>
                          <tr><th>URL de horarios</th><td><?php $Sevius->formularioInput('horario',@$t[$i]['URL_HORARIO'],'text',150); ?></td></tr>
                          <tr><th>URL de calendarios</th><td><?php $Sevius->formularioInput('calendario',@$t[$i]['URL_CALENDARIO'],'text',150); ?></td></tr>
                        </table>
                      <?php
                    }
                    else
                    {
                      $Sevius->formularioAbrir("borrar$i",true,true,"#{$t[$i]['CODNUM']}");
                      $Sevius->formularioInput('eliminar',$t[$i]['CODNUM']);
                      $Sevius->formularioCerrar();
                      $Sevius->formularioAbrir("datos$i",true,true,"#{$t[$i]['CODNUM']}");
                      $Sevius->formularioInput('editando',$t[$i]['CODNUM']);
                      $Sevius->formularioCerrar();
                      ?>
                        Código <?php echo $t[$i]['CODNUM']; ?>
                        <br>
                        <table class='campos'>
                          <tr><th>Nombre</th><td><?php echo $t[$i]['NOMBRE']; ?></td></tr>
                          <?php if($t[$i]['NAME']) { ?><tr><th>&nbsp;</th><td><?php echo $t[$i]['NAME']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['CYD_UBICADO']) { ?><tr><th>Ubicación</th><td><?php echo $t[$i]['UBICADO'].($t[$i]['CYD_UBICADO']?' ('.$t[$i]['CYD_UBICADO'].')':null); ?></td></tr><?php } ?>
                          <?php if($t[$i]['CAMPUS']) { ?><tr><th>Campus</th><td><?php echo $t[$i]['CAMPUS2'].($t[$i]['CAMPUS']?' ('.$t[$i]['CAMPUS'].')':null); ?></td></tr><?php } ?>
                          <?php if($t[$i]['CEN_CODNUM']) { ?><tr><th>Código centro UXXI-AC</th><td><?php echo $t[$i]['CEN_CODNUM']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['DEP_CODALF']) { ?><tr><th>Código de departamento</th><td><?php echo $t[$i]['DEP_CODALF']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['CEN_HOMINIS']) { ?><tr><th>Código centro UXXI_RH</th><td><?php echo $t[$i]['CEN_HOMINIS']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['UNI_HOMINIS']) { ?><tr><th>Código de unidad</th><td><?php echo $t[$i]['UNI_HOMINIS']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['DIRECCION']) { ?><tr><th>Direccion</th><td><?php echo $t[$i]['DIRECCION']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['LOCALIDAD']) { ?><tr><th>Localidad</th><td><?php echo $t[$i]['LOCALIDAD']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['CP']) { ?><tr><th>C.P.</th><td><?php echo $t[$i]['CP']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['TELEFONO']) { ?><tr><th>Teléfono</th><td><?php echo $t[$i]['TELEFONO']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['FAX']) { ?><tr><th>Fax</th><td><?php echo $t[$i]['FAX']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['EMAIL']) { ?><tr><th>Correo electrónico</th><td><?php echo $t[$i]['EMAIL']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['WWW']) { ?><tr><th>Direccion web</th><td><?php echo $t[$i]['WWW']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['CREACION']) { ?><tr><th>Fecha de creacion</th><td><?php echo $t[$i]['CREACION']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['PUBLICACION']) { ?><tr><th>Fecha de publicación</th><td><?php echo $t[$i]['PUBLICACION']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['CONVENIO']) { ?><tr><th>Convenio</th><td><?php echo $t[$i]['CONVENIO']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['ENTIDAD']) { ?><tr><th>Entidad</th><td><?php echo $t[$i]['ENTIDAD']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['FECHA_BAJA']) { ?><tr><th>Fecha de baja</th><td><?php echo $t[$i]['FECHA_BAJA']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['OTROSDATOS']) { ?><tr><th>Otros datos</th><td><?php echo $t[$i]['OTROSDATOS']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['URL_HORARIO']) { ?><tr><th>URL de horarios</th><td><?php echo $t[$i]['URL_HORARIO']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['URL_CALENDARIO']) { ?><tr><th>URL de calendarios</th><td><?php echo $t[$i]['URL_CALENDARIO']; ?></td></tr><?php } ?>
                          <?php if($t[$i]['CARGO1']) { ?><tr><th>Cargo principal</th><td><?php echo $t[$i]['ELCARGO1'].($t[$i]['CARGO1']?' ('.$t[$i]['CARGO1'].')':null); ?></td></tr><?php } ?>
                          <?php if($t[$i]['CARGO2']) { ?><tr><th>Cargo secundario</th><td><?php echo $t[$i]['ELCARGO2'].($t[$i]['CARGO2']?' ('.$t[$i]['CARGO2'].')':null); ?></td></tr><?php } ?>
                        </table>
                      <?php
                    }
                    ?>
                      <span class='titulocargos'>Cargos</span>
                      <table class='cargos'><tbody id='cargos_<?php echo $t[$i]['CODNUM']; ?>'>
                      <?php
                        for($j=0;$j<$t[$i]['N'];$j++)
                        {
                          if(isset($_REQUEST['editando']) && $_REQUEST['editando']==$t[$i]['CODNUM'])
                          {
                            ?>
                              <tr id='fila_<?php echo $t[$i]['CODNUM']; ?>_<?php echo $j; ?>'>
                                <td><?php $Sevius->formularioInput("id[$j]",$t[$i]['T'][$j]['ID'],'text',2); ?></td>
                                <td><?php $Sevius->formularioInput("cargo[$j]",$t[$i]['T'][$j]['CAR_CODNUM'],'select',$valoresCargo); ?></td>
                                <td><?php $Sevius->formularioInput("subcargo[$j]",$t[$i]['T'][$j]['SUBCARGO'],'text',100); ?></td>
                                <td><?php $Sevius->formularioInput("dni[$j]",$t[$i]['T'][$j]['PRS_DNIPRS'],'text',15); ?></td>
                                <td><?php $Sevius->formularioInput("estado[$j]",$t[$i]['T'][$j]['ESTADO'],'select',$valoresEstado); ?></td>
                                <td>
                                  <a id='X_<?php echo $t[$i]['CODNUM']; ?>_<?php echo $j; ?>' <?php echo $Sevius->icono('trash'); ?>> Eliminar cargo</a>
                                </td>
                                <?php
                                  $Sevius->jsEvent("X_{$t[$i]['CODNUM']}_$j",'click',
                                      "document.getElementById('fila_{$t[$i]['CODNUM']}_{$j}').parentNode.removeChild(document.getElementById('fila_{$t[$i]['CODNUM']}_{$j}'));");
                                ?>
                              </tr>
                            <?php
                            $Sevius->formularioCerrar();
                          }
                          else
                          {
                            ?>
                              <tr id='fila_<?php echo $t[$i]['CODNUM']; ?>_<?php echo $j; ?>'>
                                <td><?php echo $t[$i]['T'][$j]['ID']; ?></td>
                                <td><?php echo $t[$i]['T'][$j]['CARGO'].($t[$i]['T'][$j]['CAR_CODNUM']?' ('.$t[$i]['T'][$j]['CAR_CODNUM'].')':null); ?></td>
                                <td><?php echo $t[$i]['T'][$j]['SUBCARGO']; ?></td>
                                <td><?php echo $t[$i]['T'][$j]['TRATAMIENTO'].' '.$t[$i]['T'][$j]['NOMPRS'].' '.$t[$i]['T'][$j]['LL1PRS'].' '.$t[$i]['T'][$j]['LL2PRS'].($t[$i]['T'][$j]['PRS_DNIPRS']?' ('.$t[$i]['T'][$j]['PRS_DNIPRS'].')':null); ?></td>
                                <td><?php echo $t[$i]['T'][$j]['ESTADO2']; ?></td>
                              </tr>
                            <?php
                          }
                        }
                      ?>
                      </tbody></table>

                      <div>
                      <?php
                        if(isset($_REQUEST['editando']) && $_REQUEST['editando']==$t[$i]['CODNUM'])
                        {
                          ?>
                            <a id='nuevo_<?php echo $t[$i]['CODNUM']; ?>' <?php echo $Sevius->icono('plus');?>> Añadir cargo</a>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a id='cancelar_<?php echo $t[$i]['CODNUM']; ?>' <?php echo $Sevius->icono('x');?>> Cerrar edición</a>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a id='grabar_<?php echo $t[$i]['CODNUM']; ?>' <?php echo $Sevius->icono('check');?>> Grabar</a>
                          <?php
                          $Sevius->jsEvent("grabar_{$t[$i]['CODNUM']}",'click',"document.datos$i.submit();");
                          $Sevius->jsEvent("cancelar_{$t[$i]['CODNUM']}",'click',"document.cancelar$i.submit();");
                          $Sevius->jsEvent("nuevo_{$t[$i]['CODNUM']}",'click',
                          str_replace('""',"''",
                          "  var x = document.getElementById('cargos_{$t[$i]['CODNUM']}');
                             var n = document.getElementById('N');
                             var tr = document.createElement('tr');
                             tr.id = 'fila_{$t[$i]['CODNUM']}_'+(n.value);
                             var td = document.createElement('td');
                             td.innerHTML = \"".$Sevius->formularioInputTxT("id[\"+(n.value)+\"]",null,'text',2)."\";
                             tr.appendChild(td);
                             td = document.createElement('td');
                             td.innerHTML = \"".$Sevius->formularioInputTxT("cargo[\"+(n.value)+\"]",null,'select',$valoresCargo)."\";
                             tr.appendChild(td);
                             td = document.createElement('td');
                             td.innerHTML = \"".$Sevius->formularioInputTxT("subcargo[\"+(n.value)+\"]",null,'text',100)."\";
                             tr.appendChild(td);
                             td = document.createElement('td');
                             td.innerHTML = \"".$Sevius->formularioInputTxT("dni[\"+(n.value)+\"]",null,'text',15)."\";
                             tr.appendChild(td);
                             td = document.createElement('td');
                             td.innerHTML = \"".$Sevius->formularioInputTxT("estado[\"+(n.value)+\"]",null,'select',$valoresEstado)."\";
                             tr.appendChild(td);
                             td = document.createElement('td');
                             td.innerHTML = \"<a id='X_{$t[$i]['CODNUM']}_{$j}' ".$Sevius->icono('trash')."> Eliminar cargo</a>\";
                             tr.appendChild(td);
                             x.appendChild(tr);
                             document.getElementById('X_{$t[$i]['CODNUM']}_'+n.value).addEventListener('click',function(evt) { var id = evt.target.id.substr(2); document.getElementById('fila_'+id).parentNode.removeChild(document.getElementById('fila_'+id)); });
                             n.value++;
                          "));
                        }
                        else
                        {
                          echo "<a id='eliminar_{$t[$i]['CODNUM']}' ".$Sevius->icono('trash')."> Eliminar centro</a>";
                          echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                          echo "<a id='grabar_{$t[$i]['CODNUM']}' ".$Sevius->icono('pencil')."> Editar</a>";
                          $Sevius->jsEvent("eliminar_{$t[$i]['CODNUM']}",'click',"document.borrar$i.submit();");
                          $Sevius->jsEvent("grabar_{$t[$i]['CODNUM']}",'click',"document.datos$i.submit();");
                          $Sevius->formularioCerrar();
                        }
                      ?>
                      </div>
                </div>
              </td></tr>
            <?php
          }
        ?>
      </table>

      <div class='btnInferior'>
        <a id='altanuevo' <?php echo $Sevius->icono('plus');?>> Añadir nuevo</a>
      </div>
    <?php

    $Sevius->formularioAbrir("alta_nuevo",true,true,"#alta");
    $Sevius->formularioInput('editando','alta');
    $Sevius->formularioCerrar();
    $Sevius->jsEvent("altanuevo",'click',"document.alta_nuevo.submit();");
    $Sevius->jsEvent("altanuevo1",'click',"document.alta_nuevo.submit();");
    $Sevius->jsEvent("altanuevo2",'click',"document.alta_nuevo.submit();");
  }
?>
