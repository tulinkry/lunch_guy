<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

/**
 * Class Momento
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Momento extends AbstractParser
{

    protected static $selector = 'div.list-content div.food-list-block div.show table + table tr';

    public function isSupported($format)
    {
        return ($format == 'momento');
    }

    public function supports()
    {
        return [ 'momento' ];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data) {
        $result = [];

        foreach ($data as $food) {
            $key = null;
            if (empty($food))
                continue;


            if(count($food) !== 5) {
                continue;
            }

            if (preg_match('/Polévka/ui', $food[0]))
                $key = static::KEY_SOUPS;
            else if (preg_match('/(Menu)|(Minutka)/ui', $food[0]))
                $key = static::KEY_MAIN;
            else if (preg_match('/(Teplý pult)/ui', $food[0]))
                $key = 'Teplý pult';
            else if (preg_match('/(Zeleninový talíř|Salát)/ui', $food[0]))
                $key = static::KEY_SALADS;

            if ($key !== null && isset($food[1]) && isset($food[4])) {
                $result[$key][] = [
                    $food[2],
                    intval($food[4])
                ];
            }
        }
        return $result;
    }
}

//Zeleninový salát se smaženými kuřecími stripsy 1,3,7,12