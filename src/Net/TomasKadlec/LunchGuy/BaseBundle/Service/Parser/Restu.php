<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

/**
 * Class Restu
 *
 * Parser implementation for Restu
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Restu extends AbstractParser
{

    protected static $selector = '.restaurant-menu-list .menu-section';
    protected $months = array(
        "ledna",
        "února",
        "března",
        "dubna",
        "května",
        "června",
        "července",
        "srpna",
        "září",
        "října",
        "listopadu",
        "prosince"
    );

    public function isSupported($format)
    {
        return ($format == 'restu');
    }

    public function supports()
    {
        return ['restu'];
    }

    /**
     * @inheritdoc
     */
    public function parse($format, $data, $charset = 'utf-8')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $data = $this
            ->getCrawler($data, $charset)
            ->filter(static::$selector)
            ->each(function ($node) {
                $dateText = trim($node->filter('h4')->text());
                $dateTextSplit = explode(' ', $dateText);
                if (count($dateTextSplit) !== 3) {
                    return []; // date not available
                }

                $day = $dateTextSplit[1];
                $month = array_search($dateTextSplit[2], $this->months);

                if ($month === false) {
                    return []; // date not available
                }

                $date = \DateTime::createFromFormat(
                    'j.n',
                    $day . ($month + 1)
                );

                if ($date->format('j.n.Y') !== (new \DateTime())->format('j.n.Y')) {
                    return []; // the current day is missing
                }

                $results = [];

                $key = $food = $price = null;
                foreach ($node->filter('li.c-menu-item ul li') as $tmp) {
                    if (strpos($tmp->getAttribute('class'), 'menu-section__item-desc') !== false) {
                        $food = trim($tmp->nodeValue);
                    } else if (strpos($tmp->getAttribute('class'), 'menu-section__item-price') !== false) {
                        $price = intval($tmp->nodeValue);
                    }

                    if (!is_null($food) && !is_null($price)) {
                        $key = static::KEY_MAIN;
                        if (preg_match('/polévk(a|y)/ui', $food)) {
                            $key = static::KEY_SOUPS;
                        } else if (preg_match('/pizza/ui', $food)) {
                            $key = 'Pizza';
                        }

                        $results[$key][] = array($food, $price);
                        $food = $price = null;
                    }
                }

                return $results;

            });

        $data = array_filter($data, function ($row) {
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
    protected function process($data)
    {
        return $data;
    }
}