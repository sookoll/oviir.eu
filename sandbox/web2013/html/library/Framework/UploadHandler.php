<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_varasalv_modul
 */
//error_reporting(E_ERROR);
class UploadHandler {
	
	protected $options;
	protected $error_messages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'missing_upload_folder' => 'Upload folder do not exist!'
    );
	public $uploadedFiles = array();
	
	function __construct($options = null) {
		$this->options = array(
			'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
			'param_name' => 'files',
			// Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true
		);
		
		if ($options)
            $this->options = array_merge($this->options, $options);
		$this->initialize();
	}
	
	protected function get_error_message($error) {
        return array_key_exists($error, $this->error_messages) ?
            $this->error_messages[$error] : $error;
    }
	
	protected function initialize(){
		$upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
        // Parse the Content-Disposition header, if available:
        $file_name = isset($_SERVER['HTTP_CONTENT_DISPOSITION']) ? rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $_SERVER['HTTP_CONTENT_DISPOSITION'])) : null;
        // Parse the Content-Range header, which has the following form: Content-Range: bytes 0-524287/2000000
        $content_range = isset($_SERVER['HTTP_CONTENT_RANGE']) ? preg_split('/[^0-9]+/', $_SERVER['HTTP_CONTENT_RANGE']) : null;
        $size =  $content_range ? $content_range[3] : null;
        $files = array();
		if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $files[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    $file_name ? $file_name : $upload['name'][$index],
                    $size ? $size : $upload['size'][$index],
                    $upload['type'][$index],
                    $upload['error'][$index],
                    $index,
                    $content_range
                );
            }
        } else {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
            $files[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                $file_name ? $file_name : (isset($upload['name']) ? $upload['name'] : null),
                $size ? $size : (isset($upload['size']) ? $upload['size'] : $_SERVER['CONTENT_LENGTH']),
                isset($upload['type']) ? $upload['type'] : $_SERVER['CONTENT_TYPE'],
                isset($upload['error']) ? $upload['error'] : null,
                null,
                $content_range
            );
        }
		
        $this->uploadedFiles = $files;
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null){
		$file = new stdClass();
        $file->name = $this->get_file_name($name, $type, $index, $content_range);
        $file->size = $this->fix_integer_overflow(intval($size));
        $file->type = $type;
		if (!is_dir($this->options['upload_dir']))
			$error = 'missing_upload_folder';
		if($this->validate($uploaded_file, $file, $error, $index)) {
			$upload_dir = $this->options['upload_dir'];
			$file_path = $this->options['upload_dir'].DIRECTORY_SEPARATOR.$file->name;
			$append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents($file_path,fopen($uploaded_file, 'r'),FILE_APPEND);
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents($file_path,fopen('php://input', 'r'),$append_file ? FILE_APPEND : 0);
            }
			$file_size = $this->get_file_size($file_path, $append_file);
            if ($file_size !== $file->size && (!$content_range && $this->options['discard_aborted_uploads'])) {
            	unlink($file_path);
                $file->error = 'abort';
            }
			$file->size = $file_size;
		}
		return $file;
	}
	
	protected function get_file_name($name, $type, $index, $content_range) {
        
		// Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = str_replace(' ','-',$name);
		$name = preg_replace('/[^a-z0-9-._]/i','',$name);
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        
		while(is_dir($this->options['upload_dir'].DIRECTORY_SEPARATOR.$name)) {
            $name = $this->upcount_name($name);
        }
        // Keep an existing filename if this is part of a chunked upload:
        $uploaded_bytes = $this->fix_integer_overflow(intval($content_range[1]));
        while(is_file($this->options['upload_dir'].DIRECTORY_SEPARATOR.$name)) {
            if ($uploaded_bytes === $this->get_file_size(
                    $this->options['upload_dir'].DIRECTORY_SEPARATOR.$name)) {
                break;
            }
            $name = $this->upcount_name($name);
        }
		
        return $name;
    }

	protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return '('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?:\(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }
	
	// Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }
	
	protected function get_file_size($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache) {
            clearstatcache(true, $file_path);
        }
        return $this->fix_integer_overflow(filesize($file_path));
    }
	
	protected function get_file_objects($iteration_method = 'get_file_object') {
        $upload_dir = $this->options['upload_dir'];
        if (!is_dir($upload_dir)) {
            return array();
        }
        return array_values(array_filter(array_map(
            array($this, $iteration_method),
            scandir($upload_dir)
        )));
    }
	
	protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(intval($_SERVER['CONTENT_LENGTH']));
		
		$val = trim(ini_get('post_max_size'));
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        $len = $this->fix_integer_overflow($val);
        if ($content_length > $len) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size'] && ($file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size'])) {
            $file->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] && $file_size < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files']) && (count($this->get_file_objects('is_valid_file_object')) >= $this->options['max_number_of_files'])) {
            $file->error = $this->get_error_message('max_number_of_files');
            return false;
        }
        
        return true;
    }
	
}


?>