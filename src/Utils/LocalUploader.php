<?php


namespace App\Utils;


use App\Utils\Interfaces\UploaderInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class LocalUploader implements UploaderInterface
{
    private $targetDirectory;

    public $file;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload($file)
    {
        $video_number = random_int(1,10000000);
        $fileName = $video_number.'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        $original_file_name = $this->clear(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        return [$fileName, $original_file_name];
    }

    public function delete($path)
    {
        $fileSystem = new Filesystem();
        try {
            $fileSystem->remove('.'.$path);
        } catch (IOExceptionInterface $exception) {

            echo "An error occurred while deleting a file at ".$exception->getPath();
        }

        return true;

    }

    private function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    private function clear($string)
    {
        $string =  preg_replace('/[^A-Za-z0-9- ]+/', '', $string);
        return $string;
    }

}