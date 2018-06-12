<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class SimiGastro
 *
 * Parser implementation for SimiGastro (Cesta Casem)
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class CervenaCibule extends AbstractParser
{
    const HEADING = 1;
    const FOOD = 2;

    protected static $selector = 'div.content > *';

    public function isSupported($format)
    {
        return ($format == 'ccibule');
    }

    public function supports()
    {
        return [ 'ccibule' ];
    }

    public function parse($format, $data, $charset = 'UTF-8')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $data = $this
            ->getCrawler($data, $charset)
            ->filter(static::$selector)
            ->each(function (Crawler $node) {
                if (in_array($node->nodeName(), ['h1', 'h2', 'h3', 'h4']))
                    return (object) [ 'type' => self::HEADING, 'text' => trim($node->text()) ];
                else if ($node->nodeName() == 'p')
                    return (object) [ 'type' => self::FOOD, 'text' => trim($node->text()) ];
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
             if(empty($row->text) || preg_match("/^\s*$/u", $row->text))
                continue;

            if ($row->type === self::HEADING) {
                if (preg_match('/Polévka/', $row->text))
                    $key = static::KEY_SOUPS;
                else if (preg_match('/Speciální nabídka/', $row->text))
                    $key = 'Speciální nabídka';
                else if (preg_match('/Hlavní jídlo/', $row->text))
                    $key = static::KEY_MAIN;
                else
                    $target = null;
                //continue;
            } else if (preg_match('/vyhrazena/', $row->text))
                $key = null;
            else if ($key !== null) {
                $result[$key][] = [
                    preg_replace("/\d+(-|,-|\s*Kč).*/", '', trim($row->text)),
                    (!empty($row->text) ? intval(preg_replace("/.*?(\d+)(-|,-|\s*Kč)/", '$1', trim($row->text))) : '-')
                ];
            }
        }
        return $result;
    }
}
