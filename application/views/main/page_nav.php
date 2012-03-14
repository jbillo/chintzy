<div class="grid_12 page_nav">
    <?php if ($prev_page): ?>
        <a href="<?php echo site_url()?>?p=<?php echo $prev_page_num ?>">&laquo; Previous page</a> 
    <?php endif; ?>
    
    &nbsp;
    
    <?php if ($next_page): ?>
        <a href="<?php echo site_url()?>?p=<?php echo $next_page_num ?>">Next page &raquo;</a>
    <?php endif; ?>
</div>

<div class="clear"></div>