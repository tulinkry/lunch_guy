<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 21/05/2018
 * Time: 12:36
 */

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ExpressGrill
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class ExpressGrill extends AbstractParser
{
    protected static $selector = 'body section:nth-child(2) p';

    /** @inheritdoc */
    public function parse($format, $data, $charset = 'UTF-8') {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $data = $this
            ->getCrawler($data, $charset)
            ->filter(static::$selector)
            ->each(function (Crawler $node) {
                if((preg_match_all('/^(\d+\.\s*)?(.*)\s+(\d+kč)$/', $node->text(),$matches))) {
                    return [$matches[2][0], $matches[3][0]];
                }
                return [];
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
            if(empty($row[0]) || empty($row[1])) {
                continue;
            }

            if(preg_match('/polévka/ui', $row[0])) {
                $row[0] = preg_replace('/polévka:\s*/ui', '', $row[0]);
                $key = static::KEY_SOUPS;
            } else {
                $key = static::KEY_MAIN;
            }

            if ($key !== null && !empty($row[0]) && mb_strlen(trim($row[0])) > 1) {
                $result[$key][] = [
                    $this->mb_ucfirst(trim($row[0]), 'UTF-8'),
                    (!empty($row[1]) ? 0 + intval($row[1]) : '-')
                ];
            }
        }

        return $result;
    }

    public function mb_ucfirst($string, $encoding)
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    /** @inheritdoc */
    public function isSupported($format)
    {
        return ($format == 'expressgrill');
    }

    /** @inheritdoc */
    public function supports()
    {
        return ['expressgrill'];
    }
}