<?php
declare(strict_types=1);

namespace UniManager\Traits;

trait Auditable {
    private ?\DateTime $createdAt = null;
    private ?string $createdBy = null;
    private ?\DateTime $updatedAt = null;
    private ?string $updatedBy = null;
    
    public function setCreatedBy(string $usuario): void {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
            $this->createdBy = $usuario;
        }
    }
    
    public function setUpdatedBy(string $usuario): void {
        $this->updatedAt = new \DateTime();
        $this->updatedBy = $usuario;
    }
    
    public function getCreatedAt(): ?\DateTime {
        return $this->createdAt;
    }
    
    public function getCreatedBy(): ?string {
        return $this->createdBy;
    }
    
    public function getUpdatedAt(): ?\DateTime {
        return $this->updatedAt;
    }
    
    public function getUpdatedBy(): ?string {
        return $this->updatedBy;
    }
    
    public function getAuditInfo(): string {
        $info = "";
        
        if ($this->createdAt && $this->createdBy) {
            $fecha = $this->createdAt->format('Y-m-d H:i:s');
            $info .= "Creado por {$this->createdBy} el $fecha\n";
        }
        
        if ($this->updatedAt && $this->updatedBy) {
            $fecha = $this->updatedAt->format('Y-m-d H:i:s');
            $info .= "Actualizado por {$this->updatedBy} el $fecha";
        }
        
        return $info ?: "Sin información de auditoría";
    }
}
?>