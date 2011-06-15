<div class="searches index">
    <h2><?php __('Searches');?></h2>
    <table cellpadding="0" cellspacing="0">
    <tr>
            <th><?php echo $this->Paginator->sort('displayField');?></th>
            <th><?php echo $this->Paginator->sort('modelName');?></th>
            <th><?php echo $this->Paginator->sort('content_id');?></th>
    </tr>
    <?php
    $i = 0;
    foreach ($results as $result):
        $class = null;
        if ($i++ % 2 == 0) {
            $class = ' class="altrow"';
        }   
    ?>  
    <tr<?php echo $class;?>>
        <td><?php echo $result['Search']['displayField']; ?>&nbsp;</td>
        <td><?php echo $result['Search']['modelName']; ?>&nbsp;</td>
        <td><?php echo $result['Search']['content_id']; ?>&nbsp;</td>
    </tr>
    <?php endforeach; ?>
    </table>
    <p> 
    <?php
    echo $this->Paginator->counter(array(
    'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
    )); 
?>  </p>
    <div class="paging">
        <?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
     |  <?php echo $this->Paginator->numbers();?>
 |
        <?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
    </div>
