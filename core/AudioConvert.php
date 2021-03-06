<?php
/**
 * $Id$
 *
 * Utility class wrapped around SoX to convert audio files between different formats.
 * Conversions is forked so the calls are non-blocking
 *
 * WARNING: sox on Ubuntu requires libsox-fmt-mp3
 *
 * WARNING: opusenc is available from Ubuntu 12.10, or requires installation from git
 */

//STATUS: wip

namespace cd;

class AudioConvert
{
    public static function toMp3($in_file, $out_file, $extra = '')
    {
        if (!file_exists($in_file))
            throw new \Exception ('input file not found: '.$in_file);

        // the "&" spawns this command in a new process
        $c = 'sox "'.$in_file.'" --type mp3 --comment "" '.$extra.' "'.$out_file.'" >/dev/null 2>/dev/null &';
        shell_exec($c);
    }

    public static function toOgg($in_file, $out_file, $extra = '')
    {
        if (!file_exists($in_file))
            throw new \Exception ('input file not found: '.$in_file);

        // the "&" spawns this command in a new process
        $c = 'sox "'.$in_file.'" --type ogg --comment "" '.$extra.' "'.$out_file.'" >/dev/null 2>/dev/null &';
        shell_exec($c);
    }

    public static function toWav($in_file, $out_file, $extra = '')
    {
        if (!file_exists($in_file))
            throw new \Exception ('input file not found: '.$in_file);

        // the "&" spawns this command in a new process
        $c = 'sox "'.$in_file.'" --type wav --comment "" '.$extra.' "'.$out_file.'" >/dev/null 2>/dev/null &';
        shell_exec($c);
    }

    public static function toOpus($in_file, $out_file)
    {
        if (!file_exists($in_file))
            throw new \Exception ('input file not found: '.$in_file);

        // the "&" spawns this command in a new process
        $c = 'opusenc "'.$in_file.'" "'.$out_file.'" >/dev/null 2>/dev/null &';
        shell_exec($c);
    }

}

?>
