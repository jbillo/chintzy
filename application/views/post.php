<div class="grid_12">
    <h2><?php echo $post->title ?></h2>
    <div class="dateline">
        posted <?php echo $post->f_created_on ?>
        <?php if ($post->page == 'f'): ?>
            | 
            <?php echo $post->comment_count ?> comment<?php if ($post->comment_count != 1) echo 's' ?>
        <?php endif; ?>
    </div>
    
    <hr />
    <div>
        <?php echo $post->text ?>
    </div>
</div>

<div class="clear"></div>