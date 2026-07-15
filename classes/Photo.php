<?php
namespace App;

require_once __DIR__ . '/AbstractModel.php';

/**
 * Photo class demonstrating encapsulation for photo metadata and a helper
 * to move uploaded files into the uploads folder.
 */
class Photo extends AbstractModel {
    private string $filename;
    private string $title;
    private ?int $uploaderId = null;

    public function __construct(string $filename, string $title = '', ?int $uploaderId = null)
    {
        $this->filename = $filename;
        $this->title = $title;
        $this->uploaderId = $uploaderId;
    }

    public function getFilename(): string { return $this->filename; }
    public function setFilename(string $v): self { $this->filename = $v; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $v): self { $this->title = $v; return $this; }

    public function getUploaderId(): ?int { return $this->uploaderId; }
    public function setUploaderId(?int $id): self { $this->uploaderId = $id; return $this; }

    /**
     * Move uploaded file from temporary path to destination directory.
     * Returns destination path on success, or false on failure.
     */
    public function saveFile(string $tmpPath, string $destDir)
    {
        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0755, true)) return false;
        }
        $dest = rtrim($destDir, "\/\\") . DIRECTORY_SEPARATOR . basename($this->filename);
        if (is_uploaded_file($tmpPath)) {
            return move_uploaded_file($tmpPath, $dest) ? $dest : false;
        }
        // fallback for non-http uploads in demos/tests
        return rename($tmpPath, $dest) ? $dest : false;
    }

    protected static function tableName(): string
    {
        return 'photos';
    }

    protected function toDatabaseArray(): array
    {
        return [
            'filename' => $this->filename,
            'title' => $this->title,
            'uploader_id' => $this->uploaderId,
        ];
    }
}
