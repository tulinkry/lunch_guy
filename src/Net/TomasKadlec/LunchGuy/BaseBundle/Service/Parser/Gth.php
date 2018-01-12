<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

/**
 * Class Gth
 *
 * Parser implementation for 
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Gth extends AbstractParser
{

    protected $filter = [];

    protected static $selector = 'div.sub_page ul.foodmenu li.food';

    public function isSupported($format)
    {
        return ($format == 'gth');
    }

    public function supports()
    {
        return [ 'gth' ];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data) {
        $result = [];

        foreach ($data as $row) {
            $key = null;
            if (empty($row))
                continue;

            $food = array_map(function($e) {
                return preg_replace('/^\s*(.*)\s*$/u', '$1', $e);
            }, array_filter(explode("\n", $row[0]), function($e) {
                return !preg_match('/^\s*$/u', $e);
            }));

            if (preg_match('/Polévka/', $food[4]))
                $key = 'Polévky';
            else if (preg_match('/(Menu)|(Minutka)/', $food[4]))
                $key = 'Hlavní jídla';
            else if (preg_match('/(Teplý pult)/', $food[4]))
                $key = 'Teplý pult';
            else if (preg_match('/Zeleninový talíř/', $food[4]))
                $key = 'Saláty';

            if ($key !== null) {
                $result[$key][] = [
                    $food[7],
                    intval($food[19])
                ];
            }
        }
        return $result;
    }

}