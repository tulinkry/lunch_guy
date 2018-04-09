<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use Symfony\Component\DomCrawler\Crawler;

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

//Zeleninový salát se smaženými kuřecími stripsy 1,3,7,12