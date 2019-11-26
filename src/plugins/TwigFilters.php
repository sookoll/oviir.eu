<?php

/**
 * Pico Custom Twig Filters
 *
 * https://gist.github.com/james2doyle/6629712
 * http://twig.sensiolabs.org/doc/extensions/text.html
 */
class TwigFilters extends AbstractPicoPlugin
{
  const API_VERSION = 2;

  /**
   * Triggered after Pico has read its configuration
   *
   * @see    Pico::getConfig()
   * @param  array &$config array of config variables
   * @return void
   */
  public function onConfigLoaded(array &$config)
  {
    $this->devMode = @$config['dev_mode'];
  }

  /**
   * Add various twig variables.
   *
   * Triggered before Pico renders the page
   *
   * @see DummyPlugin::onPageRendered()
   * @param string &$templateName  file name of the template
   * @param array  &$twigVariables template variables
   * @return void
   */
  public function onPageRendering(&$templateName, array &$twigVariables)
  {
      $twigVariables['jspath'] = $this->devMode ? 'js' : 'js-min';
  }

  /**
   * Triggered when Pico registers the twig template engine
   *
   * @see Pico::getTwig()
   *
   * @param Twig_Environment &$twig Twig instance
   *
   * @return void
   */
  public function onTwigRegistered(Twig_Environment &$twig)
  {
    $twig->addFilter(new Twig_SimpleFilter('base64_encode', function($string) {
      return base64_encode($string);
    }));
  }
}
