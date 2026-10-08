<?php

declare(strict_types=1);

/**
 * User Model
 * Lost & Found SMK Informatika Sumedang
 */

require_once dirname(__DIR__, 2) . '/legacy-config/legacy_database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            'email' => strtolower(trim($email))
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmailExceptId(
        string $email,
        int $id
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE email = :email
              AND id <> :id
            LIMIT 1
        ");

        $stmt->execute([
            'email' => strtolower(trim($email)),
            'id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByNis(string $nis): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE nis = :nis
            LIMIT 1
        ");

        $stmt->execute([
            'nis' => trim($nis)
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByNisExceptId(
        string $nis,
        int $id
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE nis = :nis
              AND id <> :id
            LIMIT 1
        ");

        $stmt->execute([
            'nis' => trim($nis),
            'id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function getAdminContact(): ?array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                nama,
                email,
                no_telepon
            FROM users
            WHERE LOWER(role) = 'admin'
            ORDER BY id ASC
            LIMIT 1
        ");

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        return $admin ?: null;
    }

    public function findByGoogleId(string $googleId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE google_id = :google_id
            LIMIT 1
        ");

        $stmt->execute([
            'google_id' => $googleId
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function createGoogleUser(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users
            (
                nama,
                nis,
                email,
                no_telepon,
                google_id,
                avatar,
                password,
                password_set,
                kelas,
                role,
                created_at
            )
            VALUES
            (
                :nama,
                NULL,
                :email,
                :no_telepon,
                :google_id,
                :avatar,
                NULL,
                0,
                NULL,
                'siswa',
                NOW()
            )
        ");

        $stmt->execute([
            'nama' => trim($data['nama'] ?? ''),
            'email' => strtolower(trim($data['email'] ?? '')),
            'no_telepon' => trim($data['no_telepon'] ?? ''),
            'google_id' => $data['google_id'] ?? null,
            'avatar' => $data['avatar'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function setGoogleId(
        int $userId,
        string $googleId
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET google_id = :google_id
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $userId,
            'google_id' => $googleId
        ]);
    }

    public function updateGoogleAccount(
        int $id,
        string $googleId,
        ?string $avatar = null
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                google_id = :google_id,
                avatar = :avatar
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'google_id' => $googleId,
            'avatar' => $avatar
        ]);
    }

    public function create(array $data): int
    {
        $password = $data['password'] ?? '';

        $stmt = $this->db->prepare("
            INSERT INTO users
            (
                nama,
                nis,
                email,
                no_telepon,
                google_id,
                avatar,
                password,
                password_set,
                kelas,
                role,
                created_at
            )
            VALUES
            (
                :nama,
                :nis,
                :email,
                :no_telepon,
                :google_id,
                :avatar,
                :password,
                :password_set,
                :kelas,
                :role,
                NOW()
            )
        ");

        $stmt->execute([
            'nama' => trim($data['nama'] ?? ''),
            'nis' => $data['nis'] ?? null,
            'email' => strtolower(trim($data['email'] ?? '')),
            'no_telepon' => trim($data['no_telepon'] ?? ''),
            'google_id' => $data['google_id'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
            'password_set' => $data['password_set'] ?? 1,
            'kelas' => $data['kelas'] ?? null,
            'role' => $data['role'] ?? 'siswa'
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                nama,
                nis,
                email,
                no_telepon,
                google_id,
                avatar,
                password_set,
                kelas,
                role,
                created_at,
                password_changed_at
            FROM users
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countStudents(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
            WHERE LOWER(role) = 'siswa'
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
        ");

        return (int) $stmt->fetchColumn();
    }

    public function updateContact(
        int $id,
        string $email,
        string $noTelepon
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                email = :email,
                no_telepon = :no_telepon
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'email' => strtolower(trim($email)),
            'no_telepon' => trim($noTelepon)
        ]);
    }

    /**
     * Update profile siswa.
     *
     * Yang boleh diubah:
     * - nama
     * - nis
     * - email
     * - no_telepon
     * - kelas
     *
     * Avatar diubah melalui updateAvatar().
     */
    public function updateProfile(
        int $id,
        string $nama,
        string $nis,
        string $email,
        string $noTelepon,
        string $kelas
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                nama = :nama,
                nis = :nis,
                email = :email,
                no_telepon = :no_telepon,
                kelas = :kelas
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'nama' => trim($nama),
            'nis' => trim($nis),
            'email' => strtolower(trim($email)),
            'no_telepon' => trim($noTelepon),
            'kelas' => trim($kelas)
        ]);
    }

    /**
     * Update foto profil/avatar user.
     *
     * Nilai avatar hanya menyimpan nama file,
     * contoh:
     * avatar_13_abc123.jpg
     */
    public function updateAvatar(
        int $id,
        ?string $avatar
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                avatar = :avatar
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'avatar' => $avatar
        ]);
    }

    /**
     * Ambil nama file avatar user.
     */
    public function getAvatar(int $id): ?string
    {
        $stmt = $this->db->prepare("
            SELECT avatar
            FROM users
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $avatar = $stmt->fetchColumn();

        if ($avatar === false || $avatar === null) {
            return null;
        }

        $avatar = trim((string) $avatar);

        return $avatar !== '' ? $avatar : null;
    }

    public function updateAdminContact(
        int $id,
        string $email,
        string $noTelepon
    ): bool {
        return $this->updateContact(
            $id,
            $email,
            $noTelepon
        );
    }

    public function updateAdminUser(
        int $id,
        string $nama,
        string $nis,
        string $email,
        string $noTelepon,
        string $kelas
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                nama = :nama,
                nis = :nis,
                email = :email,
                no_telepon = :no_telepon,
                kelas = :kelas
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'nama' => trim($nama),
            'nis' => trim($nis),
            'email' => strtolower(trim($email)),
            'no_telepon' => trim($noTelepon),
            'kelas' => trim($kelas)
        ]);
    }

    public function setPassword(
        int $id,
        string $password
    ): bool {
        if (strlen($password) < 8) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET
                password = :password,
                password_set = 1,
                password_changed_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            )
        ]);
    }

    public function updatePassword(
        int $id,
        string $newPassword
    ): bool {
        return $this->setPassword(
            $id,
            $newPassword
        );
    }

    public function resetPasswordToTemporary(
        int $id,
        string $temporaryPassword
    ): bool {
        return $this->setPassword(
            $id,
            $temporaryPassword
        );
    }

    public function createResetToken(
        int $id,
        string $token,
        string $expires
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                reset_token = :reset_token,
                reset_expires = :reset_expires
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'reset_token' => $token,
            'reset_expires' => $expires
        ]);
    }

    public function findByResetToken(
        string $token
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE
                reset_token = :reset_token
                AND reset_expires IS NOT NULL
                AND reset_expires > NOW()
            LIMIT 1
        ");

        $stmt->execute([
            'reset_token' => $token
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function resetPassword(
        int $id,
        string $password
    ): bool {
        if (strlen($password) < 8) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET
                password = :password,
                password_set = 1,
                password_changed_at = NOW(),
                reset_token = NULL,
                reset_expires = NULL
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            )
        ]);
    }

    public function clearResetToken(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                reset_token = NULL,
                reset_expires = NULL
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id
        ]);
    }
}