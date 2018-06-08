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
    public function isSupported($format)
    {
        return ($format == 'puzzle');
    }

    public function supports()
    {
        return ['puzzle'];
    }

    public function mb_ucfirst($string, $encoding)
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    public function parse($format, $data, $charset = 'UTF-8')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $crawler = $this
            ->getCrawler($data, $charset);
        $standard = $crawler
            ->filter('.section-catalog .pizza-builder__wrap-check .pizza-builder__item')
            ->each(function (Crawler $node) {
                return $node->children()->each(function (Crawler $child) {
                    return mb_strtolower($child->text());
                });
            });
        $daily = $crawler
            ->filter('#wrapper section .row + .row div')
            ->each(function (Crawler $node) {
                return $node->children()->each(function (Crawler $child) {
                    return [ $child->nodeName() === 'h5' ? 'key' : 'food', $child->text() ];
                })[0];
            });
        foreach($daily as $i => &$food) {
            if(empty($food[1])) {
                unset($daily[$i]);
                continue;
            }
            if($food[0] === 'key') {
                $key = $food[1];
                unset($daily[$i]);
                continue;
            }
            if(isset($key) && $key !== null && $food[0] !== 'key') {
                $food[2] = $key; // type
                if(preg_match_all('/^\s*(.*?)\s+(\(?(\d+[a-z]?\s*,)*(\s*\d+[a-z]?)?\)?\s+)?(\d+)\s*Kč\s*$/ui', $food[1], $matches)) {
                    $food[1] = $matches[1][0]; // food
                    $food[3] = $matches[5][0]; // price
                } else {
                    unset($daily[$i]);
                }
            }
        }
        return $this->process(array_merge($daily, $standard));
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
            $food = $this->mb_ucfirst($food, 'UTF-8');
            $price = intval($row[3]);

            $key = ucfirst(trim($row[2]));

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