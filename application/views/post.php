<div class="grid_12 post">
    <h2><a href="<?php echo site_url($post->slug) ?>"><?php echo $post->title ?></a></h2>
    <div class="dateline">
        posted <?php echo $post->f_created_on ?>
        <?php if ($post->page == 'f'): ?>
            | 
            <a href="<?php echo site_url($post->slug) ?>#comment">
            <?php echo $post->comment_count ?> comment<?php if ($post->comment_count != 1) echo 's' ?>
            </a>
        <?php endif; ?>
    </div>
    
    <hr />
    <div class="post_text">
        <?php echo $post->text ?>
    </div>
</div>

<div class="clear"></div>