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

1. Execute your command.  The `exec` function takes the command you want to execute (e.g. `ls`), and optionally, the current working directory (the folder from which you want to execute the command), and an array of environment variables to make available to the command.

    ```
    <?php
    
    $command->exec("ls");
    ```

1. Alternatively, you can execute commands using the `execTerm` function.  This exposes all of the parameters that [ssh2_exec](http://php.net/ssh2_exec) takes, except for the connection resource (since it was passed in the construtor), and `$cwd`.

    ```
    <?php
    
    $command->execTerm("ls");
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

## The `exec` Function

1. `$command` _(string)_ The command you want to execute (e.g. `ls`).

1. `$cwd` _(string)_ _(optional, default: null)_ The current working directory
(the folder you want to execute the command in).

1. `$env` _(array)_ _(optional, default: [])_ An array of environment variable
that you want to make available to the command.

## The `execTerm` Function

1. `$command` _(string)_ The command you want to execute (e.g. `ls`).

1. `$cwd` _(string)_ _(optional, default: null)_ The current working directory
(the folder you want to execute the command in).

1. The rest of the parameters are the same as those passed to [ssh2_exec](http://php.net/ssh2_exec).