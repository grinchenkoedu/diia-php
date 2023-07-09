<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

use UnexpectedValueException;
use GrinchenkoUniversity\Diia\Dto\Response\ItemsListResponse;
use GrinchenkoUniversity\Diia\Dto\Response\ResponseInterface;

class ItemsListResponseMapper implements ResponseMapperInterface
{
    private string $itemsWrapper;
    private ResponseMapperInterface $responseMapper;

    public function __construct(
        string $itemsWrapper,
        ResponseMapperInterface $itemMapper
    ) {
        $this->itemsWrapper = $itemsWrapper;
        $this->responseMapper = $itemMapper;
    }

    /**
     * @param array $response
     * @return ResponseInterface|ItemsListResponse
     */
    public function mapFromResponse(array $response): ResponseInterface
    {
        if (!isset($response[$this->itemsWrapper])) {
            throw new UnexpectedValueException('The items not found in the response!');
        }

        $list = new ItemsListResponse();

        foreach ($response[$this->itemsWrapper] as $itemData) {
            $list->addItem($this->responseMapper->mapFromResponse($itemData));
        }

        return $list;
    }
}
