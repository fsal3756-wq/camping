<?php
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../lib/functions.php';

class CatalogController
{
    private $itemModel;

    public function __construct()
    {
        $this->itemModel = new Item();
    }

    public function index()
    {
        $filters = [
            'category' => $_GET['category'] ?? null,
            'search' => $_GET['search'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'status' => 'available'
        ];

        $items = $this->itemModel->getAll($filters);
        $categories = $this->itemModel->getCategories();

        return [
            'items' => $items,
            'categories' => $categories,
            'filters' => $filters
        ];
    }

    public function detail($id)
    {
        $item = $this->itemModel->getItemWithReviews($id);

        if (!$item) {
            setFlashMessage('danger', 'Item not found');
            redirect('/camping-rental-apps/index.php');
        }

        return $item;
    }

    public function checkAvailability($itemId, $startDate, $endDate, $quantity = 1)
    {
        $available = isItemAvailable($itemId, $startDate, $endDate, $quantity);

        return [
            'available' => $available,
            'message' => $available ? 'Item is available' : 'Item is not available for selected dates'
        ];
    }

    public function search()
    {
        $keyword = $_GET['search'] ?? '';

        if (empty($keyword)) {
            return [];
        }

        $items = $this->itemModel->getAll([
            'search' => $keyword,
            'status' => 'available'
        ]);

        return $items;
    }
}
