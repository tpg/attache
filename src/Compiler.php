<?php

declare(strict_types=1);

namespace TPG\Attache;

use Illuminate\Support\Arr;
use TPG\Attache\Contracts\CompilerInterface;
use TPG\Attache\Exceptions\ConfigurationException;

class Compiler implements CompilerInterface
{
    public function __construct(protected Server $server, protected string $releaseId)
    {
        //
    }

    public function getCompiledScripts(string $step, string $subStep): array
    {
        $lines = $this->server->scripts($step, $subStep);

        if (count($lines) === 0) {
            return [];
        }

        return $this->compile($lines);
    }

    protected function compile(array $lines): array
    {
        $script = array_map(function ($line) {
            preg_match_all(
                '/@(?<tag>[a-z]+?)(:(?<param>[a-z]+?))?(?=\s|\}|$)/',
                $line,
                $matches,
                PREG_SET_ORDER,
                0
            );

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

        return $script;
    }

    protected function tagValues(array $tags): array
    {
        $results = [];

        foreach ($tags as $tag) {
            $tagName = $tag['tag'].($tag['param'] ? ':'.$tag['param'] : '');

            switch ($tag['tag']) {
                case 'php':
                    $value = $this->server->phpBin();
                    break;
                case 'composer':
                    $value = $this->server->composerBin();
                    break;
                case 'root':
                    $value = $this->server->rootPath();
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
}
