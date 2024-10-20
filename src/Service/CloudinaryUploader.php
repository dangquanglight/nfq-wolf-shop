<?php

declare(strict_types=1);

namespace WolfShop\Service;

use Cloudinary\Api\ApiResponse;
use Cloudinary\Cloudinary;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class CloudinaryUploader
{
    private $params;


    public function __construct(ContainerBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @throws ApiError
     */
    public function upload($file, array $options = []): ApiResponse
    {
        // Configure an instance of your Cloudinary cloud
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $this->getConfig('cloud_name'),
                'api_key' => $this->getConfig('api_key'),
                'api_secret' => $this->getConfig('api_secret'),
                'url' => [
                    'secure' => true,
                ],
            ],
        ]);

        // Specify a default upload folder name in Cloudinary
        $options = array_merge(
            [
                'folder' => $this->getConfig('upload_folder'),
            ],
            $options
        );

        return $cloudinary->uploadApi()->upload($file, $options);
    }

    /**
     * Get config value from ENV variables
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    private function getConfig(string $name): string
    {
        return $this->params->get('wolf.cloudinary.' . $name);
    }
}
