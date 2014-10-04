<?php

namespace cd;

class HashesTest extends \PHPUnit_Framework_TestCase
{
    function VerifyHashes($hashes, $s)
    {
        foreach ($hashes as $hash => $val)
        {
            switch ($hash) {
            case 'crc32':
                $chk = HashCrc32::fromString($s);
                break;
            case 'md5':
                $chk = HashMd5::fromString($s);
                break;
            case 'sha1':
                $chk = HashSha1::fromString($s);
                break;
            case 'sha256':
                $chk = HashSha256::fromString($s);
                break;
            case 'sha512':
                $chk = HashSha512::fromString($s);
                break;

            default:
                throw new \Exception ('unknown hash '.$hash);
            }

            $this->assertEquals($val, $chk);
        }
    }

    public function test1()
    {
        $hashes_empty = array(
        'crc32'  => '00000000',
        'md5'    => 'd41d8cd98f00b204e9800998ecf8427e',
        'sha1'   => 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
        'sha256' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
        'sha512' => 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e',
        );

        $this->VerifyHashes($hashes_empty, '');
    }

    public function test2()
    {
        $hashes_fox = array(
        'crc32'  => '61ee9d45',
        'md5'    => '9e107d9d372bb6826bd81d3542a419d6',
        'sha1'   => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12',
        'sha256' => 'd7a8fbb307d7809469ca9abcb0082e4f8d5651e46d3cdb762d02d0bf37c9e592',
        'sha512' => '07e547d9586f6a73f73fbac0435ed76951218fb7d0c8d788a309d785436bbb642e93a252a954f23912547d1e8a3b5ed6e1bfd7097821233fa0538f3db854fee6',
        );

        $this->VerifyHashes($hashes_fox, 'The quick brown fox jumps over the lazy dog');
    }
}
