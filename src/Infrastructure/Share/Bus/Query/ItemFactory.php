<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Bus\Query;

use App\Infrastructure\Share\Query\Projections\ViewItem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ItemFactory
{
    private NormalizerInterface $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function create(ViewItem $viewItem, array $relations = [], array $context = []): Item
    {
        return new Item(
            $viewItem->id(),
            $this->type($viewItem),
            $this->normalizer->normalize($viewItem, null, $context),
            $relations,
            $viewItem
        );
    }

    private function type(ViewItem $viewItem): string
    {
        $path = \explode('\\', \get_class($viewItem));

        return (string) \array_pop($path);
    }
}
