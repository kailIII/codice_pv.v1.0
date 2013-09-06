<?php

defined('SYSPATH') or die('Acceso denegado');

class Controller_Pvpasajes extends Controller_DefaultTemplate {

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
            $this->template->title = 'Pasajes';
        } else {
            $url = substr($_SERVER['REQUEST_URI'], 1);
            $this->request->redirect('/login?url=' . $url);
        }
    }

    public function after() {
        $this->template->menutop = View::factory('templates/menutop')->bind('menus', $this->menus)->set('controller', 'pvpasajes');
        $oSM = New Model_menus();
        $submenus = $oSM->submenus('pvpasajes');
        $docs = FALSE;
        if ($this->user->nivel == 4) {
            $docs = TRUE;
        }
        $this->template->submenu = View::factory('templates/submenu')->bind('smenus', $submenus)->bind('doc', $docs)->set('titulo', 'PASAJES');
        parent::after();
    }

    public function action_index($id = '') {
        $oAut = new Model_Pvpasajes();
        $autorizados = $oAut->pasajesautorizados();//lista de solicitudes autorizadas
        $this->template->styles = array('media/css/tablas.css' => 'all');
        $this->template->scripts = array('media/js/jquery.tablesorter.min.js');
        $this->template->content = View::factory('pvpasajes/index')
                                        ->bind('autorizados', $autorizados)
                                        ;    
    }
    
    public function action_adicionarpasaje($id = '') {
        $fucov = ORM::factory('pvfucovs')->where('id','=',$id)->find();
        if ($fucov->loaded()) {
            $pasajes = ORM::factory('pvpasajes');
            $pasajes->id_fucov = $id;
            $pasajes->transporte = $_POST['transporte'];
            $pasajes->empresa = $_POST['empresa'];
            $pasajes->nro_boleto = $_POST['nro_boleto'];
            $fs = date('Y-m-d', strtotime(substr($_POST['fecha_ida'], 4, 10))) . ' ' . date('H:i:s', strtotime($_POST['hora_ida']));
            $fa = date('Y-m-d', strtotime(substr($_POST['fecha_llegada'], 4, 10))) . ' ' . date('H:i:s', strtotime($_POST['hora_llegada']));
            $pasajes->fecha_salida = $fs;
            $pasajes->fecha_arribo = $fa;
            $pasajes->costo = $_POST['costo'];
            $pasajes->origen = $_POST['origen'];
            $pasajes->destino = $_POST['destino'];
            $pasajes->save();
            $this->request->redirect('documento/detalle/'.$fucov->id_memo);
        }
        else
            $this->template->content = 'El FUCOV no existe';
    }
    
    public function action_eliminarpasaje($id = '') {
        $pasaje = ORM::factory('pvpasajes')->where('id','=',$id)->find();
        if ($pasaje->loaded()) {            
            $pvfucov = ORM::factory('pvfucovs')->where('id','=',$pasaje->id_fucov)->find();
            $pasaje->delete();
            $this->request->redirect('documento/detalle/'.$pvfucov->id_memo);
        }
        else
            $this->template->content = 'El FUCOV no existe';
    }
    
    public function action_editarfucov($id = '') {
        $fucov = ORM::factory('pvfucovs')->where('id','=',$id)->find();
        if ($fucov->loaded()) {
            if ($fucov->etapa_proceso <= 1){
                $fs = date('Y-m-d', strtotime(substr($_POST['fecha_salida'], 4, 10))) . ' ' . date('H:i:s', strtotime($_POST['hora_salida']));
            $fa = date('Y-m-d', strtotime(substr($_POST['fecha_arribo'], 4, 10))) . ' ' . date('H:i:s', strtotime($_POST['hora_arribo']));
            $fucov->fecha_salida = $fs;
            $fucov->fecha_arribo = $fa;
            $fucov->gasto_representacion = $_POST['gasto_representacion'];
            $fucov->gasto_imp = $_POST['gasto_imp'];
            $fucov->total_viatico = $_POST['total_viatico'];
            $fucov->total_pasaje = $_POST['total_pasaje'];
            $fucov->fecha_modificacion = date('Y-m-d H:i:s');
            $fucov->save();
            $this->request->redirect('documento/detalle/'.$fucov->id_memo);
            }
            else
                $this->template->content = '<b>EL DOCUMENTO YA FUE AUTORIZADO Y NO SE PUEDE MODIFICAR.</b><div class="info" style="text-align:center;margin-top: 50px; width:800px">
                                        <p><span style="float: left; margin-right: .3em;" class=""></span>    
                                        &larr;<a onclick="javascript:history.back(); return false;" href="#" style="font-weight: bold; text-decoration: underline;  " > Regresar<a/></p></div>';    
            
        }
        else
            $this->template->content = 'El FUCOV no existe';
    }
    
    public function action_autorizarfucov($id = '') {
        $fucov = ORM::factory('pvfucovs')->where('id','=',$id)->find();
        if ($fucov->loaded()) {
            $fucov->etapa_proceso = 2;
            $fucov->auto_pasaje = 1;            
            $fucov->save();
            $this->request->redirect('documento/detalle/'.$fucov->id_memo);
        }
    }
}
?>
