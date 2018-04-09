<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class Zomato extends AbstractParser {

    protected static $selector = '#menu-preview .tmi-groups .tmi-group .tmi-daily';

    public function isSupported($format) {
        return ($format == 'zomato');
    }

    public function supports() {
        return ['zomato'];
    }

    public function parse($format, $data, $charset = 'UTF-8') {
        if (!$this->isSupported($format))
            return new RuntimeException("Format {$format} is not supported.");
        $data = $this
                ->getCrawler($data, $charset)
                ->filter(static::$selector)
                ->each(function (Crawler $node) {
            return $node->text();
        });
        return $this->process($data);
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data) {
        $key = null;
        $result = [];

        foreach ($data as $row) {
            if (empty($row) || preg_match("/^\s*$/u", $row))
                continue;
            if (preg_match("/^\s*(\d+,?)+\s*\$/u", $row))
                continue;

            $row = preg_replace("/^\s*P\d+(.*)/ui", '$1', $row);
            $row = preg_replace("/^\s*M\d+(.*)/ui", '$1', $row);

            if (preg_match('/POLÉVKY/ui', $row)) {
                $key = static::KEY_SOUPS;
                continue;
            } else if (preg_match('/HLAVNÍ JÍDLA/ui', $row)) {
                $key = static::KEY_MAIN;
                continue;
            } else if (preg_match('/Salátky/ui', $row)) {
                $key = static::KEY_SALADS;
                continue;
            } else if (preg_match('/MOUČNÍK/ui', $row)) {
                $key = static::KEY_DESERTS;
                continue;
            }


            if ($key !== null) {
                $exploded = explode(" ", trim($row));
                $result[$key][] = [
                    trim(preg_replace("/\d+(,-|\s*Kč).*/ui", '', trim($row))),
                    (count($exploded) > 0 ? intval($exploded[count($exploded) - 1]) : '-')
                ];
            }
        }

        return $result;
    }

}
