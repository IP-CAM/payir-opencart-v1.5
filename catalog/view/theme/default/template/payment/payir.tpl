<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } else { ?>
<div class="buttons">
  <div class="right"><a href="<?php echo $action; ?>" class="button"><span><?php echo $button_confirm; ?></span></a></div>
</div>
<?php } ?>