<?php

namespace App\DTOs;

class ExploreFiltersDTO
{
    public function __construct(
        public readonly ?int $radius,
        public readonly ?string $latitude,
        public readonly ?string $longitude,
        public readonly ?object $authUser,
        public readonly ?int $propertyId,
        public readonly ?array $suburbIds,
        public readonly ?int $sortBy,
        public readonly ?string $location,
        public readonly ?bool $isMap,
        public readonly ?float $minRent,
        public readonly ?float $maxRent,
        public readonly ?int $billsIncluded,
        public readonly ?string $availability,
        public readonly ?string $minLengthOfStay,
        public readonly ?string $maxLengthOfStay,
        public readonly ?bool $isFlexible,
        public readonly ?int $housematePreferences,
        public readonly ?array $accommodation,
        public readonly ?array $placesAccepting,
        public readonly ?array $homeAccessibility,
        public readonly ?int $furnishings,
        public readonly ?int $bathroomType,
        public readonly ?int $numberOfHousematesOccupied,
        public readonly ?int $parkingType,
        public readonly ?array $propertyFacilities,
        public readonly ?array $subscriberFilters,
    ) {}
}