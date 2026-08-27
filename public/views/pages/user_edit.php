<?php

declare(strict_types=1);

auth_require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'ID tidak valid.');
    redirect(app_url('/?page=users'));
}

$user = user_find($id);
if (!$user) {
    flash_set('error', 'User tidak ditemukan.');
    redirect(app_url('/?page=users'));
}

$roles = [];
try {
    $roles = roles_all();
} catch (Throwable $e) {
    $roles = [];
}

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Edit User</div>
      <div class="card-subtitle"><?= h((string)$user['username']) ?></div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=users')) ?>">Kembali</a>
  </div>

  <div class="hr"></div>

  <form method="post" action="<?= h(app_url('/?page=user_edit&id=' . $id)) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="user.update" />
    <input type="hidden" name="id" value="<?= (int)$id ?>" />

    <div class="row">
      <div class="field">
        <div class="label">Username</div>
        <input class="input mono" name="username" value="<?= h((string)$user['username']) ?>" required />
      </div>
      <div class="field">
        <div class="label">Role</div>
        <select class="input" name="role_id" required>
          <?php foreach ($roles as $r): ?>
            <option value="<?= (int)$r['id'] ?>" <?= (int)$r['id'] === (int)$user['role_id'] ? 'selected' : '' ?>>
              <?= h((string)$r['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Status</div>
        <select class="input" name="status">
          <option value="aktif" <?= (string)$user['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="nonaktif" <?= (string)$user['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
      </div>
      <div class="field">
        <div class="label">Wajib ganti password</div>
        <select class="input" name="must_change_password">
          <option value="0" <?= (int)$user['must_change_password'] === 0 ? 'selected' : '' ?>>Tidak</option>
          <option value="1" <?= (int)$user['must_change_password'] === 1 ? 'selected' : '' ?>>Ya</option>
        </select>
      </div>
    </div>

    <div class="hr"></div>

    <div class="row">
      <div class="field">
        <div class="label">Password baru (opsional)</div>
        <input class="input" type="password" name="new_password" />
        <div class="help">Kosongkan jika tidak ingin mengubah password</div>
      </div>
      <div class="field">
        <div class="label">Konfirmasi password</div>
        <input class="input" type="password" name="new_password_confirm" />
      </div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn primary" type="submit">Simpan</button>
    </div>
  </form>
</section>
