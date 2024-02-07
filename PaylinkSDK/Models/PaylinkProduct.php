<?php

namespace PaylinkSDK\Models;

class PaylinkProduct
{
    protected string $title;
    protected float $price;
    protected int $qty;
    protected ?string $description;
    protected bool $isDigital;
    protected ?string $imageSrc;
    protected ?float $specificVat;
    protected ?float $productCost;

    public function __construct(
        $title,
        $price,
        $qty,
        $description = null,
        $isDigital = false,
        $imageSrc = null,
        $specificVat = null,
        $productCost = null
    ) {
        $this->title = $title;
        $this->price = $price;
        $this->qty = $qty;
        $this->description = $description;
        $this->isDigital = $isDigital;
        $this->imageSrc = $imageSrc;
        $this->specificVat = $specificVat;
        $this->productCost = $productCost;
    }

    /**
     * Convert the PaylinkProduct object to an associative array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->title,
            'price' => $this->price,
            'qty' => $this->qty,
            'description' => $this->description,
            'isDigital' => $this->isDigital,
            'imageSrc' => $this->imageSrc,
            'specificVat' => $this->specificVat,
            'productCost' => $this->productCost,
        ];
    }
}
