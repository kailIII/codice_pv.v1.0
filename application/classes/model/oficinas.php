<?php
defined('SYSPATH') or die ('no tiene acceso');
//descripcion del modelo productos
class Model_Oficinas extends ORM{
    protected $_table_names_plural = false;
    protected $_sorting=array('oficina'=>'ASC');
    //7una ofician tiene varios funcionarios (usuarios)
    protected $_has_many=array(
        'users' =>array(
            'model'=>'users',
            'foreign_key' =>'id_oficina'
        ),
        'carpetas' =>array(
            'model'=>'carpetas',
            'foreign_key' =>'id_oficina'
        ),
        'tipo' =>array(
            'through' => 'tipo_oficina',
        ),
//        'entidad' => array(
//            'model'=>'entidades',
//            'through' => 'entidades_oficinas',
//            'foreign_key' => 'id_oficina',
//            'far_key' => 'id_entidad',
//         ),
    );
    protected $_belogn_to=array(
        'entidades'=>array(
            'model'=>'entidades',
            'foreign_key'=>'id_oficina'
        )
    );


    
    //generar nuevo codigo de documentos para la oficina
    public function correlativo($id_oficina,$id_tipo){
        $result=ORM::factory('correlativo')->where('id_oficina','=',$id_oficina)->and_where('id_tipo','=',$id_tipo)->find();
        if($result->loaded()){
            $result->correlativo=$result->correlativo+1;            
            $result->save();
            return substr('000'.$result->correlativo,-4);
        }
        else{
            return FALSE;
        }
    }
    //function que obtine el cite (sigla) de la oficiana
    public function sigla($id_oficina){
        $result=ORM::factory('oficinas',array('id'=>$id_oficina));
        if($result->loaded()){
            return $result->sigla;
        }
        else{
            return FALSE;
        }            
    }
    public function tipo($id_tipo){
        $result=ORM::factory('tipos',array('id'=>$id_tipo));
        if($result->loaded()){
            return $result->abreviatura;
        }
        else{
            return FALSE;
        }
    }
    
    public function oficina($id){
        $results = ORM::factory("oficinas")
                ->join('users', 'INNER')
                ->on("users.id_oficina","=","oficinas.id")
                ->where("users.id", "=",$id)                
                ->find();
                return $results;
    }
    
    public function lista_oficinas()
    {
        $sql="SELECT o.id,e.id as id_entidad,o.oficina,e.entidad,e.sigla as sigla_entidad,o.sigla 
            FROM oficinas o INNER JOIN entidades e ON o.id_entidad=e.id";
        return db::query(Database::SELECT, $sql)->execute();
    }
    
    ///rodrigo - Unidad ejecutora 260813
    public function uejecutorapoa($id)///lista de unidades ejecutoras del POA
    {
        $sql="select * from oficinas where id = (select poa_unid_ejecutora from oficinas where id= $id)";
        return db::query(Database::SELECT, $sql)->execute();
    }    
    public function listaunidades($id){///
        $sql = "select ofi.id, ofi.oficina, ent.entidad 
                from oficinas ofi inner join entidades ent on ofi.id_entidad = ent.id
                where ent.id = $id
                and ofi.id = ofi.poa_unid_ejecutora";
        //return DB::query(1, $sql)->execute();
        return $this->_db->query(Database::SELECT, $sql, TRUE);    
    }    
    public function dappt($id)//direccion administrativa presupuesto
    {
        $sql="select * from oficinas where id = (select ppt_da from oficinas where id= $id)";
        //return db::query(Database::SELECT, $sql)->execute();
        return $this->_db->query(Database::SELECT, $sql, TRUE);    
    }    
    public function ueppt($id)///unidad ejecutora presupuesto
    {
        $sql="select * from oficinas where id = (select ppt_unid_ejecutora from oficinas where id= $id)";
        //return db::query(Database::SELECT, $sql)->execute();
        return $this->_db->query(Database::SELECT, $sql, TRUE);    
    }    
    public function listaunidadesppt($id){///lista de unidades ejecutoras de presupuesto
        $sql = "select ofi.id, ofi.oficina, ent.entidad 
                from oficinas ofi inner join entidades ent on ofi.id_entidad = ent.id
                where ent.id = $id
                and ofi.id = ofi.ppt_unid_ejecutora";
        return $this->_db->query(Database::SELECT, $sql, TRUE);    
    }
    ///260813
}
?>
