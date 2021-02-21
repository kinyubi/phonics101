<?php


namespace App\ReadXYZ\Helpers;


use App\ReadXYZ\Models\BoolWithMessage;

class Cleaner
{
    private int $failCt = 0;

    private function deleteDirectory(string $dirPath): void
    {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object !="..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        $this->deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        $filename = $dirPath . DIRECTORY_SEPARATOR . $object;
                        $result = unlink($filename);
                        if ($result == false) $this->failCt++;
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }

    }

    private function resetFileCount(): void
    {
        $this->failCt = 0;
    }

    public function deleteTwigCache(): BoolWithMessage
    {
        $this->resetFileCount();
        $cacheDir = Util::getReadXyzSourcePath('cache');
        $this->deleteDirectory($cacheDir);
        if (!file_exists($cacheDir)) mkdir($cacheDir);
        if ($this->failCt > 0) {
            $count = $this->failCt;
            $this->resetFileCount();
            return BoolWithMessage::badResult("Failed to delete $count files.");
        } else {
            $this->resetFileCount();
            return BoolWithMessage::goodResult();
        }
    }

    public function deleteGeneratedFiles(): BoolWithMessage
    {
        $generatedDir = Util::getPublicPath('generated');
        $this->deleteDirectory($generatedDir);
        if (!file_exists($generatedDir)) mkdir($generatedDir);
    }

}
