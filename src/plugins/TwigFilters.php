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
