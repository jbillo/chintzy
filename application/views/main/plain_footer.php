    </body>
    <?php if ($scripts): ?>
        <?php foreach ($scripts as $script): ?>
            <script type="text/javascript" src="<?php echo $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</html>