<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes an object implementing the {@see \DateTimeInterface} to a date string.
 * Denormalizes a date string to an instance of {@see \DateTime} or {@see \DateTimeImmutable}.
 */
class PatchedDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const FORMAT_KEY = 'datetime_format';
    public const TIMEZONE_KEY = 'datetime_timezone';
    public const CAST_KEY = 'datetime_cast';
    public const FORCE_TIMEZONE_KEY = 'datetime_force_timezone';

    private array $defaultContext = [
        self::FORMAT_KEY => \DateTimeInterface::RFC3339,
        self::TIMEZONE_KEY => null,
        self::CAST_KEY => null,
        self::FORCE_TIMEZONE_KEY => false,
    ];

    private const SUPPORTED_TYPES = [
        \DateTimeInterface::class => true,
        \DateTimeImmutable::class => true,
        \DateTime::class => true,
    ];

    public function __construct(array $defaultContext = [])
    {
        $this->setDefaultContext($defaultContext);
    }

    public function setDefaultContext(array $defaultContext): void
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    public function getSupportedTypes(?string $format): array
    {
        return self::SUPPORTED_TYPES;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): int|float|string
    {
        if (!$data instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('The object must implement the "\DateTimeInterface".');
        }

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
        $timezone = $this->getTimezone($context);

        if (null !== $timezone) {
            $data = clone $data;
            $data = $data->setTimezone($timezone);
        }

        return match ($context[self::CAST_KEY] ?? $this->defaultContext[self::CAST_KEY] ?? false) {
            'int' => (int) $data->format($dateTimeFormat),
            'float' => (float) $data->format($dateTimeFormat),
            default => $data->format($dateTimeFormat),
        };
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \DateTimeInterface;
    }

    /**
     * @throws NotNormalizableValueException
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): string|\DateTimeInterface
    {
        if (\is_int($data) || \is_float($data)) {
            switch ($context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY] ?? null) {
                case 'U':
                    $data = \sprintf('%d', $data);
                    break;
                case 'U.u':
                    $data = \sprintf('%.6F', $data);
                    break;
            }
        }

        if (!\is_string($data) || '' === trim($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                'Expected a valid date string.',
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
                true
            );
        }

        try {
            if (\DateTimeInterface::class === $type) {
                $type = \DateTimeImmutable::class;
            }

            $timezone = $this->getTimezone($context);

            // ⬇️ formats acceptés (ordre important)
            $formats = [];

            if (isset($context[self::FORMAT_KEY])) {
                $formats[] = $context[self::FORMAT_KEY];
            }

            if (isset($this->defaultContext[self::FORMAT_KEY])) {
                $formats[] = $this->defaultContext[self::FORMAT_KEY];
            }

            // formats supplémentaires
            $formats[] = 'Y-m-d';
            $formats[] = \DateTimeInterface::RFC3339_EXTENDED;
            $formats[] = \DateTimeInterface::RFC3339;

            $formats = array_unique($formats);

            foreach ($formats as $format) {
                if (false !== $object = $type::createFromFormat($format, $data, $timezone)) {

                    // si date seule → minuit
                    if ($format === 'Y-m-d') {
                        $object = $object->setTime(0, 0, 0);
                    }

                    return $this->enforceTimezone($object, $context);
                }
            }

            // dernier recours (DateTime natif)
            return $this->enforceTimezone(new $type($data, $timezone), $context);

        } catch (NotNormalizableValueException $e) {
            throw $e;

        } catch (\Exception $e) {
            // comportement patché
            if ($context['disable_type_enforcement'] ?? false) {
                return $data;
            }

            throw NotNormalizableValueException::createForUnexpectedDataType(
                $e->getMessage(),
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
                false,
                $e->getCode(),
                $e
            );
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, \DateTimeInterface::class, true);
    }

    private function getTimezone(array $context): ?\DateTimeZone
    {
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultContext[self::TIMEZONE_KEY];

        if (null === $dateTimeZone) {
            return null;
        }

        return $dateTimeZone instanceof \DateTimeZone
            ? $dateTimeZone
            : new \DateTimeZone($dateTimeZone);
    }

    private function enforceTimezone(\DateTime|\DateTimeImmutable $object, array $context): \DateTimeInterface
    {
        $timezone = $this->getTimezone($context);
        $forceTimezone = $context[self::FORCE_TIMEZONE_KEY] ?? $this->defaultContext[self::FORCE_TIMEZONE_KEY];

        if (null === $timezone || !$forceTimezone) {
            return $object;
        }

        return $object->setTimezone($timezone);
    }
}
