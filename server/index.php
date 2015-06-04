<?php
    error_reporting(E_ALL | E_STRICT);
    
    class FileBoxException extends Exception {
        protected $retriable;
        
        public function __construct($message, $retriable = true) {
            parent::__construct($message);
            
            $this->retriable = $retriable;
        }
        
        public function isRetriable() {
            return $this->retriable;
        }
    }
    
    $file = new \stdclass();
    $file->success = false;
    
    $directory = 'uploads/';
    $subdirs = array('audios', 'books', 'docs', 'images', 'videos');
    
    $rejected = array('cgi', 'fcgi', 'htm', 'html', 'js', 'php', 'pl', 'py');
    
    $books = array('azw', 'azw3', 'cbr', 'cbz', 'epub', 'fb2', 'mobi', 'pdf', 'prc');
    $videos = array('avi', 'flv', 'mkv', 'mov', 'mp4', 'ogg', 'ogv', 'webm', 'wmv');
    $images = array('gif', 'jpeg', 'jpg', 'png', 'svg', 'tif', 'tiff');
    $audios = array('aac', 'flac', 'mp3', 'oga', 'wav', 'wma');
    $docs = array('doc', 'docx', 'md', 'odt', 'rtf', 'tex', 'txt');
            
    try {
        if(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error']))
            throw new FileBoxException('error-invalid', false);
        
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
                throw new FileBoxException('error-php-size', false);
            case UPLOAD_ERR_FORM_SIZE:
                throw new FileBoxException('error-form-size', false);
            case UPLOAD_ERR_PARTIAL:
                throw new FileBoxException('error-partial-file');
            case UPLOAD_ERR_NO_FILE:
                throw new FileBoxException('error-no-file');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new FileBoxException('error-tmp-directory', false);
            case UPLOAD_ERR_CANT_WRITE:
                throw new FileBoxException('error-cant-write', false);
            case UPLOAD_ERR_EXTENSION:
                throw new FileBoxException('error-php-extension');
            default:
                throw new FileBoxException('error-unknown');
        }
        
        if(isset($_REQUEST['filename']) and !empty($_REQUEST['filename']))
            $file->name = $_REQUEST['filename'];
        else
            $file->name = $_FILES['file']['name'];
        $file->name = htmlentities(stripslashes(str_replace('/', '', trim($file->name))));
            
        if(empty($file->name))
            throw new FileBoxException('filename-empty');
        
        $infos = pathinfo($file->name);
            
        $infos['extension'] = strtolower($infos['extension']);
        
        if(in_array($infos['extension'], $rejected))
            throw new FileBoxException('error-file-format', false);
        
        if(isset($_REQUEST['directory']) and in_array($_REQUEST['directory'], $subdirs))
            $subdir = $_REQUEST['directory'];
        elseif(in_array($infos['extension'], $audios))
            $subdir = $subdirs[0];
        elseif(in_array($infos['extension'], $books))
            $subdir = $subdirs[1];
        elseif(in_array($infos['extension'], $docs))
            $subdir = $subdirs[2];
        elseif(in_array($infos['extension'], $images))
            $subdir = $subdirs[3];
        elseif(in_array($infos['extension'], $videos))
            $subdir = $subdirs[4];
        else
            $subdir = '';
        
        if(!empty($subdir))
          $directory .= $subdir.'/';
        
        if(!empty($infos['extension']))
            $infos['extension'] = '.' .$infos['extension'];
        
        if(!file_exists($directory))
            mkdir($directory, 0770, true);
        
        for($count = 1; file_exists($directory.$file->name); ++$count)
            $file->name = $infos['filename']." ($count)". $infos['extension'];
        
        $path = $directory.$file->name;
        if(move_uploaded_file($_FILES['file']['tmp_name'], $path)){
            chmod($path, 0644);
        
            $file->success = true;
            $file->url = dirname($_SERVER['PHP_SELF']).'/'.$path;
        } else
            throw new FileBoxException('error-move-uploaded-file');
                
        $file->size = filesize($path);
        if($file->size != $_FILES['file']['size']) {
            if(file_exists($path))
                unlink($path);
            throw new FileBoxException('error-file-aborted.');
        }
    } catch(FileBoxException $e) {
        $file->error = $e->getMessage();
        
        if(!$e->isRetriable())
            $file->preventRetry = false;
    }
    
    echo json_encode($file);