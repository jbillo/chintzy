<div class="grid_12 comment">
    <div class="grid_12 alpha omega comment_header">
        <a name="c_<?php echo $id ?>"></a>
        <strong>
            <?php if ($user_url): ?>
                <a href="<?php echo $user_url ?>" rel="nofollow">
            <?php endif; ?>
            <?php echo $user_name ?><?php if ($user_url) echo "</a>" ?>
        </strong>
        on <a href="#c_<?php echo $id ?>"><?php echo $f_created_on ?></a>
    </div>
    <div class="grid_12 alpha omega comment_text">
        <?php echo $text ?>
    </div>
</div>

<div class="clear"></div>