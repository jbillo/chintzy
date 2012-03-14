<div class="grid_12 comment_form">
    <h3>Comments</h3>
    <p>To make a comment on this post, fill out yonder fields.</p>
    
    <?php echo form_open("comment/new") ?>
        <div class="grid_2 alpha right">
            <label for="comment_name">Your name</label>
        </div>
        <div class="grid_10 omega">
            <input type="textbox" id="comment_name" name="comment_name" size="32" maxlength="64" />
        </div>
    
    </form>
</div>

<div class="clear"></div>