<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\VideoPlatform;

/**
 * Objet de transfert de données (DTO) représentant une vidéo parsée.
 *
 * Immuable par conception (readonly) : une fois instancié, ses données
 * ne peuvent plus être modifiées, ce qui garantit l'intégrité du résultat
 * retourné par VideoParserService.
 */
final readonly class ParsedVideo
{
    /**
     * @param VideoPlatform $platform  La plateforme identifiée (YouTube, TikTok, Facebook).
     * @param string        $videoId   L'identifiant unique de la vidéo sur cette plateforme.
     */
    public function __construct(
        public VideoPlatform $platform,
        public string $videoId,
    ) {}

    /**
     * Convertit le DTO en tableau associatif, pratique pour hydrater un modèle.
     *
     * @return array{platform: string, video_id: string}
     */
    public function toArray(): array
    {
        return [
            'platform' => $this->platform->value,
            'video_id' => $this->videoId,
        ];
    }
}
