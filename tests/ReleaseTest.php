<?php

declare(strict_types=1);

namespace TPG\Attache\Tests;

use TPG\Attache\Release;

class ReleaseTest extends TestCase
{
    /**
     * @test
     **/
    public function it_can_parse_release_data()
    {
        $data = implode("\n", [
            'ATTACHE-SCRIPT',
            '20210101010101',
            '20210101010102',
            '20210101010103',
            'ATTACHE-DELIM',
            'live -> /opt/tpg/attache/releases/20210101010103',
            'ATTACHE-SCRIPT',
        ]);

        $release = (new Release())->parse($data);

        self::assertSame([
            '20210101010101',
            '20210101010102',
            '20210101010103',
        ], $release->available());
        self::assertSame('20210101010103', $release->active());
    }
}
