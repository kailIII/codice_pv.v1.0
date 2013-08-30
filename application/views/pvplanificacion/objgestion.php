<script type="text/javascript">

    $(function()
    {
        $("#theTable").tablesorter({sortList:[[2,1]], 
            widgets: ['zebra'],
            headers: {             
                5: { sorter:false}//,
                //6: { sorter:false}
            }
        });
        
        //add index column with all content.
 $("table#theTable tbody tr:has(td)").each(function(){
   var t = $(this).text().toLowerCase(); //all row text
   $("<td class='indexColumn'></td>")
    .hide().text(t).appendTo(this);
 });//each tr

        
        $("#FilterTextBox").keyup(function(){
            var s = $(this).val().toLowerCase().split(" ");
            //show all rows.
            $("#theTable tbody tr:hidden").show();
            $.each(s, function(){
                $("#theTable tbody tr:visible .indexColumn:not(:contains('"
                    + this + "'))").parent().hide();
            });//each
        });//key up.
        //zebra
        
        $("#FilterTextBox").focus();
        
        
    });//document.ready
</script>
<style>
    #file-word{ display: none;  }
    td{padding:5px;}    
</style>
<h2 class="subtitulo">Objetivos de Gesti&oacute;n Para: <?php echo $oficina->oficina;?> <br/> <span>Lista de Objetivos de Gesti&oacute;n</span></h2>
<?php if(sizeof($objetivos)>0):?> 
<p style="margin: 5px auto;"> <b>Filtrar/Buscar: </b><input type="text" id="FilterTextBox" name="FilterTextBox" size="40" />
<div style="float: right;"><a href="/pvplanificacion/addobjgestion/<?php echo $oficina->id;?>" >+ Adicionar</a></div></p>
<table id="theTable" class="tablesorter" border="1px" >
    <thead>
        <tr>
            <th>C&oacute;digo</th>
            <th>Objetivo</th>
            <th>Acci&oacute;n</th>
        </tr>
    </thead>    
    <tbody>
    <?php    
    foreach( $objetivos as $obj): ?>
        <tr>
            <td ><?php echo $obj->codigo;?></td>
            <td ><?php echo $obj->objetivo;?></td>
            <td><a href="/pvplanificacion/editobjgestion/<?php echo $obj->id;?>" class="uibutton" title="Imprimir Certificado" ><img src="/media/images/edit.png"/> </a>
                <a href="/pvplanificacion/eliminarobjgestion/<?php echo $obj->id;?>" class="uibutton" title="Imprimir Certificado" ><img src="/media/images/delete.png"/> </a>
            </td>
        </tr>        
    <?php endforeach; ?>
   </tbody>   
</table>
<?php else: ?>
<div style="margin-top: 20px; padding: 10px;" class="info">
    <p><span style="float: left; margin-right: .3em;" class=""></span>    
     <strong>Info: </strong> <?php echo 'No Hay Objetivos de Gestion Para Esta Oficina.';?></p>    
</div>
<?php endif; ?>