<?php
namespace UIS\Core\File;

use Auth;
use UIS\Mvf\ValidatorTypes\Int;

class SubmittedFileValidator extends Int
{
    public function validate()
    {
        $submittedFileValue = trim($this->getVarValue());
        $submittedFile = new SubmittedFile();
        $this->setVarValue($submittedFile);
        if ($this->isEmpty()) {
            $submittedFile->setFileType(SubmittedFile::FILE_TYPE_EMPTY);
        } else if ($submittedFileValue === 'same') {
            $submittedFile->setFileType(SubmittedFile::FILE_TYPE_SAME);
        } else {
            return $this->validateTempFile($submittedFile, $submittedFileValue);
        }
        return $this->makeValid();
    }

    protected function validateTempFile(SubmittedFile $submittedFile, $id)
    {
        $tempFile = Uploader::getTempFile($id, false, true);
        if (empty($tempFile)) {
            return $this->makeError('file_not_found');
        }
        $submittedFile->setFileType(SubmittedFile::FILE_TYPE_NEW);
        $submittedFile->setTempFile($tempFile);
        return $this->makeValid();
    }
    
    public function allowChangeData()
    {
        return true;
    }

    public function passEmptyData()
    {
        return true;
    }
}
