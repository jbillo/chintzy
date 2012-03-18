<div class="container_12 grid_12 comment_form">
    <h3>New Comment</h3>
    <p>
        Have something interesting to say? Add your own comment.
        <?php if (validation_errors() or $recaptcha_error): ?>
            <div class="important error">One or more fields need to be fixed to add a comment.</div>
        <?php endif; ?>
    </p>

    <?php echo form_open("comment/submit") ?>
        <input type="hidden" name="post_id" value="<?php echo $post_id ?>">
        <div class="grid_2 alpha right <?php if (form_error("comment_name")) echo 'important' ?>">
            <label for="comment_name"
                <?php if (form_error("comment_name")) echo 'class="error"' ?>>Your name
            </label>
        </div>
        <div class="grid_10 omega">
            <input type="textbox" id="comment_name" name="comment_name" size="32" maxlength="64"
                value="<?php echo set_value("comment_name") ?>" />
        </div>

        <div class="grid_2 alpha right <?php if (form_error("comment_email")) echo 'important' ?>">
            <label for="comment_email"
                <?php if (form_error("comment_email")) echo 'class="error"' ?>>Email
            </label>
        </div>

        <div class="grid_10 omega">
            <input type="textbox" id="comment_email" name="comment_email" size="32" maxlength="64"
                value="<?php echo set_value("comment_email") ?>" />
            <br />
            <small>Your email address will only be stored and not posted publicly.</small>
        </div>

        <div class="grid_2 alpha right">
            <label for="comment_url">Website (optional)</label>
        </div>

        <div class="grid_10 omega">
            <input type="textbox" id="comment_url" name="comment_url" size="32" maxlength="256"
                value="<?php echo set_value("comment_url") ?>" />
        </div>

        <div class="grid_2 alpha right <?php if (form_error("comment_email")) echo 'important' ?>">
            <label for="comment_text"
                <?php if (form_error("comment_email")) echo 'class="error"' ?>>Comment
            </label>
        </div>

        <div class="grid_10 omega">
            <textarea id="comment_text" name="comment_text" rows="6" cols="72"><?php echo set_value("comment_text") ?></textarea>
        </div>

        <?php if ($recaptcha): ?>
            <div class="grid_2 alpha right <?php if ($recaptcha_error) echo 'important' ?>">
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