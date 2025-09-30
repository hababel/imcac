<?php if (!isset($csrf)) {
	$csrf = \Core\Lib\Csrf::token();
} ?>
<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">