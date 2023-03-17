<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cp_autocomplete")
 */
#[Entity]
#[Table(name: 'cp_autocomplete')]
class Ville
{
    #[Column(name: 'CODEPAYS', type: TYPES::STRING)]
    public string $codePays;

    #[Id]
    #[Column(name: 'CP', type: TYPES::STRING)]
    public string $cp;

    #[Column(name: 'VILLE', type: TYPES::STRING)]
    public string $ville;

    /**
     * @return string
     */
    public function getCodePays(): string
    {
        return $this->codePays;
    }

    /**
     * @param string $codePays
     */
    public function setCodePays(string $codePays): void
    {
        $this->codePays = $codePays;
    }

    /**
     * @return string
     */
    public function getCp(): string
    {
        return $this->cp;
    }

    /**
     * @param string $cp
     */
    public function setCp(string $cp): void
    {
        $this->cp = $cp;
    }

    /**
     * @return string
     */
    public function getVille(): string
    {
        return $this->ville;
    }

    /**
     * @param string $ville
     */
    public function setVille(string $ville): void
    {
        $this->ville = $ville;
    }
}
