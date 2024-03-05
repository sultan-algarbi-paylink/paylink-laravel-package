<?php

namespace Paylink\Models;

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

    private const REQUIRED_KEYS = ['title', 'price', 'qty'];

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

    /**
     * Create an array of PaylinkProduct objects from a list of items using a key map.
     *
     * @param array $items an array of invoice items
     * @param array $keyMap Key map to map your invoice item keys to PaylinkProduct keys
     *                      - key-value pairs should follow this structure: ['paylink-product-key' => 'your-item-key']
     *                      - Available PaylinkProduct keys: 'title', 'price', 'qty', 'description', 'isDigital', 'imageSrc', 'specificVat', 'productCost'
     *                      - Example: ['title' => 'name', 'price' => 'amount', 'qty' => 'quantity']
     * @return array Array of PaylinkProduct objects
     * @throws \InvalidArgumentException if any item is missing required keys
     */
    public static function createFromItems(array $items, array $keyMap): array
    {
        self::_validateItems($items, $keyMap);

        $products = [];

        foreach ($items as $item) {
            $title = $item[$keyMap['title']];
            $price = $item[$keyMap['price']];
            $qty = $item[$keyMap['qty']];
            $description = $keyMap['description'] ?? null;
            $isDigital = $keyMap['isDigital'] ?? false;
            $imageSrc = $keyMap['imageSrc'] ?? null;
            $specificVat = $keyMap['specificVat'] ?? null;
            $productCost = $keyMap['productCost'] ?? null;

            $products[] = new PaylinkProduct(
                $title,
                $price,
                $qty,
                $description,
                $isDigital,
                $imageSrc,
                $specificVat,
                $productCost
            );
        }

        return $products;
    }

    /**
     * Validate if all required keys exist in the items based on the key map
     * and check if their values match the types of corresponding keys in PaylinkProduct.
     *
     * @param array $items List of items
     * @param array $keyMap Key map
     * @return bool True if all required keys exist, false otherwise
     * @throws \InvalidArgumentException if a required key is missing in any item or if value type doesn't match
     * 
     */
    private static function _validateItems(array $items, array $keyMap): bool
    {
        foreach ($items as $index => $item) {
            foreach (self::REQUIRED_KEYS as $requiredKey) {
                if (!isset($keyMap[$requiredKey]) || !array_key_exists($keyMap[$requiredKey], $item)) {
                    throw new \InvalidArgumentException("Item at index $index is missing the required key '$requiredKey'");
                }

                $itemValue = $item[$keyMap[$requiredKey]];
                $paylinkProductType = gettype(self::${$requiredKey}); // Get the type of corresponding PaylinkProduct property

                // Check if the type of item-value matches the type of corresponding PaylinkProduct property
                if (gettype($itemValue) !== $paylinkProductType) {
                    throw new \InvalidArgumentException("Value of '$keyMap[$requiredKey]' in item at index $index does not match the type of corresponding PaylinkProduct property ('$paylinkProductType')");
                }
            }
        }

        return true;
    }
}
