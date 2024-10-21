<?php

declare(strict_types=1);

namespace WolfShop\Tests\Controller;

use Cloudinary\Api\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use WolfShop\Service\CloudinaryUploader;

class WolfItemControllerTest extends WebTestCase
{
    public const UPLOAD_IMAGE_URI = '/api/v1/item/upload-image';

    public const VALID_API_TOKEN = 'dummy-api-token';

    public function testUploadImageWithoutAuthentication(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $client->request('POST', self::UPLOAD_IMAGE_URI);

        // Validate a successful response and some content
        $this->assertResponseStatusCodeSame(401);
    }

    public function testUploadImageWithInvalidAuthentication(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $client->request(
            'POST',
            self::UPLOAD_IMAGE_URI,
            [],
            [],
            [
                'HTTP_X-API-KEY' => 'invalid-key',
            ]
        );

        // Validate a successful response and some content
        $this->assertResponseStatusCodeSame(401);
        $this->assertEquals('{"message":"Invalid credentials."}', $client->getResponse()->getContent());
    }

    public function testUploadImageWithValidAuthenticationAndEmptyBody(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $client->request(
            'POST',
            self::UPLOAD_IMAGE_URI,
            [],
            [],
            [
                'HTTP_X-API-KEY' => self::VALID_API_TOKEN,
            ]
        );

        // Validate a successful response and some content
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals('{"message":"Item name is required"}', $client->getResponse()->getContent());
    }

    public function testUploadImageWithValidAuthenticationAndEmptyFile(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $client->request(
            'POST',
            self::UPLOAD_IMAGE_URI,
            [
                'item_name' => 'Item name',
            ],
            [],
            [
                'Content-Type' => 'multipart/form-data',
                'HTTP_X-API-KEY' => self::VALID_API_TOKEN,
            ]
        );

        // Validate a successful response and some content
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals('{"message":"Image file is required"}', $client->getResponse()->getContent());
    }

    public function testUploadImageWithInvalidItemName(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Select the file from the filesystem
        $image = new UploadedFile(
            // Path to the file to send
            dirname(__FILE__) . '/../../public/test.jpg',
            // Name of the sent file
            'filename.jpg',
        );

        // Request a specific page
        $client->request(
            'POST',
            self::UPLOAD_IMAGE_URI,
            [
                'item_name' => 'Item name',
            ],
            [
                'item_image' => $image,
            ],
            [
                'Content-Type' => 'multipart/form-data',
                'HTTP_X-API-KEY' => self::VALID_API_TOKEN,
            ]
        );

        // Validate a successful response and some content
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals('{"message":"Invalid item name"}', $client->getResponse()->getContent());
    }

    public function testUploadImageWithValidItemNameAndEmptyCloudinaryResponse(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Select the file from the filesystem
        $image = new UploadedFile(
            // Path to the file to send
            dirname(__FILE__) . '/../../public/test.jpg',
            // Name of the sent file
            'filename.jpg',
        );

        $cloudinaryMock = $this->getMockBuilder(CloudinaryUploader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['upload'])
            ->getMock();
        $cloudinaryMock->expects($this->once())->method('upload')->willReturn(new ApiResponse([], []));

        $container = $client->getContainer();
        $container->set(CloudinaryUploader::class, $cloudinaryMock);

        // Request a specific page
        $client->request(
            'POST',
            self::UPLOAD_IMAGE_URI,
            [
                'item_name' => 'Item 1',
            ],
            [
                'item_image' => $image,
            ],
            [
                'Content-Type' => 'multipart/form-data',
                'HTTP_X-API-KEY' => self::VALID_API_TOKEN,
            ]
        );

        // Validate a successful response and some content
        $this->assertResponseStatusCodeSame(500);
        $this->assertEquals('{"message":"An error occrured while uploading the image"}', $client->getResponse()->getContent());
    }

    public function testUploadImageWithValidItemNameAndValidCloudinaryResponse(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Select the file from the filesystem
        $image = new UploadedFile(
            // Path to the file to send
            dirname(__FILE__) . '/../../public/test.jpg',
            // Name of the sent file
            'filename.jpg',
        );

        $cloudinaryMock = $this->getMockBuilder(CloudinaryUploader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['upload'])
            ->getMock();
        $cloudinaryMock->expects($this->once())->method('upload')->willReturn(new ApiResponse([
            'secure_url' => 'test-url',
        ], []));

        $container = $client->getContainer();
        $container->set(CloudinaryUploader::class, $cloudinaryMock);

        // Request a specific page
        $client->request(
            'POST',
            self::UPLOAD_IMAGE_URI,
            [
                'item_name' => 'Item 1',
            ],
            [
                'item_image' => $image,
            ],
            [
                'Content-Type' => 'multipart/form-data',
                'HTTP_X-API-KEY' => self::VALID_API_TOKEN,
            ]
        );

        // Validate a successful response and some content
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('{"message":"Image has been uploaded successfully"}', $client->getResponse()->getContent());
    }
}
