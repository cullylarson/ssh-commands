<?php

namespace Cully\Ssh;

use Cully\ICommand;

class Command implements ICommand {
    /**
     * @var resource
     */
    private $session;

    /**
     * @var string
     */
    private $lastCommand;
    /**
     * @var int|null
     */
    private $lastExitStatus;
    /**
     * @var string
     */
    private $lastErrorOutput;
    /**
     * @var string
     */
    private $lastStandardOutput;
    /**
     * @var bool
     */
    private $_hasRun = false;

    /**
     * @param resource $session
     */
    public function __construct($session) {
        $this->session = $session;
    }

    public function exec($command, $cwd=null, array $env=[]) {
        $this->execTerm($command, $cwd, null, $env);
    }

    /**
     * These parameters match those passed to the ssh2_exec function, except for the second
     * parameter, $cwd.
     *
     * @param $command
     * @param string|null $cwd
     * @param string|null $pty
     * @param array $env
     * @param int $width
     * @param int $height
     * @param int $width_height_type
     */
    public function execTerm($command, $cwd=null, $pty=null, array $env=[], $width=80, $height=25, $width_height_type = SSH2_TERM_UNIT_CHARS) {
        $commandAugmented = $command;
        // if we need to change the cwd
        if( $cwd !== null ) {
            $cwdEscaped = escapeshellarg($cwd);
            $commandAugmented = "cd {$cwdEscaped}; {$commandAugmented}";
        }
        // tack on the exit status so we can get it later
        $commandAugmented .= ';echo -en "\n$?"';

        // execute the command
        $stream = ssh2_exec($this->session, $commandAugmented, $pty, $env, $width, $height, $width_height_type);

        /*
         * Get the output
         */

        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        $standardStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);

        stream_set_blocking($errorStream, true);
        stream_set_blocking($standardStream, true);

        $errorOutput = stream_get_contents($errorStream);
        $standardOutputRaw = stream_get_contents($standardStream);
        $standardOutput = $standardOutputRaw;

        fclose( $stream );
        fclose( $errorStream );
        fclose( $standardStream );

        /*
         * Extract the exit status we tacked on earlier.
         */

        $exitStatus = null;
        if( preg_match( "/^(.*)\n(0|-?[1-9][0-9]*)$/s", $standardOutputRaw, $matches ) ) {
            $exitStatus = (int) $matches[2];
            $standardOutput = $matches[1];
        }

        /*
         * Finish up.
         */

        $this->_hasRun = true;
        $this->lastCommand = $command;
        $this->lastExitStatus = $exitStatus;
        $this->lastStandardOutput = $standardOutput;
        $this->lastErrorOutput = $errorOutput;
    }

    /**
     * @return bool
     */
    public function hasRun() {
        return $this->_hasRun;
    }

    /**
     * Whether the last command succeeded.  Will return FALSE if no
     * command has been executed (we haven't succeeded because we
     * haven't tried).
     *
     * A successful command is one that has an exit code of 0.
     *
     * @return bool
     */
    public function success() {
        return ($this->hasRun() && $this->getExitStatus() === 0);
    }

    /**
     * Whether the last command failed.  This will return FALSE if no
     * command has been executed (we haven't failed because we haven't
     * tried).
     *
     * @return bool
     */
    public function failure() {
        // if haven't run, then we haven't failed
        if(!$this->hasRun()) return false;
        // otherwise, if we didn't succeed, we failed
        else return !$this->success();
    }

    /**
     * The last command executed.
     *
     * @return string
     */
    public function getCommand() {
        return $this->lastCommand;
    }

    /**
     * Will be null if a command hasn't been executed, or if the
     * last executed command didn't return an exit code.
     *
     * @return int|null
     */
    public function getExitStatus() {
        return $this->lastExitStatus;
    }

    /**
     * @return string
     */
    public function getOutput() {
        return $this->lastStandardOutput;
    }

    /**
     * @return string
     */
    public function getError() {
        return $this->lastErrorOutput;
    }
}
