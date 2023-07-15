<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Mapper\Response;

use GrinchenkoUniversity\Diia\Dto\Response\ItemsListResponse;
use UnexpectedValueException;

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

    public function mapFromResponse(array $response): ItemsListResponse
    {
        if (!isset($response[$this->itemsWrapper])) {
            throw new UnexpectedValueException('The items not found in the response!');
        }

        $list = (new ItemsListResponse())
            ->setTotal($response['total'])
        ;

        foreach ($response[$this->itemsWrapper] as $itemData) {
            $list->addItem($this->responseMapper->mapFromResponse($itemData));
        }

        return $list;
    }
}
