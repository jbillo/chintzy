            <div class="grid_12" id="footer">
                Powered by ChintzyCMS
            </div>

            <div class="clear"></div>
        </div> <!-- /container -->
    </body>
    <?php if ($scripts): ?>
        <?php foreach ($scripts as $script): ?>
            <script type="text/javascript" src="<?php echo $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</html>