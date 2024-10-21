<?php

declare(strict_types=1);

namespace WolfShop\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportWolfItemsCommandTest extends KernelTestCase
{
    public const COMMAND_NAME = 'wolf:import-items';

    public function testExecuteWithNoResponseData(): void
    {
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find(self::COMMAND_NAME);

        $httpClientResponseMock = $this->createMock(MockResponse::class);
        $httpClientResponseMock->expects($this->once())->method('toArray')->willReturn([]);

        $httpClientMock = $this->createMock(MockHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.restful-api.dev/objects')
            ->willReturn($httpClientResponseMock);

        $container = self::$kernel->getContainer();
        $container->set(HttpClientInterface::class, $httpClientMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No item to import', $output);
    }

    public function testExecuteWithInvalidResponseData(): void
    {
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find(self::COMMAND_NAME);

        $httpClientResponseMock = $this->createMock(MockResponse::class);
        $httpClientResponseMock->expects($this->once())->method('toArray')->willReturn([
            [
                'id' => 1,
            ],
        ]);

        $httpClientMock = $this->createMock(MockHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($httpClientResponseMock);

        $container = self::$kernel->getContainer();
        $container->set(HttpClientInterface::class, $httpClientMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid item without name attribute - Item ID 1', $output);
    }

    public function testExecuteWithValidResponseData(): void
    {
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find(self::COMMAND_NAME);

        $httpClientResponseMock = $this->createMock(MockResponse::class);
        $httpClientResponseMock->expects($this->once())->method('toArray')->willReturn([
            [
                'id' => 1,
                'name' => 'Name 1',
            ],
            [
                'id' => 2,
                'name' => 'Name 2',
            ],
        ]);

        $httpClientMock = $this->createMock(MockHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($httpClientResponseMock);

        // Set mock classes to container
        $container = self::$kernel->getContainer();
        $container->set(HttpClientInterface::class, $httpClientMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Import process completed', $output);
    }
}
