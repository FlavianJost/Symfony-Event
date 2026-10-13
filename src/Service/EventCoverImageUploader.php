<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class EventCoverImageUploader
{
    public const UPLOAD_DIR = 'public/uploads/events';

    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly string $projectDir,
    ) {
    }

    public function upload(?UploadedFile $file, Event $event): void
    {
        if (null === $file) {
            return;
        }

        $targetDir = $this->projectDir.DIRECTORY_SEPARATOR.self::UPLOAD_DIR;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $oldCover = $event->getCoverImage();
        $fileName = strtolower($this->slugger->slug($event->getTitle())->toString()).
            '-'.uniqid().
            '.'.$file->guessExtension();

        $file->move($targetDir, $fileName);
        $event->setCoverImage($fileName);

        if (null !== $oldCover && $oldCover !== $fileName) {
            $oldFile = $targetDir.DIRECTORY_SEPARATOR.basename((string) $oldCover);
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }
    }
}