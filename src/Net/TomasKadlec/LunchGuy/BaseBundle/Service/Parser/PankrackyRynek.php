<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

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
        'pondělí' => 'monday',
        'úterý' => 'tuesday',
        'středa' => 'wednesday',
        'čtvrtek' => 'thursday',
        'pátek' => 'friday',
        'sobota' => 'saturday',
        'neděle' => 'sunday',
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

    public function parse($format, $data, $charset = 'UTF-8')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $data = $this
            ->getCrawler($data, $charset)
            ->filter(static::$selector)
            ->each(function (Crawler $node) {
                return $node->children()->each(function (Crawler $child) {
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
        $data = array_filter($data[0], function ($el) {
            return !preg_match('/^\s*$/ui', $el);
        });

        $data = array_reduce($data, function ($acc, $el) {
            if ($acc->processing &&
                preg_match('/^\w+? (\d+\.\d+\.)/ui', $el) &&
                !in_array(strtolower(preg_replace('/^(\w+?) \d+\.\d+\./ui', '\1', $el)), array_keys($this->days))) {
                $acc->processing = false;
                return $acc;
            }

            if (!$acc->processing && mb_strtolower(preg_replace('/^(\w+?) \d+\.\d+\./ui', '\1', $el)) == array_flip($this->days)[mb_strtolower(date('l'))]) {
                $acc->processing = true;
                return $acc;
            }

            if ($acc->processing) {
                foreach (explode('<br>', $el) as $k => $food) {
                    if (strpos($food, 'STÁLÁ NABÍDKA') > 0) {
                        continue;
                    }

                    if (empty($food)) {
                        continue;
                    }

                    $price = intval(preg_replace("/.*?(\d+)(,-|\s*Kč).*/ui", '\1', $food));
                    $food = preg_replace("/(\d+)(,-|\s*Kč).*/ui", '', $food);
                    $food = preg_replace('/\((\d+\s*,)*(\s*\d+\s*)*\)\s*$/', '', $food);
                    $food = str_replace('• ', '', $food);
                    $key = preg_match('/polévk|\bkrém\b/ui', $food) ? self::KEY_SOUPS : self::KEY_MAIN;
                    $acc->menu[$key][] = [$food, $price];
                }
            }

            return $acc;
        }, (object)['processing' => false, 'menu' => []]);

        return $data->menu;
    }
}