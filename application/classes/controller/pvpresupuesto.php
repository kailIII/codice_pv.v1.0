<?php

defined('SYSPATH') or die('Acceso denegado');

class Controller_Pvpresupuesto extends Controller_DefaultTemplate {

    protected $user;
    protected $menus;

    public function before() {
        $auth = Auth::instance();
        //si el usuario esta logeado entocnes mostramos el menu
        if ($auth->logged_in()) {
            //menu top de acuerdo al nivel
            $session = Session::instance();
            $this->user = $session->get('auth_user');
            $oNivel = New Model_niveles();
            $this->menus = $oNivel->menus($this->user->nivel);
            parent::before();
            $this->template->title = 'Presupuesto';
        } else {
            $url = substr($_SERVER['REQUEST_URI'], 1);
            $this->request->redirect('/login?url=' . $url);
        }
    }

    public function after() {
        $this->template->menutop = View::factory('templates/menutop')->bind('menus', $this->menus)->set('controller', 'pvpresupuesto');
        $oSM = New Model_menus();
        $submenus = $oSM->submenus('pvpresupuesto');
        $docs = FALSE;
        if ($this->user->nivel == 4) {
            $docs = TRUE;
        }
        $this->template->submenu = View::factory('templates/submenu')->bind('smenus', $submenus)->bind('doc', $docs)->set('titulo', 'PRESUPUESTO');
        parent::after();
    }

