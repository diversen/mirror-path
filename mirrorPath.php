<?php

namespace diversen;

class mirrorPath {

    /**
     * Mirror a path recursively
     * @param string $source
     * @param string $dest
     * @throws Exception
     */
    public function mirror($source, $dest) {

        if (!file_exists($source)) {
            throw new Exception('No such dir source dir: ' . $source);
        }

        if ($this->deleteBefore && file_exists($dest) && is_dir($dest)) {
            $this->deleteDir($dest);
        }

        $perms = $this->octalPerms($source);
        if (!is_dir($dest)) {
            mkdir($dest);
        }
        chmod($dest, $perms);


        foreach (
        $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(
        $source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
            if ($item->isDir()) {
                $dir = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                $this->mkdir($item, $dir);
            } else {
                $file = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                $this->copy($item, $file);
            }
        }
    }

    /**
     * mkdir. source is also a param as we will ensure correct permissions
     * @param string $source
     * @param string $dest
     */
    private function mkdir($source, $dest) {
        $perms = $this->octalPerms($source);
        if (!is_dir($dest)) {
            mkdir($dest);
        }
        chmod($dest, $perms);
    }

    /**
     * Copy a file from source to dest with validation of types
     * @param string $source
     * @param string $dest
     * @return boolean
     */
    private function copy($source, $dest) {

        if (!$this->validateDisallowTypes($source)) {
            return;
        }

        if (!$this->validateAllowTypes($source)) {
            return;
        }

        copy($source, $dest);
        $perms = $this->octalPerms($source);
        chmod($dest, $perms);
    }

    /**
     * Validate disallowtypes
     * @param string $source
     * @return boolean
     */
    private function validateDisallowTypes($source) {
        
        if (empty($this->disallowTypes)) {
            return true;
        }

        $ary = pathinfo($source);
        if (!empty($ary['extension']) && in_array($ary['extension'], $this->disallowTypes)) {
            return false;
        }
        return true;
    }

    /**
     * Validate allow types
     * @param string $source
     * @return boolean
     */
    private function validateAllowTypes($source) {
        
        if (empty($this->allowTypes)) {
            return true;
        }

        $ary = pathinfo($source);
        if (!empty($ary['extension']) && in_array($ary['extension'], $this->allowTypes)) {
            return true;
        }
        return false;
    }

    /**
     * Delete a dir recursively
     * @param string $dest
     */
    private function deleteDir($dest) {
        $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dest, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($dest);
    }

    /**
     * Returns the octal perms from a filename, e.g. 0777
     * @param string $file
     * @return int $perms
     */
    private function octalPerms($file) {
        $str = substr(sprintf('%o', fileperms($file)), -4);
        return intval($str, 8);
    }

    /**
     * var holding types to disallow e.g. ['php', 'json']
     * @var array $disallowTypes
     */
    public $disallowTypes = [];
    
    /**
     * var holding types to sallow e.g. ['php', 'json']
     * @var array $allowTypes
     */
    public $allowTypes = [];
    
    /**
     * var holding boolean to indicate if we delete before mirroring path
     * @var boolean $deleteBefore
     */
    public $deleteBefore = true;

}
