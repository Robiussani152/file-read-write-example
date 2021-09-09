<?php

class Users
{
    //user data dir const
    public const USER_DATA_DIR = __DIR__ . '/../users-data/';

    /**
     * form data read and do the reset task as flow
     *
     * @param array $request
     * @return void
     */
    public function getFormData($request)
    {
        $this->changeTemplateInfo($request);
        echo 'all done';
    }

    /**
     * change template infomation
     * generate zip
     * delete created files
     *
     * @param array $data
     * @return void
     */
    protected function changeTemplateInfo($data)
    {
        //read file
        $file = fopen(__DIR__ . '/../templates/userinfo.txt', 'r');
        $pageText = fread($file, 25000);

        //replace the desiered key with value from template
        if (key_exists('name', $data))
            $pageText = $this->changeFileData($pageText, 'ic_user_name', $data['name']);
        if (key_exists('email', $data))
            $pageText = $this->changeFileData($pageText, 'ic_user_email', $data['email']);
        if (key_exists('username', $data))
            $pageText = $this->changeFileData($pageText, 'ic_user_username', $data['username']);

        //generate a random dir name
        $dirname = mt_rand();
        mkdir(Users::USER_DATA_DIR . $dirname);
        //create a new file
        $newFile = fopen(Users::USER_DATA_DIR . $dirname . '/user-file.txt', "w");
        fwrite($newFile, $pageText);
        fclose($newFile);
        //generate zip
        $this->generateZip($dirname);
        //delete created dir with files
        $this->deleteDir(Users::USER_DATA_DIR . $dirname);
        return true;
    }

    /**
     * generate zip
     *
     * @param string $dirname
     * @return void
     */
    protected function generateZip($dirname)
    {
        $zip = new ZipArchive;
        if ($zip->open(Users::USER_DATA_DIR . 'ic-' . $dirname . '.zip', ZipArchive::CREATE) === TRUE) {
            //add files
            $zip->addFile(Users::USER_DATA_DIR . $dirname . '/user-file.txt');
            $zip->close();

            //move zip file to other folder
            rename(Users::USER_DATA_DIR . 'ic-' . $dirname . '.zip', Users::USER_DATA_DIR . 'zip-files/ic-' . $dirname . '.zip');
        }
        return true;
    }

    /**
     * change template key with value
     *
     * @param string $text
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function changeFileData($text, $key, $value = "")
    {
        return str_replace($key, $value, $text);
    }

    /**
     * delete dir with files
     *
     * @param string $dirPath
     * @return void
     */
    public static function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}
