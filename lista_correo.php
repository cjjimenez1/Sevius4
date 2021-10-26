<?php
  function main($Sevius,$conexiones)
  {
    $roles = array('U'=>'Trabajar como usuario normal');
    $conexiones[1]->parse("select * from flc_responsable where usuario = :id");
    $conexiones[1]->value('id',$Sevius->idusuario);
    $conexiones[1]->execute();
    $conexiones[1]->fetch();
    if($conexiones[1]->rows>0) $roles['A']='Trabajar como administrador';
    $conexiones[1]->parse("select * from flc_dpd where usuario = :id");
    $conexiones[1]->value('id',$Sevius->idusuario);
    $conexiones[1]->execute();
    $conexiones[1]->fetch();
    if($conexiones[1]->rows>0) $roles['D']='Trabajar como delegado de protección de datos';
    $Sevius->seleccionRol($roles);

    switch($Sevius->rol)
    {
      case 'A': return administrador($Sevius,$conexiones[1]);
      case 'D': return delegado($Sevius,$conexiones[1]);
      case 'U': return usuario($Sevius,$conexiones[1],'U');
      default: return 'ERROR: Usuario no autorizado';
    }
  }

  function administrador($Sevius,$conexion)
  {
    // si algún modo de salida es excel o pdf no debe poner el menú
    if($Sevius->fcn<>'ADMIN' && $Sevius->fcn<>'DELEGADOS') $Sevius->fcn='LISTAS';

    $filtro = isset($_REQUEST['filtro'])?$_REQUEST['filtro']:null;
    $ordenar = isset($_REQUEST['ordenar'])?$_REQUEST['ordenar']:null;

    if(!isset($_REQUEST['documento']))
      $Sevius->menu(2,$Sevius->fcn,
             array('LISTAS'=>array('Listas de correo',$Sevius->destino(true,'LISTAS').
                                   ($filtro?"&filtro={$filtro}":null).
                                   ($ordenar?"&ordenar={$ordenar}":null)),
                   'ADMIN'=>array('Usuarios administradores',$Sevius->destino(true,'ADMIN')),
                   'DELEGADOS'=>array('Usuarios delegados',$Sevius->destino(true,'DELEGADOS'))
                   ));
    switch($Sevius->fcn)
    {
      case 'LISTAS': return usuario($Sevius,$conexion,'A');
      case 'ADMIN': return administradores($Sevius,$conexion);
      case 'DELEGADOS': return admindelegados($Sevius,$conexion);
    }
  }

  function delegado($Sevius,$conexion)
  {
    return usuario($Sevius,$conexion,'D');
  }

  function usuario($Sevius,$conexion,$tipousuario)
  {
    if($tipousuario=='D' && !(isset($_REQUEST['filtro']) && $_REQUEST['filtro'])) $_REQUEST['filtro']='20';

    $sql = "select * from flc_lista";
    if($tipousuario=='U') $sql .= " where administrador = :id";
    else $sql .= " where :id = :id";

    if(isset($_REQUEST['filtro'])) switch($_REQUEST['filtro'])
    {
      case 10:
      case 11:
      case 12:
      case 13:
      case 15:
        $sql .= " and tiposolicitud = 'R'"; break;
      case 20:
      case 21:
      case 22:
      case 23:
      case 25:
        $sql .= " and tiposolicitud = 'N'"; break;
      case 30:
      case 31:
      case 34:
        $sql .= " and tiposolicitud = 'C'"; break;
    }

    if(isset($_REQUEST['filtro'])) switch($_REQUEST['filtro'])
    {
      case 1:
      case 11:
      case 21:
        $sql .= ' and fechacancelacion is null and fechacreacion is null and fecharechazo is null and fechaaceptacion is null'; break;
      case 31:
        $sql .= ' and fechasolicitudbaja is not null and fechabaja is null'; break;
      case 2:
      case 12:
      case 22:
        $sql .= ' and fechacancelacion is null and fechaaceptacion is not null and fechacreacion is null'; break;
      case 3:
      case 13:
      case 23:
        $sql .= ' and fechacancelacion is null and fecharechazo is not null'; break;
      case 4:
      case 34:
        $sql .= ' and fechabaja is not null'; break;
      case 5:
      case 15:
      case 25:
        $sql .= ' and fechacancelacion is null and fechaaceptacion is not null and fechacreacion is not null'; break;
    }

    if((isset($_REQUEST['ordenar'])) && ($_REQUEST['ordenar']=='1'))
      $sql .= " order by nombre";
    else
      $sql .= " order by id desc";

    // EJECUCIÓN DE LAS ACCIONES
    if(isset($_REQUEST['accion'])) switch($_REQUEST['accion'])
    {
      case 'exportar':
        if($tipousuario<>'U')
          return exportar($Sevius,$conexion,$tipousuario,$sql);
      break;
      case 'aceptar':
        if($tipousuario<>'U')
          aceptar($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'aceptar_CR':
        if($tipousuario<>'U')
          aceptar_CR($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'rechazar':
        if($tipousuario<>'U')
          return rechazar($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'rechazaraceptado':
        if($tipousuario<>'U')
          if(rechazaraceptado($Sevius,$conexion,$tipousuario,$_REQUEST['documento'],$_REQUEST['motivo']))
            return;
      break;
      case 'cancelar':   
        if($tipousuario<>'D')
          cancelar($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'control':
        if($tipousuario<>'U')
          control($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'visibilidad':
        if($tipousuario<>'U')
          visibilidad($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'hecho':
        if($tipousuario=='A')
          hecho($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'solicitabaja':
        if($tipousuario<>'D')
          solicitabaja($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
      case 'borrar':
        if($tipousuario<>'D')
          borrar($Sevius,$conexion,$tipousuario,$_REQUEST['documento']);
      break;
    }

    if(isset($_GET['lista']) && $_GET['lista'] && isset($_GET['modo']) && $_GET['modo'])
    {
      if($tipousuario=='U' &&  $_GET['modo']<>'N' && $_GET['modo']<>'I') $_GET['modo']='U';
      if($tipousuario=='A' &&  $_GET['modo']<>'E' && $_GET['modo']<>'G') $_GET['modo']='A';
      if($tipousuario=='D' &&  $_GET['modo']<>'A') $_GET['modo']='A';

      $x = lista($Sevius,$conexion,$_GET['lista'],$_GET['modo'],$tipousuario);
      if($x) return $x;
    }

    if($tipousuario=='A' || $tipousuario=='D')
    {
      $filtro = isset($_REQUEST['filtro'])?$_REQUEST['filtro']:null;
      $ordenar = isset($_REQUEST['ordenar'])?$_REQUEST['ordenar']:null;

      $Sevius->formularioAbrir('formularioA1',true,true);
      $Sevius->formularioInput('ordenar',$ordenar);
      echo "Filtrar: ";
      if($tipousuario=='A')
      {
        echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";
        $Sevius->formularioInput('filtro',$filtro,'radio','');
        echo "Todas ";
        //$Sevius->formularioInput('filtro',$filtro,'radio','1');
        //echo "Todas pendientes";
        $Sevius->formularioInput('filtro',$filtro,'radio','2');
        echo "Todas aceptadas ";
        $Sevius->formularioInput('filtro',$filtro,'radio','3');
        echo "Todas rechazadas ";
        //$Sevius->formularioInput('filtro',$filtro,'radio','4');
        //echo "Todas canceladas";
        //$Sevius->formularioInput('filtro',$filtro,'radio','5');
        //echo "Todas completadas<br>";
        echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $Sevius->formularioInput('filtro',$filtro,'radio','10');
        echo "Todas cambios responsable ";
        $Sevius->formularioInput('filtro',$filtro,'radio','11');
        echo "C.R.  pendientes";
        //$Sevius->formularioInput('filtro',$filtro,'radio','12');
        //echo "C.R. aceptados ";
        $Sevius->formularioInput('filtro',$filtro,'radio','13');
        echo "C.R. rechazadas";
        $Sevius->formularioInput('filtro',$filtro,'radio','15');
        echo "C.R. completadas<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
      }
      $Sevius->formularioInput('filtro',$filtro,'radio','20');
      echo "Todas solicitudes de alta ";
      $Sevius->formularioInput('filtro',$filtro,'radio','21');
      echo "S. alta  pendientes DPD";
      $Sevius->formularioInput('filtro',$filtro,'radio','22');
      echo "S. alta aceptadas DPD";
      $Sevius->formularioInput('filtro',$filtro,'radio','23');
      echo "S. alta rechazadas DPD";
      $Sevius->formularioInput('filtro',$filtro,'radio','25');
      echo "S. alta Completadas";
      if($tipousuario=='A')
      {
        echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $Sevius->formularioInput('filtro',$filtro,'radio','30');
        echo "Todas solicitudes de baja ";
        $Sevius->formularioInput('filtro',$filtro,'radio','31');
        echo "S. baja pendientes";
        $Sevius->formularioInput('filtro',$filtro,'radio','34');
        echo "S. baja completadas<br>";
      }
      $Sevius->formularioInput('boton','Filtrar','submit');
      echo '<br><br>';
      $Sevius->formularioCerrar();
      $Sevius->formularioAbrir('formularioA2',true,true);
      $Sevius->formularioInput('filtro',$filtro);
      echo "Ordenar: ";
      $Sevius->formularioInput('ordenar',$ordenar,'radio','');
      echo "Id descendente ";
      $Sevius->formularioInput('ordenar',$ordenar,'radio','1');
      echo "Nombre de la lista ";
      $Sevius->formularioInput('boton','Refrescar','submit');
      $Sevius->formularioCerrar();
      echo '<br><br>';
    }

    $conexion->parse($sql);
    $conexion->value('id',$Sevius->idusuario);
    $conexion->execute();
    $conexion->fetch();

    if($tipousuario=='U')
    {
      ?>
        <div class='btnSuperior'>
          <a href='<?php echo $Sevius->destino(true,true).'&lista=N&modo=N'.
                              (isset($_REQUEST['filtro'])?"&filtro={$_REQUEST['filtro']}":null).
                              (isset($_REQUEST['ordenar'])?"&ordenar={$_REQUEST['ordenar']}":null);
                   ?>'>Nueva solicitud</a>
        </div>
      <?php
    }

    if($conexion->rows>0)
    {
      $filtro = isset($_REQUEST['filtro'])?$_REQUEST['filtro']:null;
      $ordenar = isset($_REQUEST['ordenar'])?$_REQUEST['ordenar']:null;
      $Sevius->formularioAbrir('formulario',true,true);
      $Sevius->formularioInput('filtro',$filtro);
      $Sevius->formularioInput('ordenar',$ordenar);
      $Sevius->formularioInput('accion',null);
      $Sevius->formularioInput('documento',null);
      $Sevius->formularioCerrar();

      $Sevius->jsInicio();
      ?>
        function aceptar(i)
        {
          document.formulario.accion.value='aceptar';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }
        function aceptar_CR(i)
        {
          document.formulario.accion.value='aceptar_CR';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }
        function rechazar(i)
        {
          document.formulario.accion.value='rechazar';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }

        function cancelar(i)
        {
          document.formulario.accion.value='cancelar';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }

        function visibilidad(i)
        {
          document.formulario.accion.value='visibilidad';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }

        function control(i)
        {
          document.formulario.accion.value='control';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }

        function hecho(i)
        {
          document.formulario.accion.value='hecho';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }
        
        function solicitabaja(i)
        {
          document.formulario.accion.value='solicitabaja';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }
        function borrar(i)
        {
          document.formulario.accion.value='borrar';
          document.formulario.documento.value=i;
          document.formulario.submit();
        }
      <?php
      $Sevius->jsFin();
      ?>
      
        <div class='btnSuperior'>
          <a id='exportar1'>Exportar</a>
        </div>
      <?php
      $Sevius->jsEvent('exportar1','click',"document.formulario.accion.value='exportar'; document.formulario.submit()");
    }

    if($conexion->rows==0)
      $Sevius->SalidaAviso($tipousuario=='U'?'No tiene actualmente ninguna solicitud grabada':'No hay actualmente ninguna solicitud con esas características');
    else
    {
      ?>

        <table class='tablaListado'>
          <caption>Solicitudes de listas de correo a su nombre</caption>
          <tr>
            <?php if($tipousuario!='U') echo "<th>Id</th>"; ?>
            <th>Tipo</th>
            <th>Lista</th>
            <th>Descripción</th>
            <th>Visibilidad</th>
            <th>Control de suscripciones</th>
            <th>Fechas</th>
            <th>Acciones</th>
            <th>Detalles</th>
          </tr>
          <?php
            $desctipo = array('R'=>'Cambio de responsable','N'=>'Solicitud nueva','C'=>'Cancelacion');
            $descvisi = array('S'=>'Pública','N'=>'Privada');
            $descsusc = array('S'=>'Por suscripción','N'=>'Por invitación');
            for($i=0;$i<$conexion->rows;$i++)
            {
              ?>
                <tr>
                  <?php if($tipousuario!='U') echo "<td>{$conexion->data[$i]['ID']}</td>"; ?>
                  <td><?php echo $desctipo[$conexion->data[$i]['TIPOSOLICITUD']]; ?></td>
                  <td><?php echo $conexion->data[$i]['NOMBRE']; ?></td>
                  <td><?php echo $conexion->data[$i]['DESCRIPCION']; ?></td>
                  <td><?php echo $descvisi[$conexion->data[$i]['VISIBILIDAD']]; ?></td>
                  <td><?php echo $descsusc[$conexion->data[$i]['PORSUSCRIPCION']]; ?></td>
                  <td>
                    <?php 
                      echo "Fecha de solicitud: {$conexion->data[$i]['FECHASOLICITUD']}"; 
                        if($conexion->data[$i]['FECHAACEPTACION']) echo "<br>---------------<br>Fecha de aceptación: {$conexion->data[$i]['FECHAACEPTACION']}";
                        if($conexion->data[$i]['FECHARECHAZO']) echo "<br>---------------<br>Fecha de rechazo: {$conexion->data[$i]['FECHARECHAZO']}";
                        if($conexion->data[$i]['FECHACANCELACION']) echo "<br>---------------<br>Fecha de cancelación: {$conexion->data[$i]['FECHACANCELACION']}";
                        if($conexion->data[$i]['FECHABAJA']) echo "<br>---------------<br>Fecha de baja: {$conexion->data[$i]['FECHABAJA']}";
                        if($tipousuario=='A'){
                          if($conexion->data[$i]['FECHACREACION']) echo "<br>---------------<br>Fecha de creación: {$conexion->data[$i]['FECHACREACION']}";
                          if($conexion->data[$i]['FECHASOLICITUDBAJA']) echo "<br>---------------<br>Fecha de solicitud baja: {$conexion->data[$i]['FECHASOLICITUDBAJA']}";
                          //if($conexion->data[$i]['FECHABAJA']) echo "<br>---------------<br>Fecha de baja: {$conexion->data[$i]['FECHABAJA']}";
                          } 
                    ?>
                  </td>
                  
                  <td><!-- Columna acciones -->
                    <?php
                      switch($tipousuario)
                      {
                        case 'A':
                          if($conexion->data[$i]['TIPOSOLICITUD']=='R' &&
                             !$conexion->data[$i]['FECHAACEPTACION']   &&
                             !$conexion->data[$i]['FECHARECHAZO']      &&
                             !$conexion->data[$i]['FECHACANCELACION']  )
                          {
                            ?>
                              <input type='submit' value='Aceptar_CR' id='botonaceptar_CR<?php echo $i;?>'><br>
                              <input type='submit' value='Rechazar' id='botonrechazar<?php echo $i;?>'><br>
                              <input type='submit' value='Cancelar' id='botoncancelar<?php echo $i;?>'>
                              
                            <?php
                            $Sevius->JSEvent("botonaceptar_CR{$i}",'click',"aceptar_CR({$conexion->data[$i]['ID']});");
                            $Sevius->JSEvent("botonrechazar{$i}",'click',"rechazar({$conexion->data[$i]['ID']});");
                            $Sevius->JSEvent("botoncancelar{$i}",'click',"cancelar({$conexion->data[$i]['ID']});");
                            
                          }
                          if($conexion->data[$i]['TIPOSOLICITUD']=='C' &&
                             !$conexion->data[$i]['FECHAACEPTACION']   &&
                             !$conexion->data[$i]['FECHARECHAZO']      &&
                             !$conexion->data[$i]['FECHACANCELACION']  )
                          {
                            ?>
                              <input type='submit' value='Cancelar' id='botoncancelar<?php echo $i;?>'>
                            <?php
                            $Sevius->JSEvent("botoncancelar{$i}",'click',"cancelar({$conexion->data[$i]['ID']});");
                          }
                          if($conexion->data[$i]['TIPOSOLICITUD']=='N' &&
                             $conexion->data[$i]['FECHAACEPTACION']    &&
                             !$conexion->data[$i]['FECHARECHAZO']      &&
                             !$conexion->data[$i]['FECHACANCELACION']  &&
                             !$conexion->data[$i]['FECHACREACION']     )
                          {
                            ?>
                              <input type='submit' value='Hecho' id='botonhecho<?php echo $i;?>'>
                            <?php
                            $Sevius->JSEvent("botonhecho{$i}",'click',"hecho({$conexion->data[$i]['ID']});");
                          } else if($conexion->data[$i]['FECHASOLICITUDBAJA']&&
                                    !$conexion->data[$i]['FECHABAJA'])
                          {
                            ?>
                              <input type='submit' value='Borrada' id='botonborrar<?php echo $i;?>'>
                            <?php
                            $Sevius->JSEvent("botonborrar{$i}",'click',"borrar({$conexion->data[$i]['ID']});");
                          }
                        break;
                        case 'D':
                          if($conexion->data[$i]['TIPOSOLICITUD']=='N' &&
                             !$conexion->data[$i]['FECHAACEPTACION']   &&
                             !$conexion->data[$i]['FECHARECHAZO']      &&
                             !$conexion->data[$i]['FECHACANCELACION']  )
                          {
                            ?>
                              <input type='submit' value='Cambiar visibilidad' id='botonvisibilidad<?php echo $i;?>'><br>
                              <input type='submit' value='Cambiar control suscripción' id='botoncontrol<?php echo $i;?>'><br>
                              <input type='submit' value='Aceptar' id='botonaceptar<?php echo $i;?>'><br>
                              <input type='submit' value='Rechazar' id='botonrechazar<?php echo $i;?>'>
                            <?php
                            $Sevius->JSEvent("botonvisibilidad{$i}",'click',"visibilidad({$conexion->data[$i]['ID']});");
                            $Sevius->JSEvent("botoncontrol{$i}",'click',"control({$conexion->data[$i]['ID']});");
                            $Sevius->JSEvent("botonaceptar{$i}",'click',"aceptar({$conexion->data[$i]['ID']});");
                            $Sevius->JSEvent("botonrechazar{$i}",'click',"rechazar({$conexion->data[$i]['ID']});");
                          }
                        break;
                        case 'U':
                          if(!$conexion->data[$i]['FECHASOLICITUDBAJA'])
                          { 
                           ?>
                              <input type='submit' value='Solicitar baja' id='botonbaja<?php echo $i;?>'><br>
                           <?php   
                           $Sevius->JSEvent("botonbaja{$i}",'click',"solicitabaja({$conexion->data[$i]['ID']});");
                           }
                           if($conexion->data[$i]['TIPOSOLICITUD']=='N' &&
                             !$conexion->data[$i]['FECHAACEPTACION']   &&
                             !$conexion->data[$i]['FECHARECHAZO']      &&
                             !$conexion->data[$i]['FECHACANCELACION']  )
                          {
                            ?>
                              <input type='submit' value='Cancelar solicitud' id='botoncancelar<?php echo $i;?>'>
                              
                            <?php
                            $Sevius->JSEvent("botoncancelar{$i}",'click',"cancelar({$conexion->data[$i]['ID']});");
                            
                          }
                        break;
                      }
                    ?>
                  </td>
                  
                  <th>
                    <a href='<?php echo $Sevius->destino(true,true).
                                        '&modo=U&lista='.urlencode($conexion->data[$i]['ID']).
                                        ($_REQUEST['filtro']?"&filtro={$_REQUEST['filtro']}":null).
                                        ($_REQUEST['ordenar']?"&ordenar={$_REQUEST['ordenar']}":null); ?>'
                       <?php echo $Sevius->icono('browser'); ?>>
                    </a>
                  </th>
                </tr>
              <?php
            }
          ?>
        </table>
      <?php
    }

    if($tipousuario=='U')
    {
      ?>
        <div class='btnInferior'> <h4>"Solicitud de Nueva Lista o cambio de responsable"</h4>
          <a href='<?php echo $Sevius->destino(true,true).'&lista=N&modo=N'.
                              (@$_REQUEST['filtro']?"&filtro={$_REQUEST['filtro']}":null).
                              (@$_REQUEST['ordenar']?"&ordenar={$_REQUEST['ordenar']}":null);
                   ?>'>Solicitar</a>
        </div>
      <?php
    }

    if($tipousuario=='A'  && $conexion->rows>0)
    {
      ?>
        <div class='btnInferior'>
          <a id='exportar2'>Exportar</a>
        </div>
      <?php
      $Sevius->jsEvent('exportar2','click',"document.formulario.accion.value='exportar'; document.formulario.submit()");
    }
  }

  function lista($Sevius,$conexion,$lista,$modo,$tipousuario)
  // modos U = usuario, N = nueva, I = insertar,
  //       A = admin o dpd, E = editar, G = grabar
  {
    if($tipousuario=='U' && $modo!='U' && $modo!='N' && $modo!='I') return;
    if($tipousuario=='A' && $modo!='A' && $modo!='E' && $modo!='G') return;
    if($tipousuario=='D' && $modo!='A') return;

    $sqlDatosUsuario = "select administrador,nombre_adm,ap1_adm,ap2_adm,
                               case
                               when dep is not null then 'PDI'
                               when uni is not null then 'PAS'
                               when pi is not null then 'PI'
                               else null
                             end perfil,
                             coalesce(dep,uni,pi) tipoperfil
                      from ( select a.id administrador,a.nombre nombre_adm,a.apellido1 ap1_adm,a.apellido2 ap2_adm,
                                   (select id_departamento from h_v_sic_sevius_pdi@hom where cod_persona = b.idapl) dep,
                                   (select id_unidad from h_v_sic_sevius_pas@hom where cod_persona = b.idapl) uni,
                                   (select nvl(id_departamento,id_unidad) from h_v_sic_sevius_inv@hom where cod_persona = b.idapl) pi
                             from   sv4_usuario a,
                                    sv4_usuarioid b
                             where  a.id = :id
                             and    a.id = b.id
                             and    b.aplicacion = 'RHID'
                           )
                      where coalesce(dep,uni,pi) is not null";

    if($modo=='I' || $modo=='G')
    {
      $valido = true;
      if($modo=='I' && $_REQUEST['frmlAcep']<>'S')
      {
        $Sevius->salidaError('Debe leer y aceptar las condiciones de uso y obligaciones del administrador');
        $valido = false;
      }
      if(!$_REQUEST['NOMBRE'])
      {
        $Sevius->salidaError('El nombre debe cumplimentarse');
        $valido = false;
      }
      if(strpos($_REQUEST['NOMBRE'],' ')!==false ||
         strpos($_REQUEST['NOMBRE'],',')!==false )
      {
        $Sevius->salidaError('El nombre no puede incluir espacios ni comas...');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']<>'S' && $_REQUEST['TIPOSOLICITUD']<>'N' && $_REQUEST['TIPOSOLICITUD']<>'R')
      {
        $Sevius->salidaError('La renovación debe tener un valor entre los indicados');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']=='N' && !$_REQUEST['DESCRIPCION'])
      {
        $Sevius->salidaError('La descripción debe cumplimentarse');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']=='N' && !$_REQUEST['DESTINATARIOS'])
      {
        $Sevius->salidaError('Los destinatarios deben cumplimentarse');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']=='N' && !$_REQUEST['FINALIDAD'])
      {
        $Sevius->salidaError('La finalidad debe cumplimentarse');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']=='N' && $_REQUEST['CADUCIDAD'] && $Sevius->formatoFecha($_REQUEST['CADUCIDAD'])===false)
      {
        $Sevius->salidaError('La fecha de caducidad debe ser una fecha válida');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']=='N' && $_REQUEST['VISIBILIDAD']<>'S' && $_REQUEST['VISIBILIDAD']<>'N')
      {
        $Sevius->salidaError('La visibilidad debe tener un valor entre los indicados');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']=='N' && $_REQUEST['PORSUSCRIPCION']<>'S' && $_REQUEST['PORSUSCRIPCION']<>'N')
      {
        $Sevius->salidaError('El control de suscripciones debe tener un valor entre los indicados');
        $valido = false;
      }
      if($_REQUEST['TIPOSOLICITUD']=='N' && $_REQUEST['MODERADA']<>'S' && $_REQUEST['MODERADA']<>'N')
      {
        $Sevius->salidaError('La moderación debe tener un valor entre los indicados');
        $valido = false;
      }
      if($modo=='G' && !$_REQUEST['ADMINISTRADOR'])
      {
        $Sevius->salidaError('El documento del administrador debe cumplimentarse');
        $valido = false;
      }
      if($modo=='G' && !$_REQUEST['NOMBRE_ADM'])
      {
        $Sevius->salidaError('El nombre del administrador debe cumplimentarse');
        $valido = false;
      }
      if($modo=='G' && !$_REQUEST['AP1_ADM'])
      {
        $Sevius->salidaError('El primer apellido del administrador debe cumplimentarse');
        $valido = false;
      }
      if($modo=='G' && !$_REQUEST['AP2_ADM'])
      {
        $Sevius->salidaError('El segundo apellido del administrador debe cumplimentarse');
        $valido = false;
      }
      if($modo=='G' && !$_REQUEST['PERFIL'])
      {
        $Sevius->salidaError('El perfil del administrador debe cumplimentarse');
        $valido = false;
      }
      if($modo=='G' && !$_REQUEST['TIPOPERFIL'])
      {
        $Sevius->salidaError('La unidad del administrador debe cumplimentarse');
        $valido = false;
      }
      if($modo=='G' && !$_REQUEST['CARGO'])
      {
        $Sevius->salidaError('El cargo del administrador debe cumplimentarse');
        $valido = false;
      }
      if(!$_REQUEST['CORREO'])
      {
        $Sevius->salidaError('El correo electrónico del administrador debe cumplimentarse');
        $valido = false;
      }
      if(!$_REQUEST['TELEFONO'])
      {
        $Sevius->salidaError('El teléfono del administrador debe cumplimentarse');
        $valido = false;
      }

      if($valido && $modo=='I')
      {
        $conexion->parse("select *
                          from   flc_lista
                          where  upper(nombre) = upper(:nombre)
                          and    fechabaja is null
                          and    fechacancelacion is null
                          order by id desc");
        /************************************************
        He quitado del select las condiciones de fechas para que deje crear cambio de resonsable y no deje crear duplicadas
            and   fechaaceptacion is null
            and   fecharechazo is null
            and   fechacancelacion is null
        **************************************************/
        $conexion->value('nombre',$_REQUEST['NOMBRE']);
        $conexion->execute();
        $conexion->fetch();
        if($_REQUEST['TIPOSOLICITUD']=='N' && $conexion->rows>0)
        {
          $Sevius->salidaError('Ya se está gestionando una lista con ese nombre');
          $valido = false;
        }
        elseif($_REQUEST['TIPOSOLICITUD']=='R' && $conexion->rows==0)
        { 

          $Sevius->salidaError('No hay ninguna lista con ese nombre para cambiar su responsable');
          $valido = false;
        }
        elseif($_REQUEST['TIPOSOLICITUD']=='R')
        {
          $_REQUEST['DESCRIPCION'] = $conexion->data[0]['DESCRIPCION'];
          $_REQUEST['DESTINATARIOS'] = $conexion->data[0]['DESTINATARIOS'];
          $_REQUEST['FINALIDAD'] = $conexion->data[0]['FINALIDAD'];
          $_REQUEST['CADUCIDAD'] = $conexion->data[0]['CADUCIDAD'];
          $_REQUEST['VISIBILIDAD'] = $conexion->data[0]['VISIBILIDAD'];
          $_REQUEST['PORSUSCRIPCION'] = $conexion->data[0]['PORSUSCRIPCION'];
          $_REQUEST['MODERADA'] = $conexion->data[0]['MODERADA'];
        }
      }

      if($valido && $modo=='I')
      {
        $conexion->parse("select max(id)+1 n from flc_lista");
        $conexion->novalue();
        $conexion->execute();
        $conexion->fetch();
        $nuevo = $conexion->data[0]['N'];
        $Sevius->conexion->parse($sqlDatosUsuario);
        $Sevius->conexion->value('id',$Sevius->idusuario);
        $Sevius->conexion->execute();
        list($n,$t) = $Sevius->conexion->fetch();
        $conexion->parse("insert into flc_lista
                                 (id,nombre,tiposolicitud,descripcion,
                                  destinatarios,finalidad,caducidad,
                                  visibilidad,porsuscripcion,moderada,
                                  administrador,nombre_adm,ap1_adm,ap2_adm,
                                  perfil,tipoperfil,cargo,correo,telefono,
                                  fechasolicitud)
                          values (:id,:nombre,:tiposolicitud,:descripcion,
                                  :destinatarios,:finalidad,:caducidad,
                                  :visibilidad,:porsuscripcion,:moderada,
                                  :administrador,:nombre_adm,:ap1_adm,:ap2_adm,
                                  :perfil,:tipoperfil,:cargo,:correo,:telefono,
                                  sysdate)");
        $conexion->value('id',$nuevo);
        $conexion->value('nombre',$_REQUEST['NOMBRE']);
        $conexion->value('tiposolicitud',$_REQUEST['TIPOSOLICITUD']);
        $conexion->value('descripcion',$_REQUEST['DESCRIPCION']);
        $conexion->value('destinatarios',$_REQUEST['DESTINATARIOS']);
        $conexion->value('finalidad',$_REQUEST['FINALIDAD']);
        $conexion->value('caducidad',$_REQUEST['CADUCIDAD']);
        $conexion->value('visibilidad',$_REQUEST['VISIBILIDAD']);
        $conexion->value('porsuscripcion',$_REQUEST['PORSUSCRIPCION']);
        $conexion->value('moderada',$_REQUEST['MODERADA']);
        $conexion->value('administrador',$Sevius->idusuario);
        $conexion->value('nombre_adm',$t[0]['NOMBRE_ADM']);
        $conexion->value('ap1_adm',$t[0]['AP1_ADM']);
        $conexion->value('ap2_adm',$t[0]['AP2_ADM']);
        $conexion->value('perfil',$t[0]['PERFIL']);
        $conexion->value('tipoperfil',$t[0]['TIPOPERFIL']);
        $conexion->value('cargo',$_REQUEST['CARGO']);
        $conexion->value('correo',$_REQUEST['CORREO']);
        $conexion->value('telefono',$_REQUEST['TELEFONO']);
        $conexion->execute();
        if($conexion->error())
        {
          $Sevius->salidaError("Error al insertar: ".$conexion->error());
          $conexion->rollback();
        }
        else
        {
          $conexion->commit();
          //mandar correo a DPD de cración de lista
          if($_REQUEST['TIPOSOLICITUD']=='N')
            {
            $wasunto = "Solicitud de creación de la lista de distribución {$_REQUEST['NOMBRE']}.";
            $wcuerpo = "Existe una solicitud de creación de una nueva lista de distribución. Tendrá que acceder a la aplicación en SEVIUS para la autorización y clasificación pertinente.";
            $wfrom = "sos@us.es";
            $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom);
            $conexion->parse("select correo from flc_dpd");
            $conexion->novalue();
            $conexion->execute();
            $conexion->fetch();
            for($i=0;$i<$conexion->rows;$i++) $correo->destinatario($conexion->data[$i]['CORREO']);
            $correo->enviar();
            $Sevius->salidaAviso("Se ha solicitado la creación de la lista {$_REQUEST['NOMBRE']}");
            }
          else if ($_REQUEST['TIPOSOLICITUD']=='R') 
            {
            $wasunto = "Cambio de responsable de la lista de distribución {$_REQUEST['NOMBRE']}";
            $wcuerpo = "Su petición de cambio de responsable ha sido procesada.\n".
                       "Recuerde que la dirección de correo del administrador, y la password de administración, debe cambiarla usted en la interfaz de administración de mailman:\n".
                       "https://listas.us.es/mailman/admin/nombre_lista\n".
                       "o\n".
                       "https://listasvol.us.es/mailman/admin/nombre_lista\n".
                       "\n".
                       "Más información sobre listas en:\n".
                       "https://sic.us.es/servicios/correo-electronico/listas-de-distribucion";
            $wfrom = "sos@us.es"; 
            $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom); // remitente
            $correo->destinatario($t[0]['CORREO']);
            $correo->enviar();

            $wasunto = "Cambio de responsable de la lista de distribución {$_REQUEST['NOMBRE']}";
            $wcuerpo = "Existe una solicitud de cambio de responsable para una lista de distribución. Tendrá que acceder a la aplicación en SEVIUS para su verificación.";
            $wfrom = "sos@us.es"; //Poner aquí noreply@us.es
            $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom); // remitente
            $conexion->parse("select correo from flc_responsable");
            $conexion->novalue();
            $conexion->execute();
            $conexion->fetch();
            for($i=0;$i<$conexion->rows;$i++) $correo->destinatario($conexion->data[$i]['CORREO']);
            $correo->enviar();
            $Sevius->salidaAviso("Se ha solicitado el cambio de responsable de la lista {$_REQUEST['NOMBRE']}");
            }

          $Sevius->conexion->commit();
          
          return lista($Sevius,$conexion,$nuevo,'U',$tipousuario);
        }
      }

      if($valido && $modo=='G')
      {     
        $conexion->parse("update flc_lista
                          set    nombre             = :nombre,
                                 tiposolicitud      = :tiposolicitud,
                                 descripcion        = :descripcion,
                                 destinatarios      = :destinatarios,
                                 finalidad          = :finalidad,
                                 caducidad          = :caducidad,
                                 visibilidad        = :visibilidad,
                                 porsuscripcion     = :porsuscripcion,
                                 moderada           = :moderada,
                                 administrador      = :administrador,
                                 nombre_adm         = :nombre_adm,
                                 ap1_adm            = :ap1_adm,
                                 ap2_adm            = :ap2_adm,
                                 perfil             = :perfil,
                                 tipoperfil         = :tipoperfil,
                                 cargo              = :cargo,
                                 correo             = :correo,
                                 telefono           = :telefono
                          where  id = :id");
        $conexion->value('id',$lista);
        $conexion->value('nombre',$_REQUEST['NOMBRE']);
        $conexion->value('tiposolicitud',$_REQUEST['TIPOSOLICITUD']);
        $conexion->value('descripcion',$_REQUEST['DESCRIPCION']);
        $conexion->value('destinatarios',$_REQUEST['DESTINATARIOS']);
        $conexion->value('finalidad',$_REQUEST['FINALIDAD']);
        $conexion->value('caducidad',$_REQUEST['CADUCIDAD']);
        $conexion->value('visibilidad',$_REQUEST['VISIBILIDAD']);
        $conexion->value('porsuscripcion',$_REQUEST['PORSUSCRIPCION']);
        $conexion->value('moderada',$_REQUEST['MODERADA']);
        $conexion->value('administrador',$_REQUEST['ADMINISTRADOR']);
        $conexion->value('nombre_adm',$_REQUEST['NOMBRE_ADM']);
        $conexion->value('ap1_adm',$_REQUEST['AP1_ADM']);
        $conexion->value('ap2_adm',$_REQUEST['AP2_ADM']);
        $conexion->value('perfil',$_REQUEST['PERFIL']);
        $conexion->value('tipoperfil',$_REQUEST['TIPOPERFIL']);
        $conexion->value('cargo',$_REQUEST['CARGO']);
        $conexion->value('correo',$_REQUEST['CORREO']);
        $conexion->value('telefono',$_REQUEST['TELEFONO']);
                                 //,
                                 //fechaaceptacion    = :fechaaceptacion,
                                 //fecharechazo       = :fecharechazo,
                                 //motivorechazo      = :motivorechazo,
                                 //fechacancelacion   = :fechacancelacion,
                                 //fechacreacion      = :fechacreacion,
                                 //fechasolicitudbaja = :fechasolicitudbaja,
                                 //fechabaja          = :fechabaja
        //$conexion->value('fechaaceptacion',$_REQUEST['FECHAACEPTACION']);
        //$conexion->value('fecharechazo',$_REQUEST['FECHARECHAZO']);
        //$conexion->value('motivorechazo',$_REQUEST['MOTIVORECHAZO']);
        //$conexion->value('fechacancelacion',$_REQUEST['FECHACANCELACION']);
        //$conexion->value('fechacreacion',$_REQUEST['FECHACREACION']);
        //$conexion->value('fechasolicitudbaja',$_REQUEST['FECHASOLICITUDBAJA']);
        //$conexion->value('fechabaja',$_REQUEST['FECHABAJA']);
        $conexion->execute();
        if($conexion->error())
        {
          $Sevius->salidaError("Error al actualizar: ".$conexion->error());
          $conexion->rollback();
        }
        else
        {
          $conexion->commit();
          return lista($Sevius,$conexion,$lista,'A',$tipousuario);
        }
      }

      $modo = $modo=='I'?'N':'E';
    }

    if($modo=='N')
    {
      $Sevius->conexion->parse($sqlDatosUsuario);
      $Sevius->conexion->value('id',$Sevius->idusuario);
      $Sevius->conexion->execute();
      list($n,$t) = $Sevius->conexion->fetch();
    }
    else
    {
      $sql = "select * from flc_lista where id = :lista";
      if($modo=='U' || $modo=='N' || $modo=='I') $sql .= " and administrador = :id";
      $conexion->parse($sql);
      $conexion->value('id',$Sevius->idusuario);
      $conexion->value('lista',$lista);
      $conexion->execute();
      list($n,$t) = $conexion->fetch();
    }

    if($n==0)
    {
      $Sevius->salidaAviso('Lista no accesible');
      return;
    }

    if($modo=='N' || $modo=='E')
    {
      $Sevius->formularioURL('formulario',$Sevius->destino(true,true).'&lista='.$lista.'&modo='.($modo=='N'?'I':'G').
                                   (isset($_REQUEST['filtro'])?"&filtro={$_REQUEST['filtro']}":null).
                                   (isset($_REQUEST['ordenar'])?"&ordenar={$_REQUEST['ordenar']}":null));
    }
    ?>
      <table class='tablaListado'>
        <caption>Lista de correo <?php echo $modo=='N'?'nueva':$t[0]['NOMBRE']; ?></caption>
        <tr id='filaID'>
          <td>Identificador</td>
          <td><?php echo @$t[0]['ID']; ?></td>
        </tr>
        <tr id='filaNOMBRE'>
          <td>Nombre (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="NOMBRE" name="NOMBRE" value="'.
                     (@$_REQUEST['NOMBRE']?@$_REQUEST['NOMBRE']:@$t[0]['NOMBRE']).
                     '" maxlength="64" size="64">';
              else
                echo $t[0]['NOMBRE'];
            ?>
          </td>
        </tr>
        <tr id='filaTIPOSOLICITUD'>
          <td>Tipo de solicitud (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
              {
                echo '<select id="TIPOSOLICITUD" name="TIPOSOLICITUD">'.
                     '<option value="0"'.((@$_REQUEST['TIPOSOLICITUD']?@$_REQUEST['TIPOSOLICITUD']:@$t[0]['TIPOSOLICITUD'])=='0'?' selected':null).'>Elige un tipo de solicitud</option>'.
                     '<option value="N"'.((@$_REQUEST['TIPOSOLICITUD']?@$_REQUEST['TIPOSOLICITUD']:@$t[0]['TIPOSOLICITUD'])=='N'?' selected':null).'>Nueva lista</option>'.
                     '<option value="R"'.((@$_REQUEST['TIPOSOLICITUD']?@$_REQUEST['TIPOSOLICITUD']:@$t[0]['TIPOSOLICITUD'])=='R'?' selected':null).'>Cambio de responsable</option>'.
                    '</select>';
                $Sevius->jsEvent('TIPOSOLICITUD','click',
                 "var x = document.getElementById('TIPOSOLICITUD').value=='N'?'':'oculto';
                  document.getElementById('filaDESCRIPCION').className=x;
                  document.getElementById('filaDESTINATARIOS').className=x;
                  document.getElementById('filaFINALIDAD').className=x;
                  document.getElementById('filaCADUCIDAD').className=x;
                  document.getElementById('filaVISIBILIDAD').className=x;
                  document.getElementById('filaPORSUSCRIPCION').className=x;
                  document.getElementById('filaMODERADA').className=x;
                 ");
              }
              else
                echo $t[0]['TIPOSOLICITUD']=='N'?'Nueva lista':($t[0]['TIPOSOLICITUD']=='R'?'Cambio de responsable':($t[0]['TIPOSOLICITUD']=='C'?'Solicitud de cancelación':null));
            ?>
          </td>
        </tr>
        <tr id='filaDESCRIPCION'>
          <td>Descripción</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="DESCRIPCION" name="DESCRIPCION" value="'.
                     (@$_REQUEST['DESCRIPCION']?@$_REQUEST['DESCRIPCION']:@$t[0]['DESCRIPCION']).
                     '" maxlength="4000" size="150">';
              else
                echo $t[0]['DESCRIPCION'];
            ?>
          </td>
        </tr>
        <tr id='filaDESTINATARIOS'>
          <td>Destinatarios (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="DESTINATARIOS" name="DESTINATARIOS" value="'.
                     (@$_REQUEST['DESTINATARIOS']?@$_REQUEST['DESTINATARIOS']:@$t[0]['DESTINATARIOS']).
                     '" maxlength="4000" size="150">';
              else
                echo $t[0]['DESTINATARIOS'];
            ?>
          </td>
        </tr>
        <tr id='filaFINALIDAD'>
          <td>Finalidad (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="FINALIDAD" name="FINALIDAD" value="'.
                     (@$_REQUEST['FINALIDAD']?@$_REQUEST['FINALIDAD']:@$t[0]['FINALIDAD']).
                     '" maxlength="4000" size="150">';
              else
                echo $t[0]['FINALIDAD'];
            ?>
          </td>
        </tr>
        <tr id='filaCADUCIDAD'>
          <td>Caducidad (opcional)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="CADUCIDAD" name="CADUCIDAD" value="'.
                     (@$_REQUEST['CADUCIDAD']?@$_REQUEST['CADUCIDAD']:@$t[0]['CADUCIDAD']).
                     '" maxlength="10" size="10">';
              else
                echo $t[0]['CADUCIDAD'];
            ?>
          </td>
        </tr>
        <tr id='filaVISIBILIDAD'>
          <td>Visibilidad (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<select id="VISIBILIDAD" name="VISIBILIDAD">'.
                     '<option value="N"'.((@$_REQUEST['VISIBILIDAD']?@$_REQUEST['VISIBILIDAD']:@$t[0]['VISIBILIDAD'])=='N'?' selected':null).'>Privada</option>'.
                     '<option value="S"'.((@$_REQUEST['VISIBILIDAD']?@$_REQUEST['VISIBILIDAD']:@$t[0]['VISIBILIDAD'])=='S'?' selected':null).'>Pública</option>'.
                    '</select>';
              else
                echo $t[0]['VISIBILIDAD']=='N'?'Privada':($t[0]['VISIBILIDAD']=='S'?'Pública':null);
            ?>
          </td>
        </tr>
        <tr id='filaPORSUSCRIPCION'>
          <td>Control de suscripciones (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<select id="PORSUSCRIPCION" name="PORSUSCRIPCION">'.
                     '<option value="N"'.((@$_REQUEST['PORSUSCRIPCION']?@$_REQUEST['PORSUSCRIPCION']:@$t[0]['PORSUSCRIPCION'])=='N'?' selected':null).'>Por invitación</option>'.
                     '<option value="S"'.((@$_REQUEST['PORSUSCRIPCION']?@$_REQUEST['PORSUSCRIPCION']:@$t[0]['PORSUSCRIPCION'])=='S'?' selected':null).'>Suscripción directa</option>'.
                    '</select>';
              else
                echo $t[0]['PORSUSCRIPCION']=='N'?'Por invitación':($t[0]['TIPOSOLICITUD']=='R'?'Suscripción directa':null);
            ?>
          </td>
        </tr>
        <tr id='filaMODERADA'>
          <td>Moderada</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<select id="MODERADA" name="MODERADA">'.
                     '<option value="N"'.((@$_REQUEST['MODERADA']?@$_REQUEST['MODERADA']:@$t[0]['MODERADA'])=='N'?' selected':null).'>No</option>'.
                     '<option value="S"'.((@$_REQUEST['MODERADA']?@$_REQUEST['MODERADA']:@$t[0]['MODERADA'])=='S'?' selected':null).'>Sí</option>'.
                    '</select>';
              else
                echo $t[0]['MODERADA']=='N'?'No':($t[0]['MODERADA']=='R'?'Sí':null);
            ?>
          </td>
        </tr>
        <tr id='filaADMINISTRADOR'>
          <td>Administrador</td>
          <td>
          <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="ADMINISTRADOR" name="ADMINISTRADOR" value="'.
                     (@$_REQUEST['ADMINISTRADOR']?@$_REQUEST['ADMINISTRADOR']:@$t[0]['ADMINISTRADOR']).
                     '" maxlength="4000" size="150" readonly>';
              else
                echo $t[0]['ADMINISTRADOR'];
            ?>
          </td>
        </tr>
        <tr id='filaNOMBRE_ADM'>
          <td>Nombre de administrador</td>
          <td><?php  
                if($modo=='N' || $modo=='E')
                echo '<input id="NOMBRE_ADM" name="NOMBRE_ADM" value="'.
                     (@$_REQUEST['NOMBRE_ADM']?@$_REQUEST['NOMBRE_ADM']:@$t[0]['NOMBRE_ADM']).
                     '" maxlength="4000" size="150" readonly>';
              else
                echo $t[0]['NOMBRE_ADM']; ?>
        </td>
        </tr>
        <tr id='filaAP1_ADM'>
          <td>Primer apellido del administrador</td>
          <td><?php
                if($modo=='N' || $modo=='E')
                echo '<input id="AP1_ADM" name="AP1_ADM" value="'.
                     (@$_REQUEST['AP1_ADM']?@$_REQUEST['AP1_ADM']:@$t[0]['AP1_ADM']).
                     '" maxlength="4000" size="150" readonly>';
              else
                echo $t[0]['AP1_ADM']; ?>
        </td>
        </tr>
        <tr id='filaAP2_ADM'>
          <td>Segundo apellido del administrador</td>
          <td><?php
                if($modo=='N' || $modo=='E')
                echo '<input id="AP2_ADM" name="AP2_ADM" value="'.
                     (@$_REQUEST['AP2_ADM']?@$_REQUEST['AP2_ADM']:@$t[0]['AP2_ADM']).
                     '" maxlength="4000" size="150" readonly>';
              else
                echo $t[0]['AP2_ADM']; ?>
        </td>
        </tr>
        <tr id='filaPERFIL'>
          <td>Perfil del administrador</td>
          <td><?php
                if($modo=='N' || $modo=='E')
                echo '<input id="PERFIL" name="PERFIL" value="'.
                     (@$_REQUEST['PERFIL']?@$_REQUEST['PERFIL']:@$t[0]['PERFIL']).
                     '" maxlength="4000" size="150" readonly>';
                else
                echo $t[0]['PERFIL']; ?></td>
        </tr>
        <tr id='filaTIPOPERFIL'>
          <td>Unidad del administrador</td>
          <td><?php
                if($modo=='N' || $modo=='E')
                echo '<input id="TIPOPERFIL" name="TIPOPERFIL" value="'.
                     (@$_REQUEST['TIPOPERFIL']?@$_REQUEST['TIPOPERFIL']:@$t[0]['TIPOPERFIL']).
                     '" maxlength="4000" size="150" readonly>';
                else
                echo $t[0]['TIPOPERFIL']; ?></td>
        </tr>
        <tr id='filaCARGO'>
          <td>Cargo del administrador (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="CARGO" name="CARGO" value="'.
                     (@$_REQUEST['CARGO']?@$_REQUEST['CARGO']:@$t[0]['CARGO']).
                     '" maxlength="4000" size="150">';
              else
                echo $t[0]['CARGO'];
            ?>
          </td>
        </tr>
        <tr id='filaCORREO'>
          <td>Correo electrónico del administrador (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="CORREO" name="CORREO" value="'.
                     (@$_REQUEST['CORREO']?@$_REQUEST['CORREO']:@$t[0]['CORREO']).
                     '" maxlength="4000" size="150">';
              else
                echo $t[0]['CORREO'];
            ?>
          </td>
        </tr>
        <tr id='filaTELEFONO'>
          <td>Teléfono del administrador (*)</td>
          <td>
            <?php
              if($modo=='N' || $modo=='E')
                echo '<input id="TELEFONO" name="TELEFONO" value="'.
                     (@$_REQUEST['TELEFONO']?@$_REQUEST['TELEFONO']:@$t[0]['TELEFONO']).
                     '" maxlength="65" size="65">';
              else
                echo $t[0]['TELEFONO'];
            ?>
          </td>
        </tr>
        <?php if(isset($t[0]['FECHASOLICITUD']) && $t[0]['FECHASOLICITUD']) { ?>
        <tr id='filaFECHASOLICITUD'>
          <td>Fecha de solicitud</td>
          <td><?php  echo $t[0]['FECHASOLICITUD']; ?></td>
        </tr>
        <?php }
              if(isset($t[0]['FECHAACEPTACION']) && $t[0]['FECHAACEPTACION']) { ?>
        <tr id='filaFECHAACEPTACION'>
          <td>Fecha de aceptación</td>
          <td><?php  echo $t[0]['FECHAACEPTACION']; ?></td>
        </tr>
        <?php }
              if(isset($t[0]['FECHARECHAZO']) && $t[0]['FECHARECHAZO']) { ?>
        <tr id='filaFECHARECHAZO'>
          <td>Fecha de rechazo</td>
          <td><?php  echo $t[0]['FECHARECHAZO']; ?></td>
        </tr>
        <tr id='filaMOTIVORECHAZO'>
          <td>Motivo de rechazo</td>
          <td><?php  echo $t[0]['MOTIVORECHAZO']; ?></td>
        </tr>
        <?php }
              if(isset($t[0]['FECHACANCELACION']) && $t[0]['FECHACANCELACION']) { ?>
        <tr id='filaFECHACANCELACION'>
          <td>Fecha de cancelación</td>
          <td><?php  echo $t[0]['FECHACANCELACION']; ?></td>
        </tr>
        <?php }
              if(isset($t[0]['FECHACREACION']) && $t[0]['FECHACREACION']) { ?>
        <tr id='filaFECHACREACION'>
          <td>Fecha de creación</td>
          <td><?php  echo $t[0]['FECHACREACION']; ?></td>
        </tr>
        <?php }
              if(isset($t[0]['FECHASOLICITUDBAJA']) && $t[0]['FECHASOLICITUDBAJA']) { ?>
       <tr id='filaFECHASOLICITUDBAJA'>
          <td>Fecha de solicitud de baja</td>
          <td><?php  echo $t[0]['FECHASOLICITUDBAJA']; ?></td>
        </tr>
        <?php }
              if(isset($t[0]['FECHABAJA']) && $t[0]['FECHABAJA']) { ?>
        <tr id='filaFECHABAJA'>
          <td>Fecha de baja</td>
          <td><?php  echo $t[0]['FECHABAJA']; ?></td>
        </tr>
        <?php } ?>
      </table>
      <?php
        if($modo=='N')
        {
          ?>
            <table class='tablaVisor'>
              <tr><td colspan='2'>
                Como administrador, se le informa de las obligaciones que contrae y las responsabilidades que debe aceptar:
              </td></tr>
              <tr><td></td><td>
                <ul>
                  <li>El administrador ser&aacute responsable de la moderaci&oacuten de mensajes y del procedimiento de suscripci&oacuten/desuscripci&oacuten de usuarios correspondiente a dicha lista.</li>
                  <li>En cada mensaje los suscriptores recibir&aacuten instrucciones en el pie de firma para poder desuscribirse de la lista cuando lo desee.</li>
                  <li>El administrador deber&aacute asegurarse de que el contenido de todos los mensajes que se distribuyen a trav&eacutes de la lista est&eacute exclusivamente relacionado con la finalidad de la misma.</li>
                  <li>Los suscriptores deber&aacuten ser informados en un primer mensaje de bienvenida acerca de la protecci&oacuten de sus datos personales, debiendo ajustarse el mensaje al modelo de cl&aacuteusula informativa publicada en la web de Protecci&oacuten de Datos Personales de la US (<a href='https://sic.us.es/sites/default/files/pd/privado/modelo_clausula_informativa.docx'>https://sic.us.es/sites/default/files/pd/privado/modelo_clausula_informativa.docx</a>, si le solicitara autenticarse utilice su Uvus y su clave)</li>
                </ul>
              </td></tr>
              <tr><td colspan='2'>
                En aquellas listas en las que sea necesario el consentimiento expl&iacutecito para la legitimaci&oacuten del tratamiento, la suscripci&oacuten ser&aacute por invitaci&oacuten y la aceptaci&oacuten de la misma ser&aacute el consentimiento para pertenecer a dicha lista.
                <BR />
                M&aacutes informaci&oacuten sobre tratamientos de datos personales en la Universidad  de Sevilla en la p&aacutegina web <a href:'https://sic.us.es/proteccion-de-datos-personales'>https://sic.us.es/proteccion-de-datos-personales</a>
              </td></tr>
              <tr>
                <td colspan='2'>
                  <br>
                  <b>Acepta las condiciones de uso y obligaciones del administrador:</b>
                  <br>
                  <input type='radio' name='frmlAcep' value='S'>S&iacute
                  <input type='radio' name='frmlAcep' value='N' checked>No<br />
                </td>
              </tr>
            </table>

            <div class='btnInferior'>
              <a href='<?php echo $Sevius->destino(true,true).
                                   (isset($_REQUEST['filtro'])?"&filtro={$_REQUEST['filtro']}":null).
                                   (isset($_REQUEST['ordenar'])?"&ordenar={$_REQUEST['ordenar']}":null); ?>'>Cancelar</a>
              <input type='submit' value='Crear'>
            </div>
          <?php
        }

        if($modo=='E')
        {
          ?>
            <div class='btnInferior'>
              <a href='<?php echo $Sevius->destino(true,true)."&lista=$lista&modo=A".
                                   (isset($_REQUEST['filtro'])?"&filtro={$_REQUEST['filtro']}":null).
                                   (isset($_REQUEST['ordenar'])?"&ordenar={$_REQUEST['ordenar']}":null); ?>'>Cancelar</a>
              <input type='submit' value='Grabar'>
            </div>
          <?php
        }

        if($modo=='U')
        {
          ?>
            <div class='btnInferior'>
              <a href='<?php echo $Sevius->destino(true,true).
                                   (isset($_REQUEST['filtro'])?"&filtro={$_REQUEST['filtro']}":null).
                                   (isset($_REQUEST['ordenar'])?"&ordenar={$_REQUEST['ordenar']}":null); ?>'>Volver</a>
            </div>
          <?php
        }

        if($modo=='A')
        {
          ?>
            <div class='btnInferior'>
              <a href='<?php echo $Sevius->destino(true,true)."&lista=$lista&modo=E".
                                   (isset($_REQUEST['filtro'])?"&filtro={$_REQUEST['filtro']}":null).
                                   (isset($_REQUEST['ordenar'])?"&ordenar={$_REQUEST['ordenar']}":null); ?>'>Editar</a>
              <a href='<?php echo $Sevius->destino(true,true).
                                   (isset($_REQUEST['filtro'])?"&filtro={$_REQUEST['filtro']}":null).
                                   (isset($_REQUEST['ordenar'])?"&ordenar={$_REQUEST['ordenar']}":null); ?>'>Volver</a>
            </div>
          <?php
        }
      ?>
    <?php
    if($modo=='N' || $modo=='E')
      $Sevius->formularioCerrar();
    return 'HTML';
  }

  function administradores($Sevius,$conexion)
  {
    require 'mantenimiento.inc.php';

    $m = new mantenimiento($Sevius,$conexion,'adminstrador',
                           mantenimiento::ASPECTO_MULTIREGISTRO,
                           'flc_responsable');//,null,array('opcion'=>'A'),0);
    $m->columna('USUARIO');
    $m->cabecera('Documento');
    $m->necesario(true);
    $m->editable(true);
    $m->tipo('TEXTO 15');
    $m->caja(15);

    $m->columna('NOMBRE');
    $m->cabecera('Apellidos, nombre');
    $m->necesario(false);
    $m->editable(false);
    $m->tipo('TEXTO 60');
    $m->caja(60);
    $m->entrada("(select apellido1||' '||apellido2||', '||nombre from sv4_usuario@sv4 where id=usuario)");

    $m->columna('CORREO');
    $m->cabecera('Dirección de correo electrónico');
    $m->necesario(true);
    $m->editable(true);
    $m->tipo('TEXTO 65');
    $m->caja(50);

    $m->ordenacion('NOMBRE');
    $m->saltoDefecto(20);
    $m->alta(true);
    $m->baja(array("select case when :USUARIO<>'{$Sevius->idusuario}'  or :ROW# like 'a%' then 'S' else 'N' end from dual"));
    $m->cambio(array("select case when :USUARIO<>'{$Sevius->idusuario}'  or :ROW# like 'a%' then 'S' else 'N' end from dual"));
    $m->listartodos(true);

    echo $m->ejecutar();
    $m->mostrar();
  }

  function admindelegados($Sevius,$conexion)
  {
    require 'mantenimiento.inc.php';

    $m = new mantenimiento($Sevius,$conexion,'admindelegados',
                           mantenimiento::ASPECTO_MULTIREGISTRO,
                           'flc_dpd');//,null,array('opcion'=>'A'),0);
    $m->columna('USUARIO');
    $m->cabecera('Documento');
    $m->necesario(true);
    $m->editable(true);
    $m->tipo('TEXTO 15');
    $m->caja(15);

    $m->columna('NOMBRE');
    $m->cabecera('Apellidos, nombre');
    $m->necesario(false);
    $m->editable(false);
    $m->tipo('TEXTO 60');
    $m->caja(60);
    $m->entrada("(select apellido1||' '||apellido2||', '||nombre from sv4_usuario@sv4 where id=usuario)");

    $m->columna('CORREO');
    $m->cabecera('Dirección de correo electrónico');
    $m->necesario(true);
    $m->editable(true);
    $m->tipo('TEXTO 65');
    $m->caja(50);

    $m->ordenacion('NOMBRE');
    $m->saltoDefecto(20);
    $m->alta(true);
    $m->baja(true);
    $m->cambio(true);
    $m->listartodos(true);

    echo $m->ejecutar();
    $m->mostrar();
  }

  function exportar($Sevius,$conexion,$tipousuario,$sql)
  {
    $conexion->parse($sql);
    $conexion->value('id',$Sevius->idusuario);
    $conexion->execute();
    $conexion->fetch();
    if($conexion->rows>0)
    {
      require('PHPExcel/PHPExcel.php');
      require('PHPExcel/PHPExcel/IOFactory.php');
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->getProperties()->setCreator("Sevius")
    							 ->setTitle("Listas de correo")
    							 ->setDescription("")
    							 ->setKeywords("")
    							 ->setCategory("");
      $objPHPExcel->setActiveSheetIndex(0);

      $columnas = array('A'=>'Id',
                        'B'=>'Nombre',
                        'C'=>'Tipo solicitud',
                        'D'=>'Descripcion',
                        'E'=>'Destinatarios',
                        'F'=>'Finalidad',
                        'G'=>'Caducidad',
                        'H'=>'Visibilidad',
                        'I'=>'Por suscripcion',
                        'J'=>'Moderada',
                        'K'=>'Administrador',
                        'L'=>'Nombre_adm',
                        'M'=>'Ap1_adm',
                        'N'=>'Ap2_adm',
                        'O'=>'Perfil',
                        'P'=>'Tipo perfil',
                        'Q'=>'Correo',
                        'R'=>'Telefono',
                        'S'=>'Fecha solicitud',
                        'T'=>'Fecha aceptacion',
                        'U'=>'Fecha rechazo',
                        'V'=>'Motivo rechazo',
                        'W'=>'Fecha cancelacion',
                        'X'=>'Fecha creacion',
                        'Y'=>'Fecha solicitud baja',
                        'Z'=>'Fecha baja',
                        'AA'=>'Cargo');
      foreach($columnas as $c=>$v)
      {
        $objPHPExcel->getActiveSheet()->getStyle($c.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle($c.'1')->getFill()->getStartColor()->setARGB('00000099');
        $objPHPExcel->getActiveSheet()->getStyle($c.'1')->getFont()->getColor()->setARGB('00ffffff');
        $objPHPExcel->getActiveSheet()->setCellValue($c.'1',$v);
      }

      for($i=0;$i<$conexion->rows;$i++)
      {
    		if($i%2)
    		{
    		  foreach($columnas as $c=>$v)
    		  {
      		  $objPHPExcel->getActiveSheet()->getStyle($c.($i+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      		  $objPHPExcel->getActiveSheet()->getStyle($c.($i+2))->getFill()->getStartColor()->setARGB('00e0e0e0');
          }
    		}

        foreach($columnas as $c=>$v)
      		$objPHPExcel->getActiveSheet()->setCellValue($c.($i+2),$conexion->data[$i][strtoupper(str_replace(' ',null,$v))]);
      }

      foreach($columnas as $c=>$v)
        $objPHPExcel->getActiveSheet()->getColumnDimension($c)->setAutoSize(true);

      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
      $objWriter->save('php://output');
      return "ADJUNTO:listas.xls";
    }
  }

  function aceptar($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    if($t[0]['FECHAACEPTACION']) return;

    if($tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='R' ||
       $tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='C' ||
       $tipousuario!='D' && $t[0]['TIPOSOLICITUD']=='N'  ) return;

    // update en tabla para poner fecha de aceptación
    $conexion->parse("update flc_lista
                      set    fechaaceptacion = sysdate
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha aceptado la lista $documento");
      $conexion->commit();
    }

    // envío de correos
    $wasunto = "Lista de distribución {$t[0]['NOMBRE']} autorizada por la Delegación de Protección de Datos";
    $wcuerpo = "Una solicitud de creación lista ha sido autorizada por la Delegación de Protección de Datos.\n".
               "Tendrá que proceder a la creación y a continuación acceder a la aplicación en SEVIUS para su verificación.";
    $wfrom = "sos@us.es";//noreply@us.es
    $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom);
    $conexion->parse("select correo from flc_responsable");
    $conexion->novalue();
    $conexion->execute();
    $conexion->fetch();
    for($i=0;$i<$conexion->rows;$i++) $correo->destinatario($conexion->data[$i]['CORREO']);
    $correo->enviar();
    $Sevius->conexion->commit();
  }
  
  function rechazar($Sevius,$conexion,$tipousuario,$documento)
  {
    $Sevius->formularioAbrir('formulario',true,true);
    $Sevius->formularioInput('filtro',$_REQUEST['filtro']);
    $Sevius->formularioInput('ordenar',$_REQUEST['ordenar']);
    $Sevius->formularioInput('accion','rechazaraceptado');
    $Sevius->formularioInput('documento',$documento);
    ?>
      <h3>Para rechazar la solicitud nº <?php echo $documento; ?> es necesario indicar un motivo</h3>
    <?php
    $Sevius->formularioInput('motivo',null,'area',array(80,5));
    ?>
      <div class='btnInferior'>
        <a id='cancelar'>Cancelar</a>
        <a id='aceptar'>Aceptar</a>
      </div>
    <?php
    $Sevius->formularioCerrar();
    $Sevius->jsEvent('aceptar','click','document.formulario.submit();');
    $Sevius->jsEvent('cancelar','click',"document.formulario.accion.value=''; document.formulario.submit();");
  }

  function rechazaraceptado($Sevius,$conexion,$tipousuario,$documento,$motivo)
  {
    if(!$motivo)
    {
      $Sevius->salidaError('Debe especificar un motivo');
      rechazar($Sevius,$conexion,$tipousuario,$documento);
      return true;
    }

    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    if($t[0]['FECHAACEPTACION']) return;

    if($tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='R' ||
       $tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='C' ||
       $tipousuario!='D' && $t[0]['TIPOSOLICITUD']=='N'  ) return;

    // update en tabla para poner fecha de rechazo
    $conexion->parse("update flc_lista
                      set    fecharechazo = sysdate,
                             motivorechazo = :motivo
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->value('motivo',$motivo);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha rechazado la lista $documento");
      $conexion->commit();
    }

    // envío de correos
    $wasunto = "Lista de distribución {$t[0]['NOMBRE']} NO autorizada por la Delegación de Protección de Datos";
    $wcuerpo = "Estimado colaborador, su solicitud de creación de una nueva lista NO ha sido autorizada por la Delegación de Protección de Datos.\n".
               "\n".
               "Motivo de rechazo: $motivo\n".
               "\n".
               "Si desea subsanar o solicitar otra lista, deberá cumplimentar una nueva solicitud en: https://sevius4.us.es/index.php?solicitudlistas\n".
               "\n".
               "Más información sobre listas en:\n".
               "https://sic.us.es/servicios/correo-electronico/listas-de-distribucion\n".
               "\n".
               "Si tiene alguna duda, puede consultar con el Servicio de Atención a Usuarios (SOS):\n".
               "\n".
               "http://sic.us.es/como-podemos-ayudarte/atencion-usuarios";
    $wfrom = "sos@us.es";
    $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom); // remitente
    $correo->destinatario($t[0]['CORREO']);
    $correo->enviar();
    $Sevius->conexion->commit();
  }

  function visibilidad($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    if($t[0]['FECHARECHAZO']) return;
    if($t[0]['FECHACANCELACION']) return;

    if($tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='R' ||
       $tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='C' ||
       $tipousuario!='D' && $t[0]['TIPOSOLICITUD']=='N'  ) return;

    // update en tabla para poner fecha de aceptación
    $conexion->parse("update flc_lista
                      set    visibilidad = case when visibilidad = 'S' then 'N' else 'S' end
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha cambiado la visibilidad de la lista $documento");
      $conexion->commit();
    }

        $Sevius->conexion->commit();
  }

  function control($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    if($t[0]['FECHARECHAZO']) return;
    if($t[0]['FECHACANCELACION']) return;

    if($tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='R' ||
       $tipousuario!='A' && $t[0]['TIPOSOLICITUD']=='C' ||
       $tipousuario!='D' && $t[0]['TIPOSOLICITUD']=='N'  ) return;

    // update en tabla para poner fecha de aceptación
    $conexion->parse("update flc_lista
                      set    porsuscripcion = case when porsuscripcion = 'S' then 'N' else 'S' end
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha cambiado el control de suscripciones de la lista $documento");
      $conexion->commit();
    }

        $Sevius->conexion->commit();
  }

  function hecho($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    if($t[0]['FECHARECHAZO']) return;
    if($t[0]['FECHACANCELACION']) return;

    if($tipousuario!='A' || $t[0]['TIPOSOLICITUD']!='N') return;

    // update en tabla para poner fecha de creacion
    
    $conexion->parse("update flc_lista
                      set fechacreacion = sysdate
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha hecho la lista $documento");
      $conexion->commit();
    }
    // envío de correos
    $wasunto = "Lista de distribución {$t[0]['NOMBRE']} creada por el SIC";
    $wcuerpo = "Su solicitud de creación de lista de correo ha sido atendida por el SIC.\n".
               "Más información sobre listas en\n".
               "https://sic.us.es/servicios/correo-electronico/listas-de-distribucion\n".
               "\n".
               "Si tiene alguna duda, puede consultar con el Servicio de Atención a Usuarios (SOS):\n".
               "http://sic.us.es/como-podemos-ayudarte/atencion-usuarios";
    $wfrom = "sos@us.es";// noreply@us.es
    $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom); // remitente
    $correo->destinatario($t[0]['CORREO']);
    $correo->enviar();
    $Sevius->conexion->commit();
  }
  
  function aceptar_CR($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    if($t[0]['FECHARECHAZO']) return;
    if($t[0]['FECHACANCELACION']) return;

    if($tipousuario!='A' || $t[0]['TIPOSOLICITUD']!='R') return;

    // update en tabla para poner fecha de aceptación y creacion
    
    $conexion->parse("update flc_lista
                      set fechaaceptacion= sysdate, fechacreacion= sysdate 
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha creado la lista $documento");
      $conexion->commit();
    }
    // envío de correos
    $wasunto = "Lista de distribución {$t[0]['NOMBRE']} creada por el SIC";
    $wcuerpo = "Su solicitud de creación de lista de correo ha sido atendida por el SIC.\n".
               "Más información sobre listas en:\n".
               "https://sic.us.es/servicios/correo-electronico/listas-de-distribucion\n".
               "\n".
               "Si tiene alguna duda, puede consultar con el Servicio de Atención a Usuarios (SOS):\n".
               "http://sic.us.es/como-podemos-ayudarte/atencion-usuarios";
    $wfrom = "sos@us.es";
    $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom); // remitente
    $correo->destinatario($t[0]['CORREO']);
    $correo->enviar();
    $Sevius->conexion->commit();
  }

  function cancelar($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    if($tipousuario=='D') return;
    if($tipousuario=='U')
    {
      $conexion->parse("select *
                        from   flc_lista
                        where  id = :id
                        and    administrador = :adm");
      $conexion->value('id',$documento);
      $conexion->value('adm',$Sevius->idusuario);
      $conexion->execute();
      $conexion->fetch();
      if(!$conexion->rows) return;
    }
    

    // update en tabla para poner fecha de cancelacion
    $conexion->parse("update flc_lista
                      set fechacancelacion = sysdate
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha cancelado la lista $documento");
      $conexion->commit();
    }

      $Sevius->conexion->commit();
  }

  function solicitabaja($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }

    //if($t[0]['FECHARECHAZO']) return;
    //if($t[0]['FECHACANCELACION']) return;

    if($tipousuario=='D') return;
    if($tipousuario=='U')
    {
      $conexion->parse("select *
                        from   flc_lista
                        where  id = :id
                        and    administrador = :adm");
      $conexion->value('id',$documento);
      $conexion->value('adm',$Sevius->idusuario);
      $conexion->execute();
      $conexion->fetch();
      if(!$conexion->rows) return;
    }
    

    // update en tabla para poner fecha de solicitudbaja
    $conexion->parse("update flc_lista
                      set fechasolicitudbaja = sysdate, tiposolicitud='C'
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha solicitado la baja de la lista $documento");
      $conexion->commit();
    }
    // envío de correos
    $wasunto = "Solicitud baja de lista con número $documento";
    $wcuerpo = "Se ha solicitado la baja de la lista con número $documento....";
    $wfrom = "sos@us.es";
    $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom); // remitente
    $conexion->parse("select correo from flc_responsable");
    $conexion->novalue();
    $conexion->execute();
    $conexion->fetch();
    for($i=0;$i<$conexion->rows;$i++) $correo->destinatario($conexion->data[$i]['CORREO']);
    $correo->enviar();
    $Sevius->conexion->commit();
  }

  function borrar($Sevius,$conexion,$tipousuario,$documento)
  {
    $conexion->parse("select * from flc_lista where id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    list($n,$t) = $conexion->fetch();

    if($n==0)
    {
      $Sevius->salidaError("No encontrada la lista $documento");
      return;
    }
        
    if($tipousuario=='D') return;
    if($tipousuario=='U')
    {
      $conexion->parse("select *
                        from   flc_lista
                        where  id = :id
                        and    administrador = :adm");
      $conexion->value('id',$documento);
      $conexion->value('adm',$Sevius->idusuario);
      $conexion->execute();
      $conexion->fetch();
      if(!$conexion->rows) return;
    }
    

    // update en tabla para poner fecha de baja
    $conexion->parse("update flc_lista
                      set fechabaja = sysdate
                      where  id = :id");
    $conexion->value('id',$documento);
    $conexion->execute();
    if($conexion->error())
    {
      $Sevius->salidaError($conexion->error());
      $conexion->rollback();
    }
    else if($conexion->numrows()==0)
      $Sevius->salidaAviso("No se ha podido actualizar la lista");
    else
    {
      $Sevius->salidaAviso("Se ha realizado la baja de la lista $documento");
      $conexion->commit();
    }
    // envío de correos
    $wasunto = "Baja de lista con número $documento";
    $wcuerpo = "Se ha realizado la baja de la lista con número $documento....";
    $wfrom = "sos@us.es";
    $correo = new correo($Sevius->conexion,
                         $wasunto, 
                         $wcuerpo,  
                         $wfrom); // remitente
    $conexion->parse("select correo from flc_responsable");
    $conexion->novalue();
    $conexion->execute();
    $conexion->fetch();
    for($i=0;$i<$conexion->rows;$i++) $correo->destinatario($conexion->data[$i]['CORREO']);
    $correo->enviar();
    $Sevius->conexion->commit();
  }
?>
