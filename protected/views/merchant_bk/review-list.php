
<div class="uk-width-1">
<!--<a href="<?php echo Yii::app()->request->baseUrl; ?>/merchant/review/Do/Add" class="uk-button"><i class="fa fa-plus"></i> <?php echo Yii::t("default","Add New")?></a>-->
<a href="<?php echo Yii::app()->request->baseUrl; ?>/merchant/review" class="uk-button"><i class="fa fa-list"></i> <?php echo Yii::t("default","List")?></a>
</div>

<form id="frm_table_list" method="POST" >
<input type="hidden" name="action" id="action" value="customerReviews">
<input type="hidden" name="tbl" id="tbl" value="review">
<input type="hidden" name="clear_tbl"  id="clear_tbl" value="clear_tbl">
<input type="hidden" name="whereid"  id="whereid" value="id">
<input type="hidden" name="slug" id="slug" value="review">
<table id="table_list" class="uk-table uk-table-hover uk-table-striped uk-table-condensed">
  <!--<caption>Merchant List</caption>-->
   <thead>
        <tr>			
			 <th width="5%"><?php echo Yii::t('default',"customer")?></th>
			 <th width="10%"><?php echo Yii::t('default',"Reviews")?></th>				 
			 <!--<th width="10%"><?php echo Yii::t('default',"Order Ref")?></th>	-->			 	 
			 <th width="5%"><?php echo Yii::t('default',"Rating")?></th>			 
			 <th width="5%"><?php echo Yii::t('default',"Date Created")?></th>
        </tr>
    </thead>
    <tbody>   
    </tbody>
</table>
<div class="clear"></div>
</form>