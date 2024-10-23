<?php

declare(strict_types=1);

namespace WolfShop\Controller;

use Cloudinary\Api\Exception\ApiError;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use WolfShop\Entity\Item;
use WolfShop\Service\CloudinaryUploader;

class WolfItemController extends AbstractController
{
    private const RESPONSE_SUCCESS_MESSAGE = 'Image has been uploaded successfully';

    private const RESPONSE_TOO_MANY_REQUESTS_MESSAGE = 'Too many requests';

    private const RESPONSE_ITEM_NAME_IS_REQUIRED_MESSAGE = 'Item name is required';

    private const RESPONSE_ITEM_FILE_IS_REQUIRED_MESSAGE = 'Image file is required';

    private const RESPONSE_UPLOAD_IMAGE_ERROR_MESSAGE = 'An error occrured while uploading the image';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CloudinaryUploader $cloudinaryUploader,
        private LoggerInterface $logger,
        private RateLimiterFactory $anonymousApiLimiter
    ) {
    }

    #[Route('/api/v1/item/upload-image', name: 'api_wolf_upload_item_image', methods: 'POST')]
    #[OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'item_name',
                            type: 'string',
                            description: 'Name of an item to associate with the image to be uploaded',
                        ),
                        new OA\Property(
                            property: 'item_image',
                            type: 'string',
                            format: 'binary',
                            description: 'Item image to be uploaded',
                        ),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        description: 'Returns a result message',
        response: Response::HTTP_OK,
        content: new OA\JsonContent(
            example: [
                'message' => self::RESPONSE_SUCCESS_MESSAGE,
            ]
        ),
    )]
    #[OA\Response(
        description: 'Request throttled error',
        response: Response::HTTP_TOO_MANY_REQUESTS,
        content: new OA\JsonContent(
            example: [
                'message' => self::RESPONSE_TOO_MANY_REQUESTS_MESSAGE,
            ]
        ),
    )]
    #[OA\Response(
        description: 'Validation error',
        response: Response::HTTP_BAD_REQUEST,
        content: new OA\JsonContent(
            example: [
                'message' => self::RESPONSE_ITEM_NAME_IS_REQUIRED_MESSAGE,
            ]
        ),
    )]
    #[OA\Response(
        description: 'Other internal error',
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        content: new OA\JsonContent(
            example: [
                'message' => self::RESPONSE_UPLOAD_IMAGE_ERROR_MESSAGE,
            ]
        ),
    )]
    #[OA\Response(
        description: 'Unauthorized request',
        response: Response::HTTP_UNAUTHORIZED,
    )]
    #[Security(name: 'ApiKeyAuth')]
    public function uploadImage(Request $request): JsonResponse
    {
        $itemName = $request->get('item_name');
        $uploadedFile = $request->files->get('item_image');

        $validationResult = $this->basicValidation($request, (string) $itemName, $uploadedFile);
        if (! empty($validationResult)) {
            return $validationResult;
        }

        /** @var Item $item */
        $item = $this->entityManager->getRepository(Item::class)->findOneByName($itemName);
        if ($item === null) {
            return $this->constructResponse(
                'Invalid item name',
                Response::HTTP_BAD_REQUEST
            );
        }

        // Upload the file to Cloudinary
        try {
            $uploadResult = $this->cloudinaryUploader->upload(
                @fopen($uploadedFile->getPathname(), 'rb')
            );
        } catch (ApiError $exception) {
            $this->logger->error('Upload to Cloudinary failed', [
                'exception_message' => $exception->getMessage(),
            ]);
        }

        $imageUrl = $uploadResult['secure_url'] ?? null;
        if (empty($imageUrl)) {
            return $this->constructResponse(
                self::RESPONSE_UPLOAD_IMAGE_ERROR_MESSAGE,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Store the received image URL to the database accordingly
        $item->setImgUrl((string) $imageUrl);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $this->constructResponse(self::RESPONSE_SUCCESS_MESSAGE);
    }

    /**
     * Perform basic validation
     */
    private function basicValidation(Request $request, ?string $itemName, ?UploadedFile $uploadedFile): ?JsonResponse
    {
        $limiter = $this->anonymousApiLimiter->create($request->getClientIp());
        if ($limiter->consume(1)->isAccepted() === false) {
            return $this->constructResponse(
                self::RESPONSE_TOO_MANY_REQUESTS_MESSAGE,
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        if (empty($itemName)) {
            return $this->constructResponse(
                self::RESPONSE_ITEM_NAME_IS_REQUIRED_MESSAGE,
                Response::HTTP_BAD_REQUEST
            );
        }

        if (! $uploadedFile) {
            return $this->constructResponse(
                self::RESPONSE_ITEM_FILE_IS_REQUIRED_MESSAGE,
                Response::HTTP_BAD_REQUEST
            );
        }

        $validationResult = $this->validateFile($uploadedFile);
        if ($validationResult->has(0)) {
            // Just get first error message as response message
            return $this->constructResponse(
                (string) $validationResult->get(0)->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }

        return null;
    }

    private function validateFile(UploadedFile $file): ConstraintViolationListInterface
    {
        $fileConstraints = new File([
            'maxSize' => '5M',
            'extensions' => [
                'jpeg',
                'jpg',
                'png',
            ],
        ]);

        $validator = Validation::createValidator();

        return $validator->validate($file, $fileConstraints);
    }

    /**
     * @param integer $statusCode
     */
    private function constructResponse(string $message, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return $this->json(
            [
                'message' => $message,
            ],
            $statusCode
        );
    }
}
