<?php

declare(strict_types=1);

auth_require_admin();

$roles = [];
$users = [];
try {
    $roles = roles_all();
    $users = users_all();
} catch (Throwable $e) {
    $roles = [];
    $users = [];
}

?>
<section class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Users</div>
      <div class="card-subtitle">Manajemen akses</div>
    </div>
    <a class="btn primary" href="<?= h(app_url('/?page=user_create')) ?>">Tambah User</a>
  </div>

  <div class="hr"></div>

  <div class="grid cols-2">
    <div>
      <div class="card-title" style="font-size:13px">Roles</div>
      <div class="sub" style="margin-top:4px">Buat role sesuai kebutuhan</div>

      <form method="post" action="<?= h(app_url('/?page=users')) ?>" style="margin-top:10px">
        <?= csrf_input() ?>
        <input type="hidden" name="_action" value="role.create" />
        <div style="display:flex;gap:8px;align-items:flex-end">
          <div class="field" style="flex:1;margin:0">
            <div class="label">Nama Role</div>
            <input class="input" name="name" placeholder="contoh: finance" required />
          </div>
          <button class="btn" type="submit">Tambah</button>
        </div>
      </form>

      <table class="table" style="margin-top:10px">
        <thead>
          <tr>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$roles): ?>
            <tr><td class="muted">Belum ada role.</td></tr>
          <?php endif; ?>
          <?php foreach ($roles as $r): ?>
            <tr>
              <td class="mono"><?= h((string)$r['name']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div>
      <div class="card-title" style="font-size:13px">Daftar User</div>
      <div class="sub" style="margin-top:4px">Edit password dan role</div>

      <table class="table" style="margin-top:10px">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
            <th style="width:120px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$users): ?>
            <tr><td colspan="4" class="muted">Belum ada user.</td></tr>
          <?php endif; ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td class="mono">
                <?= h((string)$u['username']) ?>
                <?php if ((int)($u['must_change_password'] ?? 0) === 1): ?>
                  <div class="sub">Wajib ganti password</div>
                <?php endif; ?>
              </td>
              <td class="mono"><?= h((string)$u['role_name']) ?></td>
              <td>
                <?php if ((string)$u['status'] === 'aktif'): ?>
                  <span class="badge success">Aktif</span>
                <?php else: ?>
                  <span class="badge muted"><?= h((string)$u['status']) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn" href="<?= h(app_url('/?page=user_edit&id=' . (int)$u['id'])) ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
