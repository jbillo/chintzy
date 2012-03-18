<div class="container_12 grid_12 comment_form">
    <h3>New Comment</h3>
    <p>Have something interesting to say? Add your own comment.</p>

    <?php echo validation_errors(); ?>

    <?php echo form_open("comment/submit") ?>
        <input type="hidden" name="post_id" value="<?php echo $post_id ?>">
        <div class="grid_2 alpha right">
            <label for="comment_name">Your name</label>
        </div>
        <div class="grid_10 omega">
            <input type="textbox" id="comment_name" name="comment_name" size="32" maxlength="64" />
        </div>

        <div class="grid_2 alpha right">
            <label for="comment_email">Email</label>
        </div>

        <div class="grid_10 omega">
            <input type="textbox" id="comment_email" name="comment_email" size="32" maxlength="64" />
            <br />
            <small>Your email address will only be stored and not posted publicly.</small>
        </div>

        <div class="grid_2 alpha right">
            <label for="comment_url">Website (optional)</label>
        </div>

        <div class="grid_10 omega">
            <input type="textbox" id="comment_url" name="comment_url" size="32" maxlength="256" />
        </div>

        <div class="grid_2 alpha right">
            <label for="comment_text">Comment</label>
        </div>

        <div class="grid_10 omega">
            <textarea id="comment_text" name="comment_text" rows="6" cols="72"></textarea>
        </div>

        <?php if ($recaptcha): ?>
            <div class="grid_2 alpha right">
                Verification
            </div>
            <div class="grid_10 omega">
                <?php echo $recaptcha; ?>
            </div>
        <?php endif; ?>

        <div class="grid_10 alpha omega prefix_2">
            <input type="submit" value="Post Comment" />
        </div>
    </form>
</div>

<div class="clear"></div>