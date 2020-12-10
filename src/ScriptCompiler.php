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
     * @var string|null
     */
    protected ?string $releaseId;

    /**
     * ScriptCompiler constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function compile(array $lines, string $path = null): array
    {
        $script = array_map(function ($line) {
            preg_match_all('/@(?<tag>[a-z]+?)(:(?<param>[a-z]+?))?(?=\s|\}|$)/', $line, $matches, PREG_SET_ORDER, 0);

            $tags = $this->tagValues(array_map(static function ($match) {
                return [
                    'tag' => Arr::get($match, 'tag'),
                    'param' => Arr::get($match, 'param'),
                ];
            }, $matches));

            foreach ($tags as $tag => $value) {
                $line = str_replace('@'.$tag, $value, $line);
            }

            return preg_replace('/\{\{\s?(.+?)\s?\}\}/', '$1', $line);
        }, $lines);

        return $this->setPath($script, $path);
    }

    public function setReleaseId(string $releaseId = null): self
    {
        $this->releaseId = $releaseId;

        return $this;
    }

    protected function tagValues(array $tags): array
    {
        $results = [];

        foreach ($tags as $tag) {
            $tagName = $tag['tag'].($tag['param'] ? ':'.$tag['param'] : '');
            $value = '';

            switch ($tag['tag']) {
                case 'php':
                    $value = $this->server->phpBin();
                    break;
                case 'composer':
                    $value = $this->server->composerBin();
                    break;
                case 'root':
                    $value = $this->server->root();
                    break;
                case 'path':
                    $value = $this->server->path($tag['param']);
                    break;
                case 'release':
                    $value = $this->releaseId;
                    break;
                case 'artisan':
                    $value = $this->server->phpBin().' '.$this->server->path('serve').'/artisan';
                    break;
                default:
                    throw new ConfigurationException('No such tag @'.$tag['tag']);
            }

            $results[$tagName] = $value;
        }

        return $results;
    }

    protected function setPath(array $scripts, string $path = null): array
    {
        if ($path) {
            return array_merge([
                'cd '.$path,
            ], $scripts);
        }

        return $scripts;
    }
}
