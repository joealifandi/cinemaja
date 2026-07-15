<?php
namespace App;

/**
 * Abstract class: defines a common contract for models that can be saved to the database.
 * Child classes must provide table name and data mapping.
 */
abstract class AbstractModel {
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    abstract protected static function tableName(): string;

    abstract protected function toDatabaseArray(): array;

    public function saveToDb(\PDO $pdo): bool
    {
        $data = $this->toDatabaseArray();
        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::tableName(),
            implode(', ', $columns),
            $placeholders
        );

        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array_values($data));
    }
}
