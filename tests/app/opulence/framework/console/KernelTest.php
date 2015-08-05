<?php
/**
 * Copyright (C) 2015 David Young
 *
 * Tests the console kernel
 */
namespace Opulence\Framework\Console;
use Monolog\Logger;
use Opulence\Console\Commands\CommandCollection;
use Opulence\Console\Commands\Compilers\Compiler as CommandCompiler;
use Opulence\Console\Requests\Parsers\StringParser;
use Opulence\Console\Requests\Tokenizers\StringTokenizer;
use Opulence\Console\Responses\Compilers\Compiler as ResponseCompiler;
use Opulence\Console\Responses\Compilers\Lexers\Lexer;
use Opulence\Console\Responses\Compilers\Parsers\Parser;
use Opulence\Tests\Applications\Mocks\MonologHandler;
use Opulence\Tests\Console\Commands\Mocks\HappyHolidayCommand;
use Opulence\Tests\Console\Commands\Mocks\SimpleCommand;
use Opulence\Tests\Console\Responses\Mocks\Response;

class KernelTest extends \PHPUnit_Framework_TestCase
{
    /** @var CommandCompiler The command compiler */
    private $compiler = null;
    /** @var CommandCollection The list of commands */
    private $commands = null;
    /** @var StringParser The request parser */
    private $parser = null;
    /** @var Response The response to use in tests */
    private $response = null;
    /** @var Kernel The kernel to use in tests */
    private $kernel = null;

    /**
     * Sets up the tests
     */
    public function setUp()
    {
        $logger = new Logger("application");
        $logger->pushHandler(new MonologHandler());
        $this->compiler = new CommandCompiler();
        $this->commands = new CommandCollection($this->compiler);
        $this->commands->add(new SimpleCommand("mockcommand", "Mocks a command"));
        $this->commands->add(new HappyHolidayCommand($this->commands));
        $this->parser = new StringParser(new StringTokenizer());
        $this->response = new Response(new ResponseCompiler(new Lexer(), new Parser()));
        $this->kernel = new Kernel($this->parser, $this->compiler, $this->commands, $logger, "0.0.0");
    }

    /**
     * Tests handling an exception
     */
    public function testHandlingException()
    {
        ob_start();
        $status = $this->kernel->handle("unclosed quote '", $this->response);
        ob_end_clean();
        $this->assertEquals(StatusCodes::FATAL, $status);
    }

    /**
     * Tests handling a help command
     */
    public function testHandlingHelpCommand()
    {
        // Try with command name
        ob_start();
        $status = $this->kernel->handle("help holiday", $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with command name with no argument
        ob_start();
        $status = $this->kernel->handle("help", $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with short name
        ob_start();
        $status = $this->kernel->handle("holiday -h", $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with long name
        ob_start();
        $status = $this->kernel->handle("holiday --help", $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling help command with non-existent command
     */
    public function testHandlingHelpCommandWithNonExistentCommand()
    {
        ob_start();
        $status = $this->kernel->handle("help fake", $this->response);
        ob_end_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    /**
     * Tests handling command with arguments and options
     */
    public function testHandlingHolidayCommand()
    {
        // Test with short option
        ob_start();
        $status = $this->kernel->handle("holiday birthday -y", $this->response);
        $this->assertEquals("Happy birthday!", ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);

        // Test with long option
        ob_start();
        $status = $this->kernel->handle("holiday Easter --yell=no", $this->response);
        $this->assertEquals("Happy Easter", ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling in a missing command
     */
    public function testHandlingMissingCommand()
    {
        ob_start();
        $status = $this->kernel->handle("fake", $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling in a simple command
     */
    public function testHandlingSimpleCommand()
    {
        ob_start();
        $status = $this->kernel->handle("mockcommand", $this->response);
        $this->assertEquals("foo", ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    /**
     * Tests handling a version command
     */
    public function testHandlingVersionCommand()
    {
        // Try with short name
        ob_start();
        $status = $this->kernel->handle("-v", $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with long name
        ob_start();
        $status = $this->kernel->handle("--version", $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }
}