<?php

namespace Test\Cully\Ssh;

use Cully\Ssh\Command;

class CommandTest extends \PHPUnit_Framework_TestCase {
    private $session;

    public function setUp() {
        /*
         * Create an ssh connection
         */

        $session = ssh2_connect(getenv("SSH_HOST"), getenv("SSH_PORT"), array('hostkey'=>'ssh-rsa')) or die("Couldn't connect.\n");

        ssh2_auth_agent($session, getenv("SSH_USER")) or die("Couldn't authenticate. You might need to: eval `ssh-agent -s` && ssh-add\n");

        $this->session = $session;
    }

    public function testSuccessExitStatus() {
        $command = new Command($this->session);

        $command->exec("ls");

        $this->assertEquals(0, $command->getExitStatus());
    }

    public function testGetCommand() {
        $command = new Command($this->session);

        $command->exec("ls");

        $this->assertEquals("ls", $command->getCommand());
    }

    public function testSuccess() {
        $command = new Command($this->session);

        $command->exec("ls");

        $this->assertTrue($command->success());
    }

    public function testDidntFail() {
        $command = new Command($this->session);

        $command->exec("ls");

        $this->assertFalse($command->failure());
    }

    public function testHasRun() {
        $command = new Command($this->session);

        $command->exec("ls");

        $this->assertTrue($command->hasRun());
    }

    public function testHasntRun() {
        $command = new Command($this->session);

        $this->assertFalse($command->hasRun());
    }

    public function testNoFailureWhenNotRun() {
        $command = new Command($this->session);

        $this->assertFalse($command->failure());
    }

    public function testNoSuccessWhenNotRun() {
        $command = new Command($this->session);

        $this->assertFalse($command->success());
    }

    public function testAssertFailExitStatus() {
        $command = new Command($this->session);

        $command->exec("ls a/path/that/hopefully/doesnt/exist/if/it/does/craziness");

        $this->assertNotEquals(0, $command->getExitStatus());
    }

    public function testFailure() {
        $command = new Command($this->session);

        $command->exec("ls a/path/that/hopefully/doesnt/exist/if/it/does/craziness");

        $this->assertTrue($command->failure());
    }

    public function testDidntSucceed() {
        $command = new Command($this->session);

        $command->exec("ls a/path/that/hopefully/doesnt/exist/if/it/does/craziness");

        $this->assertFalse($command->success());
    }

    public function testOutput() {
        $command = new Command($this->session);

        $command->exec("ls");

        $this->assertNotEmpty($command->getOutput());
    }

    public function testNoOutput() {
        $command = new Command($this->session);

        $command->exec("ls a/path/that/hopefully/doesnt/exist/if/it/does/craziness");

        $this->assertEmpty($command->getOutput());
    }

    public function testError() {
        $command = new Command($this->session);

        $command->exec("ls a/path/that/hopefully/doesnt/exist/if/it/does/craziness");

        $this->assertNotEmpty($command->getError());
    }

    public function testNoError() {
        $command = new Command($this->session);

        $command->exec("ls");

        $this->assertEmpty($command->getError());
    }
}
