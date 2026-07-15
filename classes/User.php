<?php
namespace App;

require_once __DIR__ . '/AbstractModel.php';

/**
 * Simple User class showing encapsulation (private properties + getters/setters)
 * and a couple of subclasses to demonstrate inheritance.
 */
class User extends AbstractModel {
    private string $username;
    private string $email;
    private ?string $passwordHash = null;

    public function __construct(string $username, string $email, ?string $password = null)
    {
        $this->username = $username;
        $this->email = $email;
        if ($password !== null) $this->setPassword($password);
    }

    // Encapsulation: access through methods only
    public function getUsername(): string { return $this->username; }
    public function setUsername(string $v): self { $this->username = $v; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): self { $this->email = $v; return $this; }

    public function setPassword(string $password): self
    {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    public function verifyPassword(string $password): bool
    {
        if ($this->passwordHash === null) return false;
        return password_verify($password, $this->passwordHash);
    }

    protected static function tableName(): string
    {
        return 'users';
    }

    protected function toDatabaseArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->passwordHash,
        ];
    }
}

// Inheritance examples
class Admin extends User {
    public function deleteUser(\PDO $pdo, int $userId): bool
    {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$userId]);
    }
}

class Buyer extends User {
    // Buyer-specific behaviour example
    public function viewCart(): array
    {
        return []; // stub for demo
    }
}
