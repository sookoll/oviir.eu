<?php
/**
 * A user register plugin for PicoUsers
 * {@link https://github.com/nliautaud/pico-users}
 *
 * @author  Mihkel Oviir
 * @license http://opensource.org/licenses/MIT The MIT License
 * @link    https://github.com/sookoll/pico-users-register
 * @link    http://picocms.org
 */
class PicoUsersRegister extends AbstractPicoPlugin
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
  protected $hash;
  protected $users;
  protected $configFile;
  protected $info = 'Unknown error';
  protected $group;

  /**
   * Triggered after Pico has read its configuration
   *
   * @see    Pico::getConfig()
   * @param  array &$config array of config variables
   * @return void
   */
  public function onConfigLoaded(array &$config)
  {
    $this->users = @$config['users'];
    $this->configFile = @$config['register']['users'];
    $this->hash = @$config['register']['hash'];
    $this->group = @$config['register']['group'];
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
      $method = $_SERVER['REQUEST_METHOD'];
      switch($method) {
        case 'POST':
          $save = $this->createUser();
          if ($save) {
            echo 'ok';
          } else {
            header('HTTP/1.1 400 Bad Request');
            echo $this->info;
          }
          break;
      }
      exit; // stop everything!
    }
  }

  private function createUser()
  {
    // we have a request, let's save it to file
    if (empty($_POST['username']) || empty($_POST['password'])) {
      $this->info = 'Missing username or password';
      return false;
    }
    if ($this->checkUsername($this->users, $_POST['username'])) {
      $this->info = 'Username already exist';
      return false;
    }
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    if ($this->setUser($this->group, $_POST['username'], $hash)) {
      return true;
    }
    return false;
  }

  /**
   * Return a given user data.
   * @param  string $name  the user path, like "foo/bar"
   * @return array  the user data
   */
  private function getUser($path)
  {
    $parts = explode('/', $path);
    $curr = $this->users;
    foreach ($parts as $part) {
      if (!isset($curr[$part])) {
        return false;
      }
      $curr = $curr[$part];
    }
    return array(
      'path' => $path,
      'hash' => $curr
    );
  }

  /**
   * Save a given user data.
   * @param  string $path  the user path, like "foo/bar"
   */
  private function setUser($path, $username, $hash)
  {
    $this->assignArrayByPath($this->users, $path . '/' . $username, $hash, '/');
    // validate
    if ($this->getUser($path . '/' . $username)) {
      $dumper = new \Symfony\Component\Yaml\Dumper();
      $new_yaml = $dumper->dump(['users' => $this->users], $this->getArrayDepth($this->users) + 2);
      if ($new_yaml && file_put_contents($this->configFile, $new_yaml)) {
        return true;
      }
    }
    return false;
  }

  private function checkUsername(array $arr, $key)
  {
    // is in base array?
    if (array_key_exists($key, $arr)) {
      return true;
    }
    // check arrays contained in this array
    foreach ($arr as $element) {
      if (is_array($element)) {
        if ($this->checkUsername($element, $key)) {
          return true;
        }
      }
    }
    return false;
  }

  private function assignArrayByPath(&$arr, $path, $value, $separator='.')
  {
    $keys = explode($separator, $path);
    foreach ($keys as $key) {
      $arr = &$arr[$key];
    }
    $arr = $value;
  }

  function getArrayDepth($array)
  {
    $depth = 0;
    $iteIte = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
    foreach ($iteIte as $ite) {
      $d = $iteIte->getDepth();
      $depth = $d > $depth ? $d : $depth;
    }
    return $depth;
  }
}
