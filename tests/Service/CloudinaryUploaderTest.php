<?php

declare(strict_types=1);

namespace WolfShop\Tests\Service;

use Cloudinary\Exception\ConfigurationException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use WolfShop\Service\CloudinaryUploader;

class CloudinaryUploaderTest extends KernelTestCase
{
    public function testUploadWithInvalidConfiguration(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration, please set up your environment');

        $containerBagMock = $this->createMock(ContainerBag::class);
        $containerBagMock->expects($this->any())->method('get')->willReturn('');

        $cloudinaryService = new CloudinaryUploader($containerBagMock);
        $cloudinaryService->upload('');
    }
}