    public function action_index($id = '') {
        $oAut = new Model_Pvliquidaciones();
        $aut = $oAut->pptautorizados($this->user->id_entidad);
        $this->template->styles = array('media/css/tablas.css' => 'all');
        $this->template->scripts = array('media/js/jquery.tablesorter.min.js');
        $this->template->content = View::factory('pvpresupuesto/index')
                                        ->bind('autorizados', $aut)
                                        ;
        
    }
    
public function action_ejecucion($id = ''){
    $oPpt = new Model_Pvprogramaticas();
    $ppt = $oPpt->ejecucionppt($this->user->id_entidad);
    $this->template->styles = array('media/css/tablas.css' => 'all');
    $this->template->scripts = array('media/js/jquery.tablesorter.min.js');
    $this->template->content = View::factory('pvpresupuesto/ejecucion')
                                    ->bind('presupuesto', $ppt)
                                    ;
}

public function action_saldopresupuesto($id = ''){
    $oPpt = new Model_Pvprogramaticas();
    $ppt = $oPpt->saldopresupuesto($id);
    $det = $oPpt->detallesaldopresupuesto($id);
    foreach ($det as $d)
        $detalle = $d;
    $this->template->styles = array('media/css/tablas.css' => 'all');
    $this->template->scripts = array('media/js/jquery.tablesorter.min.js');
    $this->template->content = View::factory('pvpresupuesto/saldopresupuesto')
                                    ->bind('presupuesto', $ppt)
                                    ->bind('detalle', $detalle)
                                    ->bind('id_programatica', $id)
                                    ;
}

public function action_addsaldoppt($id = ''){
    $mensajes = array();
    $programatica = ORM::factory('pvprogramaticas')->where('id', '=', $id)->and_where('estado', '=', 1)->find();
    if ($programatica->loaded()) {
        if (isset($_POST['submit'])) {
            $ejecucion = ORM::factory('pvejecuciones');
            $ejecucion->inicial = $_POST['inicial'];
            $ejecucion->vigente = $_POST['vigente'];
            $ejecucion->preventivo = $_POST['preventivo'];
            $ejecucion->comprometido = $_POST['comprometido'];
            $ejecucion->devengado = $_POST['devengado'];
            $ejecucion->saldo_devengado = $_POST['saldoDevengado'];
            $ejecucion->pagado = $_POST['pagado'];
            $ejecucion->saldo_pagar = $_POST['saldoPagar'];
            $ejecucion->estado = 1;
            $ejecucion->gestion = $_POST['gestion'];
            $ejecucion->id_programatica = $id;
            $ejecucion->id_partida = $_POST['partidas'];
            $ejecucion->save();
            $this->request->redirect('pvpresupuesto/saldopresupuesto/'.$id);
        }
        $oPart = new Model_Pvpartidas();
        $partida = $oPart->partidas_no_asignadas($id);
        foreach($partida as $p)
            $partidas[$p['id']] = $p['codigo'].' &nbsp;&nbsp;-&nbsp;&nbsp; '.$p['partida'];
        $oPpt = new Model_Pvprogramaticas();
        $ppt = $oPpt->saldopresupuesto($id);
        $det = $oPpt->detallesaldopresupuesto($id);
        foreach ($det as $d)
            $detalle = $d;
        $this->template->styles = array('media/css/tablas.css' => 'all');
        $this->template->scripts = array('media/js/jquery.tablesorter.min.js');
        $this->template->content = View::factory('pvpresupuesto/addsaldoppt')
                                        ->bind('presupuesto', $ppt)
                                        ->bind('detalle', $detalle)
                                        ->bind('id_programatica', $id)
                                        ->bind('partidas', $partidas);
                                        ;
    }
    else {
            $this->template->content = 'La categoria programatica no fue encontrada.';
    }
}


public function action_addejecucionppt(){
    $mensajes = array();
    $oficinas = ORM::factory('oficinas')->where('id_entidad','=',$this->user->id_entidad)->find_all();
    if ($oficinas) {
        if (isset($_POST['submit'])) {
            $programatica = ORM::factory('pvprogramaticas');
            $programatica->id_oficina = $_POST['oficina'];
            $programatica->id_fuente = $_POST['fuente'];
            $programatica->id_organismo = $_POST['organismo'];
            $programatica->id_programa = $_POST['programa'];
            $programatica->id_proyecto = $_POST['proyecto'];
            $programatica->id_actividadppt = $_POST['actividad'];
            $programatica->id_da = $_POST['da'];
            $programatica->id_ue = $_POST['ue'];
            $programatica->estado = 1;
            $programatica->gestion = $_POST['gestion'];
            $programatica->save();
            $this->request->redirect('pvpresupuesto/ejecucion/');
        }
        //Entidad
        $ent = ORM::factory('entidades')->where('id','=',$this->user->id_entidad)->find();
        $entidad = $ent->entidad;
        //Oficinas
        $ofi = ORM::factory('oficinas')->where('id_entidad','=',$this->user->id_entidad)->find_all();
        foreach($ofi as $o)
            $oficina[$o->id] = $o->oficina;
        //Direccion Administrativa
        $dadmin = ORM::factory('oficinas')->where('ppt_da','=',1)->and_where('id_entidad','=',$this->user->id_entidad)->find_all();
        foreach($dadmin as $d)
            $da[$d->id] = $d->ppt_cod_da.' &nbsp;&nbsp;-&nbsp;&nbsp; '.$d->oficina;
        
        //Unidad Ejecutora
        $uejec = ORM::factory('oficinas')->where('ppt_unid_ejecutora','=',1)->and_where('id_entidad','=',$this->user->id_entidad)->find_all();
        foreach($uejec as $d)
            $ue[$d->id] = $d->ppt_cod_ue.' &nbsp;&nbsp;-&nbsp;&nbsp; '.$d->oficina;
        
        //Programa
        $programa = ORM::factory('pvprogramas')->where('estado','=',1)->find_all();
        $prog[0] = '';
        foreach($programa as $p)
            $prog[$p->id] = $p->codigo.' &nbsp;&nbsp;-&nbsp;&nbsp; '.$p->programa;
            
        //Fuente
        $fuente = ORM::factory('pvfuentes')->where('estado','=',1)->find_all();
        foreach($fuente as $f)
            $fte[$f->id] = $f->codigo.' &nbsp;&nbsp;-&nbsp;&nbsp; '.$f->sigla;
        
        //Organismo
        $organismo = ORM::factory('pvorganismos')->where('estado','=',1)->find_all();
        foreach($organismo as $o)
            $org[$o->id] = $o->codigo.' &nbsp;&nbsp;-&nbsp;&nbsp; '.$o->sigla;
        $this->template->styles = array('media/css/tablas.css' => 'all','media/css/jquery-ui-1.8.16.custom.css'=>'screen');
        $this->template->scripts = array('media/js/jquery-ui-1.8.16.custom.min.js','media/js/jquery.tablesorter.min.js');
        $this->template->content = View::factory('pvpresupuesto/addejecucionppt')
                                        ->bind('entidad', $entidad)
                                        ->bind('oficina', $oficina)
                                        ->bind('da', $da)
                                        ->bind('ue', $ue)
                                        ->bind('programa', $prog)
                                        ->bind('fuente', $fte)
                                        ->bind('organismo', $org)
                                        ;
    }
    else {
            $this->template->content = 'La categoria programatica no fue encontrada.';
    }
}

public function action_editsaldoppt($id = ''){///saldo partidas
    $mensajes = array();
    //$programatica = ORM::factory('pvprogramaticas')->where('id', '=', $id)->and_where('estado', '=', 1)->find();
    $ejecucion = ORM::factory('pvejecuciones')->where('id','=',$id)->find();
    if ($ejecucion->loaded()) {
        if (isset($_POST['submit'])) {
            $ejecucion = ORM::factory('pvejecuciones');
            //$ejecucion->inicial = $_POST['inicial'];
            $ejecucion->vigente = $_POST['vigente'];
            //$ejecucion->preventivo = $_POST['preventivo'];
            //$ejecucion->comprometido = $_POST['comprometido'];
            //$ejecucion->devengado = $_POST['devengado'];
            $ejecucion->saldo_devengado = $_POST['saldoDevengado'];
            //$ejecucion->pagado = $_POST['pagado'];
            $ejecucion->saldo_pagar = $_POST['saldoPagar'];
            //$ejecucion->estado = 1;
            //$ejecucion->gestion = $_POST['gestion'];
            //$ejecucion->id_programatica = $id;
            //$ejecucion->id_partida = $_POST['partidas'];
            //$ejecucion->save();
            $mensajes['Modificado'] = 'El saldo Presupuestario fue Modificado.';
        }
        $oPpt = new Model_Pvprogramaticas();
        $ppt = $oPpt->saldopresupuesto($id);
        $det = $oPpt->detallesaldopresupuesto($ejecucion->id_programatica);
        foreach ($det as $d)
            $detalle = $d;
        $this->template->styles = array('media/css/tablas.css' => 'all');
        $this->template->scripts = array('media/js/jquery.tablesorter.min.js');
        $this->template->content = View::factory('pvpresupuesto/editsaldoppt')
                                        ->bind('presupuesto', $ppt)
                                        ->bind('detalle', $detalle)
                                        ->bind('id_programatica', $id)
                                        ->bind('ejecucion', $ejecucion)
                                        ->bind('mensajes', $mensajes)
                                        ;
    }
    else {
            $this->template->content = 'La categoria programatica no fue encontrada.';
    }
}

public function action_editarfucov($id = '') {
        $fucov = ORM::factory('pvfucovs')->where('id','=',$id)->find();
        if ($fucov->loaded()) {
            if ($fucov->etapa_proceso == 2){
            $fucov->id_programatica = $_POST['fuente'];
            $fucov->save();
            $this->request->redirect('documento/detalle/'.$fucov->id_memo);
            }
            else
            $this->template->content = '<b>EL DOCUMENTO YA FUE AUTORIZADO Y NO SE PUEDE MODIFICAR.</b><div class="info" style="text-align:center;margin-top: 50px; width:800px">
                                        <p><span style="float: left; margin-right: .3em;" class=""></span>    
                                        &larr;<a onclick="javascript:history.back(); return false;" href="#" style="font-weight: bold; text-decoration: underline;  " > Regresar<a/></p>    
</div>';    
        }
        else
            $this->template->content = 'El FUCOV no existe';
    }

public function action_autorizarfucov($id = '') {
        $pvfucov = ORM::factory('pvfucovs')->where('id','=',$id)->find();
        if ($pvfucov->loaded()) {
            if($pvfucov->id_tipoviaje == 1 || $pvfucov->id_tipoviaje == 2){
                $partidas = array("22110" => $pvfucov->total_pasaje, "22210" => $pvfucov->total_viatico);
            }
            else{
                $partidas = array("22120" => $pvfucov->total_pasaje, "22220" => $pvfucov->total_viatico, "26910" => $pvfucov->gasto_representacion);
            }
            foreach($partidas as $key=>$value){
                $partida = ORM::factory('pvpartidas')->where('codigo','=',$key)->find();
                $ejecucion = ORM::factory('pvejecuciones')->where('id_partida','=',$partida->id)->and_where('id_programatica','=',$pvfucov->id_programatica)->find();
                $liquidacion = ORM::factory('pvliquidaciones');
                $liquidacion->fecha_creacion = date("Y-m-d H:i:s");
                $liquidacion->importe_certificado = $value;
                $liquidacion->cs_preventivo = $ejecucion->preventivo;
                $liquidacion->cs_comprometido = $ejecucion->comprometido;
                $liquidacion->cs_devengado = $ejecucion->devengado;
                $liquidacion->cs_saldo_devengado = $ejecucion->saldo_devengado;
                $liquidacion->cs_pagado = $ejecucion->pagado;
                $liquidacion->cs_saldo_pagar = $ejecucion->saldo_pagar;
                $liquidacion->estado = 1;
                $liquidacion->cod_partida= $partida->codigo;
                $liquidacion->partida = $partida->partida;
                $liquidacion->id_partida = $partida->id;
                $liquidacion->id_fucov = $pvfucov->id;
                $liquidacion->save();
                $ejecucion->preventivo = $ejecucion->preventivo + $value;
                $ejecucion->saldo_devengado = $ejecucion->saldo_devengado - $value;
                $ejecucion->save();
            }
            $pvfucov->etapa_proceso = 3;
            $pvfucov->save();
            $this->request->redirect('documento/detalle/'.$pvfucov->id_memo);
    }
    else
        $this->template->content = 'El documento no existe';
}

/*
public function action_detallecertificado($id){
    //id = id_memo
    $memo = ORM::factory('documentos')->where('id', '=', $id)->find();
        if ($memo->loaded()) {
        $fucov = ORM::factory('pyvfucov')->where('id_memo', '=', $id)->find();
        $documento = ORM::factory('documentos')->where('id','=', $fucov->id_documento)->find();
            if($fucov->loaded()){
            //$pasaje = ORM::factory('pyvpasajes')->where('id_fucov','=',$fucov->id);
            $oPsj = new Model_Pyvpasajes();
            $pas = $oPsj->pasaje_asignado($fucov->id);
            foreach ($pas as $p) $pasaje = $p;
            $liq = ORM::factory('pyvliquidacion')->where('id_fucov','=',$fucov->id)->find();
            $oFte = New Model_pyvprogramatica();
            $fte = $oFte->listadetallefuentes($liq->id_programatica);
            foreach ($fte as $ft) $fuente = $ft;
    //$this->template->scripts = array('media/js/jquery-ui-1.8.16.custom.min.js','media/js/jquery.timeentry.js');
            $pyvmenu = View::factory('pyv/templates/menus')->bind('menu', $this->pmenu)->set('titulo', 'PRESUPUESTO');
                        $this->template->content = View::factory('pyv/presupuesto/detallecertificado')
                                ->bind('memo', $memo)
                                ->bind('d', $documento)
                                //->bind('tipo', $tipo)
                                //->bind('archivo', $archivo)
                                //->bind('errors', $errors)
                                //->bind('mensajes', $mensajes)
                                ->bind('f',$fucov)
                                ->bind('pasaje',$pasaje)
                                ->bind('fuente',$fuente)                                
                                ->bind('liq',$liq)
                                ->bind('menu',$menu)
                                ;
            }
            else
                $this->template->content = 'No hay FUCOV asignado';
        }
        else
        {
            $this->template->content = 'El Memor&aacute;ndum no existe';
        }
}

public function action_autorizappt($id = '') {
        $fucov = ORM::factory('pyvfucov')->where('id','=',$id)->find();
        $liquidacion = ORM::factory('pyvliquidacion')->where('id_fucov','=',$id)->find();        
        if ($liquidacion->loaded() && $fucov->loaded()) {
            if( ($fucov->etapa_proceso == 2  || $fucov->etapa_proceso == 3 ) ){
            $liquidacion->etapa_proceso = 1;
            $liquidacion->fecha_liquidacion = date('Y-m-d H:i:s');
            //$liquidacion->cod_cat_programatica = $_POST['fuente'];
            //ides: unidad_funcional,  direccion_administrativa, unidad_ejecutora, id_organismo, id_fuente, id_categoria_programatica            
            $fuente = $_POST['fuente'];
            $ides = explode("-", $fuente);
                $id_unidad_funcional = $ides[0];
                $id_dir_admin = $ides[1];
                $id_unidad_ejec = $ides[2];
                $id_organismo = $ides[3];
                $id_fuente = $ides[4];
                $id_cat_programatica = $ides[5];
            $liquidacion->id_unidad_funcional = $id_unidad_funcional;
            $liquidacion->id_da = $id_dir_admin;
            $liquidacion->id_ue = $id_unidad_ejec;
            $liquidacion->id_organismo = $id_organismo;
            $liquidacion->id_fuente = $id_fuente;
            $liquidacion->id_cat_prog = $id_cat_programatica;
            
            $liquidacion->save();
            
            $fucov->etapa_proceso = 3;
            $fucov->save();
            $this->request->redirect('pyv/detalle/'.$_POST['idMemo']);
            
        }
        else
            $this->template->content = 'El documento no se puede modificar';    
    }
    else
        $this->template->content = 'El documento no existe';
}
*/
}

?>
