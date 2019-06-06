<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use DateTime;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class PankrackyRynek
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class PankrackyRynek extends AbstractParser
{

    protected $days = [
        'pondělí',
        'úterý',
        'středa',
        'čtvrtek',
        'pátek',
        'sobota',
        'neděle',
    ];

    protected static $selector = '#k2Container .itemFullText';


    public function isSupported($format)
    {
        return ($format == 'pankrackyrynek');
    }

    public function supports()
    {
        return ['pankrackyrynek'];
    }

    public function parse($format, $data, $charset = 'UTF-8') {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $data = $this
            ->getCrawler($data, $charset)
            ->filter(static::$selector)
            ->each(function (Crawler $node) {
                return $node->children()->each(function(Crawler $child) {
                    return $child->html();
                });
            });
        return $this->process($data);
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data)
    {
        $data = array_filter($data[0], function($el) {
            return !preg_match('/^\s*$/ui', $el);
        });


        $data = array_reduce($data, function($acc, $el) {
            if($acc->processing &&
                preg_match('/^\w+? (\d+\.\d+\.)/ui', $el) &&
                !in_array(strtolower(preg_replace('/^(\w+?) \d+\.\d+\./ui', '\1', $el)), $this->days)) {
                $acc->processing = false;
                return $acc;
            }

            if(!$acc->processing && preg_replace('/^\w+? (\d+\.\d+)/ui', '\1', $el) === date('j.n.')) {
                $acc->processing = true;
                return $acc;
            }

            if($acc->processing) {
                //print_r(explode('<br>', $el));
                foreach(explode('<br>', $el) as $k => $food) {
                    if(strpos($food, 'STÁLÁ NABÍDKA') > 0) {
                        continue;
                    }
                    $price = intval(preg_replace("/.*?(\d+)(,-|\s*Kč).*/ui", '\1', $food));
                    $food = preg_replace("/(\d+)(,-|\s*Kč).*/ui", '', $food);
                    $food = preg_replace('/\((\d+\s*,)*(\s*\d+\s*)*\)\s*$/', '', $food);
                    $acc->menu[preg_match('/polévk/', $food) ? self::KEY_SOUPS : self::KEY_MAIN][] = [$food, $price];
                }
            }

            return $acc;
        }, (object) [ 'processing' => false, 'menu' => []]);

        return $data->menu;
    }
}