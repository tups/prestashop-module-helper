<?php
/**
 * User: lord
 * Date: 15/09/2017
 * Time: 09:33
 */

namespace Helper;
if(!class_exists ('\Helper\UploadHelper', false)) {
    require_once __DIR__ . '/../../classes/Helper/UploadHelper.php';
}

class ScanDir
{


    private $_acceptFileFolder = array();
    private $_directorySeparator = '/';
    private $_file = array();
    private $_folder = '';

    public function scan() {
        $this->_mkmap($this->_folder);
    }

    /**
     * Ajouter des extensions autorisées
     * @param array $extensions
     */
    public function addExtensionFile($extensions = array()) {
        $this->_acceptFileFolder = array_merge($this->_acceptFileFolder, $extensions);
    }

    /**
     *
     * @param string $folder
     */
    public function __construct($folder = '') {
        $this->_folder = $folder;
    }

    /**
     * @return array
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Creation des dossiers
     */
    public function createFolder($path)
    {
        if (!file_exists($path)) {
            $this->_rmkdir($path);
        }
    }

    protected function _rmkdir($path) {
        $path = str_replace("\\", "/", $path);
        $path = explode("/", $path);

        $rebuild = '';
        foreach($path AS $p) {
            if(strstr($p, ":") != false) {
                $rebuild = $p;
                continue;
            }
            $rebuild .= "/".$p;
            if(!is_dir($rebuild)){
                mkdir($rebuild,0775);
                chmod($rebuild, 0775);
            }
        }
    }


    function _mkmap($folder) {
        /* Tester que l'element existe et que c'est un repertoire */
        if (file_exists($folder) && is_dir($folder)) {
            /* Ouvrir le dossier */
            $folderRead = opendir($folder);

            /* Boucler sur les elements du dossier */
            while ($file = readdir($folderRead)) {
                if ($file != '.' && $file != '..') {
                    $pathfile = $folder . $file;
                    /* Tester si on est dans un dossier */
                    if (is_dir($pathfile)) {
                        $this->_mkmap($pathfile . $this->_directorySeparator);
                    }

                    /* Tester si c'est un fichier pour le mettre dans les tableaux de fichiers a parser */
                    $path_info = pathinfo($pathfile);
                    $href = str_replace(_PS_ROOT_DIR_, '', $pathfile);
                    $href = str_replace(DIRECTORY_SEPARATOR, '/', $href);

                    if (is_file($pathfile) && in_array($path_info['extension'], $this->_acceptFileFolder)) {
                        $this->_file[] = array(
                            'path' => $pathfile,
                            'file' => $file,
                            'href' => $href,
                            'date' => filemtime($pathfile)
                        );
                    }
                }
            }
            closedir($folderRead);
        }
    }

}