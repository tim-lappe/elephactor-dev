<?php

namespace VirtualTestNamespace\Printer\Application;

use VirtualTestNamespace\Framework\Exception\BadRequestException;
use VirtualTestNamespace\Printer\Application\Dto\ResponseDto;
use VirtualTestNamespace\Printer\Domain\Entity\PrintedLabel;
use VirtualTestNamespace\Printer\Domain\Exception\JobFailedException;
use VirtualTestNamespace\Printer\Domain\Repository\LabelRepository;
use function array_map;

final readonly class MovedApplicationService
{
    public function __construct(private LabelRepository $labels)
    {
    }

    /**
     * @return list<ResponseDto>
     */
    public function listLabels(): array
    {
        return array_map(
            fn (PrintedLabel $label): ResponseDto => new ResponseDto($label->name()),
            $this->labels->recent(),
        );
    }

    public function resend(): void
    {
        try {
            $this->labels->resend();
        } catch (JobFailedException $e) {
            throw new BadRequestException($e->getMessage(), $e);
        }
    }
}
