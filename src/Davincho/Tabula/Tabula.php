<?php

/*
 * This file is part of tabulapdf-php-wrapper
 *
 * (c) https://github.com/davincho/tabulapdf-php-wrapper
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Davincho\Tabula;

use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

class Tabula
{
    /**
     * File to be converted (should be a PDF)
     * @var
     */
    private $file = null;

    /**
     * Path to jar file
     */
    private $jarArchive = __DIR__ . '/../../../lib/tabula-extractor-0.7.4-SNAPSHOT-jar-with-dependencies.jar';

    /**
     * Converter constructor.
     * @param null $file
     */
    public function __construct($file = null)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    public function parse($parameters = [], $file = null) {
        $inputFile = $file !== null ? $file : $this->file;
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        if($inputFile === null || !file_exists($inputFile) || !is_readable($inputFile)) {
            throw new InvalidArgumentException('File is null, not existent or not readable');
        }

        $finder = new ExecutableFinder();
        $binary = $finder->find('java');

        if($binary === null) {
            throw new RuntimeException('Could not find java on your system');
        }

        // Jar binary, with additional java option (see https://github.com/tabulapdf/tabula-java/issues/26)
        $arguments = ['-Xss2m', '-jar', $this->jarArchive, $inputFile];

        $processBuilder = new ProcessBuilder();
        $processBuilder->setPrefix($binary)
            ->setArguments(array_merge($arguments, $parameters));

        $process = $processBuilder->getProcess();
        $process->run();

        if(!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
