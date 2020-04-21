<?php

namespace TPG\Attache;

use Illuminate\Support\Arr;
use TPG\Attache\Exceptions\ConfigurationException;

class ScriptCompiler
{
    /**
     * @var Server
     */
    protected Server $server;

    /**
     * ScriptCompiler constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function compile(array $lines): array
    {
        return array_map(function ($line) {
            preg_match_all('/@(?<tag>[a-z]+?)(?=\s|\}|$)/', $line, $matches);
            $tags = $this->tagValues(Arr::get($matches, 'tag'));

            foreach ($tags as $tag => $value) {
                $line = str_replace('@'.$tag, $value, $line);
            }

            return preg_replace('/\{\{\s?(.+?)\s?\}\}/', '$1', $line);
        }, $lines);
    }

    protected function tagValues(array $tags)
    {
        $values = array_map(function ($tag) {
            switch ($tag) {
                case 'php':
                    return $this->server->phpBin();
                case 'composer':
                    return $this->server->composerBin();
                case 'release':
                    return $this->server->latestReleaseId();
                default:
                    throw new ConfigurationException('No such tag @'.$tag);
            }
        }, $tags);

        return array_combine($tags, $values);
    }
}
