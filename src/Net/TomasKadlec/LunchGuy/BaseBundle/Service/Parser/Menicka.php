<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

/**
 * Class Menicka
 *
 * Parser implementation for 
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Menicka extends AbstractParser
{

    protected static $selector = 'div.menicka';

    public function isSupported($format)
    {
        return ($format == 'menicka');
    }

    public function supports()
    {
        return [ 'menicka' ];
    }

    /**
     * @inheritdoc
     */
    public function parse($format, $data, $charset = 'windows-1250')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $data = $this
            ->getCrawler($data, 'windows-1250')
            ->filter(static::$selector)
            ->each(function($node) {
                $date = \DateTime::createFromFormat(
                    'j.n.Y',
                    trim(
                        explode(
                            ' ',
                            $node->filter('div.datum')->text()
                        )[1]
                    )
                );

                if($date->format('j.n.Y') === (new \DateTime())->format('j.n.Y')) {
                    $results = [];

                    $key = $food = $price = null;
                    foreach($node->filter('div') as $tmp) {
                        if(strpos($tmp->getAttribute('class'), 'nabidka_1') !== false ||
                           strpos($tmp->getAttribute('class'), 'nabidka_2') !== false) {
                            $food = trim($tmp->nodeValue);
                            // alergens
                            $food = preg_replace('/\(?(\s*\d+[a-z]?\s*,)*(\s*\d+[a-z]?\s*)*\)?\s*$/', '', $food);
                            $food = preg_replace('/\/?(\s*\d+[a-z]?\s*,)*(\s*\d+[a-z]?\s*)*\/?\s*$/', '', $food);
                            // other stuff
                            $food = preg_replace('/\(MB\s+[^)]*\)\s*$/', '', $food);
                            $food = preg_replace('/\(MB\s*$/', '', $food);
                            // weight
                            $food = preg_replace('/\d+g\s*-\s*/', '', $food);
                            // weight
                            $food = preg_replace('/^\s*\d+g\s*/', '', $food);
                            $key = strpos($tmp->getAttribute('class'), 'capitalize') === false ? static::KEY_MAIN : static::KEY_SOUPS;
                        } else if (strpos($tmp->getAttribute('class'), 'cena') !== false) {
                            $price = intval($tmp->nodeValue);
                        }

                        if(!is_null($food) && !is_null($price)) {
                            $results[$key][] = array($food, $price);
                            $food = $price = null;
                        }
                    }

                    return $results;
                }
            });

        $data = array_filter($data, function($row) {
            return !empty($row);
        });
        return empty($data) ? $data : $data[0];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data) {
        return $data;
    }
}