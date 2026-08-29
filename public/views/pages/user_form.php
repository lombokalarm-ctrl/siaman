<?php

declare(strict_types=1);

auth_require_admin_or_staff();

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
      <div class="card-title">Tambah User</div>
      <div class="card-subtitle">Buat akses baru</div>
    </div>
    <a class="btn" href="<?= h(app_url('/?page=users')) ?>">Kembali</a>
  </div>

  <div class="hr"></div>

  <form method="post" action="<?= h(app_url('/?page=user_create')) ?>">
    <?= csrf_input() ?>
    <input type="hidden" name="_action" value="user.create" />

    <div class="row">
      <div class="field">
        <div class="label">Username</div>
        <input class="input mono" name="username" required />
      </div>
      <div class="field">
        <div class="label">Role</div>
        <select class="input" name="role_id" required>
          <option value="">Pilih role</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= (int)$r['id'] ?>"><?= h((string)$r['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row" style="margin-top:10px">
      <div class="field">
        <div class="label">Password</div>
        <input class="input" type="password" name="password" required />
      </div>
      <div class="field">
        <div class="label">Status</div>
        <select class="input" name="status">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>

    <div class="hr"></div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn" type="reset">Reset</button>
      <button class="btn primary" type="submit">Simpan</button>
    </div>
  </form>
</section>
