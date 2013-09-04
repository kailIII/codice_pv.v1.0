<?php
defined('SYSPATH') or die ('no tiene acceso');
//descripcion del modelo productos
class Model_Pvprogramaticas extends ORM{
    protected $_table_names_plural = false;
    
    public function ejecucionppt($id){
        $sql = "select  p.id, of.oficina, p.gestion, ent.sigla codigo_entidad, da.ppt_cod_da codigo_da, ue.ppt_cod_ue codigo_ue, 
                prog.codigo codigo_prog, proy.codigo codigo_proy, act.codigo codigo_act, act.actividad, fte.codigo codigo_fte, fte.sigla sigla_fte, org.codigo codigo_org, org.sigla sigla_org
                from pvprogramaticas p inner join oficinas of on p.id_oficina = of.id
                inner join entidades ent on of.id_entidad = ent.id
                inner join oficinas da on p.id_da = da.id 
                inner join oficinas ue on p.id_ue = ue.id
                inner join pvprogramas prog on p.id_programa = prog.id
                inner join pvproyectos proy on p.id_proyecto = proy.id
                inner join pvpptactividades act on p.id_actividadppt = act.id
                inner join pvfuentes fte on p.id_fuente = fte.id
                inner join pvorganismos org on p.id_organismo = org.id
                where ent.id =$id
                ";
        //return $this->_db->query(Database::SELECT, $sql, TRUE);
        return DB::query(1, $sql)->execute();
    }

    public function saldopresupuesto($id){
        $sql = "select part.codigo, eje.id, part.partida, eje.inicial, eje.modificado, eje.vigente, eje.preventivo, eje.comprometido, eje.devengado, eje.saldo_devengado, eje.pagado, eje.saldo_pagar
                from pvprogramaticas pro 
                inner join pvejecuciones eje on eje.id_programatica = pro.id
                inner join pvpartidas part on eje.id_partida = part.id
                where pro.id = $id";
        //return $this->_db->query(Database::SELECT, $sql, TRUE);
        return DB::query(1, $sql)->execute();
    }
    
        public function detallesaldopresupuesto($id){
        $sql = "select p.id, of.oficina unidad_funcional, p.gestion, ent.sigla, ent.entidad,
                da.ppt_cod_da codigo_da, da.oficina da, ue.ppt_cod_ue codigo_ue, ue.oficina ue,
                prog.codigo codigo_prog, proy.codigo codigo_proy, act.codigo codigo_act, act.actividad,
                fte.codigo codigo_fte, fte.denominacion fte,
                org.codigo codigo_org, org.denominacion org
                from pvprogramaticas p
                inner join oficinas of on p.id_oficina = of.id
                inner join entidades ent on of.id_entidad = ent.id
                inner join oficinas da on p.id_da = da.id
                inner join oficinas ue on p.id_ue = ue.id
                inner join pvprogramas prog on p.id_programa = prog.id
                inner join pvproyectos proy on p.id_proyecto = proy.id 
                inner join pvpptactividades act on p.id_actividadppt = act.id
                inner join pvfuentes fte on p.id_fuente = fte.id
                inner join pvorganismos org on p.id_organismo = org.id
                where p.id = $id";
        return $this->_db->query(Database::SELECT, $sql, TRUE);
        //return DB::query(1, $sql)->execute();
    }
    
    public function listafuentesuser($id){
        $sql = "select p.id, concat(p.codigo_entidad,'-',da.ppt_cod_da,'-',ue.ppt_cod_ue,'-' , prog.codigo,'-', proy.codigo,'-', act.codigo,'-',fte.codigo,'-', org.codigo,' : ', act.actividad) actividad
                from pvprogramaticas p 
                inner join oficinas da on p.id_da = da.id
                inner join oficinas ue on p.id_ue = ue.id
                inner join pvpptactividades act on p.id_actividadppt = act.id 
                inner join pvfuentes fte on p.id_fuente = fte.id
                inner join pvorganismos org on p.id_organismo = org.id
                inner join pvprogramas prog on p.id_programa = prog.id
                inner join pvproyectos proy on p.id_proyecto = proy.id
                where p.id_oficina = $id";
        return $this->_db->query(Database::SELECT, $sql, TRUE);
    }
    
