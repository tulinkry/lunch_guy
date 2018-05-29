<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Puzzle
 *
 * Parser implementation for Puzzle Salads
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Puzzle extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '.section-catalog .pizza-builder__wrap-check .pizza-builder__item';

    public function isSupported($format)
    {
        return ($format == 'puzzle');
    }

    public function supports()
    {
        return ['puzzle'];
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
                    return $child->text();
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
        $key = null;
        $result = [];

        foreach ($data as $row) {

            if (empty($row) || count($row) < 3)
                continue;

            $food = trim($row[1]);
            $food = preg_replace('/\s*\d+\s*\.\s*/', '', $food);
            $food = ucfirst(trim($row[2])) . ' - ' . $food;
            $price = intval($row[3]);

            $key = static::KEY_MAIN;

            if ($key !== null) {
                $result[$key][] = [
                    $food,
                    $price
                ];
            }
        }
        return $result;
    }

}

//Zeleninový salát se smaženými kuřecími stripsy 1,3,7,12