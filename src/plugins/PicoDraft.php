<?php
/**
 * A content editor plugin for Pico 2, using ContentTools.
 *
 * Supports PicoUsers plugin for authentification
 * {@link https://github.com/nliautaud/pico-users}
 *
 * @author  Nicolas Liautaud
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link    https://github.com/nliautaud/pico-content-editor
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

  protected $canAdd = false;
  protected $canEdit = false;
  protected $canAdmin = false;
  protected $hash = null;

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
    // check user rights
    if (!$this->setRights()) {
      $url = '403';
      header('HTTP/1.1 403 Forbidden');
      exit;
    }
    // if hash match, handle request
    if (substr($url, 0, strlen($this->hash)) === $this->hash) {
      var_dump($_GET['filename'], CONTENT_DIR . $_GET['filename'] . CONTENT_EXT);
      if ($_GET['filename'] && file_exists(CONTENT_DIR . $_GET['filename'] . CONTENT_EXT)) {
        // read file content and output
        readfile(CONTENT_DIR . $_GET['filename'] . CONTENT_EXT);
      }
      // getting the payload, decoding it and saving to file system inside content dir
      if ($_POST['payload'] && $this->saveFile($_POST['payload'])) {
        echo '1';
      }
      exit; // stop everything!
    }
    var_dump($url);
  }

  private function setRights() {
    if (class_exists('PicoUsers')) {
      $PicoUsers = $this->getPlugin('PicoUsers');
      $this->canAdd = $PicoUsers->hasRight('editor/add');
      $this->canEdit = $PicoUsers->hasRight('editor/edit');
      $this->canAdmin = $PicoUsers->hasRight('editor/admin');
      if ($this->canAdd || $this->canEdit || $this->canAdmin) {
        return true;
      }
    }
    return false;
  }

  private function saveFile($payload) {
    // we have a request from Draft, let's save it to file
    $payload = json_decode($payload);
    $fileName = strtolower($payload['filename']) . CONTENT_EXT;
    // edit
    if (
      file_exists(CONTENT_DIR . $fileName) && $this->canEdit &&
      @file_put_contents(CONTENT_DIR . $fileName, $payload['content'])
    ) {
      return true;
    }
    // add
    if (
      !file_exists(CONTENT_DIR . $fileName) && $this->$canAdd &&
      @file_put_contents(CONTENT_DIR . $fileName, $payload['content'])
    ) {
      return true;
    }
    return false;
  }
}
