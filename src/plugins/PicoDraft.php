<?php
/**
 * A content editor plugin for Pico 2
 *
 * Supports PicoUsers plugin for authentification
 * {@link https://github.com/nliautaud/pico-users}
 *
 * @author  Mihkel Oviir
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link    https://github.com/sookoll/pico-draft
 * @link    http://picocms.org
 */
class PicoDraft extends AbstractPicoPlugin
{
  /**
   * Pico API version.
   * @var int
   */
  const API_VERSION = 2;
  /**
   * This plugin depends on ...
   * @see AbstractPicoPlugin::$dependsOn
   * @var string[]
   */
  protected $dependsOn = array(
    'PicoUsers'
  );
  protected $canEdit = false;
  protected $canAdmin = false;
  protected $hash = null;
  protected $info = 'Unknown error';

  /**
   * Triggered after Pico has read its configuration
   *
   * @see    Pico::getConfig()
   * @param  array &$config array of config variables
   * @return void
   */
  public function onConfigLoaded(array &$config)
  {
    $this->hash = @$config['editor']['hash'];
    $this->template = @$config['editor']['template'];
    $this->deleteDir = @$config['editor']['content_dir_delete'];
  }

  /**
   * Triggered after Pico has evaluated the request URL
   *
   * @see    Pico::getRequestUrl()
   * @param  string &$url part of the URL describing the requested contents
   * @return void
   */
  public function onRequestUrl(&$url)
  {
    // if hash match, handle request
    if (substr($url, 0, strlen($this->hash)) === $this->hash) {
      // check user rights
      if (!$this->setRights()) {
        $url = '403';
        header('HTTP/1.1 403 Forbidden');
        exit;
      }
      $method = $_SERVER['REQUEST_METHOD'];
      switch($method) {
        case 'POST':
          $save = $this->createFile();
          if ($save) {
            echo 'ok';
          } else {
            header('HTTP/1.1 400 Bad Request');
            echo $this->info;
          }
          break;
        case 'PUT':
          $save = $this->editFile();
          if ($save) {
            echo 'ok';
          } else {
            header('HTTP/1.1 400 Bad Request');
            echo $this->info;
          }
          break;
        case 'DELETE':
          $save = $this->deleteFile();
          if ($save) {
            echo 'ok';
          } else {
            header('HTTP/1.1 400 Bad Request');
            echo $this->info;
          }
          break;
        case 'GET':
          // read file content and output
          if (file_exists(CONTENT_DIR . $_GET['path'] . CONTENT_EXT)) {
            readFile(CONTENT_DIR . $_GET['path'] . CONTENT_EXT);
          } else {
            echo $this->formatContent(file_get_contents(CONTENT_DIR . $this->template));
          }
          break;
      }
      exit; // stop everything!
    }
  }

  private function setRights()
  {
    if (class_exists('PicoUsers')) {
      $PicoUsers = $this->getPlugin('PicoUsers');
      $this->canEdit = $PicoUsers->hasRight('editor/edit');
      $this->canAdmin = $PicoUsers->hasRight('editor/admin');
      if ($this->canEdit || $this->canAdmin) {
        return true;
      }
    }
    return false;
  }

  private function createFile()
  {
    // we have a request, let's save it to file
    $payload = json_decode(file_get_contents('php://input'));
    if (!isset($payload)) {
      $this->info = 'No input data';
      return false;
    }
    $fileName = strtolower($payload->path) . CONTENT_EXT;
    if (file_exists(CONTENT_DIR . $fileName)) {
      $this->info = 'File exist';
      return false;
    }
    if (
      strlen($fileName) > strlen(CONTENT_EXT) &&
      file_put_contents(CONTENT_DIR . $fileName, $payload->content)
    ) {
      return true;
    }
    return false;
  }

  private function editFile()
  {
    // we have a request, let's save it to file
    $payload = json_decode(file_get_contents('php://input'));
    if (!isset($payload)) {
      $this->info = 'No input data';
      return false;
    }
    $fileName = strtolower($payload->path) . CONTENT_EXT;
    if (!file_exists(CONTENT_DIR . $fileName)) {
      $this->info = 'File not exist';
      return false;
    }
    if (
      strlen($fileName) > strlen(CONTENT_EXT) &&
      file_put_contents(CONTENT_DIR . $fileName, $payload->content)
    ) {
      return true;
    }
    return false;
  }

  private function deleteFile()
  {
    $payload = json_decode(file_get_contents('php://input'));
    if (!isset($payload)) {
      $this->info = 'No input data';
      return false;
    }
    $fileName = strtolower($payload->path) . CONTENT_EXT;
    if (!file_exists(CONTENT_DIR . $fileName)) {
      $this->info = 'File not exist';
      return false;
    }
    $toName = ROOT_DIR . $this->deleteDir . date('Y-m-d-h-m-s');
    if (is_dir($toName)) {
      $toName += '0';
    }
    $toName = $toName . '/' . $fileName;
    if (!is_dir(dirname($toName))) {
      mkdir(dirname($toName), 0777, true);
    }
    if (rename(CONTENT_DIR . $fileName, $toName)) {
      return true;
    }
    $this->info = 'File not exist or unknown delete error';
    return false;
  }

  private function formatContent($content)
  {
    $fp = $this->fingerprint();
    $user = $_SESSION[$fp]['path'];
    return str_replace(['<user>', '<date>'], [basename($user), date('Y-m-d')], $content);
  }
  /**
   * Return session fingerprint hash.
   * @return string
   */
  private function fingerprint()
  {
    return hash('sha256', 'pico'
      .$_SERVER['HTTP_USER_AGENT']
      .$_SERVER['REMOTE_ADDR']
      .$_SERVER['SCRIPT_NAME']
      .session_id());
  }
}
