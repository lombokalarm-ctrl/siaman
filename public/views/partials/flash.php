<?php

declare(strict_types=1);

$success = flash_get('success');
$error = flash_get('error');

?>
<?php if ($success): ?>
  <div class="alert success"><?= h($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert danger"><?= h($error) ?></div>
<?php endif; ?>

