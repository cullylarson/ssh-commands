# SSH Commands

A library for executing remote commands, via SSH, with support for exit codes,
standard output, and error output.  It fills in a gap present in the PECL ssh2 library.

## Install

```
curl -s http://getcomposer.org/installer | php
php composer.phar require cullylarson/ssh-commands
```

## Usage

1. Create an ssh2 connection resource.

    ```
    <?php
    
    $session = ssh2_connect("localhost", 22, array('hostkey'=>'ssh-rsa')) or die("Couldn't connect.");
    
    ssh2_auth_agent($session, "my_username") or die("Couldn't authenticate.");
    ```

    If you're using RSA, and you got an auth error, you might need to run this command:

    ```
    $ eval `ssh-agent -s` && ssh-add
    ```

1. Create an instance of `Cully\Ssh\Command`, passing your connection resource to the constructor.

    ```
    <?php
    
    $command = new Cully\Ssh\Command($session);
    ```

1. Execute your command.  The `exec` function takes all of the parameters that [ssh2_exec](http://php.net/ssh2_exec) takes,
except for the connection resource (since it was passed in the construtor).

    ```
    <?php
    
    $command->exec("ls");
    ```

1.  At this point, you have access to a few results:

    ```
    <?php
    
    $command->success();       // whether the command succeeded
    $command->failure();       // whether the command failed
    $command->getCommand();    // the last command executed
    $command->getExitStatus(); // the exit status of the last command executed
    $command->getOutput();     // the standard output from the last command
    $command->getError();      // the error output from the last command
    ```
