

<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">



<div class="editor-simple-headline" data-flag-hidden="<?php echo $object -> params -> hidden ?? '0'; ?>"><?php echo $object -> body; ?></div>