    public function listafuentesppt($id){
        $sql = "select p.id, concat(p.codigo_entidad,'-',da.ppt_cod_da,'-',ue.ppt_cod_ue,'-' , prog.codigo,'-', proy.codigo,'-', act.codigo,'-',fte.codigo,'-', org.codigo,' : ', act.actividad) actividad
                from pvprogramaticas p 
                inner join oficinas da on p.id_da = da.id
                inner join oficinas ue on p.id_ue = ue.id
                inner join pvpptactividades act on p.id_actividadppt = act.id 
                inner join pvfuentes fte on p.id_fuente = fte.id
                inner join pvorganismos org on p.id_organismo = org.id
                inner join pvprogramas prog on p.id_programa = prog.id
                inner join pvproyectos proy on p.id_proyecto = proy.id
                where p.id_oficina = $id
                or act.actividad = 'GESTION ADMINISTRATIVA FINANCIERA'";
        return $this->_db->query(Database::SELECT, $sql, TRUE);
    }
    
    public function pptdisponibleuser($id, $pasaje, $viatico, $viaje, $gasto, $cambio)
    {
        $oDisp = new Model_Pvprogramaticas();
        $disp = $oDisp->saldopresupuesto($id);
        $result = "<table class=\"classy\" border=\"1px\"><thead><th>C&oacute;digo</th><th>Partida</th><th>Saldo Disponible</th><th>Solicitado (Bs)</th><th>Nuevo Saldo</th></thead><tbody>";
        foreach($disp as $d)
        {
            if( $viaje == 1 || $viaje == 2){
                if( $d['codigo'] == '22110')///pasaje al interio del pais
                    $result .= "<tr><td>".$d['codigo']."</td><td>".$d['partida']."</td><td>".$d['saldo_devengado']."</td><td>".$pasaje."</td><td>".($d['saldo_devengado'] - $pasaje)."</td></tr>";
                if( $d['codigo'] == '22210')///viatico al interior
                    $result .= "<tr><td>".$d['codigo']."</td><td>".$d['partida']."</td><td>".$d['saldo_devengado']."</td><td>".$viatico."</td><td>".($d['saldo_devengado'] - $viatico)."</td></tr>";
            }
            else
            {
                $p = round($pasaje*$cambio,2);
                $v = round($viatico*$cambio,2);
                $g = round($gasto*$cambio,2);
                if( $d['codigo'] == '22120')///pasaje al exterior
                    $result .= "<tr><td>".$d['codigo']."</td><td>".$d['partida']."</td><td>".$d['saldo_devengado']."</td><td>".$p."</td><td>".($d['saldo_devengado'] - $p)."</td></tr>";
                if( $d['codigo'] == '22220')///viaticos al exterior
                    $result .= "<tr><td>".$d['codigo']."</td><td>".$d['partida']."</td><td>".$d['saldo_devengado']."</td><td>".$v."</td><td>".($d['saldo_devengado'] - $v)."</td></tr>";
                if( $d['codigo'] == '26910')///gastos de representacion
                    $result .= "<tr><td>".$d['codigo']."</td><td>".$d['partida']."</td><td>".$d['saldo_devengado']."</td><td>".$g."</td><td>".($d['saldo_devengado'] - $g)."</td></tr>";
            }
        }
        $result .= "</tbody></table>";
        //echo json_encode($result);
        return $result;
    }
    

    
    /*

    

       

    
    public function listadetallefuentes($id){
        $sql = "select p.id, concat(p.codigo_entidad,'-',da.codigo_da,'-',ue.codigo_ue,'-' , prog.codigo,'-', proy.codigo,'-', act.codigo,'-',fte.codigo,'-', org.codigo,' : ', act.actividad) actividad
                from pyvprogramatica p 
                inner join pyvunidadfuncional da on p.id_da = da.id
                inner join pyvunidadfuncional ue on p.id_ue = ue.id
                inner join pyvactividadppt act on p.id_actividadppt = act.id 
                inner join pyvfuente fte on p.id_fuente = fte.id
                inner join pyvorganismo org on p.id_organismo = org.id
                inner join pyvprograma prog on p.id_programa = prog.id
                inner join pyvproyecto proy on p.id_proyecto = proy.id
                where p.id = $id";
        return $this->_db->query(Database::SELECT, $sql, TRUE);
    }
    */    
}
?>