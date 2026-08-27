<?php

declare(strict_types=1);

function roles_all(): array
{
    $stmt = db()->query('SELECT * FROM roles ORDER BY name ASC');
    return $stmt ? $stmt->fetchAll() : [];
}

function role_find_by_name(string $name): ?array
{
    $stmt = db()->prepare('SELECT * FROM roles WHERE name = :name LIMIT 1');
    $stmt->execute(['name' => $name]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function role_create(string $name): int
{
    $name = trim(strtolower($name));
    if ($name === '') {
        throw new RuntimeException('Nama role wajib diisi.');
    }

    $stmt = db()->prepare('INSERT INTO roles (name) VALUES (:name)');
    $stmt->execute(['name' => $name]);
    return (int)db()->lastInsertId();
}
